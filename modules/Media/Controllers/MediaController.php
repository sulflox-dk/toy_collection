<?php
namespace CollectionApp\Modules\Media\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Kernel\Config;

class MediaController extends Controller {

    public function upload() {
        header('Content-Type: application/json');

        if (!isset($_FILES['file'])) {
             echo json_encode(['success' => false, 'error' => 'No file sent to server']);
             exit;
        }

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['file']['error'];
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
            exit;
        }

        $targetContext = $_POST['target_context'] ?? '';
        $targetId      = (int)($_POST['target_id'] ?? 0);

        if (!$targetId || !$targetContext) {
            echo json_encode(['success' => false, 'error' => 'Missing ID or Context']);
            exit;
        }

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
            $this->db->query("INSERT INTO media_files (file_path, file_type) VALUES (:path, 'Image')", ['path' => $publicUrl]);
            $mediaId = $this->db->lastInsertId();

            switch ($targetContext) {
                case 'collection_parent':
                    $this->db->query("INSERT INTO collection_toy_media_map (collection_toy_id, media_file_id) VALUES (:tid, :mid)", ['tid' => $targetId, 'mid' => $mediaId]);
                    break;
                case 'collection_child':
                    $this->db->query("INSERT INTO collection_toy_item_media_map (collection_toy_item_id, media_file_id) VALUES (:iid, :mid)", ['iid' => $targetId, 'mid' => $mediaId]);
                    break;
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
        $isMain    = isset($_POST['is_main']) && $_POST['is_main'] === '1' ? 1 : 0;
        $tagIds    = isset($_POST['tags']) ? $_POST['tags'] : [];

        if (!$mediaId) {
            echo json_encode(['success' => false, 'error' => 'Missing Media ID']);
            exit;
        }

        try {
            // 1. Opdater kommentar
            $this->db->query("UPDATE media_files SET user_comment = :c WHERE id = :id", ['c' => $comment, 'id' => $mediaId]);

            // 2. Opdater Tags
            $this->db->query("DELETE FROM media_file_tags_map WHERE media_file_id = :id", ['id' => $mediaId]);
            if (!empty($tagIds) && is_array($tagIds)) {
                $sql = "INSERT INTO media_file_tags_map (media_file_id, tag_id) VALUES (:mid, :tid)";
                foreach ($tagIds as $tid) {
                    $this->db->query($sql, ['mid' => $mediaId, 'tid' => (int)$tid]);
                }
            }

            // 3. Opdater Main Image (Hvis valgt)
            if ($isMain) {
                $parentMap = $this->db->query("SELECT collection_toy_id FROM collection_toy_media_map WHERE media_file_id = :mid", ['mid' => $mediaId])->fetch();
                
                if ($parentMap) {
                    $this->db->query("UPDATE collection_toy_media_map SET is_main = 0 WHERE collection_toy_id = :tid", ['tid' => $parentMap['collection_toy_id']]);
                    $this->db->query("UPDATE collection_toy_media_map SET is_main = 1 WHERE media_file_id = :mid", ['mid' => $mediaId]);
                } 
                else {
                    $childMap = $this->db->query("SELECT collection_toy_item_id FROM collection_toy_item_media_map WHERE media_file_id = :mid", ['mid' => $mediaId])->fetch();
                    if ($childMap) {
                        $this->db->query("UPDATE collection_toy_item_media_map SET is_main = 0 WHERE collection_toy_item_id = :iid", ['iid' => $childMap['collection_toy_item_id']]);
                        $this->db->query("UPDATE collection_toy_item_media_map SET is_main = 1 WHERE media_file_id = :mid", ['mid' => $mediaId]);
                    }
                }
            }

            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}