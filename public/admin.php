<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Keep session alive
$_SESSION['last_activity'] = time();

include __DIR__ . '/../config/db.php';

$admin_id = $_SESSION['user_id'];
$admin_name = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

// Ambil WebSocket config
$ws_host = defined('WS_HOST') ? WS_HOST : 'localhost';
$ws_port = defined('WS_PORT') ? WS_PORT : '8081';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Admin Live Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="stylesheet" href="css/admin_live_chat.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Reset and Body */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    height: 100vh;
    overflow: hidden;
}

/* Top Navbar */
.top-navbar {
    background: linear-gradient(135deg, #075E54 0%, #075E54 100%);
    color: white;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.navbar-left {
    display: flex;
    align-items: center;
    gap: 15px;
}
.navbar-left h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}
.navbar-right {
    display: flex;
    align-items: center;
    gap: 20px;
}
.admin-info {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.1);
    padding: 6px 15px;
    border-radius: 20px;
}
.admin-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    color: #075E54;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
.logout-btn {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 14px;
}
.logout-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}

/* Main Container */
.main-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
}
.content-wrapper {
    display: flex;
    flex: 1;
    overflow: hidden;
}

/* Container */
.container {
    display: flex;
    width: 100%;
    height: 100%;
}

/* User List */
.user-list {
    width: 320px;
    border-right: 2px solid #e0e0e0;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.user-list h3 {
    padding: 15px 20px;
    background: #fff;
    border-bottom: 1px solid #e0e0e0;
    font-size: 16px;
    color: #333;
}
.user-list-container {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}
#loadingUsers {
    padding: 20px;
    text-align: center;
    color: #666;
}

/* User Item */
.user-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    margin-bottom: 8px;
    border-radius: 10px;
    cursor: pointer;
    background: white;
    transition: all 0.3s;
    position: relative;
    border: 1px solid transparent;
}
.user-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background: transparent;
    border-radius: 10px 0 0 10px;
    transition: all 0.3s;
}
.user-item:hover {
    transform: translateX(3px);
    border-color: #128C7E;
    box-shadow: 0 2px 8px rgba(18,140,126,0.2);
}
.user-item:hover::before {
    background: #128C7E;
}
.user-item.active { 
    background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
    color: white;
    transform: translateX(0);
}
.user-item.active::before {
    background: transparent;
}
.user-item-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    position: relative;
}
.user-item.active .user-avatar {
    background: white;
    color: #075E54;
}
.status-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid white;
}
.status-dot.online {
    background: #4caf50;
    box-shadow: 0 0 8px #4caf50;
}
.status-dot.offline {
    background: #999;
}

/* Badge notifikasi */
.notif-badge {
    background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
    color: white;
    font-size: 11px;
    font-weight: bold;
    border-radius: 12px;
    padding: 3px 8px;
    min-width: 20px;
    text-align: center;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Chat Area */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #fff;
}

/* Chat Header */
.chat-header {
    background: #00796b;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
#chatTitle {
    font-size: 16px;
    font-weight: 600;
}

/* Dropdown */
.dropdown {
    position: relative;
}
.dropdown-btn {
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.3s;
}
.dropdown-btn:hover {
    background: rgba(255,255,255,0.3);
}
.dropdown-menu {
    display: none;
    position: absolute;
    top: 45px; 
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 100;
    min-width: 200px;
}
.dropdown-menu button {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    text-align: left;
    background: none;
    border: none;
    cursor: pointer;
    color: #333;
    transition: background 0.2s;
}
.dropdown-menu button:hover {
    background: #f5f5f5;
}
.dropdown-menu button i {
    margin-right: 8px;
    color: #ff4444;
}

/* Chat Box */
.chat-box {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #ECE5DD;
    display: flex;
    flex-direction: column;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #999;
}
.empty-state i {
    font-size: 80px;
    margin-bottom: 20px;
    opacity: 0.2;
    color: #128C7E;
}
.empty-state p {
    font-size: 16px;
}

