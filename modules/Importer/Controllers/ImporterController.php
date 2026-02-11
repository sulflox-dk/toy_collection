<?php
namespace CollectionApp\Modules\Importer\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Importer\Models\ImportManagerModel;
use CollectionApp\Modules\Catalog\Models\MasterToyModel;

class ImporterController extends Controller {
    private $importModel;
    private $masterToyModel;

    public function __construct() {
        parent::__construct();
        $this->importModel = new ImportManagerModel();
        $this->masterToyModel = new MasterToyModel();
    }

    public function index() {
        // Hent statistik fra vores model
        $stats = $this->importModel->getStats();

        // Forbered data til viewet (samme struktur som Dashboard)
        $data = [
            'title'   => 'Import Data',
            'stats'   => $stats,
            'scripts' => [
                'assets/js/modules/importer/importer.js'
            ]
        ];

        $this->view->render('index', $data, 'Importer');
    }

    /**
     * AJAX: Modtager URL -> Returnerer liste af fundne toys (DTOs)
     */
    public function preview() {
        header('Content-Type: application/json');
        $url = $_POST['url'] ?? '';

        if (!$url) {
            echo json_encode(['success' => false, 'error' => 'Missing URL']);
            exit;
        }

        // 1. Find Driver
        $source = $this->importModel->getSourceByUrl($url);
        if (!$source) {
            echo json_encode(['success' => false, 'error' => 'No driver found for this URL']);
            exit;
        }

        try {
            // Instancier driver dynamisk (f.eks. GalacticFiguresDriver)
            $driverClass = $source['driver_class'];
            if (!class_exists($driverClass)) {
                throw new \Exception("Driver class $driverClass not found");
            }
            $driver = new $driverClass();

            // 2. Determine Scope (Overview vs Single)
            $toysToProcess = [];
            
            if ($driver->isOverviewPage($url)) {
                $detailUrls = $driver->parseOverviewPage($url);
                // Begræns til 5 items for demo skyld
                $detailUrls = array_slice($detailUrls, 0, 5); 
                
                foreach ($detailUrls as $detailUrl) {
                    $toysToProcess[] = $driver->parseSinglePage($detailUrl);
                }
            } else {
                $toysToProcess[] = $driver->parseSinglePage($url);
            }

            // 3. Conflict Check
            $results = [];
            foreach ($toysToProcess as $dto) {
                // Konverter DTO objekt til array for nemmere JS håndtering
                $item = (array)$dto;
                
                // Tjek om vi allerede har importeret denne (via import_items)
                $linkedItem = $this->importModel->findImportItem($source['id'], $dto->externalId);
                
                if ($linkedItem) {
                    $item['status'] = 'linked'; // Allerede koblet
                    $item['existingId'] = $linkedItem['master_toy_id'];
                    $item['matchReason'] = 'External ID Match';
                } else {
                    // Tjek om navnet findes i master_toys (manuel konflikt check)
                    $existingToy = $this->masterToyModel->findByName($dto->name);
                    if ($existingToy) {
                        $item['status'] = 'conflict';
                        $item['existingId'] = $existingToy['id'];
                        $item['matchReason'] = 'Name Match';
                    } else {
                        $item['status'] = 'new';
                    }
                }
                
                // Gem source_id så vi har det til import
                $item['source_id'] = $source['id'];
                
                $results[] = $item;
            }

            echo json_encode(['success' => true, 'data' => $results, 'source' => $source['name']]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX: Udfører selve importen for valgte items
     */
    public function runImport() {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data && isset($_POST['items'])) {
             $items = json_decode($_POST['items'], true);
        } else {
             $items = $data['items'] ?? [];
        }

        if (empty($items)) {
            echo json_encode(['success' => false, 'error' => 'No items selected']);
            exit;
        }

        $successCount = 0;

        foreach ($items as $item) {
            $importItemId = null;
            
            try {
                // TRIN 1: Registrer hensigten FØRST
                // Hvis existingId findes bruger vi det, ellers NULL (ikke 0)
                $initialMasterToyId = !empty($item['existingId']) ? $item['existingId'] : null; 

                // Nu tillader databasen NULL, så denne linje virker selvom toy ikke findes endnu!
                $importItemId = $this->importModel->registerImport(
                    $item['source_id'], 
                    $initialMasterToyId, 
                    $item['externalId'], 
                    $item['externalUrl']
                );

                $masterToyId = null;
                $action = '';

                if (!empty($item['existingId'])) {
                    // UPDATE
                    $masterToyId = $item['existingId'];
                    $action = 'UPDATED';
                    
                } else {
                    // CREATE NY
                    
                    // Items mapping
                    $toyItems = [];
                    if (!empty($item['items'])) {
                        foreach ($item['items'] as $accessoryName) {
                            $toyItems[] = [
                                'subject_id' => null, 
                                'variant_description' => $accessoryName,
                                'quantity' => 1
                            ];
                        }
                    }

                    $toyData = [
                        'line_id' => 1, 
                        'product_type_id' => 1,
                        'entertainment_source_id' => 5, // Husk dit ID 5
                        
                        'name' => $item['name'],
                        'release_year' => $item['year'],
                        'wave_number' => $item['wave'] ?? '',
                        'assortment_sku' => $item['assortmentSku'] ?? '',
                        'items' => $toyItems 
                    ];
                    
                    // TRIN 2: Opret selve legetøjet
                    // Hvis denne fejler nu, så har vi allerede oprettet import_item ovenfor!
                    $masterToyId = $this->masterToyModel->create($toyData);
                    $action = 'CREATED';

                    // TRIN 3: Opdater import_item med det nye rigtige ID
                    $this->importModel->registerImport(
                        $item['source_id'], 
                        $masterToyId, 
                        $item['externalId'], 
                        $item['externalUrl']
                    );
                }

                // TRIN 4: Log succesen
                $this->importModel->log($item['source_id'], $action, $importItemId, "Imported via Frontend");
                $successCount++;

            } catch (\Exception $e) {
                // Fejlhåndtering: Nu har $importItemId en værdi (hvis DB kaldet i Trin 1 lykkedes)
                // Så nu bliver fejlen linket korrekt til elementet i listen.
                $this->importModel->log($item['source_id'], 'ERROR', $importItemId, "Failed to import " . $item['name'] . ": " . $e->getMessage());
            }
        }

        echo json_encode(['success' => true, 'count' => $successCount]);
        exit;
    }
}