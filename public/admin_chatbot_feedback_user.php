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

$query = "
    SELECT r.pertanyaan_user, r.jawaban_bot, r.feedback, u.username, r.waktu 
    FROM riwayat_chatbot r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.feedback IS NOT NULL
    ORDER BY r.waktu DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Feedback Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff; /* Ganti latar belakang menjadi putih */
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            height: 100%;
        }

        .container {
            max-width: 1200px;
            margin-top: 50px;  /* Memberikan jarak dari atas */
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
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
            background-color: #f8f9fa;
        }

        .table-section tr:hover {
            background-color: #f1f1f1;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
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
    <h2>🗣️ Feedback Pengguna terhadap Jawaban Bot</h2>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>User</th>
                <th>Pertanyaan</th>
                <th>Jawaban Bot</th>
                <th>Feedback</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['username'] ?? 'Anonim') ?></td>
                <td><?= htmlspecialchars($row['pertanyaan_user']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['jawaban_bot'])) ?></td>
                <td>
                    <?php if ($row['feedback'] === 'bantu'): ?>
                        <span class="badge badge-success">👍 Bantu</span>
                    <?php else: ?>
                        <span class="badge badge-danger">👎 Tidak</span>
                    <?php endif; ?>
                </td>
                <td><?= $row['waktu'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
