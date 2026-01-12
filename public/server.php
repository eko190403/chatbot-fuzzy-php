<?php
date_default_timezone_set('Asia/Jakarta');
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $admins = [];
    protected $userConns = [];
    private $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        
        // Use configuration from config.php
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $this->db = new PDO($dsn, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "Server started on port " . WS_PORT . "...\n";
        echo "Environment: " . APP_ENV . "\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);

        if(!isset($queryParams['user_id'])){
            echo "[ERROR] Connection rejected: No user_id\n";
            $conn->close();
            return;
        }

        $userId = (int)$queryParams['user_id'];

        $userInfo = $this->getUserInfo($userId);
        if(!$userInfo){
            echo "[ERROR] Connection rejected: Invalid user (ID: $userId)\n";
            $conn->send(json_encode(['type'=>'error','message'=>'Invalid user']));
            $conn->close();
            return;
        }

        $role = $userInfo['role'];
        $username = $userInfo['username'];
        
        echo "[CONNECTED] User: $username (ID: $userId, Role: $role)\n";

        $this->users[$conn->resourceId] = [
            'id' => $userId,
            'role' => $role,
            'conn' => $conn,
            'username' => $username
        ];

        $this->setUserStatus($userId,'online');

        if($role === 'admin'){
            $this->admins[$userId] = $conn;
        } else {
            $this->userConns[$userId] = $conn;

            if($userInfo['is_online'] == 0){
                $payload = json_encode([
                    'type'=>'new_connection',
                    'user'=>[
                        'id'=>$userId,
                        'username'=>$username,
                        'is_online'=>1
                    ]
                ]);
                foreach($this->admins as $adminConn){
                    $adminConn->send($payload);
                }
            }
        }

        $this->sendUserListToAdmins();
        $this->broadcastStatusChange($userId,true);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg,true);
        if(!$data || !isset($data['type'])) return;

        $userId = $this->users[$from->resourceId]['id'];
        $role = $this->users[$from->resourceId]['role'];
        
        echo "[Message] User: $userId, Role: $role, Type: {$data['type']}\n";

        switch($data['type']){
            case 'message':
                $this->handleNewMessage($userId,$role,$data);
                break;
            case 'get_history':
                $partnerId=$data['partner_id']??null;
                echo "[get_history] Partner ID: $partnerId\n";
                $this->sendChatHistory($from,$userId,$role,$partnerId);
                break;
            case 'delete_chat':
                $this->handleDeleteAll($userId,$role);
                break;
            case 'delete_one':
                $this->handleDeleteOne($data['message_id']??0,$userId,$role);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if(!isset($this->users[$conn->resourceId])) return;
        $userId = $this->users[$conn->resourceId]['id'];
        $role = $this->users[$conn->resourceId]['role'];

        $this->setUserStatus($userId,'offline');
        $this->broadcastStatusChange($userId,false);

        unset($this->users[$conn->resourceId]);
        if($role==='admin') unset($this->admins[$userId]);
        else unset($this->userConns[$userId]);

        $this->clients->detach($conn);
        $this->sendUserListToAdmins();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    private function setUserStatus($userId,$status){
        $stmt = $this->db->prepare("UPDATE users SET is_online=? WHERE id=?");
        $stmt->execute([$status==='online'?1:0,$userId]);
    }

    private function broadcastStatusChange($userId,$isOnline){
        $info = $this->getUserInfo($userId);
        $payload = json_encode([
            'type'=>'status_change',
            'user'=>[
                'id'=>$userId,
                'username'=>$info['username']??'User '.$userId,
                'is_online'=>$isOnline?1:0
            ]
        ]);
        foreach($this->admins as $adminConn) $adminConn->send($payload);
    }

    private function handleNewMessage($senderId,$senderRole,$data){
        if(empty($data['receiver_id']) || empty($data['message'])) return;
        $receiverId = (int)$data['receiver_id'];
        $message = trim($data['message']);
        $receiverRole = $senderRole==='admin'?'user':'admin';

        $stmt=$this->db->prepare("INSERT INTO messages (sender_id,receiver_id,message,is_read,timestamp) VALUES (?,?,?,0,NOW())");
        $stmt->execute([$senderId,$receiverId,$message]);
        $timestamp = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format(DateTime::ATOM);

        $payload = json_encode([
            'type'=>'message',
            'sender_id'=>$senderId,
            'receiver_id'=>$receiverId,
            'message'=>$message,
            'timestamp'=>$timestamp,
            'sender_name'=>$this->getUsername($senderId)
        ]);

        if($receiverRole==='admin' && isset($this->admins[$receiverId])) $this->admins[$receiverId]->send($payload);
        elseif($receiverRole==='user' && isset($this->userConns[$receiverId])) $this->userConns[$receiverId]->send($payload);

        $this->sendUserListToAdmins();
    }

    private function sendChatHistory($conn,$userId,$role,$partnerId=null){
        echo "[sendChatHistory] UserId: $userId, Role: $role, PartnerId: $partnerId\n";
        
        if($role!=='admin') $partnerId = $this->getAdminId();
        if(!$partnerId){ 
            echo "[sendChatHistory] No partner ID, sending empty history\n";
            $conn->send(json_encode(['type'=>'chat_history','messages'=>[]])); 
            return; 
        }

        $stmt=$this->db->prepare("
            SELECT m.*, u.username as sender_name
            FROM messages m
            JOIN users u ON m.sender_id=u.id
            WHERE (m.sender_id=:uid1 AND m.receiver_id=:uid2) OR (m.sender_id=:uid2 AND m.receiver_id=:uid1)
            ORDER BY m.timestamp ASC
        ");
        $stmt->execute([':uid1'=>$userId,':uid2'=>$partnerId]);
        $messages=$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "[sendChatHistory] Found " . count($messages) . " messages\n";

        foreach($messages as &$msg){
            $dt=new DateTime($msg['timestamp'], new DateTimeZone('Asia/Jakarta'));
            $msg['timestamp']=$dt->format(DateTime::ATOM);
        }

        $conn->send(json_encode(['type'=>'chat_history','messages'=>$messages]));

        $update=$this->db->prepare("UPDATE messages SET is_read=1 WHERE sender_id=:sender AND receiver_id=:receiver");
        $update->execute([':sender'=>$partnerId,':receiver'=>$userId]);

        $this->sendUserListToAdmins();
    }

    private function getAdminId(){
        $stmt=$this->db->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getUsername($userId){
        $stmt=$this->db->prepare("SELECT username FROM users WHERE id=?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    private function getUserInfo($userId){
        $stmt=$this->db->prepare("SELECT id, username, role, is_online FROM users WHERE id=?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function sendUserListToAdmins(){
        $userList=[];
        $adminId = $this->getAdminId();

        foreach($this->userConns as $id=>$conn){
            $info = $this->getUserInfo($id);

            $lastMsgStmt = $this->db->prepare("
                SELECT message, timestamp 
                FROM messages 
                WHERE (sender_id=:user AND receiver_id=:admin) OR (sender_id=:admin AND receiver_id=:user)
                ORDER BY timestamp DESC LIMIT 1
            ");
            $lastMsgStmt->execute([':user'=>$id, ':admin'=>$adminId]);
            $last = $lastMsgStmt->fetch(PDO::FETCH_ASSOC);
            $time = $last ? (new DateTime($last['timestamp'],new DateTimeZone('Asia/Jakarta')))->format(DateTime::ATOM) : '';

            $unreadStmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM messages 
                WHERE sender_id=:user AND receiver_id=:admin AND is_read=0
            ");
            $unreadStmt->execute([':user'=>$id, ':admin'=>$adminId]);
            $unreadCount = (int)$unreadStmt->fetchColumn();

            $userList[]=[
                'id'=>$id,
                'username'=>$info['username']??'User '.$id,
                'is_online'=>(int)$info['is_online'],
                'last_message'=>$last['message']??'',
                'last_time'=>$time,
                'unread_count'=>$unreadCount
            ];
        }

        $payload=json_encode(['type'=>'user_list','users'=>$userList]);
        foreach($this->admins as $adminConn) $adminConn->send($payload);
    }

    // Tambahan: handle hapus semua chat
    private function handleDeleteAll($userId,$role){
        if($role==='admin'){
            $this->db->exec("DELETE FROM messages");
            $payload=json_encode(['type'=>'delete_all']);
            foreach($this->clients as $c) $c->send($payload);
        } else {
            $stmt=$this->db->prepare("DELETE FROM messages WHERE sender_id=:uid OR receiver_id=:uid");
            $stmt->execute([':uid'=>$userId]);
            $payload=json_encode(['type'=>'delete_all','user_id'=>$userId]);
            foreach($this->clients as $c) $c->send($payload);
        }
    }

    private function handleDeleteOne($messageId,$userId,$role){
        if(!$messageId) return;
        if($role==='admin'){
            $stmt=$this->db->prepare("DELETE FROM messages WHERE id=?");
            $stmt->execute([$messageId]);
        } else {
            $stmt=$this->db->prepare("DELETE FROM messages WHERE id=? AND sender_id=?");
            $stmt->execute([$messageId,$userId]);
        }
        $payload=json_encode(['type'=>'delete_one','message_id'=>$messageId]);
        foreach($this->clients as $c) $c->send($payload);
    }
}

$server = IoServer::factory(new HttpServer(new WsServer(new ChatServer())),8081,'0.0.0.0');
$server->run();
