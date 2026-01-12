<?php
// Koneksi ke database
include __DIR__ . '/../config/db.php';

// Pastikan parameter user_id dan admin_id ada
if (isset($_GET['user_id']) && isset($_GET['admin_id'])) {
    $user_id = $_GET['user_id'];
    $admin_id = $_GET['admin_id'];

    // Query untuk mengambil riwayat chat antara admin dan user
    $query = "
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY timestamp ASC
    ";
    
    // Menyiapkan dan mengeksekusi query
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiii', $admin_id, $user_id, $user_id, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    
    // Ambil hasil query dan masukkan ke dalam array
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    // Mengembalikan data chat dalam format JSON
    echo json_encode($messages);
} else {
    // Jika parameter tidak ada, kirimkan respons error
    echo json_encode(['error' => 'Parameter tidak lengkap']);
}
?>