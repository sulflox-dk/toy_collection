<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Collection\Models\StorageModel;

class StorageController extends Controller {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new StorageModel();
    }

    public function index() {
        if (isset($_GET['ajax'])) {
            $this->renderGrid();
            exit;
        }

        $this->view->render('storage_index', [
            'title' => 'My Collection: Storage',
            'scripts' => ['assets/js/storage_manager.js']
        ], 'Collection');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $data = $this->model->getFiltered($search, $page, 20);
        $this->view->renderPartial('storage_grid', $data, 'Collection');
    }

    public function store() {
        $this->jsonHandler(function() {
            $data = [
                'name' => $_POST['name'],
                'box_code' => $_POST['box_code'],
                'location_room' => $_POST['location_room'],
                'description' => $_POST['description']
            ];
            $this->model->create($data);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $data = [
                'name' => $_POST['name'],
                'box_code' => $_POST['box_code'],
                'location_room' => $_POST['location_room'],
                'description' => $_POST['description']
            ];
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

    public function get_json() {
        $this->jsonHandler(function() {
            $id = (int)$_GET['id'];
            return $this->model->getById($id);
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
}