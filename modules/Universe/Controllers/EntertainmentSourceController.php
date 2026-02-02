<?php
namespace CollectionApp\Modules\Universe\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Kernel\Database;
use CollectionApp\Modules\Universe\Models\EntertainmentSourceModel;
use CollectionApp\Modules\Universe\Models\UniverseModel; // Til dropdown

class EntertainmentSourceController extends Controller {

    private $model;
    private $universeModel;

    public function __construct() {
        parent::__construct();
        $this->model = new EntertainmentSourceModel();
        $this->universeModel = new UniverseModel();
    }

    public function index() {
        // Hvis AJAX request -> Returner kun tabellen (grid)
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        // Ellers vis hele siden (containeren)
        $universes = $this->universeModel->getAllWithStats();
        $types = Database::getInstance()->getEnumValues('entertainment_sources', 'type');

        // Vi loader første side af data med det samme til initial visning
        $initialData = $this->model->getFiltered([], 1, 20);

        $this->view->render('sources_index', [
            'title' => 'Manage Entertainment Sources',
            'universes' => $universes,
            'types' => $types,
            'initialData' => $initialData, // Send data med til viewet
            'scripts' => ['assets/js/entertainment_source_manager.js']
        ], 'Universe');
    }

    // Helper til AJAX
    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'universe_id' => $_GET['universe_id'] ?? '',
            'type'        => $_GET['type'] ?? '',
            'search'      => $_GET['search'] ?? ''
        ];

        $data = $this->model->getFiltered($filters, $page, 20); // 20 pr. side

        // Vi bruger et nyt partial view 'sources_grid'
        $this->view->renderPartial('sources_grid', $data, 'Universe');
    }

    public function store() {
        $this->jsonHandler(function() {
            $data = [
                'universe_id' => (int)$_POST['universe_id'],
                'name' => trim($_POST['name'] ?? ''),
                'type' => $_POST['type'] ?? 'Other',
                'release_year' => $_POST['release_year'] ?? null
            ];

            if (empty($data['name']) || empty($data['universe_id'])) throw new \Exception("Name and Universe are required");
            
            $this->model->create($data);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $data = [
                'universe_id' => (int)$_POST['universe_id'],
                'name' => trim($_POST['name'] ?? ''),
                'type' => $_POST['type'] ?? 'Other',
                'release_year' => $_POST['release_year'] ?? null
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

    private function jsonHandler(callable $callback) {
        header('Content-Type: application/json');
        try {
            echo json_encode($callback());
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function get_all_simple() {
        $this->jsonHandler(function() {
            return $this->model->getAllSimple();
        });
    }
}