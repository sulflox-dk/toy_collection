<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Collection\Models\ToyModel;
use CollectionApp\Modules\Catalog\Models\CatalogModel; // Husk denne nye use-linje!

class ToyController extends Controller {

    private $toyModel;
    private $catalogModel;

    public function __construct() {
        parent::__construct();
        $this->toyModel = new ToyModel();
        $this->catalogModel = new CatalogModel(); // Vi instansierer den nye model
    }

    public function add() {
        // Bruger nu CatalogModel
        $data = ['universes' => $this->catalogModel->getAllUniverses()];
        $this->view->renderPartial('select_universe_modal', $data, 'Collection');
    }

    public function form() {
        $preSelectedUniverseId = isset($_GET['universe_id']) ? (int)$_GET['universe_id'] : null;

        $data = [
            // Her henter vi alle lister fra CatalogModel
            'universes'  => $this->catalogModel->getAllUniverses(),
            'sources'    => $this->catalogModel->getSources(),
            'storages'   => $this->catalogModel->getStorageUnits(),
            
            // Disse ligger stadig i ToyModel (da de er metadata på selve items)
            'statuses'   => $this->toyModel->getEnumValues('collection_toys', 'acquisition_status'),
            'conditions' => $this->toyModel->getEnumValues('collection_toys', 'condition'),
            'completeness' => $this->toyModel->getEnumValues('collection_toys', 'completeness_grade'),
            
            'selected_universe' => $preSelectedUniverseId
        ];

        $this->view->renderPartial('add_toy_modal', $data, 'Collection');
    }

