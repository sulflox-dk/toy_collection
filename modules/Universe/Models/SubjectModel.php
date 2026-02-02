<?php
namespace CollectionApp\Modules\Universe\Models;

use CollectionApp\Kernel\Database;

class SubjectModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFiltered($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        $sql = "SELECT s.*, 
                       (SELECT COUNT(*) FROM master_toy_items WHERE subject_id = s.id) as usage_count
                FROM subjects s";

        // --- FILTERS ---
        if (!empty($filters['type'])) {
            $where[] = "s.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            // RETTELSE: Vi bruger unikke navne til placeholders (:search1 og :search2)
            $where[] = "(s.name LIKE :search1 OR s.faction LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Count total
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // Sort & Limit
        // Vi sorterer Characters først, derefter navn
        $sql .= " ORDER BY CASE WHEN s.type = 'Character' THEN 0 ELSE 1 END, s.name ASC 
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
        $sql = "INSERT INTO subjects (name, type, faction) VALUES (:name, :type, :faction)";
        $this->db->query($sql, [
            'name' => $data['name'],
            'type' => $data['type'],
            'faction' => $data['faction'] ?: null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE subjects SET name = :name, type = :type, faction = :faction WHERE id = :id";
        return $this->db->query($sql, [
            'name' => $data['name'],
            'type' => $data['type'],
            'faction' => $data['faction'] ?: null,
            'id' => $id
        ]);
    }

    public function delete(int $id, ?int $migrateToId = null) {
        if ($migrateToId) {
            // MIGRATE: Flyt Master Toy Items
            $this->db->query("UPDATE master_toy_items SET subject_id = :newId WHERE subject_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
        } else {
            // Hvis slet uden migrering:
            // subjects tabellen bruges i master_toy_items.subject_id som er NOT NULL? 
            // Tjekker struktur... Ja, subject_id er NOT NULL i master_toy_items.
            // Så vi MÅ IKKE slette et subject, der er i brug, uden at migrere eller slette item'et.
            
            // Vi vælger at slette de items der bruger subjectet (Cascade logik i applikationen),
            // da vi ikke kan sætte det til NULL.
            $this->db->query("DELETE FROM master_toy_items WHERE subject_id = :id", ['id' => $id]);
        }

        return $this->db->query("DELETE FROM subjects WHERE id = :id", ['id' => $id]);
    }

    public function getAllSimple() {
        return $this->db->query("SELECT id, name, type, faction FROM subjects ORDER BY name ASC")->fetchAll();
    }
}