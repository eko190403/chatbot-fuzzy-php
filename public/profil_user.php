<?php
require_once 'session_init.php';
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
include __DIR__ . '/../config/db.php';

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrfToken();
    
    $nama = sanitizeInput($_POST['nama']);
    $new_email = sanitizeInput($_POST['email']);
    $password = $_POST['password'] ?? '';
    
    // Validasi email
    if (!isValidEmail($new_email)) {
        $error = "Format email tidak valid.";
    } elseif (!isGmailEmail($new_email)) {
        $error = "Email harus menggunakan Gmail.";
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $new_email, $user['id']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email sudah digunakan oleh user lain.";
        } else {
            // Update data
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $update->bind_param("sssi", $nama, $new_email, $hashed, $user['id']);
            } else {
                $update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $update->bind_param("ssi", $nama, $new_email, $user['id']);
            }
            
            if ($update->execute()) {
                $_SESSION['email'] = $new_email;
                $_SESSION['username'] = $nama;
                $update->close();
                header("Location: profil_user.php?success=1");
                exit;
            } else {
                $error = "Gagal memperbarui profil.";
            }
            $update->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Pengguna</title>
    <?= csrfMeta() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3 class="mb-3">👤 Profil Pengguna</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Profil berhasil diperbarui.</div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="<?= escape($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= escape($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <button class="btn btn-primary">💾 Simpan Perubahan</button>
        <a href="chatbot_user.php" class="btn btn-secondary">← Kembali</a>
    </form>
</div>
</body>
</html>