/* Message Bubbles */
.message {
    max-width: 70%;
    margin-bottom: 12px;
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
    position: relative;
    animation: messageSlide 0.3s ease-out;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
@keyframes messageSlide {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.message.admin { 
    background: #DCF8C6;
    color: #000;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.message.user { 
    background: white;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    color: #333;
}

/* Timestamp */
.timestamp {
    font-size: 10px;
    opacity: 0.7;
    margin-top: 4px;
    text-align: right;
}

/* Typing Indicator */
.typing-indicator {
    display: none;
    padding: 10px 20px;
    color: #666;
    font-style: italic;
    font-size: 13px;
    background: #f5f5f5;
}
.typing-indicator.active {
    display: flex;
    align-items: center;
    gap: 8px;
}
.typing-dots {
    display: flex;
    gap: 4px;
}
.typing-dots span {
    width: 6px;
    height: 6px;
    background: #128C7E;
    border-radius: 50%;
    animation: blink 1.4s infinite;
}
.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}
.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}
@keyframes blink {
    0%, 80%, 100% { opacity: 0.3; }
    40% { opacity: 1; }
}

/* Chat Input */
.chat-input {
    display: flex;
    padding: 15px 20px;
    background: white;
    border-top: 1px solid #e0e0e0;
    gap: 10px;
    position: relative;
}
.chat-input-wrapper {
    flex: 1;
    position: relative;
}
.chat-input input {
    width: 100%;
    padding: 12px 50px 12px 18px;
    border-radius: 25px;
    border: 2px solid #e0e0e0;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s;
}
.chat-input input:focus {
    border-color: #128C7E;
}
.emoji-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    transition: transform 0.2s;
}
.emoji-btn:hover {
    transform: translateY(-50%) scale(1.2);
}
.emoji-picker {
    display: none;
    position: absolute;
    bottom: 60px;
    right: 20px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    max-width: 300px;
}
.emoji-picker.active {
    display: block;
}
.emoji-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 5px;
    max-height: 200px;
    overflow-y: auto;
}
.emoji-item {
    font-size: 24px;
    cursor: pointer;
    padding: 5px;
    border-radius: 5px;
    text-align: center;
    transition: background 0.2s;
}
.emoji-item:hover {
    background: #f0f0f0;
}
}
.chat-input button {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.chat-input button:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(18,140,126,0.4);
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
    background: #128C7E;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #075E54;
}

/* Responsive */
@media (max-width: 768px) {
    .user-list {
        width: 100%;
        max-height: 180px;
        border-right: none;
        border-bottom: 2px solid #e0e0e0;
    }
    .content-wrapper {
        flex-direction: column;
    }
    .admin-info span {
        display: none;
    }
    .navbar-left h2 {
        font-size: 16px;
    }
    .chat-box {
        padding: 10px;
    }
    .user-list-container {
        padding: 5px;
    }
}
</style>
</head>
<body>

<div class="main-container">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="navbar-left">
            <i class="fas fa-comments"></i>
            <h2>Admin Live Chat</h2>
        </div>
        <div class="navbar-right">
            <div class="admin-info">
                <div class="admin-avatar"><?= strtoupper(substr($admin_name, 0, 1)) ?></div>
                <span><?= $admin_name ?></span>
            </div>
            <button class="logout-btn" onclick="location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container">
            <div class="user-list">
                <h3><i class="fas fa-users"></i> Daftar User Chat</h3>
                <div class="user-list-container" id="userListContainer">
                    <div id="loadingUsers"><i class="fas fa-spinner fa-spin"></i> Memuat daftar user...</div>
                </div>
            </div>

            <div class="chat-area">
                <div class="chat-header" id="chatHeader">
                    <span id="chatTitle">Pilih user dari daftar untuk mulai chat</span>
                    <div class="dropdown">
                        <button id="dropdownBtn" class="dropdown-btn"><i class="fas fa-ellipsis-v"></i></button>
                        <div id="dropdownMenu" class="dropdown-menu">
                            <button onclick="hapusSemua()"><i class="fas fa-trash"></i> Hapus Chat User Ini</button>
                        </div>
                    </div>
                </div>
                <div class="chat-box" id="chatBox">
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>Pilih user dari daftar untuk memulai chat</p>
                    </div>
                </div>
                <div class="typing-indicator" id="typingIndicator">
                    <div class="typing-dots"><span></span><span></span><span></span></div>
                    <span>User sedang mengetik...</span>
                </div>
                <div class="chat-input" id="chatInput">
                    <div class="chat-input-wrapper">
                        <input type="text" id="messageInput" placeholder="Ketik pesan..." onkeydown="if(event.key==='Enter'){sendMessage();}">
                        <button class="emoji-btn" onclick="toggleEmojiPicker(event)">😊</button>
                        <div class="emoji-picker" id="emojiPicker">
                            <div class="emoji-grid" id="emojiGrid"></div>
                        </div>
                    </div>
                    <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Sound (hidden audio element) -->
