<?php
require_once 'session_init.php';
session_start();

include __DIR__ . "/db.php";

if (!isset($_SESSION['user_id']) || trim($_SESSION['role']) !== 'user') {
    header("Location: login.php");
    exit();
}

// Keep session alive
$_SESSION['last_activity'] = time();

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

// Ambil WebSocket config
$ws_host = defined('WS_HOST') ? WS_HOST : 'localhost';
$ws_port = defined('WS_PORT') ? WS_PORT : '8081';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Live Chat - <?= $username ?></title>
    <?= csrfMeta() ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset & Global */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Container Utama */
        .chat-container {
            width: 100%;
            max-width: 500px;
            height: 90vh;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header */
        .chat-header {
            background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            color: #075E54;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .header-info {
            display: flex;
            flex-direction: column;
        }

        .header-info h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        #status {
            font-size: 12px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4caf50;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Dropdown Menu */
        .header-right {
            position: relative;
        }

        .dropdown-menu-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.3s;
        }

        .dropdown-menu-btn:hover {
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
            min-width: 180px;
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
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-menu button:hover {
            background: #f5f5f5;
        }

        .dropdown-menu button i {
            color: #ff4444;
        }

        /* Chat Box */
        .chat-box {
            flex: 1;
            padding: 20px;
            background: #ECE5DD;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.3;
            color: #128C7E;
        }

        /* Bubble Chat */
        .message {
            position: relative;
            max-width: 75%;
            padding: 10px 14px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
            border-radius: 18px;
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

        .message.user {
            align-self: flex-end;
            background: #DCF8C6;
            color: #000;
            border-bottom-right-radius: 4px;
        }

        .message.admin {
            align-self: flex-start;
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        /* Tombol Hapus di Bubble */
        .delete-btn {
            display: none;
            position: absolute;
            top: -8px;
            right: -8px;
            width: 22px;
            height: 22px;
            font-size: 14px;
            color: #fff;
            background: #ff4444;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(255,68,68,0.4);
        }

        .delete-btn:hover {
            transform: scale(1.1);
            background: #cc0000;
        }

        .message:hover .delete-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Timestamp */
        .timestamp {
            margin-top: 4px;
            font-size: 10px;
            opacity: 0.7;
            text-align: right;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: none;
            align-self: flex-start;
            padding: 10px 14px;
            background: white;
            border-radius: 18px;
            max-width: 75px;
        }

        .typing-indicator.active {
            display: flex;
            align-items: center;
            gap: 4px;
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

        /* Input Chat */
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
            font-size: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
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
            right: 0;
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

        .chat-input button {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s;
        }

        .chat-input button:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(18,140,126,0.4);
        }

        /* Scrollbar */
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }

        .chat-box::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background: #128C7E;
            border-radius: 10px;
        }

        /* Responsif */
        @media (max-width: 600px) {
            body {
                background: white;
            }

            .chat-container {
                width: 100%;
                height: 100vh;
                max-width: 100%;
                border-radius: 0;
            }

            .message {
                font-size: 13px;
                max-width: 85%;
            }
        }

        /* User info badge */
        .user-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-badge i {
            font-size: 10px;
        }

        /* Logout button */
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 6px 12px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>

<div class="chat-container">
    <!-- Header -->
    <div class="chat-header">
        <div class="header-left">
            <div class="admin-avatar">A</div>
            <div class="header-info">
                <h3>Admin Support</h3>
                <div id="status">
                    <span class="status-dot"></span>
                    <span>Menghubungkan...</span>
                </div>
            </div>
        </div>
        <div class="header-right">
            <button class="dropdown-menu-btn" onclick="toggleDropdown()">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div id="dropdown-menu" class="dropdown-menu">
                <button onclick="hapusSemua()">
                    <i class="fas fa-trash"></i> Hapus Semua Chat
                </button>
                <button onclick="location.href='logout.php'">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Chat Box -->
    <div class="chat-box" id="chat-box">
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <p>Mulai percakapan dengan admin</p>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div class="typing-indicator" id="typingIndicator">
        <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <!-- Input Chat -->
    <div class="chat-input">
        <div class="chat-input-wrapper">
            <input type="text" id="message" placeholder="Ketik pesan..." onkeydown="handleEnter(event)">
            <button class="emoji-btn" onclick="toggleEmojiPicker(event)">😊</button>
            <div class="emoji-picker" id="emojiPicker">
                <div class="emoji-grid" id="emojiGrid"></div>
            </div>
        </div>
        <button onclick="sendMessage()" aria-label="Kirim">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<!-- Notification Sound -->
<audio id="notificationSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZSA0PVbHm7qxZGQtDmN7xuXElBSyAzvPXijYIG2W77Ogz" type="audio/wav">
</audio>

<script>
const userId = <?= json_encode($user_id) ?>;
const username = <?= json_encode($username) ?>;
const role = "user";
const chatBox = document.getElementById("chat-box");
const statusText = document.getElementById("status");
const notificationSound = document.getElementById("notificationSound");

const WS_HOST = "<?= $ws_host ?>";
const WS_PORT = "<?= $ws_port ?>";

let ws = new WebSocket(`ws://${WS_HOST}:${WS_PORT}?user_id=${userId}`);
let typingTimeout = null;

console.log('User connecting:', userId, 'to', `ws://${WS_HOST}:${WS_PORT}`);

function playNotificationSound() {
    try {
        notificationSound.currentTime = 0;
        notificationSound.play().catch(e => console.log('Sound play failed:', e));
    } catch(e) {
        console.log('Sound error:', e);
    }
}

function showTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    indicator.classList.add('active');
    
    if(typingTimeout) clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        indicator.classList.remove('active');
    }, 3000);
}

