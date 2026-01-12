<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Keep session alive
$_SESSION['last_activity'] = time();

include __DIR__ . '/../config/db.php';

// CSRF Protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrfToken();
}

// Tambah
if (isset($_POST['tambah'])) {
    $pertanyaan = sanitizeInput($_POST['pertanyaan']);
    $jawaban = sanitizeInput($_POST['jawaban']);
    $kategori = sanitizeInput($_POST['kategori']);
    $stmt = $conn->prepare("INSERT INTO chatbot (pertanyaan, jawaban, kategori) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $pertanyaan, $jawaban, $kategori);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_chatbot_crud.php");
    exit;
}

// Update
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $pertanyaan = sanitizeInput($_POST['pertanyaan']);
    $jawaban = sanitizeInput($_POST['jawaban']);
    $kategori = sanitizeInput($_POST['kategori']);
    $stmt = $conn->prepare("UPDATE chatbot SET pertanyaan=?, jawaban=?, kategori=? WHERE id=?");
    $stmt->bind_param("sssi", $pertanyaan, $jawaban, $kategori, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_chatbot_crud.php");
    exit;
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM chatbot WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_chatbot_crud.php");
    exit;
}

// Edit
$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM chatbot WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
    $stmt->close();
}

// Pencarian
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
if ($search !== '') {
    $searchParam = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM chatbot WHERE pertanyaan LIKE ? OR jawaban LIKE ? OR kategori LIKE ? ORDER BY id DESC");
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM chatbot ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pertanyaan Chatbot</title>
    <?= csrfMeta() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/crud.css">
</head>
<body>
<div class="container">
    <div class="form-section">
        <h2><?= $editData ? "Edit Pertanyaan" : "Tambah Pertanyaan" ?></h2>
        <form method="POST">
            <?= csrfField() ?>
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Pertanyaan</label>
                <input type="text" name="pertanyaan" class="form-control" required value="<?= $editData ? escape($editData['pertanyaan']) : '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Jawaban</label>
                <textarea name="jawaban" class="form-control" rows="3" required><?= $editData ? escape($editData['jawaban']) : '' ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-select">
                    <?php
                    $kategoriList = ["jadwal kuliah", "krs", "nilai", "presensi", "ujian", "skripsi", "cuti", "yudisium", "umum"];
                    echo '<option value="">Tanpa Kategori</option>';
                    foreach ($kategoriList as $k) {
                        $selected = $editData && $editData['kategori'] === $k ? 'selected' : '';
                        echo "<option value='$k' $selected>" . ucfirst($k) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <?php if ($editData): ?>
                <button type="submit" name="update" class="btn btn-success">Simpan Perubahan</button>
                <a href="admin_chatbot_crud.php" class="btn btn-secondary">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah" class="btn btn-primary">Tambah Pertanyaan</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Daftar Pertanyaan</h2>
            <form method="GET" class="d-flex" role="search">
                <input class="form-control me-2" type="search" name="search" placeholder="Cari pertanyaan..." value="<?= escape($search) ?>">
                <button class="btn btn-outline-primary" type="submit">Cari</button>
            </form>
        </div>
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
                    <th>Kategori</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result->num_rows === 0) {
                echo "<tr><td colspan='5' class='text-center'>Tidak ada data ditemukan.</td></tr>";
            } else {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . (int)$row['id'] . "</td>
                        <td>" . escape($row['pertanyaan']) . "</td>
                        <td>" . nl2br(escape($row['jawaban'])) . "</td>
                        <td>" . escape($row['kategori']) . "</td>
                        <td>
                            <a href='?edit=" . (int)$row['id'] . "' class='btn btn-sm btn-warning'>Edit</a>
                            <a href='?hapus=" . (int)$row['id'] . "' onclick='return confirm(\"Yakin ingin menghapus?\")' class='btn btn-sm btn-danger'>Hapus</a>
                        </td>
                    </tr>";
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
