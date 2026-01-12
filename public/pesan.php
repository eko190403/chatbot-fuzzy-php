<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["error" => "Session tidak ditemukan."]);
    exit;
}

require_once 'db.php'; // Panggil koneksi database
// Pastikan di db.php variabelnya $conn

// Ambil user_id berdasarkan email login
$email = $_SESSION['email'];
$getUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
$getUser->bind_param("s", $email);
$getUser->execute();
$resultUser = $getUser->get_result();
$userData = $resultUser->fetch_assoc();
$getUser->close();

$user_id = $userData['id'] ?? 0;

if (!$user_id) {
    echo json_encode(["error" => "User tidak valid."]);
    exit;
}

// URL API Python
$api_python_url = "http://localhost:5000/ask";

if (isset($_POST['isi_pesan'])) {
    $pesan = $_POST['isi_pesan'];
    $kategori = $_POST['kategori'] ?? '';

    // Kirim pertanyaan ke API Python
    $postData = json_encode([
        "question" => $pesan,
        "kategori" => $kategori
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_python_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result_api = json_decode($response, true);

    $jawaban = $result_api['response'] ?? "Maaf, saya tidak bisa menjawab pertanyaan ini. Silakan hubungi admin di fitur live chat.";

    // Simpan ke tabel riwayat_chatbot
    $stmt = $conn->prepare("INSERT INTO riwayat_chatbot (user_id, pertanyaan_user, jawaban_bot, kategori, waktu) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(["error" => "Query gagal: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("isss", $user_id, $pesan, $jawaban, $kategori);
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Eksekusi gagal: " . $stmt->error]);
        exit;
    }

    $last_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    echo json_encode([
        "id" => $last_id,
        "jawaban" => nl2br(htmlspecialchars($jawaban))
    ]);
} else {
    echo json_encode(["error" => "Pesan tidak tersedia."]);
}
