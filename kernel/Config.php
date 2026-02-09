<?php
namespace CollectionApp\Kernel;

class Config {
    private static $settings = [];

    public static function set(array $config) {
        self::$settings = $config;
    }

    public static function load($path) {
        if (!file_exists($path)) {
            die("Konfigurationsfil mangler: " . $path);
        }
        $newSettings = require $path;

        self::$settings = array_merge(self::$settings, $newSettings);
    }

    public static function get($key, $default = null) {
        return isset(self::$settings[$key]) ? self::$settings[$key] : $default;
    }
    
    public static function all() {
        return self::$settings;
    }
}