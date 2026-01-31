<?php
// kernel/Autoloader.php
namespace CollectionApp\Kernel;

class Autoloader {
    public static function register() {
        spl_autoload_register(function ($class) {
            
            // Project-specific namespace prefix
            $prefix = 'CollectionApp\\';

            // Base directory for the namespace prefix
            $base_dir = ROOT_PATH . '/';

            // Does the class use the prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            // Get the relative class name
            $relative_class = substr($class, $len);

            // Replace namespace separators with directory separators
            // e.g. "Modules\Catalog\Controllers\ToyController" 
            // becomes "modules/Catalog/Controllers/ToyController.php"
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            // Fix case sensitivity for "modules" and "kernel" folders if needed
            // (Windows is case-insensitive, Linux is strict. We assume lowercase folder names)
            $file = str_replace('Modules/', 'modules/', $file);
            $file = str_replace('Kernel/', 'kernel/', $file);

            if (file_exists($file)) {
                require $file;
            }
        });
    }
}