<audio id="notificationSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZSA0PVbHm7qxZGQtDmN7xuXElBSyAzvPXijYIG2W77Ogz" type="audio/wav">
</audio>

<script>
const adminId = <?= json_encode($admin_id) ?>;
const WS_HOST = "<?php echo $ws_host; ?>";
const WS_PORT = "<?php echo $ws_port; ?>";

console.log('Admin ID:', adminId);
console.log('WebSocket Config:', WS_HOST + ':' + WS_PORT);

let selectedUserId = null;
let ws = null;
let newMessageNotif = {};
let knownUsers = new Set();
let typingTimeout = null;

// Notification sound
const notificationSound = document.getElementById('notificationSound');

function playNotificationSound() {
    try {
        notificationSound.currentTime = 0;
        notificationSound.play().catch(e => console.log('Sound play failed:', e));
    } catch(e) {
        console.log('Sound error:', e);
    }
}

// Format waktu
function formatTime(ts){
    let d = new Date(ts);
    if(isNaN(d)) d = new Date(ts.replace(' ', 'T'));
    let hours = d.getHours().toString().padStart(2, '0');
    let minutes = d.getMinutes().toString().padStart(2, '0');
    return `${hours}:${minutes}`;
}

// Tambah pesan ke chat
function addMessage(text, sender, timestamp){
    const chatBox = document.getElementById('chatBox');
    
    // Remove empty state if exists
    const emptyState = chatBox.querySelector('.empty-state');
    if(emptyState) emptyState.remove();
    
    const msgDiv = document.createElement('div');
    msgDiv.classList.add('message', sender);

    const contentDiv = document.createElement('div');
    contentDiv.textContent = text;
    msgDiv.appendChild(contentDiv);

    const timeDiv = document.createElement('div');
    timeDiv.className = 'timestamp';
    timeDiv.textContent = timestamp ? formatTime(timestamp) : formatTime(new Date().toISOString());
    msgDiv.appendChild(timeDiv);

    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Bersihkan chat
function clearChat(){
    const chatBox = document.getElementById('chatBox');
    chatBox.innerHTML = '<div class="empty-state"><i class="fas fa-comments"></i><p>Belum ada pesan</p></div>';
}

// Hapus semua chat user
function hapusSemua(){
    if(!selectedUserId) return alert("Pilih user terlebih dahulu!");
    if(!confirm("Yakin ingin menghapus semua riwayat chat user ini?")) return;

    fetch("hapus_chat_live.php", {
        method:"POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body:`user_id=${selectedUserId}`,
        credentials:'same-origin'
    }).then(res=>res.text())
    .then(res=>{
        if(res.trim()==="ok"){
            clearChat();
            const el=document.querySelector(`.user-item[data-userid='${selectedUserId}']`);
            if(el){
                const badge=el.querySelector('.notif-badge');
                if(badge) badge.remove();
            }
            alert("Chat user berhasil dihapus!");
            if(ws && ws.readyState===WebSocket.OPEN){
                ws.send(JSON.stringify({type:"delete_chat", user_id:selectedUserId}));
            }
        } else alert("Gagal hapus chat!");
    }).catch(console.error);
}

// Tambah atau update user di list
function addUserToList(user){
    const userListContainer = document.getElementById('userListContainer');
    
    if(knownUsers.has(user.id)){
        const el = document.querySelector(`.user-item[data-userid='${user.id}']`);
        if(el){
            // Update status dot
            const statusDot = el.querySelector('.status-dot');
            if(statusDot) {
                statusDot.className = 'status-dot ' + (user.is_online == 1 ? 'online' : 'offline');
            }

            // Update badge
            let notif = el.querySelector('.notif-badge');
            if(user.unread_count > 0){
                if(!notif) {
                    notif = document.createElement('span');
                    notif.className = 'notif-badge';
                    el.appendChild(notif);
                }
                notif.textContent = user.unread_count;
                newMessageNotif[user.id] = user.unread_count;
            } else {
                if(notif) notif.remove();
                newMessageNotif[user.id] = 0;
            }
        }
        return;
    }
    
    knownUsers.add(user.id);
    
    const div = document.createElement('div');
    div.className='user-item';
    div.dataset.userid=user.id;
    div.onclick=()=>selectUser(user.id,user.username);

    const leftDiv = document.createElement('div');
    leftDiv.className = 'user-item-left';
    
    const avatar = document.createElement('div');
    avatar.className = 'user-avatar';
    avatar.textContent = user.username.charAt(0).toUpperCase();
    
    const statusDot = document.createElement('div');
    statusDot.className = 'status-dot ' + (user.is_online == 1 ? 'online' : 'offline');
    avatar.appendChild(statusDot);
    
    const nameSpan = document.createElement('span');
    nameSpan.textContent = user.username;
    
    leftDiv.appendChild(avatar);
    leftDiv.appendChild(nameSpan);
    div.appendChild(leftDiv);

    if(user.unread_count > 0){
        const notifSpan = document.createElement('span');
        notifSpan.className='notif-badge';
        notifSpan.textContent=user.unread_count;
        div.appendChild(notifSpan);
        newMessageNotif[user.id]=user.unread_count;
    } else {
        newMessageNotif[user.id]=0;
    }

    userListContainer.appendChild(div);
}

// Load daftar user
function loadUsers(){
    fetch('get_all_chat_users.php')
    .then(res=>res.json())
    .then(users=>{
        document.getElementById('loadingUsers').style.display='none';
        users.forEach(addUserToList);
    }).catch(()=>{
        const loading = document.getElementById('loadingUsers');
        loading.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal memuat daftar user.';
    });
}

// Polling status user setiap detik
function pollUsersStatusAndMessages(){
    fetch('get_all_chat_users.php')
    .then(res=>res.json())
    .then(users=>{
        users.forEach(user=>addUserToList(user));
    })
    .catch(console.error)
    .finally(()=>setTimeout(pollUsersStatusAndMessages,1000));
}

// Pilih user untuk chat
function selectUser(id, username){
    console.log('=== SELECT USER ===');
    console.log('User ID:', id, 'Username:', username);
    
    selectedUserId=id;
    document.getElementById('chatTitle').innerHTML = `<i class="fas fa-user"></i> Chat dengan ${username}`;
    clearChat();

    document.querySelectorAll('.user-item').forEach(el=>el.classList.remove('active'));
    const selectedEl = [...document.querySelectorAll('.user-item')].find(el=>el.dataset.userid==id);
    if(selectedEl){
        selectedEl.classList.add('active');
        const notif = selectedEl.querySelector('.notif-badge');
        if(notif) notif.remove();
        newMessageNotif[id]=0;

        fetch('mark_read.php',{
            method:'POST',
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body:`user_id=${id}`,
            credentials:'same-origin'
        }).then(r => console.log('Mark as read:', r.status))
        .catch(err => console.error('Mark as read error:', err));
    }

    // Ambil history chat via WebSocket
    console.log('WebSocket state:', ws ? ws.readyState : 'null');
    if(ws && ws.readyState===WebSocket.OPEN){
        const payload = {type:'get_history', partner_id:selectedUserId};
        console.log('Sending get_history:', payload);
        ws.send(JSON.stringify(payload));
    } else {
        console.error('WebSocket not ready!');
        alert('WebSocket belum terhubung! Coba refresh halaman.');
    }
}

// Kirim pesan
function sendMessage(){
    const input=document.getElementById('messageInput');
    const message=input.value.trim();
    if(!message || !selectedUserId) return;

    const payload={type:'message', sender_id:adminId, receiver_id:selectedUserId, message:message};
    if(ws && ws.readyState===WebSocket.OPEN){
        ws.send(JSON.stringify(payload));
        addMessage(message,'admin',new Date().toISOString());
        input.value='';
    } else alert('Koneksi WebSocket belum siap.');
}

// Show typing indicator
function showTypingIndicator(){
    const indicator = document.getElementById('typingIndicator');
    indicator.classList.add('active');
    
    if(typingTimeout) clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        indicator.classList.remove('active');
    }, 3000);
}

