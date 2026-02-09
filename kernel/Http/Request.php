<?php
namespace CollectionApp\Kernel\Http;

/**
 * Request Helper
 * Provides convenient access to request data
 */
class Request {
    /**
     * Get value from $_GET
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get value from $_POST
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get all $_GET parameters
     * @return array
     */
    public static function all(): array {
        return $_GET;
    }

    /**
     * Get all $_POST parameters
     * @return array
     */
    public static function postAll(): array {
        return $_POST;
    }

    /**
     * Check if parameter exists in $_GET
     * @param string $key Parameter key
     * @return bool
     */
    public static function has(string $key): bool {
        return isset($_GET[$key]);
    }

    /**
     * Check if parameter exists in $_POST
     * @param string $key Parameter key
     * @return bool
     */
    public static function hasPost(string $key): bool {
        return isset($_POST[$key]);
    }

    /**
     * Get request method
     * @return string
     */
    public static function method(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request is POST
     * @return bool
     */
    public static function isPost(): bool {
        return self::method() === 'POST';
    }

    /**
     * Check if request is GET
     * @return bool
     */
    public static function isGet(): bool {
        return self::method() === 'GET';
    }

    /**
     * Check if request is AJAX
     * @return bool
     */
    public static function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get integer parameter
     * @param string $key Parameter key
     * @param int $default Default value
     * @return int
     */
    public static function int(string $key, int $default = 0): int {
        $value = $_GET[$key] ?? $_POST[$key] ?? $default;
        return (int)$value;
    }

    /**
     * Get string parameter (trimmed)
     * @param string $key Parameter key
     * @param string $default Default value
     * @return string
     */
    public static function string(string $key, string $default = ''): string {
        $value = $_GET[$key] ?? $_POST[$key] ?? $default;
        return trim((string)$value);
    }

    /**
     * Get array parameter
     * @param string $key Parameter key
     * @param array $default Default value
     * @return array
     */
    public static function array(string $key, array $default = []): array {
        $value = $_GET[$key] ?? $_POST[$key] ?? $default;
        return is_array($value) ? $value : $default;
    }
}
