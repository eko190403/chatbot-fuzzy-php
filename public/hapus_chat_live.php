<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Not Authorized");
}

include __DIR__ . "/db.php"; 

$current_user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Admin menghapus semua chat dengan user tertentu
if ($is_admin && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $query = $conn->prepare("DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
    $query->bind_param("iiii", $user_id, $current_user_id, $current_user_id, $user_id);
    $query->execute();
    $query->close();
    echo "ok";
    exit();
}

// User biasa menghapus semua chat-nya
if (isset($_POST['hapus_semua'])) {
    $query = $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
    $query->bind_param("ii", $current_user_id, $current_user_id);
    $query->execute();
    $query->close();
    echo "ok";
    exit();
}

// Hapus chat tertentu (user hanya bisa hapus chat yg dia kirim)
if (isset($_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);
    $query = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $query->bind_param("ii", $message_id, $current_user_id);
    $query->execute();
    $query->close();
    echo "ok";
    exit();
}

echo "error";
