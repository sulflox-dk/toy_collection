<?php
namespace CollectionApp\Modules\Universe\Models;

use CollectionApp\Kernel\Database;

class UniverseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Henter universer med statistik over hvor meget de bruges
     */
    public function getAllWithStats() {
        return $this->db->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM toy_lines WHERE universe_id = u.id) as line_count,
                   (SELECT COUNT(*) FROM entertainment_sources WHERE universe_id = u.id) as source_count
            FROM universes u
            ORDER BY u.sort_order ASC, u.name ASC
        ")->fetchAll();
    }

    public function create(string $name, string $slug, int $sortOrder, int $showOnDashboard) {
        // Tjek dublet
        $exists = $this->db->query("SELECT id FROM universes WHERE name = :name", ['name' => $name])->fetch();
        if ($exists) throw new \Exception("Universe '$name' already exists.");

        $sql = "INSERT INTO universes (name, slug, sort_order, show_on_dashboard) VALUES (:name, :slug, :order, :show)";
        $this->db->query($sql, [
            'name' => $name, 'slug' => $slug, 'order' => $sortOrder, 'show' => $showOnDashboard
        ]);
        return $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $slug, int $sortOrder, int $showOnDashboard) {
        $exists = $this->db->query("SELECT id FROM universes WHERE name = :name AND id != :id", ['name' => $name, 'id' => $id])->fetch();
        if ($exists) throw new \Exception("Universe '$name' already exists.");

        $sql = "UPDATE universes SET name = :name, slug = :slug, sort_order = :order, show_on_dashboard = :show WHERE id = :id";
        return $this->db->query($sql, [
            'name' => $name, 'slug' => $slug, 'order' => $sortOrder, 'show' => $showOnDashboard, 'id' => $id
        ]);
    }

    /**
     * Sletter et univers. Migrerer data hvis target ID er sat.
     */
    public function delete(int $id, ?int $migrateToId = null) {
        if ($migrateToId) {
            // 1. MIGRATE: Flyt Toy Lines
            $this->db->query("UPDATE toy_lines SET universe_id = :newId WHERE universe_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
            
            // 2. MIGRATE: Flyt Entertainment Sources
            $this->db->query("UPDATE entertainment_sources SET universe_id = :newId WHERE universe_id = :oldId", 
                ['newId' => $migrateToId, 'oldId' => $id]);
        } else {
            // HVIS SLET UDEN MIGRERING:
            // Sæt referencer til NULL (hvis databasen tillader det) eller slet (cascade).
            // I din struktur er universe_id på toy_lines nullable? Tjekker config...
            // toy_lines.universe_id er DEFAULT NULL.
            // entertainment_sources.universe_id er NOT NULL og har CASCADE delete.
            
            // For at være sikker, sætter vi toy_lines til NULL manuelt (orphan prevention)
            $this->db->query("UPDATE toy_lines SET universe_id = NULL WHERE universe_id = :id", ['id' => $id]);
            
            // Entertainment sources slettes automatisk via DB Foreign Key Cascade, 
            // men vi kan også gøre det manuelt for tydelighedens skyld
            $this->db->query("DELETE FROM entertainment_sources WHERE universe_id = :id", ['id' => $id]);
        }

        // Slet selve universet
        return $this->db->query("DELETE FROM universes WHERE id = :id", ['id' => $id]);
    }

    /**
     * Henter en simpel liste af universer (ID og Navn) til dropdowns.
     * Sorteret alfabetisk.
     */
    public function getAllSimple() {
        return $this->db->query("SELECT id, name FROM universes ORDER BY name ASC")->fetchAll();
    }
}