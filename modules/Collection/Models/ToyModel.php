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
}