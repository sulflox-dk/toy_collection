<?php
namespace CollectionApp\Modules\Media\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Kernel\Config;
use CollectionApp\Modules\Media\Models\MediaModel; // Husk at use Modellen

class MediaController extends Controller {

    private $mediaModel;

    public function __construct() {
        parent::__construct();
        $this->mediaModel = new MediaModel();
    }

    public function upload() {
        header('Content-Type: application/json');

        // 1. Validering af filen
        if (!isset($_FILES['file'])) {
             echo json_encode(['success' => false, 'error' => 'No file sent to server']);
             exit;
        }

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->handleUploadError($_FILES['file']['error']);
            exit;
        }

        $targetContext = $_POST['target_context'] ?? '';
        $targetId      = (int)($_POST['target_id'] ?? 0);

        if (!$targetId || !$targetContext) {
            echo json_encode(['success' => false, 'error' => 'Missing ID or Context']);
            exit;
        }

        // 2. Fil-håndtering (Brug konstanter!)
        
        // Brug den fysiske sti fra constants.php
        // Vi tilføjer en slash til sidst for en sikkerheds skyld
        $targetDir = rtrim(UPLOAD_PATH, '/') . '/'; 

        // Opret mappe hvis den mangler
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
                exit;
            }
        }

        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('img_') . '.' . $ext;
        
        // Fuld fysisk sti til filen (C:/.../assets/uploads/img_123.jpg)
        $destination = $targetDir . $newFilename;
        
        // URL til filen (Bruges i browseren og gemmes i DB)
        // Vi gemmer den relative sti (/assets/uploads/img_123.jpg)
        // Det gør det nemmere at flytte sitet til et andet domæne senere.
        // Hvis du absolut vil have fuld URL: Config::get('base_url') . UPLOADS_URI . '/' . $newFilename;
        $publicUrl = UPLOADS_URI . '/' . $newFilename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            
            // 3. Database logik
            try {
                $mediaId = $this->mediaModel->create($publicUrl, 'Image');

                // Link til den rigtige kontekst
                if ($targetContext === 'collection_parent') {
                    $this->mediaModel->linkToParent($mediaId, $targetId);
                } elseif ($targetContext === 'collection_child') {
                    $this->mediaModel->linkToChild($mediaId, $targetId);
                } elseif ($targetContext === 'catalog_parent') {
                    $this->mediaModel->linkToMasterParent($mediaId, $targetId);
                } elseif ($targetContext === 'catalog_child') {
                    $this->mediaModel->linkToMasterChild($mediaId, $targetId);
                }

                echo json_encode(['success' => true, 'file_path' => $publicUrl, 'media_id' => $mediaId]);
            } catch (\Exception $e) {
                // Hvis DB fejler, slet filen igen for at undgå 'ghost files'
                if (file_exists($destination)) unlink($destination);
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Could not move file']);
        }
        exit;
    }

    public function update_metadata() {
        header('Content-Type: application/json');
        
        $mediaId   = (int)($_POST['media_id'] ?? 0);
        $comment   = $_POST['user_comment'] ?? '';
        $isMain    = isset($_POST['is_main']) && $_POST['is_main'] === '1'; // true/false
        $tagIds    = isset($_POST['tags']) ? $_POST['tags'] : [];

        if (!$mediaId) {
            echo json_encode(['success' => false, 'error' => 'Missing Media ID']);
            exit;
        }

        try {
            // 1. Opdater kommentar
            $this->mediaModel->updateComment($mediaId, $comment);

            // 2. Opdater Tags
            $this->mediaModel->updateTags($mediaId, $tagIds);

            // 3. Opdater Main Image (Hvis valgt)
            if ($isMain) {
                $this->mediaModel->setAsMain($mediaId);
            }

            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // Hjælper til at udskrive fejlkoder (flyttet ud for læsbarhed)
    private function handleUploadError($errorCode) {
        $errorMap = [
            1 => 'File exceeds upload_max_filesize in php.ini',
            2 => 'File exceeds MAX_FILE_SIZE directive',
            3 => 'File was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload',
        ];
        $errorMessage = isset($errorMap[$errorCode]) ? $errorMap[$errorCode] : 'Unknown upload error';
        echo json_encode(['success' => false, 'error' => $errorMessage]);
    }

    public function delete() {
        header('Content-Type: application/json');
        
        $mediaId = (int)($_GET['id'] ?? 0);
        
        if (!$mediaId) {
            echo json_encode(['success' => false, 'error' => 'Missing ID']);
            exit;
        }

        try {
            // Kald modellen for at udføre sletningen
            $success = $this->mediaModel->delete($mediaId);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}