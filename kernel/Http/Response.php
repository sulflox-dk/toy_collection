<?php
namespace CollectionApp\Kernel\Http;

/**
 * Response Helper
 * Provides standardized JSON response formatting
 */
class Response {
    /**
     * Send JSON response
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send success response
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     */
    public static function success($data = null, string $message = '', int $statusCode = 200): void {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send error response
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional error details
     */
    public static function error(string $message, int $statusCode = 400, array $errors = []): void {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Send validation error response
     * @param array $errors Validation errors
     * @param string $message Error message
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void {
        self::error($message, 422, $errors);
    }

    /**
     * Send not found response
     * @param string $message Not found message
     */
    public static function notFound(string $message = 'Resource not found'): void {
        self::error($message, 404);
    }

    /**
     * Send server error response
     * @param string $message Error message
     */
    public static function serverError(string $message = 'Internal server error'): void {
        self::error($message, 500);
    }

    /**
     * Send HTML response
     * @param string $html HTML content
     */
    public static function html(string $html): void {
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }
}
