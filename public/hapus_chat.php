<?php
require_once 'session_init.php';
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$email = $_SESSION['email'];

// Ambil user_id dengan prepared statement
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

if (!$userData) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user_id = (int)$userData['id'];

// Hapus semua chat user dengan prepared statement
$stmt = $conn->prepare("DELETE FROM riwayat_chatbot WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Kembali ke index
header("Location: index.php");
exit();
?>
exit();
?>
