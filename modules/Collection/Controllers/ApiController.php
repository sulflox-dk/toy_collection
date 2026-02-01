<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\CatalogModel;
use CollectionApp\Modules\Collection\Models\ToyModel;

class ApiController extends Controller {

    private $catalogModel;
    private $toyModel;

    public function __construct() {
        parent::__construct();
        // Vi instansierer CatalogModel så vi kan hente data fra databasen
        $this->catalogModel = new CatalogModel();
        $this->toyModel = new ToyModel();
    }

    /**
     * URL: &action=get_manufacturers
     */
    public function get_manufacturers() {
        $universeId = (int)($_GET['universe_id'] ?? 0);
        
        // Hent data via CatalogModel
        $data = $this->catalogModel->getManufacturersByUniverse($universeId);
        
        $this->jsonResponse($data);
    }

    /**
     * URL: &action=get_lines
     */
    public function get_lines() {
        $manufacturerId = (int)($_GET['manufacturer_id'] ?? 0);
        
        $data = $this->catalogModel->getLinesByManufacturer($manufacturerId);
        
        $this->jsonResponse($data);
    }

    /**
     * URL: &action=get_master_toys
     * (Bemærk: Denne hed før get_items, men vi har opdateret JS til at kalde get_master_toys)
     */
    public function get_master_toys() {
        $lineId = (int)($_GET['line_id'] ?? 0);
        
        $data = $this->catalogModel->getMasterToysByLine($lineId);
        
        $this->jsonResponse($data);
    }

    /**
     * URL: &action=get_master_toy_items
     * (Bemærk: Denne hed før get_toy_parts, men vi har opdateret JS)
     */
    public function get_master_toy_items() {
        $masterToyId = (int)($_GET['master_toy_id'] ?? 0);
        
        $data = $this->catalogModel->getMasterToyItems($masterToyId);
        
        $this->jsonResponse($data);
    }

    /**
     * Hjælpefunktion til at sende JSON svar korrekt tilbage
     */
    private function jsonResponse($data) {
        // Ryd evt. output buffer så vi ikke sender HTML fejl med
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function delete_media() {
        header('Content-Type: application/json');
        $mediaId = (int)($_GET['id'] ?? 0);

        if (!$mediaId) {
            echo json_encode(['success' => false, 'error' => 'Missing ID']);
            exit;
        }

        try {
            $success = $this->toyModel->deleteMedia($mediaId);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // I modules/Collection/Controllers/ToyController.php
    public function delete_item() {
        header('Content-Type: application/json');
        $itemId = (int)($_GET['id'] ?? 0);

        if (!$itemId) {
            echo json_encode(['success' => false, 'error' => 'Missing Item ID']);
            exit;
        }

        try {
            $success = $this->toyModel->deleteItem($itemId);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}