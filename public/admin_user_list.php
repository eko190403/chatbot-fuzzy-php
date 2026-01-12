<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time();
include __DIR__ . '/../config/db.php';

// Pencarian dan Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "SELECT * FROM users WHERE 1=1";

if ($search !== '') {
    $escaped = $conn->real_escape_string($search);
    $sql .= " AND (username LIKE '%$escaped%' OR email LIKE '%$escaped%')";
}

if ($role_filter !== 'all') {
    $sql .= " AND role = '" . $conn->real_escape_string($role_filter) . "'";
}

if ($status_filter !== 'all') {
    $status = $status_filter === 'online' ? 1 : 0;
    $sql .= " AND is_online = $status";
}

$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);

$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$online_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_online=1")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pengguna - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            display: flex;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar .brand {
            padding: 25px 20px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .sidebar .brand h3 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .sidebar .brand small {
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .sidebar .menu {
            padding: 15px 0;
        }
        
        .sidebar .menu a {
            display: block;
            padding: 12px 20px;
            color: #2c3e50;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar .menu a:hover {
            background: #f5f5f5;
            border-left-color: #5c6bc0;
            padding-left: 25px;
        }
        
        .sidebar .menu a.active {
            background: #eceff1;
            color: #5c6bc0;
            border-left-color: #5c6bc0;
            font-weight: 600;
        }
        
        .sidebar .menu a.logout {
            color: #e74c3c;
            margin-top: 10px;
        }
        
        .sidebar .menu a.logout:hover {
            background: #ffebee;
            border-left-color: #e74c3c;
        }
        
        /* Topbar Styles */
        .topbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .topbar-left h2 {
            font-size: 20px;
            color: #2c3e50;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .topbar-right span {
            color: #2c3e50;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
            }
            
            .btn-toggle {
                display: block;
            }
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
        }
        
        .main-area {
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .page-header p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stats-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .mini-stat {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .mini-stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .mini-stat-icon.blue { background: #e8eaf6; color: #5c6bc0; }
        .mini-stat-icon.green { background: #eceff1; color: #607d8b; }
        
        .mini-stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 2px;
        }
        
        .mini-stat-info p {
            font-size: 13px;
            color: #7f8c8d;
        }
        
        .filter-panel {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 45px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
        }
        
        .filter-select {
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            min-width: 150px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #5c6bc0;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4e5ba6;
        }
        
        .btn-secondary {
            background: #ecf0f1;
            color: #7f8c8d;
        }
        
        .btn-secondary:hover {
            background: #bdc3c7;
        }
        
        .users-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        thead th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        tbody td {
            padding: 18px 20px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
            color: #2c3e50;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        
        .user-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .user-info p {
            font-size: 13px;
            color: #7f8c8d;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge.online {
            background: #e8f4f8;
            color: #546e7a;
        }
        
        .badge.offline {
            background: #f5f5f5;
            color: #9e9e9e;
        }
        
        .badge.admin {
            background: #ede7f6;
            color: #7e57c2;
        }
        
        .badge.user {
            background: #eceff1;
            color: #607d8b;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #95a5a6;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <?php include 'topbar.php'; ?>
        <section class="main-area">
            <div class="page-header">
                <h2><i class="fas fa-users"></i> Daftar Pengguna</h2>
                <p>Kelola dan monitor semua pengguna yang terdaftar</p>
            </div>
            
            <div class="stats-bar">
                <div class="mini-stat">
                    <div class="mini-stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="mini-stat-info">
                        <h3><?= number_format($total_users) ?></h3>
                        <p>Total Pengguna</p>
                    </div>
                </div>
                
                <div class="mini-stat">
                    <div class="mini-stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="mini-stat-info">
                        <h3><?= number_format($online_users) ?></h3>
                        <p>Pengguna Online</p>
                    </div>
                </div>
            </div>
            
            <div class="filter-panel">
                <form method="GET" class="filter-form">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" placeholder="Cari username atau email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <select name="role" class="filter-select">
                        <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>Semua Role</option>
                        <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    
                    <select name="status" class="filter-select">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="online" <?= $status_filter === 'online' ? 'selected' : '' ?>>Online</option>
                        <option value="offline" <?= $status_filter === 'offline' ? 'selected' : '' ?>>Offline</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    
                    <?php if ($search || $role_filter !== 'all' || $status_filter !== 'all'): ?>
                        <a href="admin_user_list.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="users-table-container">
                <div class="table-wrapper">
                    <?php if ($result->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pengguna</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Bergabung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $user['id'] ?></td>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar">
                                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                </div>
                                                <div class="user-info">
                                                    <h4><?= htmlspecialchars($user['username']) ?></h4>
                                                    <p><?= htmlspecialchars($user['email']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $user['role'] ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $user['is_online'] ? 'online' : 'offline' ?>">
                                                <i class="fas fa-circle"></i> <?= $user['is_online'] ? 'Online' : 'Offline' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <h3>Tidak ada pengguna ditemukan</h3>
                            <p>Coba ubah filter atau kata kunci pencarian Anda</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
