<?php
// ===== SESSION TAHAN LAMA =====
require_once 'session_init.php';
session_start();

// Validasi session admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Keep session alive
$_SESSION['last_activity'] = time();

include __DIR__ . '/../config/db.php';

$admin_id = $_SESSION['user_id'];
$admin_name = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

// Ambil WebSocket config - pastikan sudah terdefinisi
$ws_host = defined('WS_HOST') ? WS_HOST : 'localhost';
$ws_port = defined('WS_PORT') ? WS_PORT : '8081';

// DEBUG: Tampilkan info di HTML comment
echo "<!-- DEBUG INFO:\n";
echo "Admin ID: $admin_id\n";
echo "Admin Name: $admin_name\n";
echo "Session Role: " . $_SESSION['role'] . "\n";
echo "WS_HOST defined: " . (defined('WS_HOST') ? 'YES' : 'NO') . "\n";
echo "WS_HOST value: $ws_host\n";
echo "WS_PORT value: $ws_port\n";
echo "-->\n";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Admin Live Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="stylesheet" href="css/admin_live_chat.css">
<style>
.notif-badge { background-color:red; color:white; font-size:12px; font-weight:bold; border-radius:50%; padding:2px 6px; margin-left:6px; display:inline-block; }
.user-item.active { background-color:#e0e0e0; }
.message.admin { text-align:right; }
.message.user { text-align:left; }
</style>
</head>
<body>

<div class="container">
    <div class="user-list" id="userList">
        <h3>Daftar User Chat</h3>
        <div id="wsStatus" style="padding:8px; margin-bottom:10px; background:#ffc107; color:#000; font-size:12px; border-radius:4px;">
            🔄 Menghubungkan ke WebSocket...
        </div>
        <div id="loadingUsers">Memuat daftar user...</div>
    </div>

    <div class="chat-area">
        <div class="chat-header" id="chatHeader">
            <span id="chatTitle">Pilih user dari daftar untuk mulai chat</span>
            <div class="dropdown">
                <button id="dropdownBtn" class="dropdown-btn">&#9662;</button>
                <div id="dropdownMenu" class="dropdown-menu">
                    <button onclick="hapusSemua()">🗑 Hapus Chat User Ini</button>
                    <button onclick="logoutAdmin()">🚪 Logout</button>
                </div>
            </div>
        </div>
        <div class="chat-box" id="chatBox"></div>
        <div class="chat-input" id="chatInput">
            <input type="text" id="messageInput" placeholder="Ketik pesan..." onkeydown="if(event.key==='Enter'){sendMessage();}">
            <button onclick="sendMessage()">&#9658;</button>
        </div>
    </div>
</div>

<script>
const adminId = <?= json_encode($admin_id) ?>;
const adminName = '<?= $admin_name ?>';

console.log('===== ADMIN CHAT INITIALIZATION =====');
console.log('Admin ID:', adminId, 'Type:', typeof adminId);
console.log('Admin Name:', adminName);

if (!adminId || adminId === null || adminId === 0) {
    alert('ERROR: Admin ID tidak valid! Session mungkin expired.');
    console.error('Invalid Admin ID');
}

const WS_HOST = "<?php echo $ws_host; ?>";
const WS_PORT = "<?php echo $ws_port; ?>";
console.log('WebSocket Config:', WS_HOST + ':' + WS_PORT);

// Validate WebSocket config
if (!WS_HOST || WS_HOST === '' || !WS_PORT || WS_PORT === '') {
    console.error('ERROR: WebSocket configuration not loaded!');
    alert('ERROR: WebSocket configuration gagal dimuat!\n\nWS_HOST: "' + WS_HOST + '"\nWS_PORT: "' + WS_PORT + '"');
}

let selectedUserId = null;
let ws = null;
let newMessageNotif = {};
let knownUsers = new Set();

// ===== FORMAT WAKTU =====
function formatTime(ts){
    let d = new Date(ts);
    if(isNaN(d)) d = new Date(ts.replace(' ', 'T'));
    return d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
}

// ===== TAMBAH PESAN =====
function addMessage(text, sender, timestamp){
    console.log('Adding message to UI:', {text, sender, timestamp});
    const chatBox = document.getElementById('chatBox');
    const msgDiv = document.createElement('div');
    msgDiv.classList.add('message', sender);

    const contentDiv = document.createElement('div');
    contentDiv.textContent = (sender==='admin'?'Admin: ':'') + text;
    msgDiv.appendChild(contentDiv);

    const timeDiv = document.createElement('div');
    timeDiv.className = 'timestamp';
    timeDiv.textContent = timestamp ? formatTime(timestamp) : formatTime(new Date().toISOString());
    msgDiv.appendChild(timeDiv);

    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
    console.log('Message added to chat box');
}

// ===== CLEAR CHAT =====
function clearChat(){
    const chatBox = document.getElementById('chatBox');
    chatBox.innerHTML = '';
    console.log('Chat cleared');
}

// ===== HAPUS SEMUA CHAT USER =====
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
                if(badge) badge.style.display='none';
            }
            if(ws && ws.readyState===WebSocket.OPEN){
                ws.send(JSON.stringify({type:"delete_chat", user_id:selectedUserId}));
            }
        } else alert("Gagal hapus chat!");
    }).catch(console.error);
}

