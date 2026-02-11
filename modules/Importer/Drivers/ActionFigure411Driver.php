<?php
namespace CollectionApp\Modules\Importer\Drivers;

use CollectionApp\Modules\Importer\Models\ScrapedToyDTO;
use \DOMDocument;
use \DOMXPath;

class ActionFigure411Driver implements SiteDriverInterface {
    
    public function getSiteName(): string {
        return "Action Figure 411";
    }

    public function canHandle(string $url): bool {
        return strpos($url, 'actionfigure411.com') !== false;
    }

    public function isOverviewPage(string $url): bool {
        return false; 
    }

    public function parseOverviewPage(string $url): array {
        return [];
    }

    public function parseSinglePage(string $url): ScrapedToyDTO {
        $html = $this->fetchUrl($url);
        
        // FIX 1: Fjernet deprecated mb_convert_encoding.
        // I stedet bruger vi dette hack til at tvinge UTF-8 i DOMDocument:
        if (empty($html)) {
             throw new \Exception("Empty HTML returned from URL");
        }
        
        $dom = new DOMDocument();
        // Vi undertrykker warnings (@) og tilføjer xml-encoding header for at sikre UTF-8
        // LIBXML_NOERROR | LIBXML_NOWARNING skjuler HTML5 fejl som DOMDocument ikke kender
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;
        
        // ID fra URL
        $parts = explode('/', $url);
        $lastPart = end($parts);
        $dto->externalId = str_replace('.php', '', $lastPart);

        // --- NAME ---
        // Navnet står i H1 og indeholder ofte "Star Wars " som prefix
        $rawName = $this->getText($xpath, "//h1");
        $dto->name = str_replace(['Star Wars ', 'Action Figure'], '', $rawName);
        $dto->name = trim($dto->name);

        // --- DATA EXTRACTOR (REGEX) ---
        // Vi leder i hele HTML'en, men renser lidt først for at gøre matching nemmere
        // Vi leder efter mønstre som: "Year: 1983" eller "Year: <b>1983</b>"
        
        // 1. YEAR
        // Matcher "Year:" efterfulgt af evt tags/space, og så 4 cifre
        if (preg_match('/Year:.*?(\d{4})/is', $html, $matches)) {
            $dto->year = $matches[1];
        }

        // 2. SERIES (Toy Line)
        // Matcher "Series:" ... indtil næste <br> eller </div>
        if (preg_match('/Series:.*?>(.*?)<(\/a|br|\/div)/is', $html, $matches)) {
            $dto->toyLine = trim(strip_tags($matches[1]));
        }

        // 3. WAVE
        if (preg_match('/Wave:.*?>(.*?)<(\/a|br|\/div)/is', $html, $matches)) {
            $dto->wave = trim(strip_tags($matches[1]));
        }

        // 4. MANUFACTURER
        // Prøver at finde "Manufacturer: Kenner" hvis det findes, ellers gæt
        if (preg_match('/Manufacturer:.*?>(.*?)<(\/a|br|\/div)/is', $html, $matches)) {
             $dto->manufacturer = trim(strip_tags($matches[1]));
        } 
        
        // Fallback manufacturer logik hvis regex fejler
        if (empty($dto->manufacturer)) {
            if (stripos($html, 'Kenner') !== false && stripos($dto->toyLine, 'Vintage Collection') !== false) {
                 $dto->manufacturer = 'Kenner';
            } elseif (stripos($html, 'Hasbro') !== false) {
                 $dto->manufacturer = 'Hasbro';
            }
        }

        // 5. SKU / ASSORTMENT
        // AF411 har ofte ikke SKU, men de har deres eget ID. 
        // Nogle gange står der "UPC: 12345"
        if (preg_match('/UPC:.*?(\d{10,13})/is', $html, $matches)) {
            $dto->assortmentSku = $matches[1];
        }

        // 6. IMAGES
        // AF411 billeder ligger ofte i en container med class 'show-image' eller lignende
        // Eller vi leder efter billeder i /images/ mappen som ikke er ikoner
        $imgNodes = $xpath->query("//img");
        foreach ($imgNodes as $node) {
            if ($node instanceof \DOMElement) {
                $src = $node->getAttribute('src');
                
                // Filtrer irrelevante billeder fra
                if (strpos($src, 'actionfigure411-logo') !== false) continue;
                if (strpos($src, 'facebook') !== false) continue;
                
                // Tjek om det ligner et produktbillede
                if (strpos($src, '/images/') !== false || strpos($src, 'actionFigures') !== false) {
                    // Fix relative URL
                    if (strpos($src, 'http') === false) {
                        $src = 'https://www.actionfigure411.com' . $src;
                    }
                    
                    // Tilføj hvis det ikke allerede findes
                    if (!in_array($src, $dto->images)) {
                        $dto->images[] = $src;
                    }
                }
            
            }
        }
        
        // Sorter billeder: Hvis vi finder et billede der matcher ID'et, sæt det først
        // F.eks. tie-fighter-2196.jpg
        $idBase = preg_replace('/-\d+$/', '', $dto->externalId); // fjerner ID tallet til sidst
        
        usort($dto->images, function($a, $b) use ($idBase) {
            $aScore = strpos($a, $idBase) !== false ? 1 : 0;
            $bScore = strpos($b, $idBase) !== false ? 1 : 0;
            return $bScore - $aScore; // Højeste score først
        });

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
        
        // VIGTIGT: AF411 kræver nogle gange cookies eller headers
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        // Deaktiver SSL tjek lokalt
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        $result = curl_exec($ch);
        return $result ?: '';
    }
}