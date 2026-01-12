<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_POST['kategori']) && $_POST['kategori'] !== '') {
    $kategori = sanitizeInput($_POST['kategori']);

    // Ambil pertanyaan berdasarkan kategori dengan prepared statement
    $stmt = $conn->prepare("SELECT pertanyaan FROM chatbot WHERE kategori = ?");
    $stmt->bind_param("s", $kategori);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Ambil hanya variasi pertama sebelum tanda '|'
            $pertama = explode('|', $row['pertanyaan'])[0];
            echo '<div class="pertanyaan-item">' . escape($pertama) . '</div>';
        }
    } else {
        echo '<div class="text-muted">Tidak ada pertanyaan untuk kategori ini.</div>';
    }
    $stmt->close();
} else {
    echo '<div class="text-muted">Pilih kategori untuk melihat pertanyaan.</div>';
}
?>
