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
        
        .stat-icon.blue { background: #e3f2fd; color: #2196f3; }
        .stat-icon.green { background: #e8f5e9; color: #4caf50; }
        .stat-icon.orange { background: #fff3e0; color: #ff9800; }
        .stat-icon.purple { background: #f3e5f5; color: #9c27b0; }
        .stat-icon.red { background: #ffebee; color: #f44336; }
        .stat-icon.teal { background: #e0f2f1; color: #009688; }
        
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
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .user-status.offline {
            background: #ecf0f1;
            color: #95a5a6;
        }
        
        .feedback-rating {
            font-size: 18px;
            color: #ffc107;
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
