# GHPIMS Installation Guide

## Prerequisites

- **PHP 8.1+** with extensions: PDO, pdo_mysql, mbstring, openssl, json
- **MySQL 8.0+** or **MariaDB 10.6+**
- **Apache** with mod_rewrite enabled
- **Composer** (PHP dependency manager)
- **Git** (optional, for version control)

---

## Installation Steps

### 1. Clone or Download the Project

```bash
cd c:\xampp\htdocs
git clone <repository-url> ghpims
# OR extract the ZIP file to c:\xampp\htdocs\ghpims
```

### 2. Install Dependencies

```bash
cd ghpims
composer install
```

### 3. Configure Environment

```bash
# Copy the example environment file
copy .env.example .env

# Edit .env file with your database credentials
notepad .env
```

Update the following in `.env`:
```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ghpims
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Create Database

```bash
# Open MySQL command line or phpMyAdmin
mysql -u root -p

# Create database
CREATE DATABASE ghpims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 5. Import Database Schema

```bash
# Import the main schema
mysql -u root -p ghpims < database/db_improved.sql
```

### 6. Set Permissions

```bash
# Windows (PowerShell as Administrator)
icacls storage /grant Users:F /T
icacls public/assets/uploads /grant Users:F /T

# Linux/Mac
chmod -R 775 storage
chmod -R 775 public/assets/uploads
```

### 7. Configure Apache

**Option A: Using XAMPP**

1. Start Apache from XAMPP Control Panel
2. Access: `http://localhost/ghpims/public`

**Option B: Virtual Host (Recommended)**

Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName ghpims.local
    DocumentRoot "C:/xampp/htdocs/ghpims/public"
    
    <Directory "C:/xampp/htdocs/ghpims/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/ghpims-error.log"
    CustomLog "logs/ghpims-access.log" common
</VirtualHost>
```

Edit `C:\Windows\System32\drivers\etc\hosts` (as Administrator):
```
127.0.0.1 ghpims.local
```

Restart Apache and access: `http://ghpims.local`

### 8. Create Default Admin User

```sql
-- Run this in MySQL
USE ghpims;

-- Insert default admin user (password: admin123)
INSERT INTO users (
    service_number, first_name, last_name, rank, role_id, 
    username, password_hash, email, status
) VALUES (
    'ADMIN001', 'System', 'Administrator', 'Administrator', 1,
    'admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lE7zJQK5TARG',
    'admin@ghpims.local', 'Active'
);
```

**Default Login:**
- Username: `admin`
- Password: `admin123`

⚠️ **IMPORTANT:** Change the default password immediately after first login!

---

## Post-Installation

### 1. Test the Installation

Visit `http://localhost/ghpims/public` or `http://ghpims.local`

You should see the login page.

### 2. Login with Default Credentials

- Username: `admin`
- Password: `admin123`

### 3. Change Default Password

1. Login with default credentials
2. Go to Profile → Change Password
3. Set a strong password

### 4. Configure System Settings

1. Go to Settings
2. Configure:
   - Station details
   - Email settings (optional)
   - File upload limits
   - Session timeout

---

## Troubleshooting

### Issue: 404 Error on All Pages

**Solution:** Enable mod_rewrite in Apache

```bash
# Edit httpd.conf
# Uncomment this line:
LoadModule rewrite_module modules/mod_rewrite.so

# Restart Apache
```

### Issue: Database Connection Failed

**Solution:** Check database credentials in `.env` file

```bash
# Test database connection
mysql -u root -p -h localhost ghpims
```

### Issue: Permission Denied Errors

**Solution:** Set proper permissions

```bash
# Windows
icacls storage /grant Users:F /T

# Linux/Mac
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Issue: Blank Page or PHP Errors

**Solution:** Enable error display

```bash
# Edit .env
APP_DEBUG=true

# Check PHP error log
tail -f C:\xampp\php\logs\php_error_log
```

### Issue: Session Not Working

**Solution:** Check session directory permissions

```bash
# Ensure storage/sessions is writable
icacls storage\sessions /grant Users:F /T
```

---

## Security Checklist

Before deploying to production:

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Generate unique `APP_KEY` in `.env`
- [ ] Enable HTTPS (SSL certificate)
- [ ] Restrict database user permissions
- [ ] Set proper file permissions (read-only for most files)
- [ ] Configure firewall rules
- [ ] Enable automatic backups
- [ ] Review and configure security headers
- [ ] Disable directory listing
- [ ] Remove or secure phpMyAdmin

---

## Backup & Restore

### Backup Database

```bash
mysqldump -u root -p ghpims > backup_$(date +%Y%m%d).sql
```

### Backup Files

```bash
# Backup uploads and logs
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz storage/uploads
tar -czf logs_backup_$(date +%Y%m%d).tar.gz storage/logs
```

### Restore Database

```bash
mysql -u root -p ghpims < backup_20241217.sql
```

---

## Updating the System

### Update Code

```bash
cd c:\xampp\htdocs\ghpims
git pull origin main
composer install
```

### Run Migrations

```bash
mysql -u root -p ghpims < database/migration.sql
```

### Clear Cache

```bash
# Delete cache files
del /Q storage\cache\*
```

---

## Support

For issues and support:

- **Documentation:** See `DEVELOPMENT_PLAN.md` and `DATABASE_DOCUMENTATION.md`
- **Logs:** Check `storage/logs/app.log`
- **Database Issues:** Check MySQL error log

---

## System Requirements (Production)

### Minimum Requirements
- **CPU:** 2 cores
- **RAM:** 4GB
- **Storage:** 50GB SSD
- **PHP:** 8.1+
- **MySQL:** 8.0+
- **Apache:** 2.4+

### Recommended Requirements
- **CPU:** 4+ cores
- **RAM:** 8GB+
- **Storage:** 100GB+ SSD
- **PHP:** 8.2+
- **MySQL:** 8.0+ or MariaDB 10.11+
- **Apache:** 2.4+ with HTTP/2

---

## Performance Optimization

### Enable OPcache

Edit `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Enable MySQL Query Cache

Edit `my.ini`:
```ini
query_cache_type=1
query_cache_size=64M
```

### Enable Gzip Compression

Edit `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

**Installation Complete!** 🎉

Your GHPIMS system is now ready to use.
