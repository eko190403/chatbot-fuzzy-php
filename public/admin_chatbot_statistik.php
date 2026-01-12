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

// Total interaksi chatbot
$total = $conn->query("SELECT COUNT(*) as total FROM riwayat_chatbot")->fetch_assoc()['total'];

// Statistik berdasarkan kategori
$kategori = $conn->query("
    SELECT chatbot.kategori, COUNT(*) as jumlah 
    FROM chatbot 
    JOIN riwayat_chatbot ON chatbot.pertanyaan = riwayat_chatbot.pertanyaan_user 
    GROUP BY chatbot.kategori
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Statistik Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff; /* Ganti latar belakang menjadi putih */
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding-top: 50px; /* Memberikan jarak dari atas */
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            background-color: #fff;
        }

        .card h5 {
            font-size: 18px;
            font-weight: bold;
            color: #444;
        }

        .list-group-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .badge {
            background-color: #34a853;
            color: white;
            border-radius: 12px;
            padding: 5px 12px;
        }

        .badge-success {
            background-color: #28a745;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .card p, .card h5 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📈 Statistik Penggunaan Chatbot</h2>

    <div class="card mb-4 p-4">
        <h5>Total Interaksi Chatbot</h5>
        <p class="fs-4 text-primary"><?= $total ?> interaksi</p>
    </div>

    <div class="card p-4">
        <h5>Kategori Paling Sering Ditanyakan</h5>
        <ul class="list-group list-group-flush">
            <?php if ($kategori->num_rows > 0): ?>
                <?php while ($row = $kategori->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= ucfirst($row['kategori']) ?>
                        <span class="badge bg-success rounded-pill"><?= $row['jumlah'] ?> kali</span>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="list-group-item">Belum ada data interaksi berdasarkan kategori.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</body>
</html>
