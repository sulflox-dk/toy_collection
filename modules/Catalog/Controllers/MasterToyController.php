<?php
namespace CollectionApp\Modules\Catalog\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\MasterToyModel;
use CollectionApp\Modules\Catalog\Models\ToyLineModel;
use CollectionApp\Modules\Universe\Models\UniverseModel;
use CollectionApp\Modules\Universe\Models\EntertainmentSourceModel;
use CollectionApp\Modules\Universe\Models\SubjectModel; // Husk at denne bruges

class MasterToyController extends Controller {

    private $model;
    private $lineModel;
    private $uniModel;
    private $sourceModel;
    private $subModel; // RETTELSE 1: Tilføjet property

    public function __construct() {
        parent::__construct();
        $this->model = new MasterToyModel();
        $this->lineModel = new ToyLineModel();
        $this->uniModel = new UniverseModel();
        $this->sourceModel = new EntertainmentSourceModel();
        $this->subModel = new SubjectModel(); // RETTELSE 2: Instansieret her
    }

    public function index() {
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

    // --- STEP 1: Vælg Univers ---
    public function modal_step1() {
        $universes = $this->uniModel->getAllWithStats();
        $this->view->renderPartial('master_toy_modal_step1_universe', ['universes' => $universes], 'Catalog');
    }

    // --- STEP 2: Indtast Data (Add/Edit) ---
    public function modal_step2() {
        $universeId = $_GET['universe_id'] ?? null;
        $toyId = $_GET['id'] ?? null;
        $toy = null;

        if ($toyId) {
            $toy = $this->model->getById($toyId);
            if (!$toy) die("Toy not found");
        }

        // Product Types
        $prodTypeModel = new \CollectionApp\Modules\Catalog\Models\ProductTypeModel();
        $types = $prodTypeModel->getAllSimple();

        // Toy Lines
        $lines = $this->lineModel->getAllSimple();
        
        // Sources
        $sources = $this->sourceModel->getAllSimple(); 

        // Subjects (Nu virker denne, da subModel er oprettet)
        $subjects = $this->subModel->getAllSimple();

        $this->view->renderPartial('master_toy_modal_step2_form', [
            'toy' => $toy,
            'universe_id' => $universeId,
            'types' => $types,
            'lines' => $lines,
            'sources' => $sources,
            'subjects' => $subjects
        ], 'Catalog');
    }

    public function store() {
        $this->jsonHandler(function() {
            $data = $this->getPostData();
            $id = $this->model->create($data);
            return ['success' => true, 'id' => $id];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            if (!$id) throw new \Exception("Missing ID");
            
            $data = $this->getPostData();
            $this->model->update($id, $data);
            return ['success' => true, 'id' => $id];
        });
    }

    private function getPostData() {
        $items = [];
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $i) {
                if (!empty($i['subject_id'])) {
                    $items[] = [
                        'subject_id' => (int)$i['subject_id'],
                        'variation_name' => trim($i['variation_name'] ?? ''),
                        'quantity' => (int)($i['quantity'] ?? 1)
                    ];
                }
            }
        }

        return [
            'line_id' => (int)$_POST['line_id'],
            'product_type_id' => (int)$_POST['product_type_id'],
            'entertainment_source_id' => (int)$_POST['entertainment_source_id'],
            'name' => trim($_POST['name']),
            'release_year' => !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null,
            'wave_number' => trim($_POST['wave_number'] ?? ''),
            'assortment_sku' => trim($_POST['assortment_sku'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'items' => $items
        ];
    }

    // RETTELSE 3: Tilføjet jsonHandler helper metoden
    private function jsonHandler(callable $callback) {
        header('Content-Type: application/json');
        try {
            echo json_encode($callback());
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}