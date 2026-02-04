<?php
namespace CollectionApp\Modules\Universe\Models;

use CollectionApp\Kernel\Database;

class EntertainmentSourceModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFiltered($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        $sql = "SELECT es.*, u.name as universe_name,
                       (SELECT COUNT(*) FROM master_toys WHERE entertainment_source_id = es.id) as toy_count
                FROM entertainment_sources es
                JOIN universes u ON es.universe_id = u.id";

        // --- FILTERS ---
        
        // 1. Universe
        if (!empty($filters['universe_id'])) {
            $where[] = "es.universe_id = :uid";
            $params['uid'] = $filters['universe_id'];
        }

        // 2. Type
        if (!empty($filters['type'])) {
            $where[] = "es.type = :type";
            $params['type'] = $filters['type'];
        }

        // 3. Search (Name)
        if (!empty($filters['search'])) {
            $where[] = "es.name LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Apply Where
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Tæl total (til pagination)
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // Sorting & Pagination
        $sql .= " ORDER BY u.name ASC, es.release_year ASC, es.name ASC 
                  LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params)->fetchAll();

        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public function create($data) {
        $sql = "INSERT INTO entertainment_sources (universe_id, name, type, release_year) 
                VALUES (:uid, :name, :type, :year)";
        $this->db->query($sql, [
            'uid' => $data['universe_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'year' => $data['release_year'] ?: null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE entertainment_sources SET 
                universe_id = :uid, name = :name, type = :type, release_year = :year 
                WHERE id = :id";
        return $this->db->query($sql, [
            'uid' => $data['universe_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'year' => $data['release_year'] ?: null,
            'id' => $id
        ]);
    }

    public function delete(int $id, ?int $migrateToId = null) {
        if ($migrateToId) {
            // 1. MIGRATE: Flyt Master Toys til den nye kilde
            $this->db->query("UPDATE master_toys SET entertainment_source_id = :newId WHERE entertainment_source_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
        } else {
            // Hvis slet uden migrering: Sæt master_toys reference til NULL (orphan prevention)
            $this->db->query("UPDATE master_toys SET entertainment_source_id = NULL WHERE entertainment_source_id = :id", ['id' => $id]);
        }

        // Slet selve kilden
        return $this->db->query("DELETE FROM entertainment_sources WHERE id = :id", ['id' => $id]);
    }

    /**
     * Henter simpel liste af alle kilder (til dropdowns)
     */
    public function getAllSimple() {
        return $this->db->query("
            SELECT es.id, es.name, es.type, u.name as universe_name 
            FROM entertainment_sources es
            JOIN universes u ON es.universe_id = u.id
            ORDER BY u.name ASC, es.name ASC
        ")->fetchAll();
    }
}