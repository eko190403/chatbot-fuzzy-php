<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Keep session alive
$_SESSION['last_activity'] = time();
include 'db.php';

// Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM users";
if ($search !== '') {
    $escaped = $conn->real_escape_string($search);
    $sql .= " WHERE username LIKE '%$escaped%' OR email LIKE '%$escaped%'";
}
$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pengguna Terdaftar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/lit_user.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="section">
        <div class="d-flex justify-content-between align-items-center mb-3">
        
            <form method="GET" class="d-flex" role="search">
                <input class="form-control me-2" type="search" name="search" placeholder="Cari pengguna..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-primary" type="submit">Cari</button>
            </form>
        </div>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center">Tidak ada data ditemukan.</td></tr>
                <?php else: ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= $row['role'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
