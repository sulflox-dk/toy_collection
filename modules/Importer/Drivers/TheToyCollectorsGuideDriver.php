<?php
namespace CollectionApp\Modules\Importer\Drivers;

use CollectionApp\Modules\Importer\Models\ScrapedToyDTO;
use \DOMDocument;
use \DOMXPath;

class TheToyCollectorsGuideDriver implements SiteDriverInterface {
    
    public function getSiteName(): string {
        return "The Toy Collectors Guide";
    }

    public function canHandle(string $url): bool {
        return strpos($url, 'thetoycollectorsguide.com') !== false;
    }

    public function isOverviewPage(string $url): bool {
        // På dette site er kategorisiderne = oversigtssider
        // Hvis der IKKE er en #hash i URL'en, behandler vi den som en oversigt
        return strpos($url, '#item-') === false;
    }

    public function parseOverviewPage(string $url): array {
        $html = $this->fetchUrl($url);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $urls = [];
        
        // Vi finder alle billeder i indholdet, som ser ud til at være figurer
        // Vi filtrerer små ikoner og layout-billeder fra
        $nodes = $xpath->query("//div[contains(@class, 'entry-content')]//img");

        $count = 0;
        foreach ($nodes as $node) {
        if ($node instanceof \DOMElement) {     
            $src = $node->getAttribute('src');
            $width = $node->getAttribute('width');
            
            // Filtrer støj fra (logoer, små ikoner, etc.)
            if ($width && $width < 100) continue; 
            if (strpos($src, 'logo') !== false) continue;
            
            // Vi laver en "fake" URL, der peger på dette specifikke item-index
            // Controlleren tror det er en ny side, men vi ved bedre.
            $urls[] = $url . '#item-' . $count;
            $count++;
        }
        }

        return $urls; // Returnerer f.eks. 20 "links" der alle er den samme side med forskelligt index
    }

    public function parseSinglePage(string $url): ScrapedToyDTO {
        // 1. Udpak Index fra URL'en (#item-X)
        $fragment = parse_url($url, PHP_URL_FRAGMENT); // "item-5"
        // Sikkerhedstjek: Hvis fragment mangler, start ved 0
        $index = $fragment ? (int)str_replace('item-', '', $fragment) : 0;
        
        // 2. Hent selve siden (uden # delen)
        $cleanUrl = strtok($url, '#');
        $html = $this->fetchUrl($cleanUrl);
        
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;
        
        // Find billeder igen
        $nodes = $xpath->query("//div[contains(@class, 'entry-content')]//img");
        
        $validNodes = [];
        foreach ($nodes as $node) {
            if ($node instanceof \DOMElement) { 
            $w = $node->getAttribute('width');
            // Samme filter som i parseOverviewPage
            if (($w && $w < 100) || strpos($node->getAttribute('src'), 'logo') !== false) continue;
            $validNodes[] = $node;
            }
        }

        // Tjek om index findes
        if (!isset($validNodes[$index])) {
            // Fallback hvis indekset er skævt (f.eks. hvis siden har ændret sig)
            if (count($validNodes) > 0) {
                $targetImgNode = $validNodes[0];
            } else {
                throw new \Exception("Could not find any valid item on page for index $index");
            }
        } else {
            $targetImgNode = $validNodes[$index];
        }

        // --- DATA EKSTRAKTION ---
        
        // 1. BILLEDER
        $src = $targetImgNode->getAttribute('src');
        $cleanSrc = preg_replace('/-\d+x\d+(?=\.(jpg|png|jpeg))/i', '', $src);
        $dto->images[] = $cleanSrc;

        // 2. NAVN (FIXET: Altid sæt en værdi!)
        $altText = $targetImgNode->getAttribute('alt');
        
        if (!empty($altText)) {
            $dto->name = $altText;
        } else {
            // FALLBACK: Brug filnavnet hvis ALT tekst mangler
            // F.eks. "http.../luke-skywalker.jpg" -> "luke-skywalker"
            $filename = pathinfo($src, PATHINFO_FILENAME);
            // Rens filnavnet lidt (fjern bindestreger)
            $filename = str_replace(['-', '_'], ' ', $filename);
            // Fjern dimensioner igen hvis de sneg sig med
            $filename = preg_replace('/\d+x\d+/', '', $filename);
            $dto->name = ucwords($filename);
        }
        
        // Sikkerhedsnet: Hvis navnet STADIG er tomt (meget usandsynligt)
        if (empty($dto->name)) {
            $dto->name = "Unknown Item #" . ($index + 1);
        }

        // 3. ID
        // Nu hvor $dto->name har en værdi, crasher denne linje ikke
        $dto->externalId = md5($cleanUrl . '|' . $index . '|' . $dto->name);

        // 4. MERE INFO (Year, Manufacturer)
        $pageTitle = $this->getText($xpath, "//h1");
        $dto->toyLine = str_replace('The Toy Collectors Guide', '', $pageTitle);
        $dto->toyLine = trim($dto->toyLine);

        if (preg_match('/(19|20)\d{2}/', $dto->toyLine, $matches)) {
            $dto->year = $matches[0];
        } else {
            // Hvis ikke i titlen, så prøv at finde årstal i content
             $content = $this->getText($xpath, "//div[contains(@class, 'entry-content')]");
             if (preg_match('/(19|20)\d{2}/', $content, $matches)) {
                 $dto->year = $matches[0];
             }
        }

        // Rengøring af navn
        $dto->name = str_replace(['Image', 'Photo'], '', $dto->name);
        $dto->name = trim($dto->name);

        return $dto;
    }

    private function getText(DOMXPath $xpath, string $query): string {
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return '';
    }

    private function fetchUrl(string $url): string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        return $result ?: '';
    }
}