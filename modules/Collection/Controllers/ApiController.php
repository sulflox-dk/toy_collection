<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Catalog\Models\CatalogModel;

class ApiController extends Controller {

    private $catalogModel;

    public function __construct() {
        parent::__construct();
        // Vi instansierer CatalogModel så vi kan hente data fra databasen
        $this->catalogModel = new CatalogModel();
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
}