# 🚀 HostGator cPanel Deployment Guide
# ABO-WBO Management System - Production Deployment

## 📋 Pre-Deployment File Analysis

### ✅ SAFE TO DEPLOY (Production-Ready Files)
```
app/
├── Controllers/          # All PHP controllers
├── Core/                # Framework core files
├── Middleware/          # Authentication middleware
├── Models/              # Database models
├── Repositories/        # Data repositories
├── Services/            # Business logic
├── Utils/               # Utility functions
├── Validators/          # Form validators
└── Views/               # All view templates

config/
├── app.php              # App configuration (no secrets)
├── database.php         # DB config template
└── storage.php          # Storage configuration

database/
├── migrations/          # Database migration files
├── seeds/               # Sample data (optional)
└── *.sql               # Schema files

public/
├── index.php            # Application entry point
├── assets/              # CSS, JS, images
├── uploads/             # User uploads folder
└── .htaccess           # Apache configuration

resources/
└── views/               # All view templates

routes/
└── web.php             # Application routes

vendor/                  # Composer dependencies (if needed)
└── autoload.php        # Autoloader

Root Files:
├── composer.json        # Dependency management
├── .htaccess           # Root Apache config
├── README.md           # Documentation
└── phpunit.xml         # Testing config
```

### ❌ NEVER DEPLOY (Security Risk / Unnecessary)
```
🔥 CRITICAL - NEVER UPLOAD:
├── .env                 # Contains secrets and passwords
├── .env.local          # Local environment variables
├── .env.example        # Template (safe but not needed)
├── storage/logs/       # Local log files
├── storage/cache/      # Local cache files
├── storage/sessions/   # Local session data
└── storage/temp/       # Temporary files

💾 DEVELOPMENT FILES - EXCLUDE:
├── .vscode/            # VS Code settings
├── .idea/              # PhpStorm settings
├── .git/               # Git repository (use Git deployment instead)
├── node_modules/       # Node.js dependencies
├── tests/              # Unit tests (optional)
├── *.log              # Log files
├── Thumbs.db          # Windows thumbnails
├── .DS_Store          # macOS metadata
└── github-setup-commands.txt  # Setup instructions
```

## 🏢 HostGator cPanel Step-by-Step Deployment

### Phase 1: Environment Setup on HostGator

#### Step 1: Access HostGator cPanel
1. **Login to HostGator cPanel**
   - URL: `https://gator4xxx.hostgator.com:2083` (replace with your server)
   - Username: Your cPanel username
   - Password: Your cPanel password

#### Step 2: Create Subdomain for Staging
1. **Navigate to Subdomains**
   - In cPanel, find "Subdomains" under "Domains" section
   - Click "Subdomains"

2. **Create Staging Subdomain**
   ```
   Subdomain: staging
   Domain: j-abo-wbo.org
   Document Root: /public_html/staging
   ```
   - Click "Create"

3. **Verify Subdomain Creation**
   - Confirm `staging.j-abo-wbo.org` is created
   - Document root should be `/public_html/staging`

#### Step 3: Create Production Directory
1. **Navigate to File Manager**
   - In cPanel, click "File Manager"
   - Go to `/public_html/`

2. **Create App Directory**
   ```
   Directory: /public_html/app
   ```
   - Right-click → Create Folder → Name: "app"

### Phase 2: Database Setup

#### Step 4: Create Databases
1. **Navigate to MySQL Databases**
   - In cPanel, find "MySQL Databases"

2. **Create Staging Database**
   ```
   Database Name: cpanel_user_abo_staging
   ```
   - Click "Create Database"

3. **Create Production Database**
   ```
   Database Name: cpanel_user_abo_production
   ```
   - Click "Create Database"

4. **Create Database User**
   ```
   Username: cpanel_user_abo_user
   Password: [Generate strong password - SAVE THIS!]
   ```
   - Click "Create User"

5. **Assign User to Databases**
   - Select user: `cpanel_user_abo_user`
   - Select databases: Both staging and production
   - Privileges: "ALL PRIVILEGES"
   - Click "Make Changes"

### Phase 3: Git Deployment Setup

#### Step 5: Enable Git Version Control (Recommended Method)
1. **Navigate to Git Version Control**
   - In cPanel, find "Git Version Control"
   - Click to open

2. **Create Repository for Staging**
   ```
   Repository Path: /public_html/staging
   Clone URL: https://github.com/cyberOromo/abo-wbo-management-system.git
   Branch: develop
   ```
   - Click "Create"

3. **Create Repository for Production**
   ```
   Repository Path: /public_html/app
   Clone URL: https://github.com/cyberOromo/abo-wbo-management-system.git
   Branch: main
   ```
   - Click "Create"

### Phase 4: Environment Configuration

#### Step 6: Create Environment Files
1. **Navigate to Staging Directory**
   - File Manager → `/public_html/staging`

