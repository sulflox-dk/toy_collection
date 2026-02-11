<?php
namespace CollectionApp\Modules\Importer\Drivers;

use CollectionApp\Modules\Importer\Models\ScrapedToyDTO;
use \DOMDocument;
use \DOMXPath;

class GalacticFiguresDriver implements SiteDriverInterface {
    
    public function getSiteName(): string {
        return "Galactic Figures";
    }

    public function canHandle(string $url): bool {
        return strpos($url, 'galacticfigures.com') !== false;
    }

    public function isOverviewPage(string $url): bool {
        return strpos($url, 'type=toyline') !== false;
    }

    public function parseOverviewPage(string $url): array {
        $html = $this->fetchUrl($url);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $urls = [];
        $nodes = $xpath->query("//a[contains(@href, 'figureDetails.aspx')]");

        foreach ($nodes as $node) {
            if ($node instanceof \DOMElement) {
                $href = $node->getAttribute('href');
                if (strpos($href, 'http') === false) {
                    $href = 'https://galacticfigures.com/' . ltrim($href, '/');
                }
                $urls[] = $href;
            }
        }
        return array_unique($urls);
    }

    public function parseSinglePage(string $url): ScrapedToyDTO {
        $html = $this->fetchUrl($url);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;
        
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $dto->externalId = $params['id'] ?? md5($url);

        // --- BASIS DATA ---
        $dto->name = $this->getText($xpath, "//h1") ?: 'Unknown Toy';
        $dto->year = $this->getText($xpath, "//span[@id='yearLabel']") ?: '';
        
        // --- NY DATA MAPPING (Juster disse ID'er hvis de er tomme i testen) ---
        
        // Manufacturer (Ofte i en label der hedder manufacturerLabel)
        $dto->manufacturer = $this->getText($xpath, "//span[@id='manufacturerLabel']");

        // Toy Line (Serie)
        $dto->toyLine = $this->getText($xpath, "//a[@id='toyLineLink']"); // Ofte et link
        if (!$dto->toyLine) {
             $dto->toyLine = $this->getText($xpath, "//span[@id='toyLineLabel']");
        }

        // Assortment / SKU
        // Nogle gange står der "VC number: VC03" eller lignende
        $dto->assortmentSku = $this->getText($xpath, "//span[@id='collectionNumberLabel']"); 
        
        // Wave (Ofte svær, men prøver at finde 'Wave' i teksten)
        // Her leder vi efter en span der indeholder ordet 'Wave' hvis der ikke er et ID
        // $dto->wave = ... (Kræver nok manuel inspektion af sitet for at finde mønsteret)

        // --- ITEMS / TILBEHØR ---
        // Her prøver vi at finde sektionen "Accessories" eller "Includes"
        // Strategi: Find elementet med teksten "Accessories", hop til næste element, split ved komma
        $accessoriesText = $this->getText($xpath, "//*[@id='accessoriesLabel']");
        
        if ($accessoriesText) {
            // Split ved komma og rens
            $items = explode(',', $accessoriesText);
            foreach($items as $item) {
                $cleanItem = trim($item);
                if (!empty($cleanItem)) {
                    $dto->items[] = $cleanItem;
                }
            }
        }

        // --- BILLEDER ---
        $imgNodes = $xpath->query("//img[@id='mainImage']");
        foreach ($imgNodes as $node) {
            if ($node instanceof \DOMElement) {
                $src = $node->getAttribute('src');
                if (strpos($src, 'http') === false) {
                    $src = 'https://galacticfigures.com/' . ltrim($src, '/');
                }
                $dto->images[] = $src;
            }
        }

        return $dto;
    }

    // --- HJÆLPERE ---

    private function fetchUrl(string $url): string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $result = curl_exec($ch);
        return $result ?: '';
    }

    private function getText(DOMXPath $xpath, string $query): string {
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return '';
    }
}