<?php
include "db.php"; // Pastikan koneksi database benar

$username = "adminbaru";
$password = "admin123"; 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = "admin";

// Pastikan koneksi database tersedia
if (!$koneksi) {
    die("Koneksi database tidak tersedia.");
}

// Cek apakah username sudah ada
$stmt = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Username '$username' sudah ada!";
} else {
    // Jika username belum ada, buat akun admin
    $stmt = $koneksi->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
    
    if (!$stmt) {
        die("Persiapan query gagal: " . $koneksi->error);
    }

    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "✅ Akun admin berhasil dibuat!<br>";
        echo "🔹 Username: <b>$username</b><br>";
        echo "🔹 Password: <b>$password</b><br>";
    } else {
        echo "❌ Gagal membuat akun admin! Error: " . $stmt->error;
    }
}

// Tutup statement dan koneksi
$stmt->close();
$koneksi->close();
?>
