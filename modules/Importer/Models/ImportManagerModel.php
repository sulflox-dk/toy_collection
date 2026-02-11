<?php
namespace CollectionApp\Modules\Importer\Models;

use CollectionApp\Kernel\Database;

class ImportManagerModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // =========================================================================
    // SECTION: SOURCES (Fra din gamle ImportSourceModel)
    // =========================================================================

    /**
     * Henter alle aktive kilder (Bruges f.eks. til dropdowns eller lister)
     */
    public function getAll() {
        return $this->db->query("SELECT * FROM import_sources WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
    }

    /**
     * Finder en kilde baseret på dens slug
     */
    public function getBySlug($slug) {
        return $this->db->query("SELECT * FROM import_sources WHERE slug = :slug", ['slug' => $slug])->fetch();
    }

    /**
     * NY: Finder en kilde baseret på URL'en (f.eks. finder Galactic Figures hvis url indeholder deres domæne)
     */
    public function getSourceByUrl(string $url) {
        // Vi henter alle kilder og tjekker om URL'en matcher en af dem
        $sources = $this->getAll();
        foreach ($sources as $source) {
            if (strpos($url, $source['base_url']) !== false) {
                return $source;
            }
        }
        return null;
    }

    /**
     * Statistik: Hvor mange items har vi fra hver kilde?
     */
    public function getStats() {
        return $this->db->query("
            SELECT s.id, s.name, s.base_url, COUNT(i.id) as imported_count, MAX(i.last_imported_at) as last_activity
                FROM import_sources s
                LEFT JOIN import_items i ON s.id = i.source_id
                GROUP BY s.id
        ")->fetchAll();
    }

    // =========================================================================
    // SECTION: ITEMS & LINKS (Fra din gamle ImportManagerModel)
    // =========================================================================

    /**
     * Hjælper: Tjekker om et eksternt ID allerede er importeret
     */
    public function findImportItem(int $sourceId, string $externalId) {
        return $this->db->query(
            "SELECT * FROM import_items WHERE source_id = :sid AND external_id = :eid",
            ['sid' => $sourceId, 'eid' => $externalId]
        )->fetch();
    }

    /**
     * Opretter eller opdaterer linket mellem ekstern side og vores database
     * (Dette erstatter din 'linkItem' metode, men logikken er præcis den samme)
     */
    public function registerImport(int $sourceId, ?int $masterToyId, string $externalId, string $externalUrl) {
        // Tjek om linket allerede findes
        $existing = $this->findImportItem($sourceId, $externalId);

        if ($existing) {
            // Opdater timestamp
            // Hvis masterToyId er null (fordi vi lige har startet en ny import), så lad være med at overskrive det eksisterende ID med NULL.
            // Opdater KUN hvis vi faktisk har et ID.
            $sql = "UPDATE import_items SET last_imported_at = NOW()";
            $params = ['id' => $existing['id']];

            if ($masterToyId !== null) {
                $sql .= ", master_toy_id = :mid";
                $params['mid'] = $masterToyId;
            }

            $sql .= " WHERE id = :id";
            
            $this->db->query($sql, $params);
            return $existing['id'];
        } else {
            // Opret nyt link
            $this->db->query(
                "INSERT INTO import_items (source_id, master_toy_id, external_id, external_url, last_imported_at) 
                 VALUES (:sid, :mid, :eid, :url, NOW())",
                ['sid' => $sourceId, 'mid' => $masterToyId, 'eid' => $externalId, 'url' => $externalUrl]
            );
            return $this->db->lastInsertId();
        }
    }

    // =========================================================================
    // SECTION: LOGGING (Fra din gamle ImportManagerModel)
    // =========================================================================

    public function log(int $sourceId, string $action, ?int $importItemId = null, string $message = '') {
        $this->db->query(
            "INSERT INTO import_logs (source_id, import_item_id, action, message) 
             VALUES (:sid, :iid, :act, :msg)",
            ['sid' => $sourceId, 'iid' => $importItemId, 'act' => strtoupper($action), 'msg' => $message]
        );
    }
}