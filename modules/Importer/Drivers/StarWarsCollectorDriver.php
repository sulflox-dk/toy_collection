<?php
namespace CollectionApp\Modules\Importer\Drivers;

use CollectionApp\Modules\Importer\Models\ScrapedToyDTO;
use \DOMDocument;
use \DOMXPath;

class StarWarsCollectorDriver implements SiteDriverInterface {
    
    public function getSiteName(): string {
        return "Star Wars Collector";
    }

    public function canHandle(string $url): bool {
        return strpos($url, 'starwarscollector.com') !== false;
    }

    public function isOverviewPage(string $url): bool {
        // Enkelt-sider slutter ofte på /vcXX-navn/ eller lignende.
        // Oversigtssider er ofte kortere eller har 'category' i URL (hvis WP).
        // Men den nemmeste test er: Har siden "Figure Includes" eller "Year Released"?
        // Vi antager false som default og lader brugeren styre det via "Analyze".
        // Men hvis URL slutter på / (som kategorier gør), kan det være begge dele.
        // Vi satser på at de URL'er du gav er enkelt-sider.
        return false; 
    }

    public function parseOverviewPage(string $url): array {
        return [];
    }

    public function parseSinglePage(string $url): ScrapedToyDTO {
        $html = $this->fetchUrl($url);
        
        if (empty($html)) {
             throw new \Exception("Empty HTML returned from URL");
        }

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;
        
        // ID fra URL (f.eks. vc01-dengar)
        // URL slutter ofte på /
        $path = parse_url($url, PHP_URL_PATH);
        $path = rtrim($path, '/');
        $parts = explode('/', $path);
        $dto->externalId = end($parts); 

        // --- 1. NAVN ---
        // Titel er ofte: "The Vintage Collection - VC01: Dengar"
        $rawTitle = $this->getText($xpath, "//h1");
        
        // Rens navnet: Fjern alt før kolonet hvis det findes
        if (strpos($rawTitle, ':') !== false) {
            $nameParts = explode(':', $rawTitle);
            $dto->name = trim(end($nameParts)); // Tag delen efter kolon
        } else {
            $dto->name = $rawTitle;
        }
        
        // Fallback: Hvis navnet stadig indeholder "The Vintage Collection", fjern det
        $dto->name = str_ireplace(['The Vintage Collection - ', 'The Vintage Collection'], '', $dto->name);
        $dto->name = trim($dto->name, ' -');

        // --- 2. DATA FIELDS (REGEX) ---
        // Vi henter hele teksten fra indholdet for at søge i den
        $content = $this->getText($xpath, "//div[contains(@class, 'entry-content')]"); // WordPress standard
        if (empty($content)) {
            $content = $this->getText($xpath, "//body");
        }

        // A. Year Released
        if (preg_match('/Year Released:\s*(\d{4})/i', $content, $matches)) {
            $dto->year = $matches[1];
        }

        // B. Figure # (Bruges som Wave Number / ID)
        if (preg_match('/Figure #:\s*(.*?)(?=\n|$)/i', $content, $matches)) {
            $dto->wave = trim($matches[1]);
        }

        // C. Assortment / SKU
        if (preg_match('/Assortment:\s*(.*?)(?=\n|$)/i', $content, $matches)) {
            $dto->assortmentSku = trim($matches[1]);
        }
        
        // D. Toy Line (Fra URL eller Titel)
        if (stripos($url, 'vintage-collection') !== false) {
            $dto->toyLine = 'The Vintage Collection';
            $dto->manufacturer = 'Hasbro';
        } elseif (stripos($url, 'black-series') !== false) {
            $dto->toyLine = 'The Black Series';
             $dto->manufacturer = 'Hasbro';
        } else {
            $dto->manufacturer = 'Hasbro'; // Default for dette site
        }

        // --- 3. ITEMS / ACCESSORIES ---
        // Sektionen starter med "Figure Includes:" og indeholder bullets (•)
        // Vi finder elementet der indeholder "Figure Includes"
        // Dette er lidt tricky med XPath da teksten kan være blandet.
        
        // Vi prøver Regex på content først, da det er struktureret tekst
        if (preg_match('/Figure Includes:(.*?)(?=(Navigation|Home|About Us|$))/is', $content, $matches)) {
            $itemsBlock = $matches[1];
            // Split ved bullets eller newlines
            $lines = preg_split('/(•|\n)/', $itemsBlock);
            foreach ($lines as $line) {
                $cleanItem = trim($line);
                if (!empty($cleanItem) && strlen($cleanItem) > 2) {
                    $dto->items[] = $cleanItem;
                }
            }
        }

        // --- 4. BILLEDER ---
        // SWC bruger ofte gallerier. Vi leder efter billeder i content.
        $imgNodes = $xpath->query("//div[contains(@class, 'entry-content')]//img");
        
        foreach ($imgNodes as $node) {
        if ($node instanceof \DOMElement) {     
            $src = $node->getAttribute('src');
            
            // Filtrer støj
            if (strpos($src, 'logo') !== false) continue;
            if (strpos($src, 'facebook') !== false) continue;
            
            // Fix URL
            if (strpos($src, 'http') === false) {
                $src = 'https://starwarscollector.com' . $src;
            }

            // Hent store billeder (fjern -150x150 osv hvis WP bruger det)
            $cleanSrc = preg_replace('/-\d+x\d+(?=\.(jpg|png|jpeg))/i', '', $src);
            
            if (!in_array($cleanSrc, $dto->images)) {
                $dto->images[] = $cleanSrc;
            }
        }
        }

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