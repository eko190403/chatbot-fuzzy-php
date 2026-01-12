<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$pageTitle = "Dashboard";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?> - Admin Panel</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <?php include 'topbar.php'; ?>
        <section class="main-area">
            <h3>Selamat Datang di Dashboard Admin</h3>
            <p>Ini adalah halaman utama Admin AkademikaBot.</p>
        </section>
    </main>
</body>
</html>
