<?php
/**
 * Configuration file - loads environment variables
 */

// Load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found at: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            // Set as environment variable and in $_ENV
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Load environment variables
loadEnv(__DIR__ . '/.env');

// Helper function to get env variables with default
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    
    // Convert boolean strings
    if (strtolower($value) === 'true') return true;
    if (strtolower($value) === 'false') return false;
    if (strtolower($value) === 'null') return null;
    
    return $value;
}

// Database configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'chat_system'));

// WebSocket configuration
define('WS_HOST', env('WS_HOST', 'localhost'));
define('WS_PORT', env('WS_PORT', '8081'));

// Security configuration
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 86400));
define('CSRF_TOKEN_NAME', env('CSRF_TOKEN_NAME', 'csrf_token'));

// Application configuration
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', false));

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}