// ===== TAMBAH/UPDATE USER LIST =====
function addUserToList(user){
    if(knownUsers.has(user.id)){
        const el = document.querySelector(`.user-item[data-userid='${user.id}']`);
        if(el){
            const nameSpan = el.querySelector('span:first-child');
            nameSpan.textContent = user.username + (user.is_online==1 ? ' 🟢':' ⚪');
            const notif = el.querySelector('.notif-badge');
            if(user.unread_count>0){
                notif.style.display='inline-block';
                notif.textContent = user.unread_count;
                newMessageNotif[user.id]=user.unread_count;
            } else {
                notif.style.display='none';
                newMessageNotif[user.id]=0;
            }
        }
        return;
    }
    knownUsers.add(user.id);
    const userList = document.getElementById('userList');
    const div = document.createElement('div');
    div.className='user-item';
    div.dataset.userid=user.id;
    div.onclick=()=>selectUser(user.id,user.username);

    const nameSpan = document.createElement('span');
    nameSpan.textContent=user.username + (user.is_online==1?' 🟢':' ⚪');
    div.appendChild(nameSpan);

    const notifSpan = document.createElement('span');
    notifSpan.className='notif-badge';
    if(user.unread_count>0){
        notifSpan.style.display='inline-block';
        notifSpan.textContent=user.unread_count;
        newMessageNotif[user.id]=user.unread_count;
    } else {
        notifSpan.style.display='none';
        newMessageNotif[user.id]=0;
    }
    div.appendChild(notifSpan);

    userList.appendChild(div);
}

// ===== LOAD USERS =====
function loadUsers(){
    fetch('get_all_chat_users.php', {credentials:'same-origin'})
    .then(res=>res.json())
    .then(users=>{
        document.getElementById('loadingUsers').style.display='none';
        users.forEach(addUserToList);
    }).catch(()=>document.getElementById('loadingUsers').textContent='Gagal memuat daftar user.');
}

// ===== POLLING USERS =====
function pollUsersStatusAndMessages(){
    fetch('get_all_chat_users.php', {credentials:'same-origin'})
    .then(res=>res.json())
    .then(users=> users.forEach(user=>addUserToList(user)))
    .finally(()=>setTimeout(pollUsersStatusAndMessages,1000));
}

