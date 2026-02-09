<?php
namespace CollectionApp\Modules\Media\Models;

use CollectionApp\Kernel\Database;

class MediaModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Opretter selve fil-referencen i databasen
     */
    public function create(string $filePath, string $type = 'Image') {
        $this->db->query(
            "INSERT INTO media_files (file_path, file_type) VALUES (:path, :type)", 
            ['path' => $filePath, 'type' => $type]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Linker et billede til et Parent Toy
     */
    public function linkToParent(int $mediaId, int $toyId) {
        // Tjek om det er det første billede, så gør vi det automatisk til Main
        $existing = $this->db->query("SELECT 1 FROM collection_toy_media_map WHERE collection_toy_id = :tid", ['tid' => $toyId])->fetch();
        $isMain = $existing ? 0 : 1;

        $sql = "INSERT INTO collection_toy_media_map (collection_toy_id, media_file_id, is_main) VALUES (:tid, :mid, :main)";
        return $this->db->query($sql, ['tid' => $toyId, 'mid' => $mediaId, 'main' => $isMain]);
    }

    /**
     * Linker et billede til et Child Item (figur/del)
     */
    public function linkToChild(int $mediaId, int $itemId) {
        // Tjek om det er det første billede
        $existing = $this->db->query("SELECT 1 FROM collection_toy_item_media_map WHERE collection_toy_item_id = :iid", ['iid' => $itemId])->fetch();
        $isMain = $existing ? 0 : 1;

        $sql = "INSERT INTO collection_toy_item_media_map (collection_toy_item_id, media_file_id, is_main) VALUES (:iid, :mid, :main)";
        return $this->db->query($sql, ['iid' => $itemId, 'mid' => $mediaId, 'main' => $isMain]);
    }

    public function updateComment(int $mediaId, string $comment) {
        return $this->db->query(
            "UPDATE media_files SET user_comment = :c WHERE id = :id", 
            ['c' => $comment, 'id' => $mediaId]
        );
    }

    public function updateTags(int $mediaId, array $tagIds) {
        // 1. Slet eksisterende tags for dette billede
        $this->db->query("DELETE FROM media_file_tags_map WHERE media_file_id = :id", ['id' => $mediaId]);
        
        // 2. Indsæt nye tags
        if (!empty($tagIds)) {
            $sql = "INSERT INTO media_file_tags_map (media_file_id, tag_id) VALUES (:mid, :tid)";
            foreach ($tagIds as $tid) {
                $this->db->query($sql, ['mid' => $mediaId, 'tid' => (int)$tid]);
            }
        }
    }

    /**
     * Den komplekse logik: Sæt dette billede som MAIN, og fjern flaget fra alle andre i samme gruppe
     */
    public function setAsMain(int $mediaId) {
        // Liste over alle map-tabeller og deres ID-kolonner
        $tables = [
            ['table' => 'collection_toy_media_map',      'col' => 'collection_toy_id'],
            ['table' => 'collection_toy_item_media_map', 'col' => 'collection_toy_item_id'],
            ['table' => 'master_toy_media_map',          'col' => 'master_toy_id'],
            ['table' => 'master_toy_item_media_map',     'col' => 'master_toy_item_id']
        ];

        foreach ($tables as $t) {
            $table = $t['table'];
            $col   = $t['col'];

            // Tjek om billedet findes i denne tabel
            $row = $this->db->query("SELECT $col FROM $table WHERE media_file_id = :mid", ['mid' => $mediaId])->fetch();
            
            if ($row) {
                $parentId = $row[$col];
                // 1. Nulstil alle for dette parent ID
                $this->db->query("UPDATE $table SET is_main = 0 WHERE $col = :pid", ['pid' => $parentId]);
                // 2. S�t den valgte som main
                $this->db->query("UPDATE $table SET is_main = 1 WHERE media_file_id = :mid", ['mid' => $mediaId]);
                return true; // Succes, stop loop
            }
        }
        return false;
    }

    public function getMediaTags() {
        return $this->db->query("SELECT * FROM media_tags ORDER BY tag_name ASC")->fetchAll();
    }

    /**
     * Henter alle billeder for en bestemt kontekst (Parent eller Child)
     * Returnerer format der passer direkte til JS createMediaRow
     */
    public function getImages(string $context, int $targetId) {
        // Map context til tabel- og kolonne-navne
        $map = [
            'collection_parent' => ['table' => 'collection_toy_media_map',      'col' => 'collection_toy_id'],
            'collection_child'  => ['table' => 'collection_toy_item_media_map', 'col' => 'collection_toy_item_id'],
            'catalog_parent'    => ['table' => 'master_toy_media_map',          'col' => 'master_toy_id'],
            'catalog_child'     => ['table' => 'master_toy_item_media_map',     'col' => 'master_toy_item_id'],
        ];

        if (!isset($map[$context])) return [];

        $tableMap = $map[$context]['table'];
        $colId    = $map[$context]['col'];

        $sql = "SELECT mf.id as media_id, mf.file_path, mf.user_comment, mmap.is_main
                FROM media_files mf
                JOIN $tableMap mmap ON mf.id = mmap.media_file_id
                WHERE mmap.$colId = :tid
                ORDER BY mmap.sort_order ASC, mf.id ASC";
        
        $images = $this->db->query($sql, ['tid' => $targetId])->fetchAll();

        // Hent tags (u�ndret)
        foreach ($images as &$img) {
            $tagSql = "SELECT t.id, t.tag_name 
                       FROM media_tags t
                       JOIN media_file_tags_map map ON t.id = map.tag_id
                       WHERE map.media_file_id = :mid";
            $img['tags'] = $this->db->query($tagSql, ['mid' => $img['media_id']])->fetchAll();
        }

        return $images;
    }

    /**
     * Hj�lper til at finde alle media_id'er for en given entity type
     */
    public function getMediaIdsForEntity(string $type, int $entityId): array {
        $table = '';
        $col = '';

        switch ($type) {
            // Collection
            case 'collection_parent':
                $table = 'collection_toy_media_map';
                $col = 'collection_toy_id';
                break;
            case 'collection_child':
                $table = 'collection_toy_item_media_map'; 
                $col = 'collection_toy_item_id';
                break;
                
            // Catalog / Master
            case 'catalog_parent':
                $table = 'master_toy_media_map';
                $col = 'master_toy_id';
                break;
            case 'catalog_child':
                $table = 'master_toy_item_media_map';
                $col = 'master_toy_item_id';
                break;
                
            default:
                return [];
        }

        // Vi henter kun ID'erne
        $rows = $this->db->query(
            "SELECT media_file_id FROM $table WHERE $col = :id", 
            ['id' => $entityId]
        )->fetchAll();

        return array_column($rows, 'media_file_id');
    }

    /**
     * Sletter en medie-fil både fra databasen og fra filsystemet
     */
    public function delete(int $mediaId) {
        // 1. Hent stien for at kunne slette filen fysisk
        $media = $this->db->query("SELECT file_path FROM media_files WHERE id = :id", ['id' => $mediaId])->fetch();
        if (!$media) return false;

        // 2. Slet tags tilknyttet billedet
        $this->db->query("DELETE FROM media_file_tags_map WHERE media_file_id = :id", ['id' => $mediaId]);

        // 3. Slet selve rækken i media_files
        $this->db->query("DELETE FROM media_files WHERE id = :id", ['id' => $mediaId]);

        // 4. Slet filen fra disken
        // NY LOGIK: Brug konstanter i stedet for Config::get
        
        // Vi skal finde filnavnet. Databasen indeholder typisk en URL (http://.../assets/uploads/fil.jpg)
        // Vi skal konvertere det til en fysisk sti (C:/.../assets/uploads/fil.jpg)
        
        $fileName = basename($media['file_path']); // Henter "fil.jpg" fra stien
        $fullSystemPath = UPLOAD_PATH . '/' . $fileName;

        if (file_exists($fullSystemPath)) {
            unlink($fullSystemPath);
        }
        return true;
    }

    /**
     * Linker et billede til et Master Toy (Catalog Parent)
     */
    public function linkToMasterParent(int $mediaId, int $toyId) {
        // Tjek om det er det f�rste billede
        $existing = $this->db->query("SELECT 1 FROM master_toy_media_map WHERE master_toy_id = :tid", ['tid' => $toyId])->fetch();
        $isMain = $existing ? 0 : 1;

        $sql = "INSERT INTO master_toy_media_map (master_toy_id, media_file_id, is_main) VALUES (:tid, :mid, :main)";
        return $this->db->query($sql, ['tid' => $toyId, 'mid' => $mediaId, 'main' => $isMain]);
    }

    /**
     * Linker et billede til et Master Toy Item (Catalog Child)
     */
    public function linkToMasterChild(int $mediaId, int $itemId) {
        $existing = $this->db->query("SELECT 1 FROM master_toy_item_media_map WHERE master_toy_item_id = :iid", ['iid' => $itemId])->fetch();
        $isMain = $existing ? 0 : 1;

        $sql = "INSERT INTO master_toy_item_media_map (master_toy_item_id, media_file_id, is_main) VALUES (:iid, :mid, :main)";
        return $this->db->query($sql, ['iid' => $itemId, 'mid' => $mediaId, 'main' => $isMain]);
    }


    /**
     * Henter billeder til Media Library med filtrering og pagination
     */
    public function getLibraryImages($filters = [], $page = 1, $perPage = 40) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereClauses = [];
        $joins = [];

        // BASE SQL
        $sql = "SELECT DISTINCT m.* FROM media_files m";

        // --- FILTER: CONNECTION TYPE ---
        if (!empty($filters['connection'])) {
            $conn = $filters['connection'];
            
            if ($conn === 'none') {
                // Find dem UDEN forbindelser (LEFT JOIN hvor id er NULL)
                $sql .= " LEFT JOIN collection_toy_media_map ctm ON m.id = ctm.media_file_id
                          LEFT JOIN collection_toy_item_media_map ctim ON m.id = ctim.media_file_id
                          LEFT JOIN master_toy_media_map mtm ON m.id = mtm.media_file_id
                          LEFT JOIN master_toy_item_media_map mtim ON m.id = mtim.media_file_id ";
                $whereClauses[] = "(ctm.collection_toy_id IS NULL AND ctim.collection_toy_item_id IS NULL AND mtm.master_toy_id IS NULL AND mtim.master_toy_item_id IS NULL)";
            } 
            elseif ($conn === 'collection') {
                $sql .= " LEFT JOIN collection_toy_media_map ctm ON m.id = ctm.media_file_id
                          LEFT JOIN collection_toy_item_media_map ctim ON m.id = ctim.media_file_id ";
                $whereClauses[] = "(ctm.collection_toy_id IS NOT NULL OR ctim.collection_toy_item_id IS NOT NULL)";
            }
            elseif ($conn === 'catalog') {
                $sql .= " LEFT JOIN master_toy_media_map mtm ON m.id = mtm.media_file_id
                          LEFT JOIN master_toy_item_media_map mtim ON m.id = mtim.media_file_id ";
                $whereClauses[] = "(mtm.master_toy_id IS NOT NULL OR mtim.master_toy_item_id IS NOT NULL)";
            }
        }

        // --- FILTER: TAG ---
        if (!empty($filters['tag_id'])) {
            $sql .= " JOIN media_file_tags_map tm ON m.id = tm.media_file_id ";
            $whereClauses[] = "tm.tag_id = :tag_id";
            $params['tag_id'] = $filters['tag_id'];
        }

        // --- FILTER: SEARCH (Avanceret: s�g i filnavn ELLER relaterede toy navne) ---
        if (!empty($filters['search'])) {
            // Vi er n�dt til at joine det hele for at s�ge i toy names
            // For at undg� dublerede joins tjekker vi ikke om de allerede er lavet, 
            // men bruger bare LEFT JOINs der d�kker det hele til s�gning.
            $sql .= " 
                LEFT JOIN collection_toy_media_map s_ctm ON m.id = s_ctm.media_file_id
                LEFT JOIN collection_toys s_ct ON s_ctm.collection_toy_id = s_ct.id
                LEFT JOIN master_toys s_mt_c ON s_ct.master_toy_id = s_mt_c.id -- Navn via collection

                LEFT JOIN master_toy_media_map s_mtm ON m.id = s_mtm.media_file_id
                LEFT JOIN master_toys s_mt ON s_mtm.master_toy_id = s_mt.id -- Navn via catalog
            ";
            
            $term = '%' . $filters['search'] . '%';
            $whereClauses[] = "(
                m.original_filename LIKE :s1 OR 
                m.user_comment LIKE :s2 OR 
                s_mt_c.name LIKE :s3 OR 
                s_mt.name LIKE :s4
            )";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
            $params['s4'] = $term;
        }

        // SAML QUERY
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // T�L TOTAL (Til pagination)
        // Vi erstatter SELECT ... FROM med SELECT COUNT FROM for at v�re effektive
        $countSql = "SELECT COUNT(DISTINCT m.id) FROM " . substr($sql, strpos($sql, "FROM") + 5);
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // SORTING & LIMIT
        $sql .= " ORDER BY m.uploaded_at DESC, m.id DESC LIMIT :limit OFFSET :offset";
        
        // PDO Limit trick (fordi PDO nogle gange driller med string limit)
        // Vi binder dem manuelt som integers i execute, eller bruger direkte values i stringen hvis sikkert.
        // Her bruger vi bind params men s�rger for typen i execute er implicit via arrayet, hvilket kan drille LIMIT.
        // Den sikre m�de i min simple DB wrapper er at s�tte dem direkte, da page/perPage er integers castet i controlleren.
        $sql = str_replace(':limit', (int)$perPage, $sql);
        $sql = str_replace(':offset', (int)$offset, $sql);

        $images = $this->db->query($sql, $params)->fetchAll();

        return [
            'images' => $images,
            'total' => $total,
            'pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Henter alle detaljer og connections for et medie-id
     */
    public function getDetails(int $mediaId) {
        // 1. Hent basis info
        $media = $this->db->query("SELECT * FROM media_files WHERE id = :id", ['id' => $mediaId])->fetch();
        if (!$media) return null;

        // 2. Hent tags
        $media['tags'] = $this->db->query("
            SELECT t.tag_name 
            FROM media_tags t
            JOIN media_file_tags_map map ON t.id = map.tag_id
            WHERE map.media_file_id = :mid
        ", ['mid' => $mediaId])->fetchAll();

        // 3. Hent Connections (Det tunge l�s)
        $connections = [];

        // A. Collection Parents
        $rows = $this->db->query("
            SELECT ct.id, mt.name, 'Collection Toy' as type, 'collection_parent' as context
            FROM collection_toy_media_map map
            JOIN collection_toys ct ON map.collection_toy_id = ct.id
            JOIN master_toys mt ON ct.master_toy_id = mt.id
            WHERE map.media_file_id = :mid
        ", ['mid' => $mediaId])->fetchAll();
        $connections = array_merge($connections, $rows);

        // B. Collection Items (Parts/Figures)
        $rows = $this->db->query("
            SELECT cti.id, s.name, 'Collection Item' as type, 'collection_child' as context
            FROM collection_toy_item_media_map map
            JOIN collection_toy_items cti ON map.collection_toy_item_id = cti.id
            JOIN master_toy_items mti ON cti.master_toy_item_id = mti.id
            JOIN subjects s ON mti.subject_id = s.id
            WHERE map.media_file_id = :mid
        ", ['mid' => $mediaId])->fetchAll();
        $connections = array_merge($connections, $rows);

        // C. Master Toys (Catalog Parents)
        $rows = $this->db->query("
            SELECT mt.id, mt.name, 'Catalog Master Toy' as type, 'catalog_parent' as context
            FROM master_toy_media_map map
            JOIN master_toys mt ON map.master_toy_id = mt.id
            WHERE map.media_file_id = :mid
        ", ['mid' => $mediaId])->fetchAll();
        $connections = array_merge($connections, $rows);

        // D. Master Toy Items (Catalog Items)
        $rows = $this->db->query("
            SELECT mti.id, s.name, 'Catalog Item' as type, 'catalog_child' as context
            FROM master_toy_item_media_map map
            JOIN master_toy_items mti ON map.master_toy_item_id = mti.id
            JOIN subjects s ON mti.subject_id = s.id
            WHERE map.media_file_id = :mid
        ", ['mid' => $mediaId])->fetchAll();
        $connections = array_merge($connections, $rows);

        $media['connections'] = $connections;
        return $media;
    }

    /**
     * Fjerner en specifik connection (Unlink)
     */
    public function removeConnection(int $mediaId, string $context, int $targetId) {
        $map = [
            'collection_parent' => ['table' => 'collection_toy_media_map',      'col' => 'collection_toy_id'],
            'collection_child'  => ['table' => 'collection_toy_item_media_map', 'col' => 'collection_toy_item_id'],
            'catalog_parent'    => ['table' => 'master_toy_media_map',          'col' => 'master_toy_id'],
            'catalog_child'     => ['table' => 'master_toy_item_media_map',     'col' => 'master_toy_item_id'],
        ];

        if (!isset($map[$context])) return false;

        $table = $map[$context]['table'];
        $col   = $map[$context]['col'];

        return $this->db->query(
            "DELETE FROM $table WHERE media_file_id = :mid AND $col = :tid", 
            ['mid' => $mediaId, 'tid' => $targetId]
        );
    }

    /**
     * Henter tags med antallet af filer, der bruger dem
     */
    public function getTagsWithCounts() {
        return $this->db->query("
            SELECT t.*, COUNT(map.media_file_id) as usage_count
            FROM media_tags t
            LEFT JOIN media_file_tags_map map ON t.id = map.tag_id
            GROUP BY t.id
            ORDER BY t.tag_name ASC
        ")->fetchAll();
    }

    public function createTag(string $name) {
        // Tjek for dubletter
        $exists = $this->db->query("SELECT id FROM media_tags WHERE tag_name = :name", ['name' => $name])->fetch();
        if ($exists) throw new \Exception("Tag '$name' already exists.");

        $this->db->query("INSERT INTO media_tags (tag_name) VALUES (:name)", ['name' => $name]);
        return $this->db->lastInsertId();
    }

    public function updateTag(int $id, string $name) {
        $exists = $this->db->query("SELECT id FROM media_tags WHERE tag_name = :name AND id != :id", ['name' => $name, 'id' => $id])->fetch();
        if ($exists) throw new \Exception("Another tag with name '$name' already exists.");

        return $this->db->query("UPDATE media_tags SET tag_name = :name WHERE id = :id", ['name' => $name, 'id' => $id]);
    }

    /**
     * Sletter et tag. Hvis $migrateToId er sat, flyttes forbindelserne f�rst.
     */
    public function deleteTag(int $id, ?int $migrateToId = null) {
        if ($migrateToId) {
            // 1. MIGRATE LOGIK
            // Vi bruger IGNORE, fordi hvis en fil allerede har B�DE det gamle og det nye tag,
            // vil en normal UPDATE fejle pga. primary key collision.
            // IGNORE g�r, at den hopper over dem, der allerede har det nye tag.
            $sql = "UPDATE IGNORE media_file_tags_map SET tag_id = :newId WHERE tag_id = :oldId";
            $this->db->query($sql, ['newId' => $migrateToId, 'oldId' => $id]);
            
            // Ryd op i dem der evt. ikke blev opdateret (fordi de allerede fandtes p� m�l-tagget)
            $this->db->query("DELETE FROM media_file_tags_map WHERE tag_id = :oldId", ['oldId' => $id]);
        } else {
            // 2. REN SLETNING
            // Slet mapningerne f�rst (Cascade burde g�re det, men vi er sikre her)
            $this->db->query("DELETE FROM media_file_tags_map WHERE tag_id = :id", ['id' => $id]);
        }

        // Til sidst slettes selve tagget
        return $this->db->query("DELETE FROM media_tags WHERE id = :id", ['id' => $id]);
    }
}