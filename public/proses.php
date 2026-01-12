<?php
session_start();
require_once 'db.php'; // pakai koneksi dari db.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Query pakai prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        die("Query error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek apakah username ditemukan
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Simpan data session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: chatbot.php");
            }
            exit();
        } else {
            echo "<script>alert('❌ Password salah!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('❌ Username tidak ditemukan!'); window.location='login.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
