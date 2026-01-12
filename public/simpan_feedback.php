<?php
include __DIR__ . '/../config/db.php';

if (isset($_POST['id']) && isset($_POST['feedback'])) {
    $id = intval($_POST['id']);
    $feedback = $_POST['feedback'] === 'bantu' ? 'bantu' : 'tidak';

    // Pastikan kolom feedback ada di tabel riwayat_chatbot
    $stmt = $conn->prepare("UPDATE riwayat_chatbot SET feedback = ? WHERE id = ?");
    if (!$stmt) {
        die("Query gagal: " . $conn->error);
    }

    $stmt->bind_param("si", $feedback, $id);

    if ($stmt->execute()) {
        echo "✔ Feedback berhasil disimpan!";
    } else {
        echo "❌ Gagal menyimpan feedback: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Data tidak lengkap.";
}
?>
