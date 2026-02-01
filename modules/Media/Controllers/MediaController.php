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

        // 1. Validering af filen (Controller logik)
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

        // 2. Fil-håndtering (Flyt filen på disken)
        $uploadPath = Config::get('upload_path', 'assets/uploads/');
        $baseUrl    = Config::get('base_url');
        $uploadDir  = Config::get('upload_dir', 'assets/uploads/');

        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true)) {
                echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
                exit;
            }
        }

        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('img_') . '.' . $ext;
        $destination = $uploadPath . $newFilename;
        $publicUrl   = $baseUrl . $uploadDir . $newFilename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            
            // 3. Database logik (Nu via Model)
            $mediaId = $this->mediaModel->create($publicUrl, 'Image');

            // Link til den rigtige kontekst
            if ($targetContext === 'collection_parent') {
                $this->mediaModel->linkToParent($mediaId, $targetId);
            } elseif ($targetContext === 'collection_child') {
                $this->mediaModel->linkToChild($mediaId, $targetId);
            }

            echo json_encode(['success' => true, 'file_path' => $publicUrl, 'media_id' => $mediaId]);
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