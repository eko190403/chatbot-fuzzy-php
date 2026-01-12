<?php
require_once 'session_init.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
include __DIR__ . '/../config/db.php';

if (isset($_POST['tambah'])) {
    checkCsrfToken();
    
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validasi email
    if (!isValidEmail($email)) {
        $error = "Format email tidak valid.";
    } else {
        // Gunakan prepared statement untuk keamanan
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, STATUS, is_online) VALUES (?, ?, ?, 'admin', 'offline', 0)");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $current_user_id = (int)$_SESSION['user_id'];
    
    // Jangan hapus diri sendiri
    if ($id != $current_user_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}
$admins = $conn->query("SELECT * FROM users WHERE role = 'admin' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Akun Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .table td, .table th {
            vertical-align: middle;
            padding: 12px;
        }

        .table-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-section table, .table-section th, .table-section td {
            border: 1px solid #ddd;
        }

        .table-section th {
            background-color: #343a40;
            color: white;
        }

        .table-section tr:hover {
            background-color: #f1f1f1;
        }

        .btn-sm {
            font-size: 14px;
            padding: 6px 12px;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .table td, .table th {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍💼 Manajemen Admin</h2>

    <form method="POST" class="mb-4 p-3 border rounded bg-white">
        <div class="row g-2">
            <div class="col"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
            <div class="col"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
            <div class="col"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <div class="col-auto"><button type="submit" name="tambah" class="btn btn-primary">Tambah Admin</button></div>
        </div>
    </form>

    <table class="table table-bordered table-striped bg-white">
        <thead class="table-dark">
            <tr><th>ID</th><th>Username</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php while ($row = $admins->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td>
                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus admin ini?')">Hapus</a>
                    <?php else: ?>
                        <span class="text-muted">Admin aktif</span>
                    <?php endif ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