2. **Create Staging .env File**
   ```env
   APP_ENV=staging
   APP_DEBUG=true
   APP_URL=https://staging.j-abo-wbo.org

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=cpanel_user_abo_staging
   DB_USERNAME=cpanel_user_abo_user
   DB_PASSWORD=[your_database_password]

   MAIL_MAILER=smtp
   MAIL_HOST=mail.j-abo-wbo.org
   MAIL_PORT=587
   MAIL_USERNAME=staging@j-abo-wbo.org
   MAIL_PASSWORD=[your_email_password]
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=staging@j-abo-wbo.org
   MAIL_FROM_NAME="ABO-WBO Staging"
   ```

3. **Create Production .env File**
   - Navigate to `/public_html/app`
   - Create `.env` with production settings:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://j-abo-wbo.org/app

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=cpanel_user_abo_production
   DB_USERNAME=cpanel_user_abo_user
   DB_PASSWORD=[your_database_password]

   MAIL_MAILER=smtp
   MAIL_HOST=mail.j-abo-wbo.org
   MAIL_PORT=587
   MAIL_USERNAME=noreply@j-abo-wbo.org
   MAIL_PASSWORD=[your_email_password]
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@j-abo-wbo.org
   MAIL_FROM_NAME="ABO-WBO Organization"
   ```

#### Step 7: Set File Permissions
1. **Select All Files in Staging**
   - File Manager → `/public_html/staging`
   - Select all files (Ctrl+A)
   - Right-click → Change Permissions

2. **Set Permissions**
   ```
   Files: 644 (rw-r--r--)
   Directories: 755 (rwxr-xr-x)
   ```
   - Check "Recurse into subdirectories"
   - Click "Change Permissions"

3. **Special Permissions for Storage**
   ```
   /storage/ → 755
   /storage/logs/ → 755
   /storage/uploads/ → 755
   /storage/cache/ → 755
   /storage/sessions/ → 755
   ```

4. **Repeat for Production**
   - Same permissions for `/public_html/app`

### Phase 5: Database Migration

#### Step 8: Import Database Schema
1. **Access phpMyAdmin**
   - cPanel → phpMyAdmin
   - Select staging database

2. **Import Schema**
   - Click "Import" tab
   - Choose file: Upload `database/schema.sql`
   - Click "Go"

3. **Run Data Insertion**
   - Import `database/comprehensive_data_insertion.sql`
   - Import any other required SQL files

4. **Repeat for Production Database**
   - Select production database
   - Import same files

### Phase 6: Testing & Verification

#### Step 9: Test Staging Deployment
1. **Access Staging Site**
   - URL: `https://staging.j-abo-wbo.org`
   - Verify homepage loads

2. **Test Key Functionality**
   ```
   ✓ Homepage loads without errors
   ✓ Login page accessible
   ✓ Database connection working
   ✓ Tasks module loads
   ✓ Meetings module loads
   ✓ Events module loads
   ✓ Donations module loads
   ```

3. **Check Error Logs**
   - cPanel → Error Logs
   - Look for any PHP errors

#### Step 10: Deploy to Production
1. **After Staging Tests Pass**
   - Access production site: `https://j-abo-wbo.org/app`
   - Verify all functionality

2. **Update Git Repositories**
   - cPanel → Git Version Control
   - Pull latest changes for both staging and production

## 🔧 Post-Deployment Configuration

### Security Headers (.htaccess)
```apache
# Add to /public_html/app/.htaccess
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000"
</IfModule>

# Hide sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

### Backup Configuration
```bash
# Create backup script in cPanel cron jobs
# Daily backup at 2 AM
0 2 * * * /usr/bin/mysqldump -u [username] -p[password] [database] > /home/[user]/backups/backup_$(date +\%Y\%m\%d).sql
```

## 📞 Troubleshooting

### Common Issues & Solutions
```
❌ 500 Internal Server Error
Solution: Check error logs, verify .htaccess syntax, check file permissions

❌ Database Connection Error
Solution: Verify database credentials in .env, check database user permissions

❌ File Permission Errors
Solution: Set correct permissions (644 for files, 755 for directories)

❌ Email Not Working
Solution: Verify SMTP settings, check mail server configuration
```

## ✅ Deployment Checklist

### Before Deployment
- [ ] GitHub repository is up to date
- [ ] All sensitive files are in .gitignore
- [ ] Database credentials are secure
- [ ] Email configuration is ready

### During Deployment
- [ ] Subdomain created (staging.j-abo-wbo.org)
- [ ] App directory created (/public_html/app)
- [ ] Databases created (staging + production)
- [ ] Database user created and assigned
- [ ] Git repositories cloned
- [ ] Environment files configured
- [ ] File permissions set correctly
- [ ] Database schema imported

### After Deployment
- [ ] Staging site tested thoroughly
- [ ] Production site verified
- [ ] Error logs checked
- [ ] Backup system configured
- [ ] Security headers added

## 🎯 URLs After Deployment
- **Staging**: https://staging.j-abo-wbo.org
- **Production**: https://j-abo-wbo.org/app
- **Admin Panel**: https://j-abo-wbo.org/app/admin
- **API Docs**: https://j-abo-wbo.org/app/api/docs

---
**Ready to Deploy! Follow each step carefully and test thoroughly.**