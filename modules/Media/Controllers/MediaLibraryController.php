<?php
namespace CollectionApp\Modules\Media\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Media\Models\MediaModel;

class MediaLibraryController extends Controller {

    private $mediaModel;

    public function __construct() {
        parent::__construct();
        $this->mediaModel = new MediaModel();
    }

    public function index() {
        // Hent tags til filter-dropdown
        $tags = $this->mediaModel->getMediaTags();

        // Hvis det er et AJAX kald (filtrering/side-skift), returner kun gitteret
        if (isset($_GET['ajax_grid'])) {
            $this->renderGrid();
            exit;
        }

        // Ellers vis hele siden
        $this->view->render('library', [
            'title' => 'Media Library',
            'tags' => $tags,
            'scripts' => [
                'assets/js/modules/media/media-library.js' // Vi laver denne fil om lidt
            ],
            'styles' => [
                'assets/css/modules/media-library.css' // Og denne
            ]
        ], 'Media');
    }

    // Helper til at rendere selve grid-HTML'en (bruges b�de ved load og ajax)
    private function renderGrid() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'connection' => $_GET['filter_connection'] ?? '',
            'tag_id'     => $_GET['filter_tag'] ?? '',
            'search'     => $_GET['search'] ?? ''
        ];

        $data = $this->mediaModel->getLibraryImages($filters, $page, 30); // 30 pr side

        // Send HTML tilbage (ikke JSON, men HTML fragmenter, nemmere at inds�tte)
        // Vi bruger en lille intern view-fil til grid-items
        $this->view->renderPartial('library_grid', $data, 'Media');
    }

    // Modal Details (AJAX)
    public function details() {
        $id = (int)$_GET['id'];
        $media = $this->mediaModel->getDetails($id);

        if (!$media) {
            echo "<div class='alert alert-danger'>Media not found</div>";
            exit;
        }

        // Render modal content
        $this->view->renderPartial('media_details_modal', ['media' => $media], 'Media');
    }

    public function delete_bulk() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['ids'])) {
            echo json_encode(['success' => false, 'error' => 'No IDs provided']);
            exit;
        }

        $count = 0;
        foreach ($input['ids'] as $id) {
            if ($this->mediaModel->delete((int)$id)) {
                $count++;
            }
        }

        echo json_encode(['success' => true, 'deleted' => $count]);
        exit;
    }

    public function unlink() {
        header('Content-Type: application/json');
        
        $mediaId  = (int)$_POST['media_id'];
        $targetId = (int)$_POST['target_id'];
        $context  = $_POST['context'];

        if ($this->mediaModel->removeConnection($mediaId, $context, $targetId)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Could not remove connection']);
        }
        exit;
    }
}