// ===== PILIH USER =====
function selectUser(id, username){
    console.log('=== SELECT USER ===');
    console.log('User ID:', id, 'Username:', username);
    
    selectedUserId=id;
    document.getElementById('chatTitle').textContent='Chat dengan '+username;
    clearChat();

    document.querySelectorAll('.user-item').forEach(el=>el.classList.remove('active'));
    const selectedEl = [...document.querySelectorAll('.user-item')].find(el=>el.dataset.userid==id);
    if(selectedEl){
        selectedEl.classList.add('active');
        const notif = selectedEl.querySelector('.notif-badge');
        if(notif) notif.style.display='none';
        newMessageNotif[id]=0;

        fetch('mark_read.php',{
            method:'POST',
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body:`user_id=${id}`,
            credentials:'same-origin'
        }).then(r => console.log('Mark as read response:', r.status));
    }

    // Ambil history chat via WebSocket
    console.log('WebSocket state:', ws ? ws.readyState : 'null');
    if(ws && ws.readyState===WebSocket.OPEN){
        const payload = {type:'get_history', partner_id:selectedUserId};
        console.log('Sending get_history request:', payload);
        ws.send(JSON.stringify(payload));
    } else {
        console.error('❌ Cannot get history: WebSocket not connected');
        alert('WebSocket belum terhubung! Status: ' + (ws ? ws.readyState : 'null'));
    }
}
    }
}

// ===== KIRIM PESAN =====
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

// ===== LOGOUT ADMIN =====
function logoutAdmin(){
    if(confirm("Yakin ingin logout?")){
        fetch('logout.php', {method:'POST', credentials:'same-origin'})
        .finally(()=> location.href='login.php');
    }
}

// ===== DROPDOWN =====
const dropdownBtn=document.getElementById('dropdownBtn');
const dropdownMenu=document.getElementById('dropdownMenu');
dropdownBtn.addEventListener('click',()=>{dropdownMenu.style.display=(dropdownMenu.style.display==='block'?'none':'block');});
window.addEventListener('click',(e)=>{if(!e.target.closest('.dropdown')) dropdownMenu.style.display='none';});

// ===== WEBSOCKET DENGAN RECONNECT =====
let reconnectAttempts = 0;
const maxReconnectDelay = 5000;

function updateWsStatus(message, color){
    const statusEl = document.getElementById('wsStatus');
    if(statusEl){
        statusEl.textContent = message;
        statusEl.style.backgroundColor = color;
        statusEl.style.color = (color === '#4caf50' ? '#fff' : '#000');
    }
}

