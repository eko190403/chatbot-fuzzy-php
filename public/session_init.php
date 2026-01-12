<?php
/**
 * Session Initialization
 * Call this BEFORE session_start() to configure session settings
 */

// Load config if not already loaded
if (!defined('SESSION_LIFETIME')) {
    require_once __DIR__ . '/config.php';
}

// Set session configuration (must be before session_start)
if (session_status() === PHP_SESSION_NONE) {
    // Set session save path
    $session_path = sys_get_temp_dir();
    if (is_writable($session_path)) {
        ini_set('session.save_path', $session_path);
    }
    
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    // Disable strict mode for better compatibility
    ini_set('session.use_strict_mode', 0);
}
