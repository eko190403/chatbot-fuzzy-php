<?php
/**
 * Database Connection
 * Now uses config.php for secure credential management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../public/security.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Set charset to prevent encoding issues
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    if (APP_DEBUG) {
        die("Koneksi gagal: " . $conn->connect_error);
    } else {
        die("Database connection error. Please contact administrator.");
    }
}

// Set security headers
setSecurityHeaders();
?>
