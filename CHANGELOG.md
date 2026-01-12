# CHANGELOG

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-01-11 - SECURITY UPDATE ⚠️

### 🔒 Security
- **CRITICAL**: Fixed SQL injection vulnerabilities in 15+ locations
- **CRITICAL**: Added CSRF protection to all forms
- **CRITICAL**: Migrated hardcoded credentials to environment variables
- **HIGH**: Added rate limiting for login (5/5min) and register (3/10min)
- **HIGH**: Implemented XSS protection with escape functions
- **HIGH**: Added security headers (X-Frame-Options, CSP, etc.)
- **MEDIUM**: Enhanced session security (HTTPOnly, SameSite)
- **MEDIUM**: Added input validation and sanitization

### ✨ Added
- New `config.php` for centralized configuration management
- New `security.php` with security helper functions
- New `.env` file support for environment variables
- New `.gitignore` to protect sensitive files
- New `README.md` with comprehensive documentation
- New `SECURITY_AUDIT.md` with security assessment
- New `PERUBAHAN.md` with detailed change list
- New `QUICK_START.md` for quick setup guide
- New `requirements.txt` for Python dependencies
- New rate limiting system with file-based tracking
- New CSRF token generation and validation
- New escape functions for XSS protection

### 🔧 Changed
- **db.php**: Now uses config.php and security.php
- **server.php**: Loads config from environment variables
- **app.py**: Uses python-dotenv for configuration
- **login.php**: Added CSRF and rate limiting
- **register.php**: Added CSRF and rate limiting
- **index.php**: Fixed SQL injection, added CSRF
- **chat.php**: Dynamic WebSocket URL from config
- **profil_user.php**: Fixed SQL injection, added validation
- **admin_chatbot_crud.php**: Fixed multiple SQL injections
- **admin_manage_account.php**: Fixed SQL injection, added CSRF
- **get_pertanyaan.php**: Fixed SQL injection
- **hapus_chat.php**: Fixed SQL injection
- **update_online.php**: Fixed SQL injection
- **set_offline.php**: Fixed SQL injection

### 🐛 Fixed
- SQL injection in user authentication
- SQL injection in admin CRUD operations
- SQL injection in chat history queries
- SQL injection in search functionality
- XSS vulnerabilities in all output
- Missing CSRF protection
- Hardcoded database credentials
- Hardcoded WebSocket configuration
- Session hijacking vulnerabilities
- Rate limiting absence

### 📝 Documentation
- Comprehensive README.md with installation guide
- Security audit report
- Detailed changelog
- Quick start guide
- Python requirements file

### 🔄 Refactored
- Database connection handling
- Error handling and logging
- Input validation logic
- Output escaping
- Session management

---

## [1.0.0] - Original Version

### Features
- User registration and login
- AI Chatbot with fuzzy matching
- Live chat with admin via WebSocket
- Admin dashboard
- Chatbot training interface
- User management
- Chat history
- Feedback system
- Category-based questions
- Real-time notifications

### Technology Stack
- PHP 7.4+
- MySQL
- Python Flask
- RapidFuzz (fuzzy matching)
- Ratchet (WebSocket)
- Bootstrap 5
- jQuery

---

## Version History

- **2.0.0** (2026-01-11) - Security Update - Major security fixes
- **1.0.0** (Initial) - Initial release with basic features

---

## Upgrade Guide (1.0.0 → 2.0.0)

### Required Actions:

1. **Backup Everything**
   ```bash
   mysqldump -u root chat_system > backup.sql
   ```

2. **Install New Dependencies**
   ```bash
   composer install
   pip install -r requirements.txt
   ```

3. **Create .env File**
   ```bash
   copy .env.example .env
   # Edit .env dengan konfigurasi Anda
   ```

4. **Create Logs Directory**
   ```bash
   mkdir logs
   mkdir logs\rate_limit
   ```

5. **Update Database** (if schema changes)
   - No schema changes in this version

6. **Test Everything**
   - Login/Register
   - Admin functions
   - Chatbot
   - Live chat
   - WebSocket

### Breaking Changes:

- Environment variables now required (.env file)
- All forms require CSRF tokens
- Rate limiting may block rapid requests
- Some function signatures changed in security.php

### Migration Notes:

- Old sessions may need to be cleared
- Update any custom integrations to use new security functions
- Update deployment scripts to use .env

---

## Security Policy

### Reporting Vulnerabilities

If you discover a security vulnerability, please email the development team instead of using the issue tracker.

### Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 2.0.x   | ✅ Yes            |
| 1.0.x   | ❌ No (upgrade)   |

---

## Credits

- **Security Audit**: GitHub Copilot AI
- **Original Developer**: [Your Name]
- **Date**: January 11, 2026

---

## License

This project is for academic/thesis purposes.
