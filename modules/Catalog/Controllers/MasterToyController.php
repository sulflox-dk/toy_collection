<?php
namespace CollectionApp\Modules\Catalog\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\MasterToyModel;
use CollectionApp\Modules\Catalog\Models\ToyLineModel;
use CollectionApp\Modules\Catalog\Models\ManufacturerModel;
use CollectionApp\Modules\Catalog\Models\ProductTypeModel; // <--- NY
use CollectionApp\Modules\Universe\Models\UniverseModel;
use CollectionApp\Modules\Universe\Models\EntertainmentSourceModel;
use CollectionApp\Modules\Universe\Models\SubjectModel;
use CollectionApp\Modules\Media\Models\MediaModel;

class MasterToyController extends Controller {

    private $model;
    private $lineModel;
    private $manModel; // <--- NY
    private $ptModel;
    private $uniModel;
    private $sourceModel;
    private $subModel;

    public function __construct() {
        parent::__construct();
        $this->model = new MasterToyModel();
        $this->lineModel = new ToyLineModel();
        $this->manModel = new ManufacturerModel(); // <--- NY
        $this->ptModel = new ProductTypeModel();
        $this->uniModel = new UniverseModel();
        $this->sourceModel = new EntertainmentSourceModel();
        $this->subModel = new SubjectModel();
    }

    // ... (index, renderGrid, delete, modal_step1 metoderne er u�ndrede) ...

    public function index() {
        // (Behold din eksisterende index metode her)
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        $universes = $this->uniModel->getAllWithStats();
        $lines = $this->lineModel->getAllSimple();
        $sources = $this->sourceModel->getAllSimple();
        $manufacturers = $this->manModel->getAllSimple();
        $productTypes = $this->ptModel->getAllSimple();

        $initialData = $this->model->getFiltered([], 1, 20);

        $viewMode = $_COOKIE['catalog_view_mode'] ?? 'list';
        
        $this->view->render('master_toy_index', [
            'title' => 'Catalog: Master Toys',
            'universes' => $universes,
            'lines' => $lines,
            'sources' => $sources,
            'manufacturers' => $manufacturers, // <--- NY
            'productTypes' => $productTypes,   // <--- NY
            'initialData' => $initialData,
            'view_mode' => $viewMode,
            'scripts' => [
                'assets/js/collection-media.js', 
                'assets/js/master_toy_manager.js',
                'assets/js/collection-form.js'
            ] 
        ], 'Catalog');
    }
    
    // (Behold din renderGrid og delete metode...)
    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'universe_id'     => $_GET['universe_id'] ?? null,
            'line_id'         => $_GET['line_id'] ?? null,
            'source_id'       => $_GET['source_id'] ?? null,
            
            // Tjek at disse linjer er med:
            'manufacturer_id' => $_GET['manufacturer_id'] ?? null,
            'product_type_id' => $_GET['product_type_id'] ?? null,
            'image_status'    => $_GET['image_status'] ?? null,
            // ---------------------------
            
