<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include('db.php');

$admin_id = $_SESSION['user_id'];

// Ambil semua user yang pernah chat dengan admin, tampilkan unread count
$sql = "
    SELECT DISTINCT 
        u.id, 
        u.username, 
        u.is_online,
        (
            SELECT COUNT(*) 
            FROM messages m2 
            WHERE m2.sender_id = u.id 
              AND m2.receiver_id = ? 
              AND m2.is_read = 0
        ) as unread_count
    FROM users u
    INNER JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE u.role = 'user' 
      AND (m.sender_id = ? OR m.receiver_id = ?)
    ORDER BY u.username ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $admin_id, $admin_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => (int)$row['id'],
        'username' => $row['username'],
        'is_online' => (int)$row['is_online'],
        'unread_count' => (int)$row['unread_count']
    ];
}

header('Content-Type: application/json');
echo json_encode($users);
