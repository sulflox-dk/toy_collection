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
}