<?php
require_once 'session_init.php';
session_start();

echo "<h2>Session Debug</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "\nAll Session Data:\n";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    include __DIR__ . '/../config/db.php';
    
    echo "<h2>Database Check</h2>";
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, username, email, role, is_online FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<pre>";
        $user = $result->fetch_assoc();
        echo "User found in database:\n";
        print_r($user);
        echo "</pre>";
        
        if ($user['role'] === 'admin') {
            echo "<p style='color:green; font-weight:bold;'>✅ User is ADMIN</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ User is NOT admin (role: {$user['role']})</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ User NOT found in database!</p>";
    }
    
    echo "<h2>All Admins in Database</h2>";
    $result2 = $conn->query("SELECT id, username, email, role FROM users WHERE role = 'admin'");
    if ($result2->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
        while ($row = $result2->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['role']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>❌ NO ADMIN found in database!</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Not logged in</p>";
}

echo "<h2>WebSocket Config</h2>";
require_once __DIR__ . '/../config/config.php';
echo "<pre>";
echo "WS_HOST: " . WS_HOST . "\n";
echo "WS_PORT: " . WS_PORT . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "</pre>";

echo "<hr>";
echo "<a href='admin_chat.php'>Go to Admin Chat</a> | ";
echo "<a href='login.php'>Login</a>";
?>
