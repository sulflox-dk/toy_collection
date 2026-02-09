<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Collection\Models\ToyModel;
use CollectionApp\Modules\Catalog\Models\ManufacturerModel;
use CollectionApp\Modules\Catalog\Models\ProductTypeModel;
use CollectionApp\Modules\Universe\Models\UniverseModel;
use CollectionApp\Modules\Media\Models\MediaModel;

class ShowcaseController extends Controller {

    private $toyModel;
    private $uniModel;
    private $manModel;
    private $ptModel;
    private $mediaModel;

    public function __construct() {
        parent::__construct();
        $this->toyModel = new ToyModel();
        $this->uniModel = new UniverseModel();
        $this->manModel = new ManufacturerModel();
        $this->ptModel = new ProductTypeModel();
        $this->mediaModel = new MediaModel();
    }

    // --- ETAPE 1: OVERVIEW ---
    public function index() {
        // Hvis det er et AJAX kald (filtrering), returner kun grid
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        // Hent data til dropdowns (Vi genbruger dem, men fjerner Storage/Source i viewet)
        $data = [
            'title' => 'Collection Showcase',
            'universes' => $this->uniModel->getAllSimple(),
            'manufacturers' => $this->manModel->getAllSimple(),
            'productTypes' => $this->ptModel->getAllSimple(),
            // Vi henter data første gang
            'initialData' => $this->toyModel->getFiltered([], 1, 24)
        ];

        // Vi bruger et nyt view 'showcase_index'
        $this->view->render('showcase_index', $data, 'Collection');
    }

    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        
        // Vi tillader filtrering, men skjuler interne felter (Storage, Source)
        $filters = [
            'universe_id'     => $_GET['universe_id'] ?? '',
            'manufacturer_id' => $_GET['manufacturer_id'] ?? '',
            'line_id'         => $_GET['line_id'] ?? '',
            'product_type_id' => $_GET['product_type_id'] ?? '',
            'completeness'    => $_GET['completeness'] ?? '',
            'search'          => $_GET['search'] ?? ''
        ];

        $data = $this->toyModel->getFiltered($filters, $page, 24);
        $this->view->renderPartial('showcase_grid', $data, 'Collection');
    }

    // --- ETAPE 2: DETAIL VIEW ---
    public function view() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) header('Location: /collection/showcase');

        // 1. Hent Hoved Toy Data
        $toy = $this->toyModel->getToyById($id);
        if (!$toy) die("Toy not found");

        // 2. Hent Child Items
        $items = $this->toyModel->getChildItems($id);

        // 3. Hent Billeder (Både hoved-toy og items samlet til galleri)
        $gallery = [];
        
        // Hoved billeder
        $parentImages = $this->mediaModel->getImages('collection_parent', $id);
        foreach($parentImages as $img) $gallery[] = $img;

        // Child billeder
        foreach($items as $item) {
            $childImages = $this->mediaModel->getImages('collection_child', $item['id']);
            foreach($childImages as $img) $gallery[] = $img;
        }

        // 4. Hent Relaterede Toys (Til bunden)
        $related = $this->toyModel->getRelatedToys($id, $toy['master_toy_id'], $toy['line_id']);

        $this->view->render('showcase_detail', [
            'toy' => $toy,
            'items' => $items,
            'gallery' => $gallery,
            'related' => $related
        ], 'Collection');
    }
}