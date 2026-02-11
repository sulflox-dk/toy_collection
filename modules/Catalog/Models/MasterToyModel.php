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
        $having = [];

        // Base Select
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
                
                -- JOINS TIL SØGNING
                LEFT JOIN master_toy_items mti_search ON mt.id = mti_search.master_toy_id
                LEFT JOIN subjects s_search ON mti_search.subject_id = s_search.id";

        // --- WHERE FILTERS ---

        if (!empty($filters['id'])) {
            $where[] = "mt.id = :id";
            $params['id'] = $filters['id'];
        }

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

        // --- NYT: MANUFACTURER FILTER ---
        if (!empty($filters['manufacturer_id'])) {
            $where[] = "tl.manufacturer_id = :mid";
            $params['mid'] = $filters['manufacturer_id'];
        }

        // --- NYT: PRODUCT TYPE FILTER ---
        if (!empty($filters['product_type_id'])) {
            $where[] = "mt.product_type_id = :ptid";
            $params['ptid'] = $filters['product_type_id'];
        }

        // --- NYT: IMAGE STATUS FILTER ---
        if (!empty($filters['image_status'])) {
            if ($filters['image_status'] === 'has_image') {
                $where[] = "mf.file_path IS NOT NULL";
            } elseif ($filters['image_status'] === 'missing_image') {
                $where[] = "mf.file_path IS NULL";
            }
        }
        // --------------------------------

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
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

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        // --- HAVING CLAUSE (Owned Status) ---
        if (!empty($filters['owned_status'])) {
            if ($filters['owned_status'] === 'owned') {
                $having[] = "collection_count > 0";
            } elseif ($filters['owned_status'] === 'not_owned') {
                $having[] = "collection_count = 0";
            }
        }

        if (!empty($having)) {
            $sql .= " HAVING " . implode(' AND ', $having);
        }

        // --- Count Total ---
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $total = $this->db->query($countSql, $params)->fetchColumn();

        // --- Sort & Limit ---
        if (empty($filters['raw_result'])) {
            $sql .= " ORDER BY mt.name ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        }

        $results = $this->db->query($sql, $params)->fetchAll();

        if (!empty($filters['raw_result'])) {
            return $results;
        }

        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public function delete($id) {
        // Bem�rk: Sletning af master toy er farligt hvis det bruges i collection_toys.
        // Vi b�r tjekke f�rst.
        $usage = $this->db->query("SELECT COUNT(*) FROM collection_toys WHERE master_toy_id = :id", ['id' => $id])->fetchColumn();
        if ($usage > 0) {
            throw new \Exception("Cannot delete Toy. It is used in $usage Collection entries.");
        }

        $mediaModel = new \CollectionApp\Modules\Media\Models\MediaModel();

        // 1. SLET CHILD ITEMS + MEDIER
        $items = $this->getItems($id); // Bruger getItems() i MasterToyModel
        
        foreach ($items as $item) {
            $mediaIds = $mediaModel->getMediaIdsForEntity('catalog_child', $item['id']);
            
            foreach ($mediaIds as $mid) {
                // A. Slet relation i map-tabel
                $this->db->query("DELETE FROM master_toy_item_media_map WHERE media_file_id = :mid", ['mid' => $mid]);
                
                // B. Slet fil
                $mediaModel->delete($mid);
            }
            
            // C. Slet selve item-r�kken
            $this->db->query("DELETE FROM master_toy_items WHERE id = :id", ['id' => $item['id']]);
        }

        // 2. SLET MASTER TOY MEDIER (Parent)
        $parentMediaIds = $mediaModel->getMediaIdsForEntity('catalog_parent', $id);
        foreach ($parentMediaIds as $mid) {
            // A. Slet relation
            $this->db->query("DELETE FROM master_toy_media_map WHERE media_file_id = :mid", ['mid' => $mid]);
            
            // B. Slet fil
            $mediaModel->delete($mid);
        }

        // 3. SLET MASTER TOY DATA (Hoved-r�kken)
        return $this->db->query("DELETE FROM master_toys WHERE id = :id", ['id' => $id]);

    }

    public function getById($id) {
        $sql = "SELECT mt.*, 
                    tl.universe_id, 
                    tl.manufacturer_id,
                    mf.file_path as image_path
                FROM master_toys mt
                LEFT JOIN toy_lines tl ON mt.line_id = tl.id
                LEFT JOIN master_toy_media_map mtmm ON mt.id = mtmm.master_toy_id AND mtmm.is_main = 1
                LEFT JOIN media_files mf ON mtmm.media_file_id = mf.id
                WHERE mt.id = :id";
                
        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    public function create($data) {
        // 1. Tr�k items ud af data-arrayet (s� det ikke �del�gger INSERT)
        $items = $data['items'] ?? [];
        unset($data['items']);

        // 2. Opret Master Toy
        $sql = "INSERT INTO master_toys (
                    line_id, product_type_id, entertainment_source_id, 
                    name, release_year, wave_number, assortment_sku
                ) VALUES (
                    :line_id, :product_type_id, :entertainment_source_id, 
                    :name, :release_year, :wave_number, :assortment_sku
                )";
        
        $this->db->query($sql, $data);
        $id = $this->db->lastInsertId();

        // 3. Gem Items
        $this->saveItems($id, $items);

        return $id;
    }

    public function update($id, $data) {
        // 1. Tr�k items ud
        $items = $data['items'] ?? [];
        unset($data['items']);

        // 2. Tilf�j ID til params
        $data['id'] = $id;

        // 3. Opdater Master Toy
        $sql = "UPDATE master_toys SET 
                    line_id = :line_id, 
                    product_type_id = :product_type_id, 
                    entertainment_source_id = :entertainment_source_id, 
                    name = :name, 
                    release_year = :release_year, 
                    wave_number = :wave_number, 
                    assortment_sku = :assortment_sku 
                WHERE id = :id";
        
        $this->db->query($sql, $data);

        // 4. Opdater Items (Slet gamle -> Inds�t nye)
        $this->saveItems($id, $items);
    }

    private function saveItems($masterToyId, $items) {
        // 1. Hent alle eksisterende IDs for dette Master Toy fra databasen
        $existingIds = $this->db->query(
            "SELECT id FROM master_toy_items WHERE master_toy_id = :id", 
            ['id' => $masterToyId]
        )->fetchAll(\PDO::FETCH_COLUMN);

        // Hvis formen er tom, slet alt (med forsigtighed, men n�dvendigt)
        if (empty($items)) {
            if (!empty($existingIds)) {
                $idsStr = implode(',', array_map('intval', $existingIds));
                $this->db->query("DELETE FROM master_toy_items WHERE id IN ($idsStr)");
            }
            return;
        }

        $processedIds = []; // Holder styr p� hvilke IDs vi har h�ndteret (opdateret/oprettet)

        // 2. Loop gennem de items der kommer fra formen
        foreach ($items as $item) {
            $itemId = isset($item['id']) ? (int)$item['id'] : 0;

            if ($itemId > 0 && in_array($itemId, $existingIds)) {
                // SCENARIE A: ID findes i DB -> OPDATER (Bevarer relationer!)
                $sql = "UPDATE master_toy_items 
                        SET subject_id = :sid, 
                            variant_description = :var, 
                            quantity = :qty 
                        WHERE id = :id";
                
                $this->db->query($sql, [
                    'sid' => $item['subject_id'],
                    'var' => $item['variant_description'],
                    'qty' => $item['quantity'],
                    'id'  => $itemId
                ]);
                
                $processedIds[] = $itemId; // Husk at vi har gemt denne
            } else {
                // SCENARIE B: Nyt item (ingen ID eller ukendt ID) -> OPRET
                $sql = "INSERT INTO master_toy_items (master_toy_id, subject_id, variant_description, quantity) 
                        VALUES (:mtid, :sid, :var, :qty)";
                
                $this->db->query($sql, [
                    'mtid' => $masterToyId,
                    'sid'  => $item['subject_id'],
                    'var'  => $item['variant_description'],
                    'qty'  => $item['quantity']
                ]);
                // (Nye items har ingen relationer endnu, s� det er fint)
            }
        }

        // 3. SCENARIE C: Slet items der var i DB, men IKKE var med i formen (bruger har fjernet dem)
        $idsToDelete = array_diff($existingIds, $processedIds);
        
        if (!empty($idsToDelete)) {
            $idsStr = implode(',', array_map('intval', $idsToDelete));
            $this->db->query("DELETE FROM master_toy_items WHERE id IN ($idsStr)");
        }
    }

    public function getItems($masterToyId) {
        return $this->db->query("
            SELECT mti.*, s.name as subject_name, s.type as subject_type
            FROM master_toy_items mti
            LEFT JOIN subjects s ON mti.subject_id = s.id
            WHERE mti.master_toy_id = :id
            ORDER BY mti.id ASC
        ", ['id' => $masterToyId])->fetchAll();
    }

    public function findByName($name) {
        return $this->db->query("SELECT * FROM master_toys WHERE name = :name", ['name' => $name])->fetch();
    }
}