    public function store() {
        // (Uændret logik - bruger stadig ToyModel til at gemme)
        if (empty($_POST['manufacturer_id']) || empty($_POST['line_id']) || empty($_POST['master_toy_id'])) {
            echo '<div class="alert alert-danger m-3">Error: Please select Universe, Manufacturer, Line, and Toy.</div>';
            exit;
        }

        $parentData = [
            'master_toy_id'       => $_POST['master_toy_id'],
            'is_loose'            => isset($_POST['is_loose']) ? 1 : 0, 
            'purchase_date'       => $this->nullIfEmpty($_POST['purchase_date']),
            'purchase_price'      => $this->nullIfEmpty($_POST['purchase_price']),
            'source_id'           => $this->nullIfEmpty($_POST['source_id']),
            'acquisition_status'  => $this->nullIfEmpty($_POST['acquisition_status']),
            'condition'           => $this->nullIfEmpty($_POST['condition']),
            'completeness_grade'  => $this->nullIfEmpty($_POST['completeness_grade']),
            'storage_id'          => $this->nullIfEmpty($_POST['storage_id']),
            'personal_toy_id'     => $this->nullIfEmpty($_POST['personal_toy_id']),
            'user_comments'       => $this->nullIfEmpty($_POST['user_comments'])
        ];

        $parentId = $this->toyModel->create($parentData);

        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (empty($item['master_toy_item_id'])) continue;

                $childData = [
                    'pid'         => $parentId,
                    'mid'         => $item['master_toy_item_id'],
                    'cond'        => $this->nullIfEmpty($item['condition']),
                    'loose'       => isset($item['is_loose']) ? 1 : 0,
                    'is_repo'     => $this->nullIfEmpty($item['is_reproduction']),
                    'comments'    => $this->nullIfEmpty($item['user_comments']),
                    'p_date'      => $this->nullIfEmpty($item['purchase_date']),
                    'p_price'     => $this->nullIfEmpty($item['purchase_price']),
                    'src_id'      => $this->nullIfEmpty($item['source_id']),
                    'acq_status'  => $this->nullIfEmpty($item['acquisition_status']),
                    'exp_date'    => $this->nullIfEmpty($item['expected_arrival_date']),
                    'pers_id'     => $this->nullIfEmpty($item['personal_item_id']),
                    'stor_id'     => $this->nullIfEmpty($item['storage_id'])
                ];

                $this->toyModel->createItem($childData);
            }
        }

        $_GET['id'] = $parentId;
        $this->media_step();
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        
        $toy = $this->toyModel->getToyById($id);
        if (!$toy) {
            echo '<div class="alert alert-danger m-3">Error: Toy not found (ID: ' . $id . ')</div>';
            exit;
        }

        $data = [
            'mode' => 'edit',
            'toy' => $toy,
            'childItems' => $this->toyModel->getChildItems($id),
            
            // HER SKER MAGIEN: Vi bruger nu CatalogModel til at hente de afhængige dropdowns
            'universes'     => $this->catalogModel->getAllUniverses(),
            'manufacturers' => $this->catalogModel->getManufacturersByUniverse($toy['universe_id']),
            'lines'         => $this->catalogModel->getLinesByManufacturer($toy['manufacturer_id']),
            'masterToys'    => $this->catalogModel->getMasterToysByLine($toy['line_id']),
            
            'sources'       => $this->catalogModel->getSources(),
            'storages'      => $this->catalogModel->getStorageUnits(),
            
            'statuses'      => $this->toyModel->getEnumValues('collection_toys', 'acquisition_status'),
            'conditions'    => $this->toyModel->getEnumValues('collection_toys', 'condition'),
            'completeness'  => $this->toyModel->getEnumValues('collection_toys', 'completeness_grade'),
            'selected_universe' => $toy['universe_id']
        ];

        $this->view->renderPartial('add_toy_modal', $data, 'Collection');
    }

    public function update() {
        // (Uændret logik - bruger stadig ToyModel til at gemme)
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(400); // Fortæl browseren det er en fejl
            echo "Error: Missing ID";
            exit;
        }

        $parentData = [
            'master_toy_id' => $_POST['master_toy_id'],
            'is_loose' => isset($_POST['is_loose']) ? 1 : 0,
            'purchase_date' => $this->nullIfEmpty($_POST['purchase_date']),
            'purchase_price' => $this->nullIfEmpty($_POST['purchase_price']),
            'source_id' => $this->nullIfEmpty($_POST['source_id']),
            'acquisition_status' => $this->nullIfEmpty($_POST['acquisition_status']),
            'condition' => $this->nullIfEmpty($_POST['condition']),
            'completeness_grade' => $this->nullIfEmpty($_POST['completeness_grade']),
            'storage_id' => $this->nullIfEmpty($_POST['storage_id']),
            'personal_toy_id' => $this->nullIfEmpty($_POST['personal_toy_id']),
            'user_comments' => $this->nullIfEmpty($_POST['user_comments'])
        ];
        
        $this->toyModel->update($id, $parentData);

        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (isset($item['id']) && $item['id']) {
                    $childData = [
                        'mid' => $item['master_toy_item_id'],
                        'cond' => $this->nullIfEmpty($item['condition']),
                        'loose' => isset($item['is_loose']) ? 1 : 0,
                        'is_repo' => $this->nullIfEmpty($item['is_reproduction']),
                        'comments' => $this->nullIfEmpty($item['user_comments']),
                        'p_date' => $this->nullIfEmpty($item['purchase_date']),
                        'p_price' => $this->nullIfEmpty($item['purchase_price']),
                        'src_id' => $this->nullIfEmpty($item['source_id']),
                        'acq_status' => $this->nullIfEmpty($item['acquisition_status']),
                        'exp_date' => $this->nullIfEmpty($item['expected_arrival_date']),
                        'pers_id' => $this->nullIfEmpty($item['personal_item_id']),
                        'stor_id' => $this->nullIfEmpty($item['storage_id'])
                    ];
                    
                    $this->toyModel->updateItem($item['id'], $childData);
                } 
            }
        }
        
        $_GET['id'] = $id;
        $this->media_step();
        exit;
    }

    public function media_step() {
        $toyId = (int)($_GET['id'] ?? 0);
        
        $toy = $this->toyModel->getMediaStepInfo($toyId);
        if (!$toy) {
            echo '<div class="alert alert-danger m-3">Error: Toy not found. Cannot load media upload.</div>';
            exit;
        }

        $data = [
            'toy' => $toy,
            'items' => $this->toyModel->getItemsForMedia($toyId),
            'available_tags' => $this->toyModel->getMediaTags()
        ];

        $this->view->renderPartial('add_media_modal', $data, 'Collection');
    }
    
    private function nullIfEmpty($val) {
        return ($val === '' || $val === 'Select...') ? null : $val;
    }
}