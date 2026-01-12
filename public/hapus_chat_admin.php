<?php
include __DIR__ . "/../config/db.php";
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin'){
    http_response_code(403);
    exit("Not Authorized");
}

$admin_id = $_SESSION['user_id'];

// Hapus semua chat dengan user tertentu
if(isset($_POST['hapus_semua'], $_POST['user_id'])){
    $user_id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)");
    $stmt->bind_param("iiii",$admin_id,$user_id,$user_id,$admin_id);
    $stmt->execute();
    echo "ok"; exit();
}

// Hapus 1 chat
if(isset($_POST['message_id'])){
    $message_id = intval($_POST['message_id']);
    $stmt = $conn->prepare("DELETE FROM messages WHERE id=?");
    $stmt->bind_param("i",$message_id);
    $stmt->execute();
    echo "ok"; exit();
}

echo "error";
?>
