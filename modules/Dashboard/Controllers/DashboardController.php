<?php
namespace CollectionApp\Modules\Dashboard\Controllers;

use CollectionApp\Kernel\Controller;
use CollectionApp\Modules\Dashboard\Models\StatsModel;

class DashboardController extends Controller {

    public function index() {
        // Opret instans af den nye Model
        $statsModel = new StatsModel();

        // 1. HENT STATISTIK (Nu via model)
        $rawStats = $statsModel->getDashboardStats();

        // Omstrukturér data til viewet
        $stats = [];
        foreach ($rawStats as $row) {
            $stats[$row['universe']][$row['manufacturer']][] = $row;
        }

        // 2. HENT RECENTLY ADDED (Nu via model)
        $recentToys = $statsModel->getRecentAdditions(20);

        // 3. SEND DATA TIL VIEWET
        $data = [
            'title'      => 'Dashboard',
            'stats'      => $stats,
            'recentToys' => $recentToys,
            'scripts'    => [
                'assets/js/collection.js',
                'assets/js/dashboard.js'
            ]
        ];

        $this->view->render('index', $data, 'Dashboard');
    }
}