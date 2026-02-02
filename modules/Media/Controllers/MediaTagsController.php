<?php
namespace CollectionApp\Modules\Media\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Media\Models\MediaModel;

class MediaTagsController extends Controller {

    private $mediaModel;

    public function __construct() {
        parent::__construct();
        $this->mediaModel = new MediaModel();
    }

    public function index() {
        $tags = $this->mediaModel->getTagsWithCounts();
        
        $this->view->render('tags_index', [
            'title' => 'Media Tags',
            'tags' => $tags,
            'scripts' => ['assets/js/media_tags.js'] // Vi opretter denne om lidt
        ], 'Media');
    }

    public function store() {
        $this->jsonHandler(function() {
            $name = trim($_POST['tag_name'] ?? '');
            if (empty($name)) throw new \Exception("Tag name is required");
            $this->mediaModel->createTag($name);
            return ['success' => true];
        });
    }

    public function update() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $name = trim($_POST['tag_name'] ?? '');
            if (!$id || empty($name)) throw new \Exception("Invalid data");
            $this->mediaModel->updateTag($id, $name);
            return ['success' => true];
        });
    }

    public function delete() {
        $this->jsonHandler(function() {
            $id = (int)$_POST['id'];
            $migrateTo = !empty($_POST['migrate_to_id']) ? (int)$_POST['migrate_to_id'] : null;
            
            if (!$id) throw new \Exception("Missing ID");
            
            $this->mediaModel->deleteTag($id, $migrateTo);
            return ['success' => true];
        });
    }

    // Helper til JSON responses
    private function jsonHandler(callable $callback) {
        header('Content-Type: application/json');
        try {
            echo json_encode($callback());
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}