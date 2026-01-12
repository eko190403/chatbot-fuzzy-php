<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Keep session alive
$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Kalender Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Upload Kalender Akademik</h2>
   <form action="simpan_kalender.php" method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="judul" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
        <label>Upload File (PDF)</label>
        <input type="file" name="kalender" accept="application/pdf" required class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>

</div>
</body>
</html>
