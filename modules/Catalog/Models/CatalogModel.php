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
        return $this->db->query("SELECT * FROM master_toys WHERE line_id = :lid ORDER BY name ASC", ['lid' => $lineId])->fetchAll();
    }

    public function getSources() {
        return $this->db->query("SELECT * FROM sources ORDER BY name ASC")->fetchAll();
    }

    public function getStorageUnits() {
        return $this->db->query("SELECT * FROM storage_units ORDER BY name ASC")->fetchAll();
    }
}