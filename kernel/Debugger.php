<?php
// kernel/Debugger.php
namespace CollectionApp\Kernel;

class Debugger {
    
    private static $logs = [];
    private static $queries = [];
    private static $startTime;
    private static $isInitialized = false;

    public static function init() {
        if (self::$isInitialized) return;
        
        self::$startTime = microtime(true);
        self::$isInitialized = true;

        // Tjek config
        $debugMode = Config::get('debug_mode', false);

        if ($debugMode) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            set_error_handler(function($errno, $errstr, $errfile, $errline) {
                // ... (samme fejlhåndtering som før)
                $type = match ($errno) {
                    E_WARNING => 'Warning',
                    E_NOTICE => 'Notice',
                    E_USER_ERROR => 'Error',
                    default => 'Error'
                };
                self::log("PHP $type: $errstr i " . basename($errfile) . " på linje $errline", $type);
                return false; 
            });
        }
    }

    public static function log($message, $type = 'info') {
        self::$logs[] = [
            'time' => microtime(true) - self::$startTime,
            'message' => $message,
            'type' => $type
        ];
    }

    public static function addQuery($sql, $params = [], $duration = 0, $error = null) {
        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'error' => $error
        ];
    }

    /**
     * Beregner data og inkluderer view-filen
     */
    public static function render() {
        if (!Config::get('debug_mode')) return;

        // 1. Forbered data til viewet
        $totalTime = round((microtime(true) - self::$startTime) * 1000, 2);
        
        $queries = self::$queries;
        $logs = self::$logs;
        $queryCount = count($queries);
        
        $sqlTime = 0;
        foreach($queries as $q) $sqlTime += $q['duration'];
        $sqlTime = round($sqlTime * 1000, 2);

        $errorCount = 0;
        foreach ($logs as $l) {
            if (str_contains(strtolower($l['type']), 'error')) $errorCount++;
        }

        // 2. Indlæs view-filen
        // Variablerne defineret ovenfor ($totalTime, $queries osv.) er tilgængelige i filen
        require ROOT_PATH . '/views/partials/debug_bar.php';
    }
}