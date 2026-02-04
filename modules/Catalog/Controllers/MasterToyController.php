<?php
namespace CollectionApp\Modules\Catalog\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\MasterToyModel;
use CollectionApp\Modules\Catalog\Models\ToyLineModel;
use CollectionApp\Modules\Catalog\Models\ManufacturerModel; // <--- NY
use CollectionApp\Modules\Universe\Models\UniverseModel;
use CollectionApp\Modules\Universe\Models\EntertainmentSourceModel;
use CollectionApp\Modules\Universe\Models\SubjectModel;

class MasterToyController extends Controller {

    private $model;
    private $lineModel;
    private $manModel; // <--- NY
    private $uniModel;
    private $sourceModel;
    private $subModel;

    public function __construct() {
        parent::__construct();
        $this->model = new MasterToyModel();
        $this->lineModel = new ToyLineModel();
        $this->manModel = new ManufacturerModel(); // <--- NY
        $this->uniModel = new UniverseModel();
        $this->sourceModel = new EntertainmentSourceModel();
        $this->subModel = new SubjectModel();
    }

    // ... (index, renderGrid, delete, modal_step1 metoderne er uændrede) ...

    public function index() {
        // (Behold din eksisterende index metode her)
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        $universes = $this->uniModel->getAllWithStats();
        $lines = $this->lineModel->getAllSimple();
        $sources = $this->sourceModel->getAllSimple(); 
        $initialData = $this->model->getFiltered([], 1, 20);
        
        $this->view->render('master_toy_index', [
            'title' => 'Catalog: Master Toys',
            'universes' => $universes,
            'lines' => $lines,
            'sources' => $sources,
            'initialData' => $initialData,
            'scripts' => ['assets/js/master_toy_manager.js'] 
        ], 'Catalog');
    }
    
    // (Behold din renderGrid og delete metode...)
    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'universe_id' => $_GET['universe_id'] ?? '',
            'line_id'     => $_GET['line_id'] ?? '',
            'source_id'   => $_GET['source_id'] ?? '',
            'search'      => $_GET['search'] ?? ''
        ];

        $data = $this->model->getFiltered($filters, $page, 20);
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

            // Hent de tilknyttede ID'er fra legetøjet
            $universeId = $toy['universe_id'];
            $manufacturerId = $toy['manufacturer_id'];

            // Hent Manufacturers for det pågældende univers
            if ($universeId) {
                $manResult = $this->manModel->getFiltered(['universe_id' => $universeId], 1, 1000);
                $manufacturers = $manResult['data'] ?? [];
            }

            // Hent KUN Toy Lines for den pågældende producent
            if ($manufacturerId) {
                $lineResult = $this->lineModel->getFiltered(['manufacturer_id' => $manufacturerId], 1, 1000);
                $lines = $lineResult['data'] ?? [];
            }
        } 
        elseif ($universeId) {
            // --- NYOPRETTELSE (fra Step 1) ---
            $manResult = $this->manModel->getFiltered(['universe_id' => $universeId], 1, 1000);
            $manufacturers = $manResult['data'] ?? [];
            
            // Lines forbliver tomme indtil brugeren vælger en producent i UI (håndteres af JS)
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
        // Byg items arrayet (uændret)
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $i) {
                if (!empty($i['subject_id'])) {
                    $items[] = [
                        'subject_id' => (int)$i['subject_id'],
                        'variant_description' => trim($i['variant_description'] ?? ''),
                        'quantity' => (int)($i['quantity'] ?? 1)
                    ];
                }
            }
        }

        // Helper til at håndtere NULL værdier
        $toIntOrNull = function($val) {
            return !empty($val) ? (int)$val : null;
        };

        return [
            'line_id' => (int)$_POST['line_id'],
            'product_type_id' => (int)$_POST['product_type_id'],
            // FIX: Brug helperen herunder, så tom værdi bliver NULL
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

    // Denne skal også findes:
    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            if (!$id) throw new \Exception("Missing ID");
            $data = $this->getPostData();
            $this->model->update($id, $data); // Kalder modellens update
            return ['success' => true, 'id' => $id];
        });
    }
}