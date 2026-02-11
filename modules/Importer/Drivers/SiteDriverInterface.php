<?php
namespace CollectionApp\Modules\Importer\Drivers;

use CollectionApp\Modules\Importer\Models\ScrapedToyDTO;

interface SiteDriverInterface {
    /**
     * Returns the name of the site (e.g. "Galactic Figures")
     */
    public function getSiteName(): string;

    /**
     * Determines if this driver can handle the given URL
     */
    public function canHandle(string $url): bool;

    /**
     * Returns true if the URL is a "List/Overview" page, false if it's a single item
     */
    public function isOverviewPage(string $url): bool;

    /**
     * Parses a single detail page and returns one Toy DTO
     */
    public function parseSinglePage(string $url): ScrapedToyDTO;

    /**
     * Parses an overview page and returns a list of detail URLs
     * (The core will then call parseSinglePage for each of them)
     * @return string[] Array of URLs
     */
    public function parseOverviewPage(string $url): array;
}