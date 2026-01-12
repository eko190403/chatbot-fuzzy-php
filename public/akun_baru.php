<?php
include __DIR__ . "/../config/db.php"; // Pastikan file ini memiliki koneksi dengan variabel $conn

$username = "adminbaru";
$password = "admin123"; 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah username sudah ada
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Username sudah ada!";
} else {
    // Jika username belum ada, buat akun admin
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if ($stmt->execute()) {
        echo "✅ Akun admin berhasil dibuat!<br>";
        echo "🔹 Username: <b>$username</b><br>";
        echo "🔹 Password: <b>$password</b><br>";
    } else {
        echo "❌ Gagal membuat akun admin!";
    }
}

// Tutup statement dan koneksi
$stmt->close();
$conn->close();
?>
