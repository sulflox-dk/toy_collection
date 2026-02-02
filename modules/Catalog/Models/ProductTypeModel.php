<?php
namespace CollectionApp\Modules\Catalog\Models;

use CollectionApp\Kernel\Database;

class ProductTypeModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFiltered($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        $sql = "SELECT pt.*, 
                       (SELECT COUNT(*) FROM master_toys WHERE product_type_id = pt.id) as toy_count
                FROM product_types pt";

        if (!empty($filters['search'])) {
            $where[] = "pt.type_name LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Count Total
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // Sort & Limit
        $sql .= " ORDER BY pt.type_name ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params)->fetchAll();

        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public function create($name) {
        $exists = $this->db->query("SELECT id FROM product_types WHERE type_name = :name", ['name' => $name])->fetch();
        if ($exists) throw new \Exception("Type '$name' already exists.");

        $this->db->query("INSERT INTO product_types (type_name) VALUES (:name)", ['name' => $name]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name) {
        $exists = $this->db->query("SELECT id FROM product_types WHERE type_name = :name AND id != :id", ['name' => $name, 'id' => $id])->fetch();
        if ($exists) throw new \Exception("Type '$name' already exists.");

        return $this->db->query("UPDATE product_types SET type_name = :name WHERE id = :id", ['name' => $name, 'id' => $id]);
    }

    public function delete($id, $migrateToId = null) {
        if ($migrateToId) {
            $this->db->query("UPDATE master_toys SET product_type_id = :newId WHERE product_type_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
        } else {
            // Sæt til NULL (Allowed ifølge din SQL struktur)
            $this->db->query("UPDATE master_toys SET product_type_id = NULL WHERE product_type_id = :id", ['id' => $id]);
        }

        return $this->db->query("DELETE FROM product_types WHERE id = :id", ['id' => $id]);
    }

    public function getAllSimple() {
        return $this->db->query("SELECT id, type_name as name FROM product_types ORDER BY type_name ASC")->fetchAll();
    }
}