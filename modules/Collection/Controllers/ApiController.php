<?php
namespace CollectionApp\Modules\Collection\Controllers;

use CollectionApp\Kernel\Controller;

class ApiController extends Controller {

    public function get_manufacturers() {
        $universeId = (int)($_GET['universe_id'] ?? 0);
        
        $sql = "SELECT DISTINCT m.id, m.name 
                FROM manufacturers m
                JOIN toy_lines tl ON m.id = tl.manufacturer_id
                WHERE tl.universe_id = :uid
                AND m.show_on_dashboard = 1 
                ORDER BY m.name ASC";
                
        $data = $this->db->query($sql, ['uid' => $universeId])->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function get_lines() {
        $manId = (int)($_GET['manufacturer_id'] ?? 0);
        $uniId = (int)($_GET['universe_id'] ?? 0);

        $sql = "SELECT id, name 
                FROM toy_lines 
                WHERE manufacturer_id = :mid 
                AND universe_id = :uid
                AND show_on_dashboard = 1
                ORDER BY name ASC";

        $data = $this->db->query($sql, ['mid' => $manId, 'uid' => $uniId])->fetchAll();

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * NY: Hent Items (Master Toys) baseret på Linje ID
     */
    public function get_items() {
        $lineId = (int)($_GET['line_id'] ?? 0);

        $sql = "SELECT id, name 
                FROM master_toys 
                WHERE line_id = :lid 
                ORDER BY name ASC";

        $data = $this->db->query($sql, ['lid' => $lineId])->fetchAll();

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * NY: Hent Master Toy Items (Dele definitioner) baseret på Master Toy ID
     * Bruges til at fylde dropdowns i børne-rækkerne (fx "Lightsaber", "Figure")
     */
    public function get_toy_parts() {
        $toyId = (int)($_GET['master_toy_id'] ?? 0);

        // Hent delene og join med subjects for at få navnet
        $sql = "SELECT mti.id, s.name, s.type, mti.quantity
                FROM master_toy_items mti
                JOIN subjects s ON mti.subject_id = s.id
                WHERE mti.master_toy_id = :tid
                ORDER BY s.type = 'Character' DESC, s.name ASC"; // Figur først, så resten

        $data = $this->db->query($sql, ['tid' => $toyId])->fetchAll();

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}