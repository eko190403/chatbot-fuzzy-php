<?php
session_start();
header('Content-Type: application/json');

if (!isset($_GET['user_id'], $_GET['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

$user_id = intval($_GET['user_id']);
$admin_id = intval($_GET['admin_id']);

// Pastikan user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "chat_system");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}

$sql = "DELETE FROM messages WHERE 
        (sender_id = ? AND receiver_id = ?) OR 
        (sender_id = ? AND receiver_id = ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare statement gagal.']);
    exit;
}

$stmt->bind_param("iiii", $user_id, $admin_id, $admin_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus chat.']);
}

$stmt->close();
$conn->close();
