<?php
// bootstrap.php
session_start();

// 1. Definer rod-stien (Anchor)
define('ROOT_PATH', __DIR__);

// 2. Load konstanter (Så vi har CONFIG_PATH, KERNEL_PATH osv.)
require_once ROOT_PATH . '/config/constants.php';

// 3. Load Autoloader
// Bemærk: Vi bruger KERNEL_PATH konstanten nu, som er defineret i constants.php
require_once KERNEL_PATH . '/Autoloader.php';
CollectionApp\Kernel\Autoloader::register();

use CollectionApp\Kernel\Config;
use CollectionApp\Kernel\Debugger;
use CollectionApp\Kernel\Database; // Tilføj denne for at kunne teste DB

// 4. Load Konfigurationsfiler
$appConfig = require CONFIG_PATH . '/app.php';
$dbConfig  = require CONFIG_PATH . '/database.php';

// Flet dem sammen til ét stort indstillings-array
$fullConfig = array_merge($appConfig, $dbConfig);

// 5. Gem konfigurationen i Config-klassen
Config::set($fullConfig);

// 6. Start Debugger (hvis aktiveret i config)
if (Config::get('debug_mode')) {
    // Sæt PHP error reporting også
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    Debugger::init(); // Din metode hed init() i din gamle kode, så vi holder fast i det
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// 7. Global Exception Handler (Valgfrit, men god praksis)
set_exception_handler(function ($e) {
    if (Config::get('debug_mode')) {
        echo "<h1>Uncaught Exception</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "<h1>Something went wrong</h1>";
        echo "<p>Please try again later.</p>";
        // Her kunne man logge fejlen til en fil i LOG_PATH
    }
});