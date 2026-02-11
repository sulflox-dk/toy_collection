<?php
namespace CollectionApp\Modules\Importer\Drivers;

use CollectionApp\Modules\Importer\Models\ScrapedToyDTO;
use \DOMDocument;
use \DOMXPath;

class GalacticCollectorDriver implements SiteDriverInterface {
    
    public function getSiteName(): string {
        return "Galactic Collector";
    }

    public function canHandle(string $url): bool {
        return strpos($url, 'galacticcollector.com') !== false;
    }

    public function isOverviewPage(string $url): bool {
        // Vi fokuserer på figursider nu (URL indeholder /fig/)
        return strpos($url, '/fig/') === false; 
    }

    public function parseOverviewPage(string $url): array {
        return []; // Ikke implementeret endnu
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
        
        // ID fra URL
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $dto->externalId = $pathInfo['filename']; 

        // --- 1. HOVEDFIGUR DATA ---
        
        // Navn (H1)
        $dto->name = $this->getText($xpath, "//h1");

        // Årstal
        // Galactic Collector skriver ofte "Year: 1983" i teksten
        $content = $this->getText($xpath, "//body");
        if (preg_match('/Year:.*?(\d{4})/i', $content, $matches)) {
            $dto->year = $matches[1];
        }

        // Toy Line & Manufacturer (GC er primært Vintage Kenner)
        $dto->toyLine = 'Kenner Vintage Star Wars';
        $dto->manufacturer = 'Kenner';
        // Justering baseret på årstal
        if ($dto->year >= 1983) $dto->toyLine = 'Kenner Return of the Jedi';
        if ($dto->year <= 1979) $dto->toyLine = 'Kenner Star Wars';
        if ($dto->year >= 1980 && $dto->year <= 1982) $dto->toyLine = 'Kenner Empire Strikes Back';

        // Hovedbillede
        $mainImg = $xpath->query("//img[contains(@class, 'main-image')]"); // Gæt på class, ellers tager vi første store
        if ($mainImg->length == 0) {
            $mainImg = $xpath->query("//div[contains(@class, 'figure-image')]//img");
        }
        
        if ($mainImg->length > 0) {
            $mainImgItem0 = $mainImg->item(0);
            if ($mainImgItem0  instanceof \DOMElement) {
            $src = $mainImgItem0->getAttribute('src');
            $dto->images[] = $this->fixUrl($src);
            }
        }

        // --- 2. DYK NED I ACCESSORIES (DEEP SCRAPE) ---
        
        // Find links til accessories. De ligger typisk i /acc/ mappen
        $accLinks = $xpath->query("//a[contains(@href, '/acc/')]");
        
        // Brug et array til at undgå dubletter (samme link kan forekomme flere gange)
        $processedAccUrls = [];

        foreach ($accLinks as $link) {
        if ($link instanceof \DOMElement) {    
            $href = $link->getAttribute('href');
            $accUrl = $this->fixUrl($href);
            
            if (in_array($accUrl, $processedAccUrls)) continue;
            $processedAccUrls[] = $accUrl;

            // HENT UNDERSIDEN (Det her tager lidt tid pr. figur)
            $subHtml = $this->fetchUrl($accUrl);
            if ($subHtml) {
                $subDom = new DOMDocument();
                @$subDom->loadHTML('<?xml encoding="UTF-8">' . $subHtml, LIBXML_NOERROR | LIBXML_NOWARNING);
                $subXpath = new DOMXPath($subDom);

                // A. Navn på tilbehør (H1 på undersiden)
                $accName = $this->getText($subXpath, "//h1");
                
                // Rens navn (fjern figurens navn hvis det står der, f.eks. "Bib Fortuna Staff" -> "Staff")
                // Det er en smagssag, men ofte pænere.
                // $accName = str_ireplace($dto->name, '', $accName);
                $accName = trim($accName);

                if (!empty($accName)) {
                    $dto->items[] = $accName;
                }

                // B. Billede af tilbehør
                // Vi leder efter det primære billede på undersiden
                $accImgNodes = $subXpath->query("//img");
                // Ofte er det største billede eller det første i content
                foreach ($accImgNodes as $node) {
                if ($node instanceof \DOMElement) {    
                    $src = $node->getAttribute('src');
                    // Simpel filter: Hvis det indeholder ordet fra accessory URL'en, er det nok det rigtige
                    $urlParts = explode('/', $accUrl);
                    $slug = end($urlParts);
                    
                    if (strpos($src, $slug) !== false || strpos($src, 'images') !== false) {
                        $fullSrc = $this->fixUrl($src);
                        // Tilføj til hovedlisten af billeder
                        if (!in_array($fullSrc, $dto->images)) {
                            $dto->images[] = $fullSrc;
                        }
                        break; // Tag kun ét billede pr. accessory for ikke at spamme
                    }
                }
                }
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

    private function fixUrl($url) {
        if (strpos($url, 'http') === false) {
            return 'https://galacticcollector.com' . $url;
        }
        return $url;
    }

    private function fetchUrl(string $url): string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        // Timeout er vigtig her, hvis vi henter mange undersider
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
        
        $result = curl_exec($ch);
        return $result ?: '';
    }
}