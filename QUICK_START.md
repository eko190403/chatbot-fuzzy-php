# ⚡ QUICK START GUIDE

## 🚀 Instalasi Cepat

### 1. Install Dependencies
```bash
composer install
pip install -r requirements.txt
```

### 2. Setup Database
```bash
mysql -u root -e "CREATE DATABASE chat_system"
mysql -u root chat_system < "chat_system (3).sql"
```

### 3. Configure Environment
```bash
copy .env.example .env
# Edit .env dengan text editor
```

### 4. Create Folders
```bash
mkdir logs
mkdir logs\rate_limit
```

### 5. Start Services
```bash
# Terminal 1: Start WebSocket
php server.php

# Terminal 2: Start Python API
python app.py

# Terminal 3: Start Apache/MySQL (XAMPP)
```

### 6. Access Application
- User: http://localhost/chat_sistem/
- Admin: http://localhost/chat_sistem/admin_dashboard.php

---

## 🔧 Konfigurasi .env

```env
# Database
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=chat_system

# WebSocket
WS_HOST=localhost
WS_PORT=8081

# Application
APP_ENV=development
APP_DEBUG=true
```

---

## 🔒 Fitur Keamanan

✅ SQL Injection Protection  
✅ CSRF Protection  
✅ XSS Protection  
✅ Rate Limiting  
✅ Security Headers  
✅ Input Validation  
✅ Session Security  
✅ Environment Variables

---

## 📁 File Penting

- **config.php** - Konfigurasi aplikasi
- **security.php** - Helper keamanan
- **db.php** - Database connection
- **.env** - Environment variables (JANGAN COMMIT!)

---

## 🐛 Common Issues

**CSRF Error**: Clear cookies  
**Rate Limit**: Hapus `logs/rate_limit/*`  
**WebSocket**: Cek `server.php` & firewall  
**Python API**: Install `requirements.txt`

---

## 📝 Admin Default

Create via SQL:
```sql
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@gmail.com', 
'$2y$10$...hash...', 'admin');
```

Generate hash:
```php
php -r "echo password_hash('password123', PASSWORD_DEFAULT);"
```

---

## ⚠️ Production Checklist

- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] Ganti password
- [ ] Setup SSL/HTTPS
- [ ] Configure firewall
- [ ] Setup backups
- [ ] JANGAN commit .env

---

**Version**: 2.0 (Security Update)  
**Date**: 11 Januari 2026
