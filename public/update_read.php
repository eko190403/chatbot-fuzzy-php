<?php
include __DIR__ . '/../config/db.php';
$admin_id = 4; // id admin
$sender_id = $_POST['sender_id'] ?? 0;

if($sender_id){
    $sql = "UPDATE chat SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $sender_id, $admin_id);
    $stmt->execute();
}
