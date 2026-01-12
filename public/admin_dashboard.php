<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time();
include __DIR__ . '/../config/db.php';

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Active users today
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user' AND is_online=1");
$stats['active_users'] = $result->fetch_assoc()['total'];

// Total chatbot questions
$result = $conn->query("SELECT COUNT(*) as total FROM chatbot");
$stats['total_questions'] = $result->fetch_assoc()['total'];

// Total messages
$result = $conn->query("SELECT COUNT(*) as total FROM messages");
$stats['total_messages'] = $result->fetch_assoc()['total'];

// Messages today
$result = $conn->query("SELECT COUNT(*) as total FROM messages WHERE DATE(timestamp) = CURDATE()");
$stats['messages_today'] = $result->fetch_assoc()['total'];

// Total feedback - check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'feedback'");
if ($table_check && $table_check->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(*) as total FROM feedback");
    $stats['total_feedback'] = $result->fetch_assoc()['total'];
} else {
    $stats['total_feedback'] = 0;
}

// Recent users
$recent_users = $conn->query("SELECT id, username, email, created_at, is_online FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 5");

// Recent feedback - check if table exists
if ($table_check && $table_check->num_rows > 0) {
    $recent_feedback = $conn->query("SELECT f.*, u.username FROM feedback f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC LIMIT 5");
} else {
    $recent_feedback = $conn->query("SELECT NULL LIMIT 0"); // empty result
}

$pageTitle = "Dashboard";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?> - Admin Panel</title>
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
            background: #fafafa;
            border-left-color: #e57373;
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
        
        .welcome-header {
            margin-bottom: 30px;
        }
        
        .welcome-header h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .welcome-header p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-icon.blue { background: #eceff1 !important; color: #607d8b !important; }
        .stat-icon.green { background: #eceff1 !important; color: #607d8b !important; }
        .stat-icon.orange { background: #f5f5f5 !important; color: #78909c !important; }
        .stat-icon.purple { background: #ede7f6 !important; color: #7e57c2 !important; }
        .stat-icon.red { background: #fafafa !important; color: #9e9e9e !important; }
        .stat-icon.teal { background: #e0f2f1 !important; color: #546e7a !important; }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        
        .panel-header {
            padding: 20px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .panel-header h3 {
            font-size: 18px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .panel-body {
            padding: 20px;
        }
        
        .user-item, .feedback-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
            transition: background 0.2s;
        }
        
        .user-item:hover, .feedback-item:hover {
            background: #f8f9fa;
        }
        
        .user-item:last-child, .feedback-item:last-child {
            border-bottom: none;
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
            margin-right: 15px;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .user-email {
            font-size: 13px;
            color: #7f8c8d;
        }
        
        .user-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .user-status.online {
            background: #eceff1 !important;
            color: #607d8b !important;
        }
        
        .user-status.offline {
            background: #f5f5f5 !important;
            color: #bdbdbd !important;
        }
        
        .feedback-rating {
            font-size: 18px;
            color: #9e9e9e !important;
            margin-right: 15px;
        }
        
        .feedback-text {
            flex: 1;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .feedback-user {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #95a5a6;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <?php include 'topbar.php'; ?>
        <section class="main-area">
            <div class="welcome-header">
                <h2>👋 Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                <p>Berikut adalah ringkasan sistem AkademikaBot Anda</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['active_users']) ?></div>
                    <div class="stat-label">Pengguna Online</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['total_questions']) ?></div>
                    <div class="stat-label">Pertanyaan Chatbot</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['total_messages']) ?></div>
                    <div class="stat-label">Total Pesan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon teal">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['messages_today']) ?></div>
                    <div class="stat-label">Pesan Hari Ini</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['total_feedback']) ?></div>
                    <div class="stat-label">Total Feedback</div>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-user-plus"></i> Pengguna Terbaru</h3>
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <?php if ($recent_users->num_rows > 0): ?>
                            <?php while($user = $recent_users->fetch_assoc()): ?>
                                <div class="user-item">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
                                        <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                                    </div>
                                    <span class="user-status <?= $user['is_online'] ? 'online' : 'offline' ?>">
                                        <?= $user['is_online'] ? 'Online' : 'Offline' ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>Belum ada pengguna</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-comment-alt"></i> Feedback Terbaru</h3>
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <?php if ($recent_feedback->num_rows > 0): ?>
                            <?php while($feedback = $recent_feedback->fetch_assoc()): ?>
                                <div class="feedback-item">
                                    <div class="feedback-rating">
                                        <?php for($i = 0; $i < $feedback['rating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="feedback-text">
                                        <?= htmlspecialchars($feedback['message']) ?>
                                        <div class="feedback-user">
                                            <i class="fas fa-user"></i> <?= htmlspecialchars($feedback['username']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-comment-slash"></i>
                                <p>Belum ada feedback</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
