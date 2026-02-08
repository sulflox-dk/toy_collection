<?php
namespace CollectionApp\Modules\Collection\Models;

use CollectionApp\Kernel\Database;

class ToyModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- PARENT TOY CRUD ---

    public function getToyById(int $id) {
        return $this->db->query("
            SELECT ct.*, 
                   mt.line_id, l.manufacturer_id, l.universe_id, 
                   mt.name as toy_name
            FROM collection_toys ct
            JOIN master_toys mt ON ct.master_toy_id = mt.id
            JOIN toy_lines l ON mt.line_id = l.id
            JOIN manufacturers m ON l.manufacturer_id = m.id
            WHERE ct.id = :id", 
            ['id' => $id]
        )->fetch();
    }

    public function create(array $data) {
        $sql = "INSERT INTO collection_toys 
                (master_toy_id, is_loose, purchase_date, purchase_price, source_id, acquisition_status, `condition`, completeness_grade, storage_id, personal_toy_id, user_comments) 
                VALUES 
                (:master_toy_id, :is_loose, :purchase_date, :purchase_price, :source_id, :acquisition_status, :condition, :completeness_grade, :storage_id, :personal_toy_id, :user_comments)";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data) {
        $sql = "UPDATE collection_toys SET 
            master_toy_id = :master_toy_id,
            is_loose = :is_loose,
            purchase_date = :purchase_date,
            purchase_price = :purchase_price,
            source_id = :source_id,
            acquisition_status = :acquisition_status,
            `condition` = :condition,
            completeness_grade = :completeness_grade,
            storage_id = :storage_id,
            personal_toy_id = :personal_toy_id,
            user_comments = :user_comments
            WHERE id = :id";
        
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }

    // --- CHILD ITEMS CRUD ---

    public function getChildItems(int $parentId) {
        // RETTET: Aliases ændret fra part_name/type til master_toy_item_name/type
        return $this->db->query("
            SELECT cti.*, 
                   s.name as master_toy_item_name, 
                   s.type as master_toy_item_type
            FROM collection_toy_items cti
            LEFT JOIN master_toy_items mti ON cti.master_toy_item_id = mti.id
            LEFT JOIN subjects s ON mti.subject_id = s.id
            WHERE cti.collection_toy_id = :id
            ORDER BY s.type = 'Character' DESC, s.name ASC
        ", ['id' => $parentId])->fetchAll();
    }

    public function createItem(array $data) {
        $sql = "INSERT INTO collection_toy_items 
                (collection_toy_id, master_toy_item_id, `condition`, is_loose, is_reproduction, user_comments, quantity_owned,
                 purchase_date, purchase_price, source_id, acquisition_status, expected_arrival_date, personal_item_id, storage_id) 
                VALUES 
                (:pid, :mid, :cond, :loose, :is_repo, :comments, 1,
                 :p_date, :p_price, :src_id, :acq_status, :exp_date, :pers_id, :stor_id)";
        
        return $this->db->query($sql, $data);
    }

    public function updateItem(int $itemId, array $data) {
        $sql = "UPDATE collection_toy_items SET 
                master_toy_item_id = :mid, `condition` = :cond, is_loose = :loose, is_reproduction = :is_repo, 
                user_comments = :comments, purchase_date = :p_date, purchase_price = :p_price, 
                source_id = :src_id, acquisition_status = :acq_status, expected_arrival_date = :exp_date, 
                personal_item_id = :pers_id, storage_id = :stor_id
                WHERE id = :item_id";
        
        $data['item_id'] = $itemId;
        return $this->db->query($sql, $data);
    }

    // --- DROPDOWNS & CATALOG DATA ---

    public function getAllUniverses() {
        return $this->db->query("SELECT * FROM universes ORDER BY sort_order ASC")->fetchAll();
    }

    public function getManufacturersByUniverse(int $universeId) {
        return $this->db->query("
            SELECT DISTINCT m.* FROM manufacturers m
            JOIN toy_lines l ON m.id = l.manufacturer_id
            WHERE l.universe_id = :uid 
            ORDER BY m.name ASC", 
            ['uid' => $universeId]
        )->fetchAll();
    }

    public function getLinesByManufacturer(int $manufacturerId) {
        return $this->db->query("SELECT * FROM toy_lines WHERE manufacturer_id = :mid ORDER BY name ASC", ['mid' => $manufacturerId])->fetchAll();
    }

    public function getMasterToysByLine(int $lineId) {
        return $this->db->query("SELECT * FROM master_toys WHERE line_id = :lid ORDER BY name ASC", ['lid' => $lineId])->fetchAll();
    }

    public function getSources() {
        return $this->db->query("SELECT * FROM sources ORDER BY name ASC")->fetchAll();
    }

    public function getStorageUnits() {
        return $this->db->query("SELECT * FROM storage_units ORDER BY name ASC")->fetchAll();
    }

    // --- MEDIA SPECIFIC ---

    public function getMediaStepInfo(int $id) {
        $toy = $this->db->query("
            SELECT ct.id, mt.name as toy_name 
            FROM collection_toys ct
            JOIN master_toys mt ON ct.master_toy_id = mt.id
            WHERE ct.id = :id", 
            ['id' => $id]
        )->fetch();

        return $toy;
    }

    public function getItemsForMedia(int $parentId) {
        return $this->db->query("
            SELECT cti.id, mti.variant_description, s.name as subject_name, s.type
            FROM collection_toy_items cti
            JOIN master_toy_items mti ON cti.master_toy_item_id = mti.id
            JOIN subjects s ON mti.subject_id = s.id
            WHERE cti.collection_toy_id = :pid", 
            ['pid' => $parentId]
        )->fetchAll();
    }

    public function getEnumValues($table, $column) {
        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
        $row = $this->db->query($sql)->fetch();
        if ($row) {
            preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
            if (isset($matches[1])) {
                return explode("','", $matches[1]);
            }
        }
        return [];
    }

    public function deleteItem(int $itemId) {
        $mediaLinks = $this->db->query(
            "SELECT media_file_id FROM collection_toy_item_media_map WHERE collection_toy_item_id = :id", 
            ['id' => $itemId]
        )->fetchAll();

        if (!empty($mediaLinks)) {
            foreach ($mediaLinks as $link) {
                $this->deleteMedia((int)$link['media_file_id']);
            }
        }

        return $this->db->query("DELETE FROM collection_toy_items WHERE id = :id", ['id' => $itemId]);
    }

    // modules/Collection/Models/ToyModel.php

    public function delete(int $id) {
        // Initialiser MediaModel
        $mediaModel = new \CollectionApp\Modules\Media\Models\MediaModel();

        // 1. SLET CHILD ITEMS + MEDIER
        $items = $this->getChildItems($id);
        
        foreach ($items as $item) {
            // Hent medie-ID'er
            $mediaIds = $mediaModel->getMediaIdsForEntity('collection_child', $item['id']);
            
            foreach ($mediaIds as $mid) {
                // A. Slet først relationen i map-tabellen (Vigtigt når du ikke bruger CASCADE!)
                $this->db->query("DELETE FROM collection_toy_item_media_map WHERE media_file_id = :mid", ['mid' => $mid]);
                
                // B. Slet selve filen og media_files rækken
                $mediaModel->delete($mid);
            }

            // C. Slet selve item-rækken i databasen (Dataen)
            $this->db->query("DELETE FROM collection_toy_items WHERE id = :id", ['id' => $item['id']]);
        }

        // 2. SLET PARENT TOY MEDIER
        $parentMediaIds = $mediaModel->getMediaIdsForEntity('collection_parent', $id);
        foreach ($parentMediaIds as $mid) {
            // A. Slet relationen
            $this->db->query("DELETE FROM collection_toy_media_map WHERE media_file_id = :mid", ['mid' => $mid]);
            
            // B. Slet filen
            $mediaModel->delete($mid);
        }

        // 3. SLET PARENT TOY DATA (Hoved-rækken)
        $this->db->query("DELETE FROM collection_toys WHERE id = :id", ['id' => $id]);
    }

    public function deleteMedia(int $mediaId) {
        $mediaModel = new \CollectionApp\Modules\Media\Models\MediaModel();
        $success = $mediaModel->delete($mediaId);

        if ($success) {
            $this->db->query("DELETE FROM collection_toy_media_map WHERE media_file_id = :id", ['id' => $mediaId]);
            $this->db->query("DELETE FROM collection_toy_item_media_map WHERE media_file_id = :id", ['id' => $mediaId]);
            return true;
        }
        return false;
    }

    public function getFiltered($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        // Hoved-Query: Joiner Collection -> Master -> Stamdata
        $sql = "SELECT ct.*, 
                       mt.name as toy_name,
                       mt.release_year as release_year,
                       mt.wave_number as wave_number,
                       pt.type_name as product_type,
                       tl.name as line_name,
                       m.name as manufacturer_name,
                       su.box_code,
                       su.name as storage_name,
                       ps.name as purchase_source_name,
                       
                       -- Billede logik (Fallback til master)
                       COALESCE(mf.file_path, mf_master.file_path) as image_path,
                       (CASE WHEN mf.file_path IS NULL AND mf_master.file_path IS NOT NULL THEN 1 ELSE 0 END) as is_stock_image,

                       (SELECT COUNT(*) FROM collection_toy_items WHERE collection_toy_id = ct.id) as item_count,
                       
                       -- MISSING ITEMS LOGIK (RETTET)
                       (
                           SELECT GROUP_CONCAT(s_missing.name SEPARATOR ', ')
                           FROM master_toy_items mti
                           JOIN subjects s_missing ON mti.subject_id = s_missing.id
                           -- Vi tjekker om du ejer denne specifikke 'master_toy_item'
                           LEFT JOIN collection_toy_items cti_check 
                                  ON cti_check.collection_toy_id = ct.id 
                                  AND cti_check.master_toy_item_id = mti.id  -- <--- RETTET HER: Bruger master_toy_item_id
                           WHERE mti.master_toy_id = ct.master_toy_id
                             AND cti_check.id IS NULL -- Vi vil kun have dem, brugeren IKKE har
                             -- Blacklist / Filter typer:
                             AND s_missing.type NOT IN ('Packaging', 'Paperwork')
                       ) as missing_items_list,

                       es.name as source_name,
                       es.type as source_type,
                       es.release_year as source_year
                FROM collection_toys ct
                JOIN master_toys mt ON ct.master_toy_id = mt.id
                LEFT JOIN toy_lines tl ON mt.line_id = tl.id
                LEFT JOIN manufacturers m ON tl.manufacturer_id = m.id
                LEFT JOIN product_types pt ON mt.product_type_id = pt.id
                LEFT JOIN entertainment_sources es ON mt.entertainment_source_id = es.id
                LEFT JOIN storage_units su ON ct.storage_id = su.id
                LEFT JOIN sources ps ON ct.source_id = ps.id
                
                -- Billed joins
                LEFT JOIN collection_toy_media_map ctmm ON ct.id = ctmm.collection_toy_id AND ctmm.is_main = 1
                LEFT JOIN media_files mf ON ctmm.media_file_id = mf.id
                
                LEFT JOIN master_toy_media_map mtmm ON ct.master_toy_id = mtmm.master_toy_id AND mtmm.is_main = 1
                LEFT JOIN media_files mf_master ON mtmm.media_file_id = mf_master.id";

        // --- FILTERS ---

        // Master Data Filters
        if (!empty($filters['universe_id'])) {
            $where[] = "tl.universe_id = :uid";
            $params['uid'] = $filters['universe_id'];
        }
        if (!empty($filters['line_id'])) {
            $where[] = "mt.line_id = :lid";
            $params['lid'] = $filters['line_id'];
        }
        if (!empty($filters['entertainment_source_id'])) {
            $where[] = "mt.entertainment_source_id = :esid";
            $params['esid'] = $filters['entertainment_source_id'];
        }

        // Collection Data Filters
        if (!empty($filters['storage_id'])) {
            $where[] = "ct.storage_id = :stid";
            $params['stid'] = $filters['storage_id'];
        }
        if (!empty($filters['source_id'])) { // Purchase Source
            $where[] = "ct.source_id = :srcid";
            $params['srcid'] = $filters['source_id'];
        }
        if (!empty($filters['acquisition_status'])) {
            $where[] = "ct.acquisition_status = :acq";
            $params['acq'] = $filters['acquisition_status'];
        }

        // Search (Søger i både navn, ID og box code)
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $where[] = "(mt.name LIKE :s1 OR ct.personal_toy_id LIKE :s2 OR su.box_code LIKE :s3)";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Count Total
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // --- SORTERING (Her kommer ændringen til Dashboard) ---
        
        if (isset($filters['sort']) && $filters['sort'] === 'newest') {
            // Dashboard: Vis nyeste tilføjelser først
            $sql .= " ORDER BY ct.id DESC";
        } else {
            // Standard: Sorter alfabetisk efter legetøjsnavn
            $sql .= " ORDER BY mt.name ASC";
        }

        // Limit og Offset
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params)->fetchAll();

        // VIGTIGT: Returner i det format din Controller forventer (array vs raw data)
        // I den gamle metode returnerede du et array med ['data', 'total', ...].
        // Men din getRecentAdditions kaldte bare ->fetchAll() direkte.
        // For at understøtte BEGGE dele, gør vi sådan her:
        
        // Hvis vi kun skal bruge raw data (f.eks. til dashboardet som ikke bruger pagineringsobjektet direkte endnu)
        if (isset($filters['raw_result']) && $filters['raw_result'] === true) {
            return $results;
        }

        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }
}