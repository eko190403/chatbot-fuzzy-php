<?php
require_once 'session_init.php';
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include __DIR__ . '/../config/db.php';

if (!isset($_POST['user_id'])) {
    http_response_code(400);
    exit('Missing user_id');
}

$user_id = (int)$_POST['user_id'];
$admin_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
$stmt->bind_param('ii', $user_id, $admin_id);
$stmt->execute();
$stmt->close();

echo 'ok';
