<?php
/**
 * Keep Session Alive
 * Call this via AJAX to prevent session timeout
 */
require_once 'session_init.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
    echo json_encode([
        'status' => 'ok',
        'time' => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'No active session'
    ]);
}
