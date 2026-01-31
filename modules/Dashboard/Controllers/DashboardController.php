<?php
namespace CollectionApp\Modules\Dashboard\Controllers;

use CollectionApp\Kernel\Controller;

class DashboardController extends Controller {

    public function index() {
        // ==========================================
        // 1. HENT STATISTIK (Uændret fra din gamle kode)
        // ==========================================
        $sql = "
            SELECT 
                u.name as universe,
                m.name as manufacturer,
                tl.name as line,
                tl.id as line_id,
                
                -- FIGURER: Ejet
                SUM(CASE 
                    WHEN s.type = 'Character' AND ct.acquisition_status = 'In Hand' 
                    THEN COALESCE(mti.quantity, 1) ELSE 0 
                END) as fig_owned,
                
                -- FIGURER: Aventer (Pre-order, Shipped etc.)
                SUM(CASE 
                    WHEN s.type = 'Character' AND ct.acquisition_status != 'In Hand' AND ct.acquisition_status IS NOT NULL
                    THEN COALESCE(mti.quantity, 1) ELSE 0 
                END) as fig_pending,
                
                -- ANDET (Køretøjer, Playsets): Ejet
                SUM(CASE 
                    WHEN s.type != 'Character' AND s.type IS NOT NULL AND ct.acquisition_status = 'In Hand' 
                    THEN COALESCE(mti.quantity, 1) ELSE 0 
                END) as other_owned,
                
                -- ANDET: Aventer
                SUM(CASE 
                    WHEN s.type != 'Character' AND s.type IS NOT NULL AND ct.acquisition_status != 'In Hand' AND ct.acquisition_status IS NOT NULL
                    THEN COALESCE(mti.quantity, 1) ELSE 0 
                END) as other_pending

            FROM toy_lines tl
            JOIN manufacturers m ON tl.manufacturer_id = m.id
            JOIN universes u ON tl.universe_id = u.id
            
            LEFT JOIN master_toys mt ON mt.line_id = tl.id
            LEFT JOIN collection_toys ct ON ct.master_toy_id = mt.id
            LEFT JOIN master_toy_items mti ON mt.id = mti.master_toy_id
            LEFT JOIN subjects s ON mti.subject_id = s.id

            WHERE u.show_on_dashboard = 1
              AND m.show_on_dashboard = 1
              AND tl.show_on_dashboard = 1
            
            GROUP BY u.id, m.id, tl.id
            ORDER BY u.name, m.name, tl.name
        ";

        $rawStats = $this->db->query($sql)->fetchAll();

        // Omstrukturér data til viewet
        $stats = [];
        foreach ($rawStats as $row) {
            $stats[$row['universe']][$row['manufacturer']][] = $row;
        }

        // ==========================================
        // 2. HENT RECENTLY ADDED (Opdateret logik)
        // ==========================================
        // Vi joiner nu manufacturers og lines for at kunne vise "Hasbro / TVC"
        $sqlRecent = "
            SELECT 
                ct.*, 
                mt.name as toy_name,
                l.name as line_name,
                m.name as manufacturer_name
            FROM collection_toys ct
            JOIN master_toys mt ON ct.master_toy_id = mt.id
            JOIN toy_lines l ON mt.line_id = l.id
            JOIN manufacturers m ON l.manufacturer_id = m.id
            ORDER BY ct.id DESC 
            LIMIT 20
        ";
        
        $recentToys = $this->db->query($sqlRecent)->fetchAll();

        // ==========================================
        // 3. SEND DATA TIL VIEWET
        // ==========================================
        $data = [
            'title'      => 'Dashboard',
            'stats'      => $stats,
            'recentToys' => $recentToys, // Bemærk navneændring fra recentItems til recentToys for tydelighed
            'scripts'    => [
                'assets/js/collection.js',
                'assets/js/dashboard.js'
            ]
        ];

        // Vi renderer stadig 'index' viewet (som indeholder dashboard koden)
        $this->view->render('index', $data, 'Dashboard');
    }
}