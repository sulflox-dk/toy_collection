<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Kernel\Database; // NY: For at kunne hente Enums
use CollectionApp\Modules\Collection\Models\ToyModel;
use CollectionApp\Modules\Catalog\Models\CatalogModel;
use CollectionApp\Modules\Media\Models\MediaModel;
use CollectionApp\Modules\Catalog\Models\ToyLineModel;
use CollectionApp\Modules\Universe\Models\UniverseModel;
use CollectionApp\Modules\Universe\Models\EntertainmentSourceModel;
use CollectionApp\Modules\Collection\Models\StorageModel;
use CollectionApp\Modules\Catalog\Models\ManufacturerModel;
use CollectionApp\Modules\Catalog\Models\ProductTypeModel; // <--- NY

class ToyController extends Controller {

    private $toyModel;
    private $catalogModel;
    private $mediaModel;

    public function __construct() {
        parent::__construct();
        $this->toyModel = new ToyModel();
        $this->catalogModel = new CatalogModel();
        $this->mediaModel = new MediaModel();
    }

    public function add() {
        // Henter universer fra CatalogModel
        $data = ['universes' => $this->catalogModel->getAllUniverses()];
        $this->view->renderPartial('select_universe_modal', $data, 'Collection');
    }

    public function form() {
        $preSelectedUniverseId = isset($_GET['universe_id']) ? (int)$_GET['universe_id'] : null;
        $db = Database::getInstance(); // Hent DB instans til enums

        $data = [
            'universes'  => $this->catalogModel->getAllUniverses(),
            'sources'    => $this->catalogModel->getSources(),
            'storages'   => $this->catalogModel->getStorageUnits(),
            
            // NYT: Henter enums direkte fra Database-hjælperen
            'statuses'   => $db->getEnumValues('collection_toys', 'acquisition_status'),
            'conditions' => $db->getEnumValues('collection_toys', 'condition'),
            'completeness' => $db->getEnumValues('collection_toys', 'completeness_grade'),
            
            'selected_universe' => $preSelectedUniverseId
        ];

        $this->view->renderPartial('add_toy_modal', $data, 'Collection');
    }

    public function create() {
        if (empty($_POST['manufacturer_id']) || empty($_POST['line_id']) || empty($_POST['master_toy_id'])) {
            echo '<div class="alert alert-danger m-3">Error: Please select Universe, Manufacturer, Line, and Toy.</div>';
            exit;
        }

        $parentData = [
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

        $parentId = $this->toyModel->create($parentData);

        if (isset($_POST['items']) && is_array($_POST['items'])) {
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

                $this->toyModel->createItem($childData);
            }
        }

        $_GET['id'] = $parentId;
        $_GET['new_entry'] = true;
        $this->media_step();
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();
        
        $toy = $this->toyModel->getToyById($id);
        if (!$toy) {
             echo '<div class="alert alert-danger m-3">Error: Toy not found (ID: ' . $id . ')</div>';
             exit;
        }

        // NYT: Bruger CatalogModel og det nye navn getMasterToyItems
        $availableParts = $this->catalogModel->getMasterToyItems($toy['master_toy_id']);

        $data = [
            'mode' => 'edit',
            'toy' => $toy,
            'childItems' => $this->toyModel->getChildItems($id),
            'availableParts' => $availableParts, 
            
            'universes'     => $this->catalogModel->getAllUniverses(),
            'manufacturers' => $this->catalogModel->getManufacturersByUniverse($toy['universe_id']),
            'lines'         => $this->catalogModel->getLinesByManufacturer($toy['manufacturer_id']),
            'masterToys'    => $this->catalogModel->getMasterToysByLine($toy['line_id']),
            
            'sources'       => $this->catalogModel->getSources(),
            'storages'      => $this->catalogModel->getStorageUnits(),
            
            // Henter enums direkte fra Database
            'statuses'      => $db->getEnumValues('collection_toys', 'acquisition_status'),
            'conditions'    => $db->getEnumValues('collection_toys', 'condition'),
            'completeness'  => $db->getEnumValues('collection_toys', 'completeness_grade'),
            
            'selected_universe' => $toy['universe_id']
        ];

        $this->view->renderPartial('add_toy_modal', $data, 'Collection');
    }

    public function update() {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing ID']);
            exit;
        }

