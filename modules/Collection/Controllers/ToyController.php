<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;

class ToyController extends Controller {

    public function add() {
        // Bruges til "Change Universe" knappen eller start-flowet
        $universes = $this->db->query("SELECT * FROM universes ORDER BY sort_order ASC")->fetchAll();
        $data = ['universes' => $universes];
        $this->view->renderPartial('select_universe_modal', $data, 'Collection');
    }

    public function form() {
        $preSelectedUniverseId = isset($_GET['universe_id']) ? (int)$_GET['universe_id'] : null;

        // 1. Hent dropdown data til felterne
        $universes = $this->db->query("SELECT * FROM universes ORDER BY sort_order ASC")->fetchAll();
        $sources = $this->db->query("SELECT * FROM sources ORDER BY name ASC")->fetchAll();
        $storages = $this->db->query("SELECT * FROM storage_units ORDER BY name ASC")->fetchAll();
        
        // 2. Hent ENUM værdier
        $statuses = $this->getEnumValues('collection_toys', 'acquisition_status');
        $conditions = $this->getEnumValues('collection_toys', 'condition');
        $completeness = $this->getEnumValues('collection_toys', 'completeness_grade');

        $data = [
            'universes'  => $universes,
            'sources'    => $sources,
            'storages'   => $storages,
            'statuses'   => $statuses,
            'conditions' => $conditions,
            'completeness' => $completeness,
            'selected_universe' => $preSelectedUniverseId
        ];

        $this->view->renderPartial('add_toy_modal', $data, 'Collection');
    }

    public function store() {
        if (!isset($_POST['master_toy_id']) || empty($_POST['master_toy_id'])) {
            die("Error: Master Toy ID is missing.");
        }

        // 1. TJEK OM DE 4 HOVEDFELTER ER UDFYLDT
        if (empty($_POST['manufacturer_id']) || empty($_POST['line_id']) || empty($_POST['master_toy_id'])) {
            die("Error: Please select Universe, Manufacturer, Line, and Toy.");
        }

        // 2. TJEK OM DER ER MINDST ÉT ITEM
        // Vi tjekker om 'items' arrayet findes, og om det indeholder data
        $hasItems = false;
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                // Tjek om der rent faktisk er valgt en del (master_toy_item_id) i rækken
                if (!empty($item['master_toy_item_id'])) {
                    $hasItems = true;
                    break;
                }
            }
        }

        if (!$hasItems) {
            die("Error: You must add at least one item to this collection entry.");
        }

        // 1. INDSÆT PARENT
        $data = [
            'master_toy_id'       => $_POST['master_toy_id'],
            'is_loose'            => isset($_POST['is_loose']) ? 1 : 0, 
            'purchase_date'       => $this->nullIfEmpty($_POST['purchase_date']),
            'purchase_price'      => $this->nullIfEmpty($_POST['purchase_price']),
            'source_id'           => $this->nullIfEmpty($_POST['source_id']),
            'acquisition_status'  => $this->nullIfEmpty($_POST['acquisition_status']),
            'condition'           => $this->nullIfEmpty($_POST['condition']),
            'completeness_grade'  => $this->nullIfEmpty($_POST['completeness_grade']),
            'storage_id'          => $this->nullIfEmpty($_POST['storage_id']),
            'personal_toy_id'     => $this->nullIfEmpty($_POST['personal_toy_id']),
            'user_comments'       => $this->nullIfEmpty($_POST['user_comments'])
        ];

        $sqlParent = "INSERT INTO collection_toys 
                      (master_toy_id, is_loose, purchase_date, purchase_price, source_id, acquisition_status, `condition`, completeness_grade, storage_id, personal_toy_id, user_comments) 
                      VALUES 
                      (:master_toy_id, :is_loose, :purchase_date, :purchase_price, :source_id, :acquisition_status, :condition, :completeness_grade, :storage_id, :personal_toy_id, :user_comments)";
        
        $this->db->query($sqlParent, $data);
        $parentId = $this->db->lastInsertId();

        // 2. INDSÆT CHILD ITEMS
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            
            $sqlChild = "INSERT INTO collection_toy_items 
                         (collection_toy_id, master_toy_item_id, `condition`, is_loose, is_reproduction, user_comments, quantity_owned,
                          purchase_date, purchase_price, source_id, acquisition_status, expected_arrival_date, personal_item_id, storage_id) 
                         VALUES 
                         (:pid, :mid, :cond, :loose, :is_repo, :comments, 1,
                          :p_date, :p_price, :src_id, :acq_status, :exp_date, :pers_id, :stor_id)";

            foreach ($_POST['items'] as $item) {
                if (empty($item['master_toy_item_id'])) continue;

                $childData = [
                    'pid'         => $parentId,
                    'mid'         => $item['master_toy_item_id'],
                    'cond'        => $this->nullIfEmpty($item['condition']),
                    'loose'       => isset($item['is_loose']) ? 1 : 0,
                    'is_repo'     => $this->nullIfEmpty($item['is_reproduction']),
                    'comments'    => $this->nullIfEmpty($item['user_comments']),
                    'p_date'      => $this->nullIfEmpty($item['purchase_date']),
                    'p_price'     => $this->nullIfEmpty($item['purchase_price']),
                    'src_id'      => $this->nullIfEmpty($item['source_id']),
                    'acq_status'  => $this->nullIfEmpty($item['acquisition_status']),
                    'exp_date'    => $this->nullIfEmpty($item['expected_arrival_date']),
                    'pers_id'     => $this->nullIfEmpty($item['personal_item_id']),
                    'stor_id'     => $this->nullIfEmpty($item['storage_id'])
                ];

                $this->db->query($sqlChild, $childData);
            }
        }

        // Vi snyder lidt og sætter GET id, da media_step kigger efter den
        $_GET['id'] = $parentId;
        
        // Kald næste trin direkte
        $this->media_step();
        exit;
    }

