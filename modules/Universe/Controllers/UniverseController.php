<?php
namespace CollectionApp\Modules\Universe\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Universe\Models\UniverseModel;

class UniverseController extends Controller {

    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new UniverseModel();
    }

    public function index() {
        $universes = $this->model->getAllWithStats();
        
        $this->view->render('index', [
            'title' => 'Manage Universes',
            'universes' => $universes,
            'scripts' => ['assets/js/universe_manager.js']
        ], 'Universe'); // Viewet ligger i modules/Universe/Views/
    }

    public function store() {
        $this->jsonHandler(function() {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? strtolower(str_replace(' ', '-', $name)));
            $sort = (int)($_POST['sort_order'] ?? 99);
            $show = isset($_POST['show_on_dashboard']) ? 1 : 0;

            if (empty($name)) throw new \Exception("Name is required");
            
            $this->model->create($name, $slug, $sort, $show);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $sort = (int)($_POST['sort_order'] ?? 99);
            $show = isset($_POST['show_on_dashboard']) ? 1 : 0;

            if (!$id || empty($name)) throw new \Exception("Invalid data");
            
            $this->model->update($id, $name, $slug, $sort, $show);
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