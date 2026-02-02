<?php
namespace CollectionApp\Modules\Catalog\Models;

use CollectionApp\Kernel\Database;

class ManufacturerModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- NYT: Advanced Filter/Grid Support ---
    public function getFiltered($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        $sql = "SELECT m.*, 
                       (SELECT COUNT(*) FROM toy_lines WHERE manufacturer_id = m.id) as line_count
                FROM manufacturers m";

        // --- FILTER: SEARCH ---
        if (!empty($filters['search'])) {
            $where[] = "m.name LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Count Total
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // Sort & Limit
        $sql .= " ORDER BY m.name ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params)->fetchAll();

        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public function create($name, $showOnDashboard) {
        $exists = $this->db->query("SELECT id FROM manufacturers WHERE name = :name", ['name' => $name])->fetch();
        if ($exists) throw new \Exception("Manufacturer '$name' already exists.");

        $this->db->query("INSERT INTO manufacturers (name, show_on_dashboard) VALUES (:name, :show)", 
            ['name' => $name, 'show' => $showOnDashboard]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name, $showOnDashboard) {
        $exists = $this->db->query("SELECT id FROM manufacturers WHERE name = :name AND id != :id", ['name' => $name, 'id' => $id])->fetch();
        if ($exists) throw new \Exception("Manufacturer '$name' already exists.");

        return $this->db->query("UPDATE manufacturers SET name = :name, show_on_dashboard = :show WHERE id = :id", 
            ['name' => $name, 'show' => $showOnDashboard, 'id' => $id]);
    }

    public function delete($id, $migrateToId = null) {
        if ($migrateToId) {
            $this->db->query("UPDATE toy_lines SET manufacturer_id = :newId WHERE manufacturer_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
        } else {
            $this->db->query("UPDATE toy_lines SET manufacturer_id = NULL WHERE manufacturer_id = :id", ['id' => $id]);
        }

        return $this->db->query("DELETE FROM manufacturers WHERE id = :id", ['id' => $id]);
    }

    public function getAllSimple() {
        return $this->db->query("SELECT id, name FROM manufacturers ORDER BY name ASC")->fetchAll();
    }
}