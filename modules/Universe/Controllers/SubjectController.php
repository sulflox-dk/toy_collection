<?php
namespace CollectionApp\Modules\Universe\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Kernel\Database;
use CollectionApp\Modules\Universe\Models\SubjectModel;

class SubjectController extends Controller {

    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new SubjectModel();
    }

    public function index() {
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        // Hent ENUM v�rdier til dropdown
        $types = Database::getInstance()->getEnumValues('subjects', 'type');
        
        // Initial data
        $initialData = $this->model->getFiltered([], 1, 20);

        $this->view->render('subjects_index', [
            'title' => 'Manage Subjects',
            'types' => $types,
            'initialData' => $initialData,
            'scripts' => ['assets/js/modules/universe/subject-manager.js']
        ], 'Universe');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'type'   => $_GET['type'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $data = $this->model->getFiltered($filters, $page, 20);
        $this->view->renderPartial('subjects_grid', $data, 'Universe');
    }

    public function store() {
        $this->jsonHandler(function() {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'type' => $_POST['type'] ?? 'Accessory',
                'faction' => trim($_POST['faction'] ?? '')
            ];

            if (empty($data['name'])) throw new \Exception("Name is required");
            
            $this->model->create($data);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'type' => $_POST['type'] ?? 'Accessory',
                'faction' => trim($_POST['faction'] ?? '')
            ];

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