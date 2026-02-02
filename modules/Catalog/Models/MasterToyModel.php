<?php
namespace CollectionApp\Modules\Catalog\Models;

use CollectionApp\Kernel\Database;

class MasterToyModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFiltered($filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];
        $joins = [];

        // Base Select
        // Vi bruger DISTINCT fordi en join med items (ved subjectsøgning) kan give dubletter
        $sql = "SELECT DISTINCT mt.*, 
                       tl.name as line_name, 
                       m.name as manufacturer_name,
                       pt.type_name as product_type,
                       mf.file_path as image_path,
                       (SELECT COUNT(*) FROM master_toy_items WHERE master_toy_id = mt.id) as item_count,
                       (SELECT COUNT(*) FROM collection_toys WHERE master_toy_id = mt.id) as collection_count,
                       es.name as source_name,
                       es.type as source_type,
                       es.release_year as source_year
                FROM master_toys mt
                LEFT JOIN toy_lines tl ON mt.line_id = tl.id
                LEFT JOIN manufacturers m ON tl.manufacturer_id = m.id
                LEFT JOIN product_types pt ON mt.product_type_id = pt.id
                LEFT JOIN entertainment_sources es ON mt.entertainment_source_id = es.id
                -- Hent primært billede
                LEFT JOIN master_toy_media_map mtmm ON mt.id = mtmm.master_toy_id AND mtmm.is_main = 1
                LEFT JOIN media_files mf ON mtmm.media_file_id = mf.id";

        // --- FILTERS ---

        // 1. Universe (Via Toy Line)
        if (!empty($filters['universe_id'])) {
            $where[] = "tl.universe_id = :uid";
            $params['uid'] = $filters['universe_id'];
        }

        // 2. Toy Line
        if (!empty($filters['line_id'])) {
            $where[] = "mt.line_id = :lid";
            $params['lid'] = $filters['line_id'];
        }

        // 3. Subject (Kræver join med items)
        if (!empty($filters['subject_id'])) {
            $joins['items'] = true; // Flag at vi skal joine items
            $where[] = "mti_filter.subject_id = :sid";
            $params['sid'] = $filters['subject_id'];
        }

        // 4. Search (Name, SKU, Subject Name)
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $joins['items_search'] = true; // Vi skal bruge items tabellen til at søge i subjects
            
            $where[] = "(
                mt.name LIKE :s1 OR 
                mt.assortment_sku LIKE :s2 OR 
                mt.wave_number LIKE :s3 OR
                s_search.name LIKE :s4
            )";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
            $params['s4'] = $term;
        }

        // --- DYNAMIC JOINS ---
        // Vi tilføjer kun tunge joins hvis nødvendigt
        if (isset($joins['items'])) {
            $sql .= " JOIN master_toy_items mti_filter ON mt.id = mti_filter.master_toy_id ";
        }
        if (isset($joins['items_search'])) {
            // Hvis vi ikke allerede har joinet items ovenfor
            if (!isset($joins['items'])) {
                $sql .= " LEFT JOIN master_toy_items mti_search ON mt.id = mti_search.master_toy_id ";
            } else {
                // Genbrug den eksisterende join alias hvis muligt, men her laver vi en separat for sikkerheds skyld
                // eller simpelthen joiner subjects på den eksisterende.
                // For simpelheds skyld i denne model:
            }
            // Join Subjects for at søge i navnet
            $alias = isset($joins['items']) ? 'mti_filter' : 'mti_search';
            $sql .= " LEFT JOIN subjects s_search ON $alias.subject_id = s_search.id ";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Count Total
        // Vi pakker det ind i en subquery for at tælle DISTINCT korrekt
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // Sort & Limit
        $sql .= " ORDER BY mt.name ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $results = $this->db->query($sql, $params)->fetchAll();

        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public function delete($id) {
        // Bemærk: Sletning af master toy er farligt hvis det bruges i collection_toys.
        // Vi bør tjekke først.
        $usage = $this->db->query("SELECT COUNT(*) FROM collection_toys WHERE master_toy_id = :id", ['id' => $id])->fetchColumn();
        if ($usage > 0) {
            throw new \Exception("Cannot delete Toy. It is used in $usage Collection entries.");
        }

        // Slet items først (Cascade burde gøre det, men vi er eksplicitte)
        $this->db->query("DELETE FROM master_toy_items WHERE master_toy_id = :id", ['id' => $id]);
        
        // Slet medie-links
        $this->db->query("DELETE FROM master_toy_media_map WHERE master_toy_id = :id", ['id' => $id]);

        return $this->db->query("DELETE FROM master_toys WHERE id = :id", ['id' => $id]);
    }
}