<?php
include('db.php');

$admin_id = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 0;
if ($admin_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'admin_id kosong atau tidak valid']);
    exit;
}

// SQL: ambil user yang pernah kirim pesan ke admin dan hitung unread messages
$sql = "
    SELECT u.id, u.username, u.is_online,
           COUNT(CASE WHEN m.is_read = 0 THEN 1 END) AS unread_count
    FROM users u
    INNER JOIN messages m ON u.id = m.sender_id
    WHERE m.receiver_id = ?
    GROUP BY u.id, u.username, u.is_online
    ORDER BY u.username ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Prepare statement gagal: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $row['unread_count'] = (int)$row['unread_count'];
    $row['is_online'] = (int)$row['is_online']; // pastikan int
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
