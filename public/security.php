<?php
/**
 * Security Helper Functions
 */

// CSRF Protection
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrfMeta() {
    $token = generateCsrfToken();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// Check CSRF token in POST requests
function checkCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!verifyCsrfToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh the page and try again.');
        }
    }
}

// XSS Protection
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function escapeJs($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

// Security Headers
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (adjust as needed)
    if (APP_ENV === 'production') {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net;");
    }
    
    // HSTS (only in production with HTTPS)
    if (APP_ENV === 'production' && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Rate Limiting (simple file-based)
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $rateDir = __DIR__ . '/logs/rate_limit';
    if (!is_dir($rateDir)) {
        @mkdir($rateDir, 0755, true);
    }
    
    $file = $rateDir . '/' . md5($identifier) . '.txt';
    $now = time();
    
    // Read existing attempts
    $attempts = [];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $attempts = $content ? json_decode($content, true) : [];
    }
    
    // Remove old attempts
    $attempts = array_filter($attempts, function($timestamp) use ($now, $timeWindow) {
        return ($now - $timestamp) < $timeWindow;
    });
    
    // Check if limit exceeded
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    // Add new attempt
    $attempts[] = $now;
    file_put_contents($file, json_encode($attempts));
    
    return true;
}

// Input Sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

// Validate Email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate Gmail
function isGmailEmail($email) {
    return isValidEmail($email) && substr(strtolower($email), -10) === '@gmail.com';
}

// Create logs directory if not exists
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0755, true);
}
