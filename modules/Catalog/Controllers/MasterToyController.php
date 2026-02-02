<?php
namespace CollectionApp\Modules\Catalog\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\MasterToyModel;
use CollectionApp\Modules\Catalog\Models\ToyLineModel;
use CollectionApp\Modules\Universe\Models\UniverseModel;
use CollectionApp\Modules\Universe\Models\SubjectModel;

class MasterToyController extends Controller {

    private $model;
    private $lineModel;
    private $uniModel;
    private $subModel;

    public function __construct() {
        parent::__construct();
        $this->model = new MasterToyModel();
        $this->lineModel = new ToyLineModel();
        $this->uniModel = new UniverseModel();
        $this->subModel = new SubjectModel();
    }

    public function index() {
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        $universes = $this->uniModel->getAllWithStats();
        $lines = $this->lineModel->getAllSimple();
        $subjects = $this->subModel->getAllSimple(); 

        $initialData = $this->model->getFiltered([], 1, 20);
        
        // RETTET: Filnavn og script navn opdateret til master_toy_
        $this->view->render('master_toy_index', [
            'title' => 'Catalog: Master Toys',
            'universes' => $universes,
            'lines' => $lines,
            'subjects' => $subjects,
            'initialData' => $initialData,
            'scripts' => ['assets/js/master_toy_manager.js'] 
        ], 'Catalog');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'universe_id' => $_GET['universe_id'] ?? '',
            'line_id'     => $_GET['line_id'] ?? '',
            'subject_id'  => $_GET['subject_id'] ?? '',
            'search'      => $_GET['search'] ?? ''
        ];

        $data = $this->model->getFiltered($filters, $page, 20);
        
        // RETTET: Filnavn opdateret
        $this->view->renderPartial('master_toy_grid', $data, 'Catalog');
    }

    public function delete() {
        header('Content-Type: application/json');
        $id = (int)$_POST['id'];
        
        try {
            if (!$id) throw new \Exception("Missing ID");
            $this->model->delete($id);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}