// Dropdown toggle
const dropdownBtn=document.getElementById('dropdownBtn');
const dropdownMenu=document.getElementById('dropdownMenu');
dropdownBtn.addEventListener('click',()=>{
    dropdownMenu.style.display=(dropdownMenu.style.display==='block'?'none':'block');
});
window.addEventListener('click',(e)=>{
    if(!e.target.closest('.dropdown')) dropdownMenu.style.display='none';
});

// WebSocket initialization
window.onload=()=>{
    const wsUrl = `ws://${WS_HOST}:${WS_PORT}?user_id=${adminId}`;
    console.log('Connecting to:', wsUrl);
    
    ws = new WebSocket(wsUrl);

    ws.onopen = ()=>{
        console.log('✅ WebSocket admin connected');
    };

    ws.onmessage = (event)=>{
        const data=JSON.parse(event.data);
        console.log('WS Message:', data.type, data);
        
        if(data.type==='chat_history' && Array.isArray(data.messages)){
            console.log('Received', data.messages.length, 'messages');
            clearChat();
            data.messages.forEach(msg=>{
                const sender = (msg.sender_id==adminId)?'admin':'user';
                addMessage(msg.message, sender, msg.timestamp);
            });
        } else if(data.type==='message'){
            const sender = (data.sender_id==adminId)?'admin':'user';
            if(data.sender_id===selectedUserId || data.receiver_id===selectedUserId){
                addMessage(data.message,sender,data.timestamp);
                
                // Play sound for incoming message from user
                if(sender === 'user') {
                    playNotificationSound();
                }
            } else {
                // Message from different user - update badge
                playNotificationSound();
                addUserToList({
                    id: data.sender_id,
                    username: data.sender_name || 'User '+data.sender_id,
                    is_online:1,
                    unread_count:(newMessageNotif[data.sender_id]||0)+1
                });
            }
        } else if(data.type==='typing'){
            if(data.user_id === selectedUserId) {
                showTypingIndicator();
            }
        } else if(data.type==='delete_chat'){
            if(selectedUserId==data.user_id) clearChat();
            const el=document.querySelector(`.user-item[data-userid='${data.user_id}']`);
            if(el) {
                const badge = el.querySelector('.notif-badge');
                if(badge) badge.remove();
            }
        }
    };

    ws.onerror = (error)=>{
        console.error('❌ WebSocket error:', error);
    };

    ws.onclose = ()=>{
        console.log('WebSocket closed');
    };

    loadUsers();
    pollUsersStatusAndMessages();
    initEmojiPicker();
};

