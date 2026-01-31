<?php
namespace CollectionApp\Kernel;

class Config {
    private static $settings = [];

    // Indlæs filen én gang ved opstart
    public static function load($path) {
        if (!file_exists($path)) {
            die("Konfigurationsfil mangler: " . $path);
        }
        self::$settings = require $path;
    }

    // Hent en værdi, f.eks. Config::get('db_host')
    public static function get($key, $default = null) {
        return isset(self::$settings[$key]) ? self::$settings[$key] : $default;
    }
}