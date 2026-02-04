<?php
namespace CollectionApp\Modules\Catalog\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\ToyLineModel;
use CollectionApp\Modules\Catalog\Models\ManufacturerModel;
use CollectionApp\Modules\Universe\Models\UniverseModel;

class ToyLineController extends Controller {

    private $model;
    private $manModel;
    private $uniModel;

    public function __construct() {
        parent::__construct();
        $this->model = new ToyLineModel();
        $this->manModel = new ManufacturerModel();
        $this->uniModel = new UniverseModel();
    }

    public function index() {
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        // Hent data til dropdowns
        $manufacturers = $this->manModel->getAllSimple();
        $universes = $this->uniModel->getAllWithStats(); // Genbrug metoden, den returnerer id+name

        $initialData = $this->model->getFiltered([], 1, 20);
        
        $this->view->render('toy_lines_index', [
            'title' => 'Manage Toy Lines',
            'manufacturers' => $manufacturers,
            'universes' => $universes,
            'initialData' => $initialData,
            'scripts' => ['assets/js/toy_line_manager.js']
        ], 'Catalog');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'universe_id'     => $_GET['universe_id'] ?? '',
            'manufacturer_id' => $_GET['manufacturer_id'] ?? '',
            'search'          => $_GET['search'] ?? ''
        ];

        $data = $this->model->getFiltered($filters, $page, 20);
        $this->view->renderPartial('toy_lines_grid', $data, 'Catalog');
    }

    public function store() {
        $this->jsonHandler(function() {
            $data = $this->getPostData();
            if (empty($data['name']) || empty($data['universe_id']) || empty($data['manufacturer_id'])) {
                throw new \Exception("Name, Universe and Manufacturer are required");
            }
            $this->model->create($data);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $data = $this->getPostData();
            if (!$id || empty($data['name'])) throw new \Exception("Invalid data");
            
            $this->model->update($id, $data);
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

    // Helper til at samle POST data
    private function getPostData() {
        return [
            'name'              => trim($_POST['name'] ?? ''),
            'universe_id'       => (int)($_POST['universe_id'] ?? 0),
            'manufacturer_id'   => (int)($_POST['manufacturer_id'] ?? 0),
            'scale'             => trim($_POST['scale'] ?? ''),
            'era_start_year'    => !empty($_POST['era_start_year']) ? (int)$_POST['era_start_year'] : null,
            'show_on_dashboard' => isset($_POST['show_on_dashboard']) ? 1 : 0
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

    public function get_json() {
        header('Content-Type: application/json');
        $manId = isset($_GET['manufacturer_id']) ? (int)$_GET['manufacturer_id'] : 0;

        if (!$manId) {
            echo json_encode([]);
            exit;
        }

        $result = $this->model->getFiltered(['manufacturer_id' => $manId]);
        $data = $result['data'] ?? [];
        $json = [];
        foreach($data as $row) {
            $json[] = ['id' => $row['id'], 'name' => $row['name']];
        }

        echo json_encode($json);
        exit;
    }
}