function connectWebSocket(){
    try {
        if (!adminId) {
            console.error('Cannot connect: Invalid admin ID');
            updateWsStatus('❌ Error: Admin ID tidak valid', '#f44336');
            return;
        }
        
        if (!WS_HOST || !WS_PORT) {
            console.error('Cannot connect: WebSocket config not loaded');
            updateWsStatus('❌ Error: WebSocket config tidak valid', '#f44336');
            return;
        }
        
        const wsUrl = `ws://${WS_HOST}:${WS_PORT}?user_id=${adminId}`;
        console.log('Connecting to WebSocket:', wsUrl);
        updateWsStatus('🔄 Menghubungkan ke WebSocket...', '#ffc107');
        
        ws = new WebSocket(wsUrl);
        console.log('WebSocket object created');
        
    } catch (error) {
        console.error('WebSocket creation error:', error);
        updateWsStatus('❌ Error: ' + error.message, '#f44336');
        alert('Error creating WebSocket: ' + error.message);
        return;
    }

    ws.onopen = ()=>{
        console.log('✅ WebSocket admin connected');
        updateWsStatus('✅ WebSocket Terhubung', '#4caf50');
        reconnectAttempts = 0;
        
        // Request initial data setelah koneksi berhasil
        console.log('Requesting user list...');
    };

    ws.onmessage = (event)=>{
        const data=JSON.parse(event.data);
        console.log('📨 WS Message received:', data.type, data);
        
        if(data.type==='error'){
            console.error('❌ WebSocket error:', data.message);
            updateWsStatus('❌ Error: ' + data.message, '#f44336');
            return;
        }
        
        if(data.type==='chat_history' && Array.isArray(data.messages)){
            console.log('📜 Chat history received:', data.messages.length, 'messages');
            console.log('Selected User ID:', selectedUserId);
            console.log('Chat box element:', document.getElementById('chatBox'));
            
            clearChat();
            
            if(data.messages.length === 0){
                console.log('⚠️ No messages in history');
                const chatBox = document.getElementById('chatBox');
                chatBox.innerHTML = '<div style="text-align:center;padding:20px;color:#999;">Belum ada pesan</div>';
            } else {
                data.messages.forEach((msg, index)=>{
                    console.log(`Message ${index+1}:`, msg.message, 'from:', msg.sender_id, 'to:', msg.receiver_id);
                    const sender = (msg.sender_id==adminId)?'admin':'user';
                    addMessage(msg.message, sender, msg.timestamp);
                });
                console.log('✅ Chat history loaded successfully - check chatBox now');
            }
        } else if(data.type==='message'){
            console.log('📩 New message received');
            const sender = (data.sender_id==adminId)?'admin':'user';
            if(data.sender_id===selectedUserId || data.receiver_id===selectedUserId){
                console.log('Message is for current chat, adding to UI');
                addMessage(data.message,sender,data.timestamp);
            } else {
                console.log('Message from different user, updating user list');
                addUserToList({
                    id: data.sender_id,
                    username: data.sender_name || 'User '+data.sender_id,
                    is_online:1,
                    unread_count:(newMessageNotif[data.sender_id]||0)+1
                });
            }
        } else if(data.type==='delete_chat'){
            console.log('🗑 Delete chat received');
            if(selectedUserId==data.user_id) clearChat();
            const el=document.querySelector(`.user-item[data-userid='${data.user_id}']`);
            if(el && el.querySelector('.notif-badge')) el.querySelector('.notif-badge').style.display='none';
        } else {
            console.log('⚠️ Unknown message type:', data.type);
        }
    };

    ws.onerror = (error)=>{
        console.error('❌ WebSocket error:', error);
        updateWsStatus('❌ WebSocket Error', '#f44336');
    };

    ws.onclose = (event)=>{
        console.log('WebSocket closed. Code:', event.code, 'Reason:', event.reason);
        updateWsStatus('🔴 WebSocket Terputus - Reconnecting...', '#ff9800');
        reconnectAttempts++;
        const delay = Math.min(1000 * reconnectAttempts, maxReconnectDelay);
        console.log(`Reconnecting in ${delay}ms... (attempt ${reconnectAttempts})`);
        setTimeout(connectWebSocket, delay);
    };
}

// ===== INIT =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}

function initializeApp() {
    console.log('===== INITIALIZING ADMIN CHAT =====');
    console.log('Admin ID:', adminId, typeof adminId);
    console.log('Admin Name:', adminName);
    console.log('WS_HOST:', WS_HOST, typeof WS_HOST);
    console.log('WS_PORT:', WS_PORT, typeof WS_PORT);
    
    if (!adminId || adminId === 0 || adminId === null) {
        alert('ERROR: Admin ID tidak ditemukan! Silakan login ulang.');
        console.error('Invalid admin ID:', adminId);
        return;
    }
    
    if (!WS_HOST || !WS_PORT) {
        alert('ERROR: WebSocket configuration tidak ditemukan!');
        console.error('Invalid WebSocket config:', WS_HOST, WS_PORT);
        return;
    }
    
    try {
        console.log('Starting WebSocket connection...');
        connectWebSocket();
        
        console.log('Loading users...');
        loadUsers();
        
        console.log('Starting polling...');
        pollUsersStatusAndMessages();
        
        console.log('===== INITIALIZATION COMPLETE =====');
    } catch (error) {
        console.error('❌ Initialization error:', error);
        alert('Error during initialization: ' + error.message);
    }
}
</script>

</body>
</html>
