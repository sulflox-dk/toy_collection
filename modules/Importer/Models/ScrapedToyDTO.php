<?php
namespace CollectionApp\Modules\Importer\Models;

class ScrapedToyDTO {
    public string $externalId;
    public string $externalUrl;
    public string $name;
    public string $description = '';
    public string $manufacturer = '';
    public string $year = '';
    
    // NYE FELTER
    public string $toyLine = '';      // F.eks. "The Vintage Collection"
    public string $wave = '';         // F.eks. "Wave 4"
    public string $assortmentSku = ''; // F.eks. "VC123" eller stregkode
    public array $items = [];         // Liste af tilbehør (Strings, f.eks. "Blaster", "Helmet")
    
    public array $images = [];
    public string $status = 'new';
    public ?int $existingId = null;
    public string $matchReason = '';  // Tilføjet for at fikse advarsel i frontend
}