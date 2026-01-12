<?php
require_once 'session_init.php';
session_start(); // Start session untuk CSRF token

require_once __DIR__ . '/../config/db.php'; // koneksi database
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrfToken();
    
    // Rate limiting berdasarkan IP (DISABLED for testing)
    // $identifier = 'register_' . $_SERVER['REMOTE_ADDR'];
    // if (!checkRateLimit($identifier, 3, 600)) {
    //     $message = '<p class="message error">Terlalu banyak percobaan registrasi. Coba lagi dalam 10 menit.</p>';
    // } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validasi
        if (strlen($username) < 3) {
            $message = '<p class="message error">Username minimal 3 karakter.</p>';
        } elseif (strlen($password) < 6) {
            $message = '<p class="message error">Password minimal 6 karakter.</p>';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
            $message = '<p class="message error">Email harus menggunakan Gmail.</p>';
        } elseif ($password !== $confirm_password) {
            $message = '<p class="message error">Konfirmasi password tidak cocok.</p>';
        } else {
            // Cek email sudah terdaftar
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = '<p class="message error">Email sudah digunakan.</p>';
            } else {
                // Simpan ke DB
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';

                $insert = $conn->prepare("INSERT INTO users (username, email, password, role, STATUS, is_online) VALUES (?, ?, ?, ?, 'offline', 0)");
                $insert->bind_param("ssss", $username, $email, $hashed_password, $role);

                if ($insert->execute()) {
                    $message = '<p class="message success">Registrasi berhasil. Silakan <a href="login.php">login</a>.</p>';
                } else {
                    $message = '<p class="message error">Terjadi kesalahan. Coba lagi.</p>';
                }
                $insert->close();
            }
            $stmt->close();
        }
    // } // Rate limit else closed
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= csrfMeta() ?>
    <style>
        body { background: #2874f0; min-height: 100vh; margin: 0; padding: 0; font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; }
        .register-container { background: #fff; padding: 36px 32px; border-radius: 12px; box-shadow: 0 4px 20px rgba(40, 116, 240, 0.10); width: 100%; max-width: 400px; box-sizing: border-box; }
        .register-container h2 { text-align: center; margin-bottom: 25px; font-size: 24px; color: #2874f0; font-weight: 700; letter-spacing: 1px; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px 15px; margin: 10px 0 18px 0; border: 1px solid #b6c3d1; border-radius: 7px; font-size: 16px; box-sizing: border-box; transition: border-color 0.2s; }
        input:focus { border-color: #2874f0; outline: none; }
        button { width: 100%; padding: 12px; background: #2874f0; border: none; color: #fff; font-size: 17px; font-weight: bold; border-radius: 7px; cursor: pointer; letter-spacing: 1px; transition: background 0.2s; }
        button:hover { background: #165bb7; }
        .message { text-align: center; margin-bottom: 10px; }
        .message.error { color: #e63946; }
        .message.success { color: #4bb543; }
        .login-link { text-align: center; margin-top: 16px; }
        .login-link a { color: #2874f0; text-decoration: none; font-weight: 500; }
        .login-link a:hover { text-decoration: underline; }
        @media (max-width: 600px) { .register-container { padding: 22px 8px; max-width: 97vw; } .register-container h2 { font-size: 19px; } input, button { font-size: 15px; } }
    </style>
</head>
<body>
<div class="register-container">
    <h2>Daftar</h2>
    <?= $message ?>
    <form method="POST" autocomplete="off">
        <?= csrfField() ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Gmail" required>
        <input type="password" name="password" placeholder="Kata Sandi" required>
        <input type="password" name="confirm_password" placeholder="Konfirmasi Kata Sandi" required>
        <button type="submit">Daftar</button>
    </form>
    <div class="login-link">
        <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </div>
</div>
</body>
</html>
