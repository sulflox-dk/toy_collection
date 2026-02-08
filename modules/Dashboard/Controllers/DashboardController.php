<?php
namespace CollectionApp\Modules\Dashboard\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Dashboard\Models\StatsModel;
use CollectionApp\Modules\Collection\Models\ToyModel;

class DashboardController extends Controller {

    public function index() {
        // Opret instans af den nye Model
        $statsModel = new StatsModel();
        $toyModel = new ToyModel();

        // 1. HENT STATISTIK (Nu via model)
        $rawStats = $statsModel->getDashboardStats();

        // Omstrukturér data til viewet
        $stats = [];
        foreach ($rawStats as $row) {
            $stats[$row['universe']][$row['manufacturer']][] = $row;
        }

        // 2. HENT RECENTLY ADDED (Nu via model)
        // Vi beder om sortering 'newest' og henter side 1 med 10 resultater
        $recentToys = $toyModel->getFiltered(['sort' => 'newest'], 1, 12);

        $viewMode = $_COOKIE['collection_view_mode'] ?? 'list';

        // 3. SEND DATA TIL VIEWET
        $data = [
            'title'             => 'Dashboard',
            'stats'             => $stats,
            'recentToys'        => $recentToys,
            'viewMode'          => $viewMode,
            'scripts'    => [
                'assets/js/collection-core.js',
                'assets/js/collection-form.js',
                'assets/js/collection_manager.js',
                'assets/js/collection-media.js',
                'assets/js/dashboard.js'
            ]
        ];

        $this->view->render('index', $data, 'Dashboard');
    }
}