ws.onopen = () => {
    statusText.innerHTML = '<span class="status-dot"></span><span>Terhubung</span>';
    console.log('User connected to WebSocket');
    ws.send(JSON.stringify({ type: "get_history" }));
};

// Event WebSocket
ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log('WS Message:', data.type, data);

    if (data.type === "chat_history" && Array.isArray(data.messages)) {
        // Remove empty state
        const emptyState = chatBox.querySelector('.empty-state');
        if(emptyState) emptyState.remove();
        
        chatBox.innerHTML = "";
        data.messages.forEach(msg => {
            const sender = String(msg.sender_id) === String(userId) ? "user" : "admin";
            addMessage(msg.message, sender, msg.timestamp, msg.id);
        });
    } else if (data.type === "message") {
        const sender = String(data.sender_id) === String(userId) ? "user" : "admin";
        addMessage(data.message, sender, data.timestamp, data.id);
        
        // Play sound for admin messages
        if(sender === "admin") {
            playNotificationSound();
        }
    } else if (data.type === "typing") {
        if(data.user_id !== userId) {
            showTypingIndicator();
        }
    }
};

ws.onclose = () => {
    statusText.innerHTML = '<span style="color: #ff4444;"><i class="fas fa-exclamation-circle"></i> Terputus</span>';
    console.log('WebSocket disconnected');
};

ws.onerror = (error) => {
    console.error('WebSocket error:', error);
    statusText.innerHTML = '<span style="color: #ff4444;"><i class="fas fa-exclamation-circle"></i> Error</span>';
};

function sendMessage() {
    const input = document.getElementById("message");
    const message = input.value.trim();
    if (!message) return;

    // Remove empty state if exists
    const emptyState = chatBox.querySelector('.empty-state');
    if(emptyState) emptyState.remove();

    ws.send(JSON.stringify({
        type: "message",
        sender_id: userId,
        receiver_id: 4, // admin_id
        message: message
    }));

    addMessage(message, "user", new Date().toISOString(), Date.now());
    input.value = "";
}

function addMessage(text, sender, timestamp = null, id = null) {
    const wrapper = document.createElement("div");
    wrapper.className = "message " + sender;
    wrapper.dataset.id = id;

    if (sender === "user") {
        const delBtn = document.createElement("button");
        delBtn.className = "delete-btn";
        delBtn.innerHTML = '<i class="fas fa-times"></i>';
        delBtn.onclick = () => hapusPerChat(wrapper, id);
        wrapper.appendChild(delBtn);
    }

    const content = document.createElement("div");
    content.textContent = text;

    const time = document.createElement("div");
    time.className = "timestamp";
    const formattedTime = timestamp 
        ? new Date(timestamp).toLocaleTimeString("id-ID", { hour: '2-digit', minute: '2-digit' }) 
        : new Date().toLocaleTimeString("id-ID", { hour: '2-digit', minute: '2-digit' });
    time.textContent = formattedTime;

    wrapper.appendChild(content);
    wrapper.appendChild(time);
    chatBox.appendChild(wrapper);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function handleEnter(e) {
    if (e.key === "Enter") {
        sendMessage();
        e.preventDefault();
    }
}

// Dropdown
function toggleDropdown() {
    const menu = document.getElementById("dropdown-menu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}

// Hapus Semua Chat
function hapusSemua() {
    if (!confirm("Yakin ingin menghapus semua riwayat chat?")) return;

    fetch("hapus_chat_live.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "hapus_semua=1"
    })
    .then(res => res.text())
    .then(res => {
        if(res.trim() === "ok") {
            chatBox.innerHTML = '<div class="empty-state"><i class="fas fa-comments"></i><p>Chat telah dihapus</p></div>';
            alert("Semua chat berhasil dihapus!");
            toggleDropdown();
            if(ws && ws.readyState === WebSocket.OPEN){
                ws.send(JSON.stringify({ type: "delete_all", user_id: userId }));
            }
        } else {
            alert("Gagal hapus semua chat!");
        }
    })
    .catch(err => console.error(err));
}

// Hapus per chat
function hapusPerChat(bubble, id) {
    if (!confirm("Hapus chat ini?")) return;

    fetch("hapus_chat_live.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "message_id=" + id
    })
    .then(res => res.text())
    .then(res => {
        if(res.trim() === "ok") {
            bubble.remove();
            if(ws && ws.readyState === WebSocket.OPEN){
                ws.send(JSON.stringify({ type: "delete_one", message_id: id }));
            }
        } else {
            alert("Gagal menghapus chat!");
        }
    })
    .catch(err => console.error(err));
}

// Jangan sembunyikan dropdown saat klik chat
window.addEventListener("click", function(e) {
    if (!e.target.closest(".dropdown-menu-btn") && !e.target.closest(".dropdown-menu")) {
        document.getElementById("dropdown-menu").style.display = "none";
    }
});

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
    const input = document.getElementById('message');
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

// Initialize emoji picker on load
initEmojiPicker();

// Send typing notification
let typingSendTimeout = null;
document.getElementById('message').addEventListener('input', function() {
    if(typingSendTimeout) clearTimeout(typingSendTimeout);
    
    if(ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({
            type: 'typing',
            user_id: userId,
            receiver_id: 4 // admin
        }));
    }
    
    typingSendTimeout = setTimeout(() => {
        // Stop typing indicator
    }, 3000);
});
</script>
</body>
</html>
