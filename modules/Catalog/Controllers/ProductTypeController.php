<?php
namespace CollectionApp\Modules\Catalog\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\ProductTypeModel;

class ProductTypeController extends Controller {

    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new ProductTypeModel();
    }

    public function index() {
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        $initialData = $this->model->getFiltered([], 1, 20);
        
        $this->view->render('product_types_index', [
            'title' => 'Manage Product Types',
            'initialData' => $initialData,
            'scripts' => ['assets/js/modules/catalog/product-type-manager.js']
        ], 'Catalog');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = ['search' => $_GET['search'] ?? ''];

        $data = $this->model->getFiltered($filters, $page, 20);
        $this->view->renderPartial('product_types_grid', $data, 'Catalog');
    }

    public function store() {
        $this->jsonHandler(function() {
            $name = trim($_POST['type_name'] ?? '');
            if (empty($name)) throw new \Exception("Name is required");
            $this->model->create($name);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $name = trim($_POST['type_name'] ?? '');
            if (!$id || empty($name)) throw new \Exception("Invalid data");
            $this->model->update($id, $name);
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
}