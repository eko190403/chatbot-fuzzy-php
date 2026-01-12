<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Keep session alive
$_SESSION['last_activity'] = time();
include __DIR__ . '/../config/db.php';

// Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM chatbot";
if ($search !== '') {
    $escaped = $conn->real_escape_string($search);
    $sql .= " WHERE pertanyaan LIKE '%$escaped%' 
           OR jawaban LIKE '%$escaped%' 
           OR kategori LIKE '%$escaped%'";
}
$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pertanyaan Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff; /* Ubah jadi putih */
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding-top: 50px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .table-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        h2 {
            margin-bottom: 20px;
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
            background-color: #111;
            color: #fff;
        }

        .table-section tr:hover {
            background-color: #f1f1f1;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .table-section {
                padding: 15px;
            }

            h2 {
                font-size: 20px;
            }

            .table td, .table th {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="table-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Daftar Pertanyaan Chatbot</h2>
            <form method="GET" class="d-flex" role="search">
                <input class="form-control me-2" type="search" name="search" placeholder="Cari pertanyaan..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-dark" type="submit">Cari</button>
            </form>
        </div>
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
                    <th>Kategori</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center">Tidak ada data ditemukan.</td></tr>
                <?php else: ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['jawaban'])) ?></td>
                            <td><?= $row['kategori'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
