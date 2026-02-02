<?php
namespace CollectionApp\Modules\Catalog\Models;

use CollectionApp\Kernel\Database;

class CatalogModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

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
        return $this->db->query("
            SELECT mt.*, 
                   pt.type_name,
                   es.name as source_material_name,
                   es.type as source_material_type
            FROM master_toys mt 
            LEFT JOIN product_types pt ON mt.product_type_id = pt.id
            LEFT JOIN entertainment_sources es ON mt.entertainment_source_id = es.id
            WHERE mt.line_id = :lid 
            ORDER BY mt.name ASC
        ", ['lid' => $lineId])->fetchAll();
    }

    public function getSources() {
        return $this->db->query("SELECT * FROM sources ORDER BY name ASC")->fetchAll();
    }

    public function getStorageUnits() {
        return $this->db->query("SELECT * FROM storage_units ORDER BY name ASC")->fetchAll();
    }

    public function getMasterToyItems(int $masterToyId) {
        return $this->db->query("
            SELECT mti.id, s.name, s.type
            FROM master_toy_items mti
            JOIN subjects s ON mti.subject_id = s.id
            WHERE mti.master_toy_id = :tid
            ORDER BY s.type = 'Character' DESC, s.name ASC
        ", ['tid' => $masterToyId])->fetchAll();
    }
}