// Emoji Picker
const emojis = ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹','👺','🤡','💩','👻','💀','☠️','👽','👾','🤖','🎃','😺','😸','😹','😻','😼','😽','🙀','😿','😾','👋','🤚','🖐','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🦻','👃','🧠','🫀','🫁','🦷','🦴','👀','👁','👅','👄','💋','🩸','❤️','🧡','💛','💚','💙','💜','🤎','🖤','🤍','💔','❣️','💕','💞','💓','💗','💖','💘','💝'];

function initEmojiPicker() {
    const emojiGrid = document.getElementById('emojiGrid');
    emojis.forEach(emoji => {
        const span = document.createElement('span');
        span.className = 'emoji-item';
        span.textContent = emoji;
        span.onclick = () => insertEmoji(emoji);
        emojiGrid.appendChild(span);
    });
}

function toggleEmojiPicker(event) {
    event.stopPropagation();
    const picker = document.getElementById('emojiPicker');
    picker.classList.toggle('active');
}

function insertEmoji(emoji) {
    const input = document.getElementById('messageInput');
    input.value += emoji;
    input.focus();
    document.getElementById('emojiPicker').classList.remove('active');
}

// Close emoji picker when clicking outside
window.addEventListener('click', function(e) {
    const picker = document.getElementById('emojiPicker');
    const emojiBtn = document.querySelector('.emoji-btn');
    if (!picker.contains(e.target) && e.target !== emojiBtn) {
        picker.classList.remove('active');
    }
});

// Send typing notification
let typingSendTimeout = null;
document.getElementById('messageInput').addEventListener('input', function() {
    if(!selectedUserId) return;
    
    if(typingSendTimeout) clearTimeout(typingSendTimeout);
    
    if(ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({
            type: 'typing',
            user_id: adminId,
            receiver_id: selectedUserId
        }));
    }
    
    typingSendTimeout = setTimeout(() => {
        // Stop typing indicator after 3 seconds
    }, 3000);
});
</script>

</body>
</html>
