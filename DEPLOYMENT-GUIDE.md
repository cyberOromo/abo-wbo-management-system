# 🚀 Production Deployment Guide for j-abo-wbo.org

## 📋 Pre-Deployment Checklist

### 1. **HostGator cPanel Preparation**
- [ ] Access cPanel at your hosting provider
- [ ] Verify PHP version (recommended: PHP 8.1+)
- [ ] Ensure MySQL/MariaDB is available
- [ ] Check disk space and bandwidth limits

### 2. **Database Setup**
```sql
-- Create production database
CREATE DATABASE abo_wbo_production;
CREATE USER 'abo_wbo_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON abo_wbo_production.* TO 'abo_wbo_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. **Environment Configuration**
```bash
# Copy .env.example to .env
cp .env.example .env

# Update production settings in .env:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://j-abo-wbo.org

DB_HOST=localhost
DB_DATABASE=abo_wbo_production
DB_USERNAME=abo_wbo_user
DB_PASSWORD=your_secure_password

MAIL_MAILER=smtp
MAIL_HOST=mail.j-abo-wbo.org
MAIL_PORT=587
MAIL_USERNAME=noreply@j-abo-wbo.org
MAIL_PASSWORD=your_email_password
```

## 🔧 Deployment Methods

### Method 1: Git Clone (Recommended)
```bash
# 1. SSH into your HostGator account
ssh username@j-abo-wbo.org

# 2. Navigate to public_html
cd public_html

# 3. Clone the repository
git clone https://github.com/cyberOromo/abo-wbo-management-system.git app

# 4. Set up the application
cd app
cp .env.example .env
# Edit .env with production settings

# 5. Set permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env

# 6. Run database setup
php database/run_migrations.php
```

### Method 2: File Upload via cPanel
```bash
# 1. Download repository as ZIP from GitHub
# 2. Extract locally
# 3. Upload via cPanel File Manager to public_html/app/
# 4. Extract files
# 5. Configure .env file
# 6. Set proper permissions
```

## 🗄️ Database Migration

### Run Database Setup Scripts
```bash
# Execute in order:
php database/create_tables.php
php database/run_migrations.php
php database/comprehensive_data_insertion.php
```

### Verify Database Structure
```bash
php database/final_hierarchy_validation.php
```

## 🔒 Security Configuration

### 1. **File Permissions**
```bash
# Set secure permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 .env
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

### 2. **Apache .htaccess** (if using Apache)
```apache
# public/.htaccess
RewriteEngine On

# Handle Angular and other SPA
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Hide sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "*.md">
    Order allow,deny
    Deny from all
</Files>
```

### 3. **Nginx Configuration** (if using Nginx)
```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name j-abo-wbo.org www.j-abo-wbo.org;
    root /home/username/public_html/app/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security
    location ~ /\. {
        deny all;
    }

    location ~* \.(env|md|log)$ {
        deny all;
    }
}
```

## 📧 Email Configuration

### SMTP Settings for HostGator
```env
MAIL_MAILER=smtp
MAIL_HOST=gator4xxx.hostgator.com
MAIL_PORT=587
MAIL_USERNAME=noreply@j-abo-wbo.org
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@j-abo-wbo.org
MAIL_FROM_NAME="ABO-WBO Organization"
```

## 🔄 Backup Strategy

### Automated Backup Script
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/username/backups"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u abo_wbo_user -p abo_wbo_production > $BACKUP_DIR/db_backup_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /home/username/public_html/app

# Keep only last 30 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

### Schedule via Cron
```bash
# Add to crontab (crontab -e)
0 2 * * * /home/username/scripts/backup.sh
```

## 🚀 Post-Deployment Testing

### 1. **URL Testing**
- [ ] https://j-abo-wbo.org (homepage)
- [ ] https://j-abo-wbo.org/app/login
- [ ] https://j-abo-wbo.org/app/dashboard
- [ ] https://j-abo-wbo.org/app/tasks
- [ ] https://j-abo-wbo.org/app/meetings
- [ ] https://j-abo-wbo.org/app/events
- [ ] https://j-abo-wbo.org/app/donations

### 2. **Functionality Testing**
- [ ] User login/logout
- [ ] Task management CRUD operations
- [ ] Meeting scheduling
- [ ] Event creation and registration
- [ ] Donation processing
- [ ] File uploads
- [ ] Email notifications

### 3. **Performance Testing**
- [ ] Page load times < 3 seconds
- [ ] Database queries optimized
- [ ] Images compressed and optimized
- [ ] CSS/JS minified

## 📞 Troubleshooting

### Common Issues
```bash
# Permission errors
chmod -R 755 storage/ public/uploads/

# Database connection errors
# Check .env database settings
# Verify database credentials

# 500 Internal Server Error
# Check Apache/Nginx error logs
# Verify .htaccess rules

# File upload issues
# Check upload_max_filesize in php.ini
# Verify directory permissions
```

### Log Files to Monitor
```bash
# Application logs
tail -f storage/logs/application.log

# Web server logs
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx

# PHP logs
tail -f /var/log/php8.1-fpm.log
```

## ✅ Go-Live Checklist

- [ ] Domain DNS pointing to HostGator
- [ ] SSL certificate installed
- [ ] Database migrated and tested
- [ ] .env configured for production
- [ ] File permissions set correctly
- [ ] Email functionality tested
- [ ] Backup system implemented
- [ ] Error monitoring in place
- [ ] Performance optimized
- [ ] Security headers configured

## 🔗 Important URLs
- **Production**: https://j-abo-wbo.org/app/
- **GitHub Repository**: https://github.com/cyberOromo/abo-wbo-management-system
- **Admin Panel**: https://j-abo-wbo.org/app/admin
- **API Documentation**: https://j-abo-wbo.org/app/api/docs

---
**Ready for Production Deployment! 🎯**