        // 1. Opdater Parent Data
        $parentData = [
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
        
        $this->toyModel->update($id, $parentData);

        // 2. Kør Smart Sync på Items (Håndterer opret, opdater og slet)
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $this->toyModel->saveItems($id, $_POST['items']);
        } else {
            // Hvis arrayet er tomt (bruger har slettet alt), skal saveItems stadig kaldes for at slette i DB
            $this->toyModel->saveItems($id, []);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

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

        if (!$toy) {
             echo '<div class="alert alert-danger m-3">Error: Toy not found. Cannot load media upload.</div>';
             exit;
        }

        // 2. Hent Items
        $items = $this->db->query("
            SELECT cti.id, mti.variant_description, s.name as subject_name, s.type
            FROM collection_toy_items cti
            JOIN master_toy_items mti ON cti.master_toy_item_id = mti.id
            JOIN subjects s ON mti.subject_id = s.id
            WHERE cti.collection_toy_id = :pid", 
            ['pid' => $toyId]
        )->fetchAll();

        // 3. Hent TAGS
        $tags = $this->mediaModel->getMediaTags();

        // 4. Hent eksisterende billeder
        $toy['images'] = $this->mediaModel->getImages('collection_parent', $toyId);
        foreach ($items as &$item) {
            $item['images'] = $this->mediaModel->getImages('collection_child', $item['id']);
        }

        // 5. Bestem MODE
        // Hvis 'new_entry' er sat i URL (fra store()), så er vi i 'create' mode. Ellers 'edit'.
        $mode = isset($_GET['new_entry']) ? 'create' : 'edit';

        $data = [
            'mode' => $mode,  // <--- Vi sender nu mode med
            'toy' => $toy,
            'items' => $items,
            'available_tags' => $tags
        ];

        $this->view->renderPartial('add_media_modal', $data, 'Collection');
    }
    
    private function nullIfEmpty($val) {
        return ($val === '' || $val === 'Select...') ? null : $val;
    }

    public function index() {
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        // Models
        $uniModel = new UniverseModel();
        $lineModel = new ToyLineModel();
        $sourceModel = new EntertainmentSourceModel();
        $storageModel = new StorageModel();
        $manModel = new ManufacturerModel(); // NY
        $ptModel = new ProductTypeModel();   // NY
        $db = Database::getInstance();

        $this->view->render('index', [
            'title' => 'My Collection',
            'universes' => $uniModel->getAllSimple(),
            'lines' => $lineModel->getAllSimple(),
            'manufacturers' => $manModel->getAllSimple(), // NY
            'productTypes' => $ptModel->getAllSimple(),   // NY
            'ent_sources' => $sourceModel->getAllSimple(),
            'storage_units' => $storageModel->getAllSimple(),
            'purchase_sources' => $db->query("SELECT * FROM sources ORDER BY name")->fetchAll(),
            'statuses' => $db->getEnumValues('collection_toys', 'acquisition_status'),
            'conditions' => $db->getEnumValues('collection_toys', 'condition'), // NY (til completeness/condition filter)
            'grades' => $db->getEnumValues('collection_toys', 'completeness_grade'), // NY
            
            'scripts' => [
                'assets/js/collection-form.js',
                'assets/js/collection_manager.js',
                'assets/js/collection-media.js'
            ]
        ], 'Collection');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'universe_id'           => $_GET['universe_id'] ?? '',
            'line_id'               => $_GET['line_id'] ?? '',
            'manufacturer_id'       => $_GET['manufacturer_id'] ?? '', // NY
            'product_type_id'       => $_GET['product_type_id'] ?? '', // NY
            'entertainment_source_id' => $_GET['ent_source_id'] ?? '',
            'storage_id'            => $_GET['storage_id'] ?? '',
            'source_id'             => $_GET['source_id'] ?? '',
            'acquisition_status'    => $_GET['status'] ?? '',
            
            'completeness'          => $_GET['completeness'] ?? '',    // NY (Grade)
            'has_missing_parts'     => $_GET['missing_parts'] ?? '',   // NY (Beregnet)
            'image_status'          => $_GET['image_status'] ?? '',    // NY
            
            'search'                => $_GET['search'] ?? ''
        ];

        $data = $this->toyModel->getFiltered($filters, $page, 20);
        $data['view_mode'] = $_COOKIE['collection_view_mode'] ?? 'list';
        $this->view->renderPartial('grid', $data, 'Collection');
    }

    public function delete() {
        // Tjek at det er et POST kald
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing ID']);
            exit;
        }

        try {
            $this->toyModel->delete($id);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            // Log evt. fejlen her
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // --- FUNKTION TIL PARTIAL REFRESH ---
    public function get_item_html() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) exit('Error: No ID');

        // Vi genbruger getFiltered for at få alle joins, billeder og missing items med
        // Vi beder om 'raw_result' => true for at slippe for paginerings-arrayet
        $results = $this->toyModel->getFiltered(['id' => $id, 'raw_result' => true], 1, 1);

        if (empty($results)) exit('Item not found');

        // Klargør data til grid.php
        $data = [
            'data' => $results,
            // Vi sender view_mode med, så den ved om det skal være tr eller card
            'view_mode' => $_COOKIE['collection_view_mode'] ?? 'list', 
            'hide_pagination' => true // Ingen sidetal
        ];

        // Render grid.php (som nu kun indeholder 1 element)
        $this->view->renderPartial('grid', $data, 'Collection');
    }

}