<?php
namespace CollectionApp\Modules\Collection\Models;

use CollectionApp\Kernel\Database;

class StorageModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllWithStats() {
        $sql = "SELECT su.*, 
                       (SELECT COUNT(*) FROM collection_toys WHERE storage_id = su.id) as toy_count
                FROM storage_units su
                ORDER BY su.box_code ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getAllSimple() {
        return $this->db->query("SELECT id, name, box_code FROM storage_units ORDER BY box_code ASC")->fetchAll();
    }

    public function getFiltered($search = '', $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $sql = "SELECT su.*, 
                       (SELECT COUNT(*) FROM collection_toys WHERE storage_id = su.id) as toy_count
                FROM storage_units su";

        if (!empty($search)) {
            $sql .= " WHERE su.name LIKE :s OR su.box_code LIKE :s OR su.location_room LIKE :s";
            $params['s'] = "%$search%";
        }

        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        $sql .= " ORDER BY su.box_code ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        return [
            'data' => $this->db->query($sql, $params)->fetchAll(),
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public function create($data) {
        $sql = "INSERT INTO storage_units (name, box_code, location_room, description) 
                VALUES (:name, :box_code, :location_room, :description)";
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE storage_units SET 
                    name = :name, 
                    box_code = :box_code, 
                    location_room = :location_room, 
                    description = :description 
                WHERE id = :id";
        $this->db->query($sql, $data);
    }

    public function delete($id, $migrateToId = null) {
        if ($migrateToId) {
            // MIGRATE: Flyt indholdet til en anden kasse
            $this->db->query("UPDATE collection_toys SET storage_id = :newId WHERE storage_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
        } else {
            // SIMPLE DELETE: Tøm kassen (Sæt storage_id til NULL for items i kassen)
            // Vi sletter IKKE legetøjet, vi fjerner det bare fra kassen.
            $this->db->query("UPDATE collection_toys SET storage_id = NULL WHERE storage_id = :id", ['id' => $id]);
        }

        // Slet selve kassen
        return $this->db->query("DELETE FROM storage_units WHERE id = :id", ['id' => $id]);
    }

    public function getById($id) {
        return $this->db->query("SELECT * FROM storage_units WHERE id = :id", ['id' => $id])->fetch();
    }
}