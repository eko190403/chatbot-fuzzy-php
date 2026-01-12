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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pertanyaan yang Sering Ditanyakan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff; /* Ganti latar belakang menjadi putih */
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding-top: 50px; /* Memberikan jarak dari atas */
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .feedback-section {
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
            background-color: #f8f9fa;
        }

        .table-section tr:hover {
            background-color: #f1f1f1;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .feedback-section {
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
    <div class="feedback-section">
        <h2>Pertanyaan yang Sering Ditanyakan</h2>
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Pertanyaan</th>
                    <th>Jumlah Ditanyakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "
                    SELECT pertanyaan_user, COUNT(*) as jumlah 
                    FROM riwayat_chatbot 
                    GROUP BY pertanyaan_user 
                    ORDER BY jumlah DESC 
                    LIMIT 10
                ";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['pertanyaan_user']) . "</td>
                        <td>{$row['jumlah']}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
