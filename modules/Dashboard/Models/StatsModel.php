<?php
namespace CollectionApp\Modules\Dashboard\Models;

use CollectionApp\Kernel\Database;

class StatsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDashboardStats() {
        $sql = "
            SELECT 
                u.name as universe,
                m.name as manufacturer,
                tl.name as line,
                tl.id as line_id,
                
                SUM(CASE 
                    WHEN s.type = 'Character' AND ct.acquisition_status = 'In Hand' 
                    THEN COALESCE(mti.quantity, 1) ELSE 0 
                END) as fig_owned,
                
                SUM(CASE 
                    WHEN s.type = 'Character' AND ct.acquisition_status != 'In Hand' AND ct.acquisition_status IS NOT NULL
                    THEN COALESCE(mti.quantity, 1) ELSE 0 
                END) as fig_pending,
                
                SUM(CASE 
                    WHEN s.type != 'Character' AND s.type IS NOT NULL AND ct.acquisition_status = 'In Hand' 
                    THEN COALESCE(mti.quantity, 1) ELSE 0 
                END) as other_owned,
                
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

        return $this->db->query($sql)->fetchAll();
    }

}