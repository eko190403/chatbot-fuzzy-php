<?php
require_once 'session_init.php';
session_start();

$error = '';
require_once 'db.php'; // koneksi database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    checkCsrfToken();
    
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Rate limiting berdasarkan IP (DISABLED for testing)
    // $identifier = 'login_' . $_SERVER['REMOTE_ADDR'];
    // if (!checkRateLimit($identifier, 5, 300)) {
    //     $error = "Terlalu banyak percobaan login. Coba lagi dalam 5 menit.";
    // }
    // Validasi input kosong
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi.";
    } 
    // Validasi email format & Gmail
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -10) !== '@gmail.com') {
        $error = "Email harus menggunakan Gmail.";
    } 
    else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $username, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Set session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['regenerated'] = false; // Will regenerate on next page
                $_SESSION['login_time'] = time();

                // Update STATUS dan is_online
                $update = $conn->prepare("UPDATE users SET STATUS='online', is_online=1 WHERE id=?");
                $update->bind_param("i", $user_id);
                $update->execute();
                $update->close();

                // Redirect sesuai role
                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Email atau password salah.";
            }
        } else {
            $error = "Email atau password salah.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Akun</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <?= csrfMeta() ?>
    <style>
        body { background: linear-gradient(135deg,#4285f4,#6fb1fc); font-family: 'Segoe UI', sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
        .login-container { background:#fff; padding:40px 30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); width:100%; max-width:400px; }
        h2 { text-align:center; margin-bottom:25px; color:#333; }
        input { width:100%; padding:14px 12px; margin:10px 0; border:1px solid #ddd; border-radius:8px; font-size:16px; }
        button { width:100%; padding:14px; background:#4285f4; border:none; color:#fff; font-size:16px; border-radius:8px; cursor:pointer; transition:0.3s; }
        button:hover { background:#357ae8; }
        .error { color:red; text-align:center; margin-bottom:15px; }
        .register-link { text-align:center; margin-top:20px; }
        .register-link a { color:#4285f4; text-decoration:none; }
        .register-link a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Masuk Akun</h2>
    <?php if ($error): ?>
        <p class="error"><?= escape($error) ?></p>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
        <?= csrfField() ?>
        <input type="email" name="email" placeholder="Email Gmail" required>
        <input type="password" name="password" placeholder="Kata Sandi" required>
        <button type="submit">Masuk</button>
    </form>
    <div class="register-link">
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</div>
</body>
</html>