            'owned_status'    => $_GET['owned_status'] ?? null,
            'search'          => $_GET['search'] ?? null,
        ];

        $data = $this->model->getFiltered($filters, $page, 20);
        $data['view_mode'] = $_COOKIE['catalog_view_mode'] ?? 'list';
        $this->view->renderPartial('master_toy_grid', $data, 'Catalog');
    }

    public function delete() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            if (!$id) throw new \Exception("Missing ID");
            $this->model->delete($id);
            return ['success' => true];
        });
    }

    public function modal_step1() {
        $universes = $this->uniModel->getAllWithStats();
        $this->view->renderPartial('master_toy_modal_step1_universe', ['universes' => $universes], 'Catalog');
    }

    public function modal_step2() {
        $universeId = $_GET['universe_id'] ?? null;
        $toyId = $_GET['id'] ?? null;
        $toy = null;

        // Initialiser tomme lister
        $manufacturers = [];
        $lines = [];

        if ($toyId) {
            // --- REDIGERING ---
            $toy = $this->model->getById($toyId);
            if (!$toy) die("Toy not found");
            
            $toy['items'] = $this->model->getItems($toyId);

            // Hent de tilknyttede ID'er fra leget�jet
            $universeId = $toy['universe_id'];
            $manufacturerId = $toy['manufacturer_id'];

            // Hent Manufacturers for det p�g�ldende univers
            if ($universeId) {
                $manResult = $this->manModel->getFiltered(['universe_id' => $universeId], 1, 1000);
                $manufacturers = $manResult['data'] ?? [];
            }

            // Hent KUN Toy Lines for den p�g�ldende producent
            if ($manufacturerId) {
                $lineResult = $this->lineModel->getFiltered(['manufacturer_id' => $manufacturerId], 1, 1000);
                $lines = $lineResult['data'] ?? [];
            }
        } 
        elseif ($universeId) {
            // --- NYOPRETTELSE (fra Step 1) ---
            $manResult = $this->manModel->getFiltered(['universe_id' => $universeId], 1, 1000);
            $manufacturers = $manResult['data'] ?? [];
            
            // Lines forbliver tomme indtil brugeren v�lger en producent i UI (h�ndteres af JS)
            $lines = [];
        }

        // Stamdata til dropdowns (Produkt typer, kilder, emner og alle universer)
        $prodTypeModel = new \CollectionApp\Modules\Catalog\Models\ProductTypeModel();
        $types = $prodTypeModel->getAllSimple();
        $sources = $this->sourceModel->getAllSimple();
        $subjects = $this->subModel->getAllSimple();
        $universes = $this->uniModel->getAllSimple(); 

        // Render viewet med alle de indhentede data
        $this->view->renderPartial('master_toy_modal_step2_form', [
            'toy' => $toy,
            'selected_universe_id' => $universeId,
            'universes' => $universes,
            'manufacturers' => $manufacturers,
            'lines' => $lines,
            'types' => $types,
            'sources' => $sources,
            'subjects' => $subjects
        ], 'Catalog');
    }

    private function getPostData() {
        $items = [];
        // Byg items arrayet (u�ndret)
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $i) {
                if (!empty($i['subject_id'])) {
                    $items[] = [
                        'id' => isset($i['id']) ? (int)$i['id'] : null,    
                        'subject_id' => (int)$i['subject_id'],
                        'variant_description' => trim($i['variant_description'] ?? ''),
                        'quantity' => (int)($i['quantity'] ?? 1)
                    ];
                }
            }
        }

        // Helper til at h�ndtere NULL v�rdier
        $toIntOrNull = function($val) {
            return !empty($val) ? (int)$val : null;
        };

        return [
            'line_id' => (int)$_POST['line_id'],
            'product_type_id' => (int)$_POST['product_type_id'],
            'entertainment_source_id' => $toIntOrNull($_POST['entertainment_source_id']),
            'name' => trim($_POST['name']),
            'release_year' => $toIntOrNull($_POST['release_year']),
            'wave_number' => trim($_POST['wave_number'] ?? ''),
            'assortment_sku' => trim($_POST['assortment_sku'] ?? ''),
            'items' => $items
        ];
    }

    private function jsonHandler(callable $callback) {
        header('Content-Type: application/json');
        try {
            echo json_encode($callback());
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function create() {
        $this->jsonHandler(function() {
            $data = $this->getPostData();
            $id = $this->model->create($data); // Kalder modellens create
            return ['success' => true, 'id' => $id];
        });
    }

    // Denne skal ogs� findes:
    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            if (!$id) throw new \Exception("Missing ID");
            $data = $this->getPostData();
            $this->model->update($id, $data); // Kalder modellens update
            return ['success' => true, 'id' => $id];
        });
    }

    public function modal_media() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) die("Missing ID");

        // 1. Hent Master Toy
        $toy = $this->model->getById($id);
        if (!$toy) die("Toy not found");

        // 2. Hent Items
        $items = $this->model->getItems($id);

        // 3. Hent Tags og Eksisterende Billeder via MediaModel
        // (Vi opretter en instans her, da den ikke er injected globalt)
        $mediaModel = new \CollectionApp\Modules\Media\Models\MediaModel();
        
        $availableTags = $mediaModel->getMediaTags();
        
        // Hent billeder for parent (�sken)
        $toy['images'] = $mediaModel->getImages('catalog_parent', $id);

        // Hent billeder for hver item (figurer/dele)
        foreach($items as &$item) {
            $item['images'] = $mediaModel->getImages('catalog_child', $item['id']);
        }

        $this->view->renderPartial('master_toy_step3_images', [
            'toy' => $toy,
            'items' => $items,
            'available_tags' => $availableTags,
            'mode' => $_GET['mode'] ?? 'edit'
        ], 'Catalog');
    }
    
    // --- Hent HTML for ét kort (til Smart Refresh) ---
    public function get_item_html() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) exit('Error: No ID');

        $filters = ['id' => $id];
        
        // Hent data - raw_result giver os rækkerne direkte
        $results = $this->model->getFiltered($filters, 1, 1);
        $toys = isset($results['data']) ? $results['data'] : $results;

        if (empty($toys)) exit('Item not found');

        // Cache busting til billede
        if (!empty($toys[0]['image_path'])) {
            $toys[0]['image_path'] .= '?t=' . time();
        }

        $data = [
            'data' => $toys, // <--- RETTET HER: Ændret fra 'initialData' til 'data'
            'view_mode' => $_COOKIE['catalog_view_mode'] ?? 'list',
            'hide_pagination' => true
        ];

        $this->view->renderPartial('master_toy_grid', $data, 'Catalog');
    }

}