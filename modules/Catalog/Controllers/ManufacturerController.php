<?php
namespace CollectionApp\Modules\Catalog\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\ManufacturerModel;

class ManufacturerController extends Controller {

    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new ManufacturerModel();
    }

    public function index() {
        // AJAX Check
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        // Initial Load
        $initialData = $this->model->getFiltered([], 1, 20);
        
        $this->view->render('manufacturers_index', [
            'title' => 'Manage Manufacturers',
            'initialData' => $initialData,
            'scripts' => ['assets/js/modules/catalog/manufacturer-manager.js']
        ], 'Catalog');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'search' => $_GET['search'] ?? ''
        ];

        $data = $this->model->getFiltered($filters, $page, 20);
        
        // Vi bruger et nyt partial view
        $this->view->renderPartial('manufacturers_grid', $data, 'Catalog');
    }

    public function store() {
        $this->jsonHandler(function() {
            $name = trim($_POST['name'] ?? '');
            $show = isset($_POST['show_on_dashboard']) ? 1 : 0;
            if (empty($name)) throw new \Exception("Name is required");
            $this->model->create($name, $show);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name'] ?? '');
            $show = isset($_POST['show_on_dashboard']) ? 1 : 0;
            if (!$id || empty($name)) throw new \Exception("Invalid data");
            $this->model->update($id, $name, $show);
            return ['success' => true];
        });
    }

    public function delete() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $migrateTo = !empty($_POST['migrate_to_id']) ? (int)$_POST['migrate_to_id'] : null;
            if (!$id) throw new \Exception("Missing ID");
            $this->model->delete($id, $migrateTo);
            return ['success' => true];
        });
    }

    public function get_all_simple() {
        $this->jsonHandler(function() {
            return $this->model->getAllSimple();
        });
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

    public function get_json() {
        header('Content-Type: application/json');
        $universeId = isset($_GET['universe_id']) ? (int)$_GET['universe_id'] : 0;

        if (!$universeId) {
            echo json_encode([]);
            exit;
        }

        // RETTELSEN ER HER: Vi skal hente ['data'] fra resultatet
        $result = $this->model->getFiltered(['universe_id' => $universeId]);
        $manufacturers = $result['data'] ?? []; // <--- VIGTIGT!
        
        $simpleList = [];
        foreach ($manufacturers as $m) {
            $simpleList[] = [
                'id' => $m['id'],
                'name' => $m['name']
            ];
        }

        echo json_encode($simpleList);
        exit;
    }
}