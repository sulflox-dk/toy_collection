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
        // A. Tjek om billedet hører til en PARENT
        $parentMap = $this->db->query(
            "SELECT collection_toy_id FROM collection_toy_media_map WHERE media_file_id = :mid", 
            ['mid' => $mediaId]
        )->fetch();
        
        if ($parentMap) {
            $toyId = $parentMap['collection_toy_id'];
            // Nulstil alle for dette toy
            $this->db->query("UPDATE collection_toy_media_map SET is_main = 0 WHERE collection_toy_id = :tid", ['tid' => $toyId]);
            // Sæt den valgte
            $this->db->query("UPDATE collection_toy_media_map SET is_main = 1 WHERE media_file_id = :mid", ['mid' => $mediaId]);
            return true;
        } 
        
        // B. Tjek om billedet hører til et CHILD ITEM
        $childMap = $this->db->query(
            "SELECT collection_toy_item_id FROM collection_toy_item_media_map WHERE media_file_id = :mid", 
            ['mid' => $mediaId]
        )->fetch();

        if ($childMap) {
            $itemId = $childMap['collection_toy_item_id'];
            // Nulstil alle for dette item
            $this->db->query("UPDATE collection_toy_item_media_map SET is_main = 0 WHERE collection_toy_item_id = :iid", ['iid' => $itemId]);
            // Sæt den valgte
            $this->db->query("UPDATE collection_toy_item_media_map SET is_main = 1 WHERE media_file_id = :mid", ['mid' => $mediaId]);
            return true;
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
        $tableMap = ($context === 'collection_parent') 
            ? 'collection_toy_media_map' 
            : 'collection_toy_item_media_map';
        
        $colId = ($context === 'collection_parent') 
            ? 'collection_toy_id' 
            : 'collection_toy_item_id';

        // 1. Hent billeder og map-info
        $sql = "SELECT mf.id as media_id, mf.file_path, mf.user_comment, mmap.is_main
                FROM media_files mf
                JOIN $tableMap mmap ON mf.id = mmap.media_file_id
                WHERE mmap.$colId = :tid
                ORDER BY mmap.sort_order ASC, mf.id ASC";
        
        $images = $this->db->query($sql, ['tid' => $targetId])->fetchAll();

        // 2. Hent tags for hvert billede
        foreach ($images as &$img) {
            $tagSql = "SELECT t.id, t.tag_name 
                       FROM media_tags t
                       JOIN media_file_tags_map map ON t.id = map.tag_id
                       WHERE map.media_file_id = :mid";
            $img['tags'] = $this->db->query($tagSql, ['mid' => $img['media_id']])->fetchAll();
            
            // Konverter tags til simpelt array af ID'er for nemmere JS håndtering, hvis nødvendigt
            // Men createMediaRow kigger efter class 'bg-dark' baseret på ID, så vi sender bare objektet.
        }

        return $images;
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
        $baseUrl = \CollectionApp\Kernel\Config::get('base_url');
        $uploadPath = \CollectionApp\Kernel\Config::get('upload_path');
        $relativeFile = str_replace($baseUrl . 'assets/uploads/', '', $media['file_path']);
        $fullSystemPath = ROOT_PATH . '/' . $uploadPath . $relativeFile;

        if (file_exists($fullSystemPath)) {
            unlink($fullSystemPath);
        }
        return true;
    }
}