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

        // Base Select (Husk DISTINCT er vigtig her, ellers får du dubletter hvis søgningen matcher flere items i samme æske)
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
                LEFT JOIN master_toy_media_map mtmm ON mt.id = mtmm.master_toy_id AND mtmm.is_main = 1
                LEFT JOIN media_files mf ON mtmm.media_file_id = mf.id
                
                -- NYE JOINS TIL SØGNING (Aliased med '_search' for ikke at konflikte med andet)
                LEFT JOIN master_toy_items mti_search ON mt.id = mti_search.master_toy_id
                LEFT JOIN subjects s_search ON mti_search.subject_id = s_search.id";

        // --- FILTERS ---

        if (!empty($filters['universe_id'])) {
            $where[] = "tl.universe_id = :uid";
            $params['uid'] = $filters['universe_id'];
        }

        if (!empty($filters['line_id'])) {
            $where[] = "mt.line_id = :lid";
            $params['lid'] = $filters['line_id'];
        }

        if (!empty($filters['source_id'])) {
            $where[] = "mt.entertainment_source_id = :esid";
            $params['esid'] = $filters['source_id'];
        }

        // UPDATED SEARCH FILTER
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $where[] = "(
                mt.name LIKE :s1 OR 
                mt.assortment_sku LIKE :s2 OR 
                mt.wave_number LIKE :s3 OR
                s_search.name LIKE :s4  -- NY SØGEBETINGELSE
            )";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
            $params['s4'] = $term;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // Count Total (Distinct er vigtig her også)
        $countSql = "SELECT COUNT(DISTINCT mt.id) FROM master_toys mt 
                     LEFT JOIN toy_lines tl ON mt.line_id = tl.id 
                     LEFT JOIN master_toy_items mti_search ON mt.id = mti_search.master_toy_id
                     LEFT JOIN subjects s_search ON mti_search.subject_id = s_search.id
                     WHERE " . (!empty($where) ? implode(' AND ', $where) : '1=1');
                     
        // Bemærk: Jeg har forsimplet countSql lidt herover for at matche where-klausulerne korrekt uden at joine alt det unødvendige (billeder osv.) i tælleren.
        // Men den nemmeste "safe fix" hvis ovenstående driller, er at bruge subquery metoden fra før:
        // $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table"; 
        // Lad os holde os til subquery metoden, den er mest sikker med dine filtre:
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