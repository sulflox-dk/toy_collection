<?php
namespace CollectionApp\Modules\Catalog\Models;

use CollectionApp\Kernel\Database;

class ToyLineModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFiltered($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        // Hent linjer med navne på Universe og Manufacturer samt antal Master Toys
        $sql = "SELECT tl.*, 
                       u.name as universe_name, 
                       m.name as manufacturer_name,
                       (SELECT COUNT(*) FROM master_toys WHERE line_id = tl.id) as toy_count
                FROM toy_lines tl
                LEFT JOIN universes u ON tl.universe_id = u.id
                LEFT JOIN manufacturers m ON tl.manufacturer_id = m.id";

        // --- FILTERS ---
        if (!empty($filters['universe_id'])) {
            $where[] = "tl.universe_id = :uid";
            $params['uid'] = $filters['universe_id'];
        }

        if (!empty($filters['manufacturer_id'])) {
            $where[] = "tl.manufacturer_id = :mid";
            $params['mid'] = $filters['manufacturer_id'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $where[] = "(tl.name LIKE :s1 OR u.name LIKE :s2 OR m.name LIKE :s3)";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Count Total
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // Sort & Limit
        $sql .= " ORDER BY tl.name ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params)->fetchAll();

        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public function create($data) {
        // Tjek dublet på navn (navn skal være unikt ifølge DB struktur)
        $exists = $this->db->query("SELECT id FROM toy_lines WHERE name = :name", ['name' => $data['name']])->fetch();
        if ($exists) throw new \Exception("Toy Line '$data[name]' already exists.");

        $sql = "INSERT INTO toy_lines (universe_id, manufacturer_id, name, scale, era_start_year, show_on_dashboard) 
                VALUES (:uid, :mid, :name, :scale, :year, :show)";
        
        $this->db->query($sql, [
            'uid' => $data['universe_id'],
            'mid' => $data['manufacturer_id'],
            'name' => $data['name'],
            'scale' => $data['scale'],
            'year' => $data['era_start_year'],
            'show' => $data['show_on_dashboard']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        // Tjek dublet (undtagen sig selv)
        $exists = $this->db->query("SELECT id FROM toy_lines WHERE name = :name AND id != :id", ['name' => $data['name'], 'id' => $id])->fetch();
        if ($exists) throw new \Exception("Toy Line '$data[name]' already exists.");

        $sql = "UPDATE toy_lines SET 
                universe_id = :uid, manufacturer_id = :mid, name = :name, 
                scale = :scale, era_start_year = :year, show_on_dashboard = :show 
                WHERE id = :id";

        return $this->db->query($sql, [
            'uid' => $data['universe_id'],
            'mid' => $data['manufacturer_id'],
            'name' => $data['name'],
            'scale' => $data['scale'],
            'year' => $data['era_start_year'],
            'show' => $data['show_on_dashboard'],
            'id' => $id
        ]);
    }

    public function delete($id, $migrateToId = null) {
        if ($migrateToId) {
            // MIGRATE: Flyt Master Toys til den nye linje
            $this->db->query("UPDATE master_toys SET line_id = :newId WHERE line_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
        } else {
            // SLET UDEN MIGRERING: Slet master toys (Cascade ville være farligt her, så vi prøver at slette items først hvis nødvendigt,
            // men master_toys tabellen har line_id som NOT NULL? Tjekker config... 
            // master_toys.line_id er NOT NULL. Så vi MÅ IKKE efterlade dem uden line_id.
            // Hvis brugeren vælger "Delete" uden migrering, er vi nødt til at slette alle Master Toys i den linje.
            // Det er en destruktiv handling!
            
            // Slet Master Toys (og deres items vil cascade hvis DB er sat op til det, ellers skal vi rydde op)
            // Vi antager app-level cleanup for sikkerheds skyld:
            
            // (Foreløbig stoler vi på DB constraints eller at brugeren migrerer. 
            // Hvis vi sletter linjen og der er master toys, vil DB kaste en Foreign Key error hvis ikke cascade er slået til.)
            
            // Vi prøver at slette master toys, der tilhører linjen (Advarsel: Dette sletter også collection items der peger på master toys!)
            $this->db->query("DELETE FROM master_toys WHERE line_id = :id", ['id' => $id]);
        }

        return $this->db->query("DELETE FROM toy_lines WHERE id = :id", ['id' => $id]);
    }

    public function getAllSimple() {
        return $this->db->query("
            SELECT tl.id, tl.name, m.name as manufacturer 
            FROM toy_lines tl
            LEFT JOIN manufacturers m ON tl.manufacturer_id = m.id
            ORDER BY tl.name ASC
        ")->fetchAll();
    }
}