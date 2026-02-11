<?php
namespace CollectionApp\Modules\Importer\Drivers;

use CollectionApp\Modules\Importer\Models\ScrapedToyDTO;
use \DOMDocument;
use \DOMXPath;

class JediTempleArchivesDriver implements SiteDriverInterface {
    
    public function getSiteName(): string {
        return "Jedi Temple Archives";
    }

    public function canHandle(string $url): bool {
        return strpos($url, 'jeditemplearchives.com') !== false;
    }

    public function isOverviewPage(string $url): bool {
        // JTA har mange typer oversigter, men vi fokuserer på enkeltsider først
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
        // UTF-8 hack
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;
        
        // ID fra URL (f.eks. Artoo-Detoo-R2-D2-Star-Wars)
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $dto->externalId = $pathInfo['filename']; // Filnavnet uden .html

        // --- NAME ---
        // Navnet står ofte i <title> eller <h1>/<h2>
        // JTA bruger ofte <title> formatet: "Review: [Navn] - [Serie]"
        $title = $this->getText($xpath, "//title");
        $dto->name = $this->cleanName($title);

        // --- DATA EXTRACTOR ---
        
        // 1. YEAR & MANUFACTURER
        // JTA har ofte en tabel eller liste med "Date First Available" eller "Release Date"
        // Vi leder efter årstal i teksten generelt, da det er mest robust her
        if (preg_match('/(19|20)\d{2}/', $html, $matches)) {
            // Find det første årstal der giver mening (ofte i copyright footer eller release date)
            // Men vi vil helst finde "Release Date: YYYY"
            if (preg_match('/Release Date:.*?(\d{4})/i', $html, $yearMatches)) {
                $dto->year = $yearMatches[1];
            } else {
                // Fallback: Hvis URL indeholder 'vintage', så er det nok 1977-1985
                if (strpos($url, 'vintage-star-wars') !== false) {
                     // Prøv at finde årstal tæt på ordet "Kenner"
                     if (preg_match('/Kenner.*?(\d{4})/s', $html, $yearMatches)) {
                         $dto->year = $yearMatches[1];
                     }
                }
            }
        }

        // 2. SERIES (Toy Line)
        // JTA er gode til at kategorisere i URL'en
        if (strpos($url, 'vintage-star-wars') !== false) {
            $dto->toyLine = 'Kenner Vintage Star Wars';
            $dto->manufacturer = 'Kenner';
        } elseif (strpos($url, 'vintage-return-of-the-jedi') !== false) {
             $dto->toyLine = 'Kenner Return of the Jedi';
             $dto->manufacturer = 'Kenner';
        } elseif (strpos($url, 'the-vintage-collection') !== false) {
             $dto->toyLine = 'The Vintage Collection';
             $dto->manufacturer = 'Hasbro';
        } elseif (strpos($url, 'the-black-series') !== false) {
             $dto->toyLine = 'The Black Series';
             $dto->manufacturer = 'Hasbro';
        }

        // 3. SKU / VC Number
        // For Vintage Collection (VC) leder vi efter "VC" nummeret i titlen eller teksten
        if (preg_match('/(VC\d{2,3})/', $title, $matches)) {
            $dto->assortmentSku = $matches[1];
            $dto->wave = $matches[1]; // Ofte bruges VC nummeret som en slags ID
        }

        // 4. ITEMS (Accessories)
        // JTA lister ofte tilbehør under "Accessories:" eller i en liste
        // Vi leder efter teksten "Accessories:" og tager det efterfølgende
        $accessoriesNode = $xpath->query("//b[contains(text(), 'Accessories:')]");
        if ($accessoriesNode->length > 0) {
            $accText = $accessoriesNode->item(0)->nextSibling->textContent ?? '';
            if (empty($accText)) {
                 $accText = $accessoriesNode->item(0)->parentNode->textContent;
                 $accText = str_replace('Accessories:', '', $accText);
            }
            
            // Rens og split
            $items = explode(',', $accText);
            foreach ($items as $item) {
                $cleanItem = trim(strip_tags($item));
                // Fjern punktummer osv.
                $cleanItem = trim($cleanItem, '.');
                if (!empty($cleanItem) && strlen($cleanItem) > 2) {
                    $dto->items[] = $cleanItem;
                }
            }
        }

        // 5. IMAGES
        // JTA har ofte billeder i en tabel eller div med class 'gallery' eller lignende
        // Vi tager alle store billeder (.jpg) der ikke er thumbnails
        $imgNodes = $xpath->query("//img");
        foreach ($imgNodes as $node) {
            if ($node instanceof \DOMElement) {    
                $src = $node->getAttribute('src');
                
                // Filtrer støj fra
                if (strpos($src, 'banner') !== false) continue;
                if (strpos($src, 'button') !== false) continue;
                if (strpos($src, 'logo') !== false) continue;
                
                // Fix relative URL
                if (strpos($src, 'http') === false) {
                    // JTA bruger ofte relative stier fra roden eller nuværende mappe
                    if (strpos($src, '/') === 0) {
                        $src = 'https://www.jeditemplearchives.com' . $src;
                    } else {
                        // Relativ til nuværende mappe (lidt tricky, men vi prøver base url)
                        $baseUrl = dirname($url);
                        $src = $baseUrl . '/' . $src;
                    }
                }
                
                // Vi vil gerne have store billeder. JTA har ofte thumbnails der slutter på _th.jpg eller lign.
                // Hvis vi finder en thumbnail, prøv at gætte det store billede
                if (strpos($src, '_th.') !== false) {
                    $largeSrc = str_replace('_th.', '.', $src);
                    if (!in_array($largeSrc, $dto->images)) {
                        $dto->images[] = $largeSrc;
                    }
                } else {
                    if (!in_array($src, $dto->images)) {
                        $dto->images[] = $src;
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
    
    private function cleanName($title) {
        // Fjern "Review", "Visual Guide", site navn osv.
        $title = str_replace(['Jedi Temple Archives', 'Review', 'Visual Guide', 'Research Droids Reviews'], '', $title);
        // Fjern alt efter " - " hvis det er en serie betegnelse
        $parts = explode(' - ', $title);
        return trim($parts[0]);
    }

    private function fetchUrl(string $url): string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // JTA kan være følsom overfor bots
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        $result = curl_exec($ch);
        return $result ?: '';
    }
}