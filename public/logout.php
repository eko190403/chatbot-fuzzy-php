<?php
// Pastikan session sudah dimulai
session_start();

require __DIR__ . '/../config/db.php'; // Koneksi ke database

if (isset($_SESSION['user_id'])) {
    // Ambil user_id dari session
    $userId = $_SESSION['user_id'];

    // Update status user menjadi offline (is_online = 0)
    $updateStatusQuery = "UPDATE users SET is_online = 0 WHERE id = ?";
    $updateStmt = $conn->prepare($updateStatusQuery);
    $updateStmt->bind_param("i", $userId);

    if ($updateStmt->execute()) {
        // Hapus session dan redirect ke halaman login
        session_unset(); // Menghapus semua session
        session_destroy(); // Menghancurkan session
        header("Location: login.php");
        exit();
    } else {
        // Jika gagal update status offline
        echo "⚠️ Gagal memperbarui status offline.";
    }
} else {
    // Jika tidak ada session aktif, redirect ke halaman login
    header("Location: login.php");
    exit();
}
?>
