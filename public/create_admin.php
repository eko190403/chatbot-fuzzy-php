<?php
/**
 * Script untuk membuat atau mengecek akun admin
 */

include 'db.php';

echo "<h1>Admin Account Manager</h1>";
echo "<style>body{font-family:monospace;padding:20px;} .success{color:green;} .error{color:red;} table{border-collapse:collapse;margin:20px 0;} td,th{border:1px solid #ddd;padding:8px;}</style>";

// Cek admin yang ada
echo "<h2>Existing Admin Accounts:</h2>";
$result = $conn->query("SELECT id, username, email, role, is_online FROM users WHERE role = 'admin'");

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Online</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['role']}</td>";
        echo "<td>" . ($row['is_online'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='success'>✅ Admin account(s) found!</p>";
} else {
    echo "<p class='error'>❌ No admin account found!</p>";
    
    // Buat admin default
    echo "<h2>Creating default admin account...</h2>";
    
    $username = 'admin';
    $email = 'admin@admin.com';
    $password = password_hash('admin123', PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param('sss', $username, $email, $password);
    
    if ($stmt->execute()) {
        $admin_id = $conn->insert_id;
        echo "<p class='success'>✅ Admin account created successfully!</p>";
        echo "<table>";
        echo "<tr><th>Username</th><td>admin</td></tr>";
        echo "<tr><th>Email</th><td>admin@admin.com</td></tr>";
        echo "<tr><th>Password</th><td>admin123</td></tr>";
        echo "<tr><th>ID</th><td>$admin_id</td></tr>";
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Failed to create admin: " . $stmt->error . "</p>";
    }
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Go to <a href='login.php'>login.php</a></li>";
echo "<li>Login with admin credentials</li>";
echo "<li>Then access <a href='admin_chat.php'>admin_chat.php</a></li>";
echo "</ol>";

echo "<hr>";
echo "<h2>All Users in Database:</h2>";
$all_users = $conn->query("SELECT id, username, email, role FROM users ORDER BY role DESC, id ASC");
if ($all_users->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    while ($row = $all_users->fetch_assoc()) {
        $highlight = $row['role'] === 'admin' ? ' style="background:#e8f5e9;"' : '';
        echo "<tr$highlight>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td><strong>{$row['role']}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
