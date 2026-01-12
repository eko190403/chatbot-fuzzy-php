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

// Hapus pertanyaan dari riwayat jika diminta
if (isset($_GET['hapus'])) {
    $pertanyaan = urldecode($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM riwayat_chatbot WHERE pertanyaan_user = ?");
    $stmt->bind_param("s", $pertanyaan);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_chatbot_training.php");
    exit;
}

// Ambil pertanyaan dari riwayat yang belum ada di tabel chatbot
$query = "
    SELECT r.pertanyaan_user, COUNT(*) as jumlah 
    FROM riwayat_chatbot r
    LEFT JOIN chatbot c ON r.pertanyaan_user = c.pertanyaan
    WHERE c.pertanyaan IS NULL
    GROUP BY r.pertanyaan_user
    ORDER BY jumlah DESC
    LIMIT 20
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pelatihan Chatbot</title>
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
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
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
    <h2>🤖 Pelatihan Chatbot - Pertanyaan Tak Dikenal</h2>

    <div class="alert alert-info">
        Berikut pertanyaan dari pengguna yang belum tersedia di database chatbot. Anda dapat melatih chatbot atau menghapus pertanyaan tersebut dari riwayat.
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Pertanyaan</th>
                <th>Jumlah Ditanyakan</th>
                <th style="width: 180px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['pertanyaan_user']) ?></td>
                        <td><?= $row['jumlah'] ?></td>
                        <td>
                            <a href="admin_chatbot_crud.php?latih=<?= urlencode($row['pertanyaan_user']) ?>" class="btn btn-sm btn-success me-1">Latih</a>
                            <a href="admin_chatbot_training.php?hapus=<?= urlencode($row['pertanyaan_user']) ?>" onclick="return confirm('Yakin ingin menghapus pertanyaan ini dari riwayat?')" class="btn btn-sm btn-danger">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">Semua pertanyaan telah terdaftar di chatbot.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