/**
     * Åbner formularen i "Edit Mode"
     */
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        
        // 1. Hent Toy Data
        $toy = $this->db->query("
            SELECT ct.*, 
                   mt.line_id, l.manufacturer_id, m.universe_id,
                   mt.name as toy_name
            FROM collection_toys ct
            JOIN master_toys mt ON ct.master_toy_id = mt.id
            JOIN toy_lines l ON mt.line_id = l.id
            JOIN manufacturers m ON l.manufacturer_id = m.id
            WHERE ct.id = :id", 
            ['id' => $id]
        )->fetch();

        if (!$toy) die("Toy not found");

        // 2. Hent Child Items
        $childItems = $this->db->query("SELECT * FROM collection_toy_items WHERE collection_toy_id = :id", ['id' => $id])->fetchAll();

        // 3. Hent Dropdown data baseret på det valgte univers/line (så dropdowns ikke er tomme)
        $universes = $this->db->query("SELECT * FROM universes ORDER BY sort_order ASC")->fetchAll();
        $manufacturers = $this->db->query("SELECT * FROM manufacturers WHERE universe_id = :uid ORDER BY name ASC", ['uid' => $toy['universe_id']])->fetchAll();
        $lines = $this->db->query("SELECT * FROM toy_lines WHERE manufacturer_id = :mid ORDER BY name ASC", ['mid' => $toy['manufacturer_id']])->fetchAll();
        $masterToys = $this->db->query("SELECT * FROM master_toys WHERE line_id = :lid ORDER BY name ASC", ['lid' => $toy['line_id']])->fetchAll();

        // 4. Hent standard lister
        $sources = $this->db->query("SELECT * FROM sources ORDER BY name ASC")->fetchAll();
        $storages = $this->db->query("SELECT * FROM storage_units ORDER BY name ASC")->fetchAll();
        $statuses = $this->getEnumValues('collection_toys', 'acquisition_status');
        $conditions = $this->getEnumValues('collection_toys', 'condition');
        $completeness = $this->getEnumValues('collection_toys', 'completeness_grade');

        $data = [
            'mode' => 'edit', // Vigtigt flag!
            'toy' => $toy,
            'childItems' => $childItems,
            'universes' => $universes,
            'manufacturers' => $manufacturers,
            'lines' => $lines,
            'masterToys' => $masterToys,
            'sources' => $sources,
            'storages' => $storages,
            'statuses' => $statuses,
            'conditions' => $conditions,
            'completeness' => $completeness,
            'selected_universe' => $toy['universe_id']
        ];

        $this->view->renderPartial('add_toy_modal', $data, 'Collection');
    }

    /**
     * Gemmer opdateringer
     */
    public function update() {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) die("Missing ID");

        // 1. OPDATER PARENT
        $sqlParent = "UPDATE collection_toys SET 
            master_toy_id = :master_toy_id,
            is_loose = :is_loose,
            purchase_date = :purchase_date,
            purchase_price = :purchase_price,
            source_id = :source_id,
            acquisition_status = :acquisition_status,
            `condition` = :condition,
            completeness_grade = :completeness_grade,
            storage_id = :storage_id,
            personal_toy_id = :personal_toy_id,
            user_comments = :user_comments
            WHERE id = :id";
        
        $data = [
            'id' => $id,
            'master_toy_id' => $_POST['master_toy_id'],
            'is_loose' => isset($_POST['is_loose']) ? 1 : 0,
            'purchase_date' => $this->nullIfEmpty($_POST['purchase_date']),
            'purchase_price' => $this->nullIfEmpty($_POST['purchase_price']),
            'source_id' => $this->nullIfEmpty($_POST['source_id']),
            'acquisition_status' => $this->nullIfEmpty($_POST['acquisition_status']),
            'condition' => $this->nullIfEmpty($_POST['condition']),
            'completeness_grade' => $this->nullIfEmpty($_POST['completeness_grade']),
            'storage_id' => $this->nullIfEmpty($_POST['storage_id']),
            'personal_toy_id' => $this->nullIfEmpty($_POST['personal_toy_id']),
            'user_comments' => $this->nullIfEmpty($_POST['user_comments'])
        ];
        
        $this->db->query($sqlParent, $data);

        // 2. OPDATER CHILD ITEMS
        // For simpelheds skyld: I en update sletter vi ofte de gamle items og opretter dem igen, 
        // ELLER vi opdaterer dem hvis de har et ID.
        // Her laver vi en simpel logik: Vi opdaterer eksisterende hvis ID findes.

        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (isset($item['id']) && $item['id']) {
                    // Update eksisterende item
                    $sqlChild = "UPDATE collection_toy_items SET 
                        master_toy_item_id = :mid, `condition` = :cond, is_loose = :loose, is_reproduction = :is_repo, 
                        user_comments = :comments, purchase_date = :p_date, purchase_price = :p_price, 
                        source_id = :src_id, acquisition_status = :acq_status, expected_arrival_date = :exp_date, 
                        personal_item_id = :pers_id, storage_id = :stor_id
                        WHERE id = :item_id";
                    
                    $this->db->query($sqlChild, [
                        'item_id' => $item['id'],
                        'mid' => $item['master_toy_item_id'],
                        'cond' => $this->nullIfEmpty($item['condition']),
                        'loose' => isset($item['is_loose']) ? 1 : 0,
                        'is_repo' => $this->nullIfEmpty($item['is_reproduction']),
                        'comments' => $this->nullIfEmpty($item['user_comments']),
                        'p_date' => $this->nullIfEmpty($item['purchase_date']),
                        'p_price' => $this->nullIfEmpty($item['purchase_price']),
                        'src_id' => $this->nullIfEmpty($item['source_id']),
                        'acq_status' => $this->nullIfEmpty($item['acquisition_status']),
                        'exp_date' => $this->nullIfEmpty($item['expected_arrival_date']),
                        'pers_id' => $this->nullIfEmpty($item['personal_item_id']),
                        'stor_id' => $this->nullIfEmpty($item['storage_id'])
                    ]);
                } 
                // (Her kunne man tilføje 'else insert' logik hvis man tilføjer nye items i edit mode)
            }
        }
        
        // I stedet for redirect, kalder vi media_step direkte
        $_GET['id'] = $id;
        $this->media_step();
        exit;
    }

    private function getEnumValues($table, $column) {
        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
        $row = $this->db->query($sql)->fetch();
        if ($row) {
            preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
            if (isset($matches[1])) {
                return explode("','", $matches[1]);
            }
        }
        return [];
    }
    
    // Hjælpefunktion til at håndtere tomme felter
    private function nullIfEmpty($val) {
        return ($val === '' || $val === 'Select...') ? null : $val;
    }

    /**
     * TRIN 3: Vis Upload Interface (View)
     * Denne skal BLIVE her, da den styrer visningen af modalen specifikt for Toy Collection
     */
    public function media_step() {
        $toyId = (int)($_GET['id'] ?? 0);
        
        // 1. Hent Parent
        $toy = $this->db->query("
            SELECT ct.id, mt.name as toy_name 
            FROM collection_toys ct
            JOIN master_toys mt ON ct.master_toy_id = mt.id
            WHERE ct.id = :id", 
            ['id' => $toyId]
        )->fetch();

        if (!$toy) die("Toy not found");

        // 2. Hent Items
        $items = $this->db->query("
            SELECT cti.id, mti.variant_description, s.name as subject_name, s.type
            FROM collection_toy_items cti
            JOIN master_toy_items mti ON cti.master_toy_item_id = mti.id
            JOIN subjects s ON mti.subject_id = s.id
            WHERE cti.collection_toy_id = :pid", 
            ['pid' => $toyId]
        )->fetchAll();

        // 3. Hent TAGS (Det er denne del, der manglede!)
        $tags = $this->db->query("SELECT * FROM media_tags ORDER BY tag_name ASC")->fetchAll();

        $data = [
            'toy' => $toy,
            'items' => $items,
            'available_tags' => $tags // Send tags videre til viewet
        ];

        $this->view->renderPartial('add_media_modal', $data, 'Collection');
    }
}