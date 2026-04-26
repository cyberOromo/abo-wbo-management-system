# 🚀 HostGator Deployment Guide - Step by Step

## 📋 DEPLOYMENT STRATEGY

### Environment Setup:
- **Staging**: `staging.j-abo-wbo.org` → `develop` branch (for testing)
- **Production**: `j-abo-wbo.org/app` → `main` branch (for live use)

---

## 🔧 STEP 1: HostGator cPanel Initial Setup

### 1.1 Access cPanel
```
1. Login to your HostGator account
2. Click "cPanel" from your hosting dashboard
3. Navigate to cPanel main interface
```

### 1.2 Create Subdomain for Staging
```
1. In cPanel, find "Subdomains" (under Domains section)
2. Click "Create Subdomain"
3. Subdomain: staging
4. Domain: j-abo-wbo.org
5. Document Root: /public_html/staging
6. Click "Create"
```

### 1.3 Set Up Directory Structure
```
1. Open "File Manager" in cPanel
2. Navigate to /public_html/
3. Create these folders:
   - /public_html/staging/ (for staging.j-abo-wbo.org)
   - /public_html/app/ (for j-abo-wbo.org/app)
```

---

## 🗄️ STEP 2: Database Setup

### 2.1 Create Staging Database
```
1. In cPanel, go to "MySQL Databases"
2. Create Database:
   - Database Name: [cpanel_user]_abo_staging
   - Click "Create Database"
```

### 2.2 Create Production Database
```
1. Create Database:
   - Database Name: [cpanel_user]_abo_production
   - Click "Create Database"
```

### 2.3 Create Database User
```
1. In "MySQL Users" section:
   - Username: [cpanel_user]_abouser
   - Password: [Generate Strong Password - SAVE THIS!]
   - Click "Create User"

2. Add User to Databases:
   - Select User: [cpanel_user]_abouser
   - Select Database: [cpanel_user]_abo_staging
   - Grant ALL PRIVILEGES
   - Click "Make Changes"
   
   Repeat for production database
```

---

## 📂 STEP 3: Prepare Files for Deployment

### 3.1 Files to INCLUDE (Deploy These):
```
✅ Application Core:
   - app/ (all controllers, models, views)
   - config/ (app.php, database.php, storage.php)
   - public/ (index.php, assets, CSS, JS)
   - resources/ (views, language files)
   - routes/ (web.php)
   - database/ (migrations, seeds, schema.sql)
   - storage/ (create empty, will be populated)
   - lang/ (language files)
   - docs/ (if needed)

✅ Configuration:
   - .htaccess (if exists in public/)
   - composer.json (if you plan to run composer on server)
   - .env.example (template for server .env)

✅ Documentation:
   - README.md
   - API-Documentation.md
   - Any deployment guides
```

### 3.2 Files to EXCLUDE (DO NOT Deploy):
```
❌ NEVER Deploy These:
   - .env (contains local secrets!)
   - .env.development
   - .env.production (local version)
   - server.log
   - Any files with passwords/secrets
   
❌ Development Files:
   - .git/ (version control stays local)
   - node_modules/ (if exists)
   - vendor/ (optional - can install on server)
   - .vscode/
   - .idea/
   - *.log files
   - .DS_Store
   - Thumbs.db
   
❌ Local Development:
   - GitHub _Setup_SampleDeployToHostGator_Info.txt
   - github-setup-commands.txt
   - Any local testing files
```

---

## 📤 STEP 4: Upload Files via cPanel

### 4.1 Prepare Archive for Upload
```
1. Create staging deployment folder on your local machine:
   - Create folder: abo-wbo-staging-deploy/

2. Copy ONLY production-safe files to this folder:
   - app/
   - config/
   - public/
   - resources/
   - routes/
   - database/
   - lang/
   - composer.json
   - .env.example
   - README.md

3. Create ZIP file: abo-wbo-staging.zip
```

### 4.2 Upload to Staging
```
1. In cPanel File Manager:
   - Navigate to /public_html/staging/
   
2. Upload files:
   - Click "Upload"
   - Select abo-wbo-staging.zip
   - Wait for upload to complete
   
3. Extract files:
   - Right-click on abo-wbo-staging.zip
   - Click "Extract"
   - Delete the ZIP file after extraction
```

---

## ⚙️ STEP 5: Configure Environment Files

### 5.1 Create Staging .env File
```
1. In File Manager, navigate to /public_html/staging/
2. Create new file: .env
3. Copy content from .env.example
4. Edit with staging settings:

APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.j-abo-wbo.org

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=[cpanel_user]_abo_staging
DB_USERNAME=[cpanel_user]_abouser
DB_PASSWORD=[your_database_password]

MAIL_MAILER=smtp
MAIL_HOST=mail.j-abo-wbo.org
MAIL_PORT=587
MAIL_USERNAME=noreply@j-abo-wbo.org
MAIL_PASSWORD=[email_password]
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@j-abo-wbo.org
MAIL_FROM_NAME="ABO-WBO Staging"
```

### 5.2 Set File Permissions
```
1. Right-click on .env → "Change Permissions"
2. Set to: 644 (Owner: Read+Write, Group: Read, Others: Read)

3. Set folder permissions:
   - storage/ → 755
   - public/uploads/ → 755
   - public/assets/ → 755
```

---

## 🗃️ STEP 6: Database Setup

### 6.1 Import Database Structure (3 Files in Order)

**IMPORTANT**: Import these files in EXACT ORDER to avoid foreign key errors

#### File 1: Drop Existing Tables (Optional - only if re-importing)
```
1. In cPanel, go to "phpMyAdmin"
2. Select staging database: [cpanel_user]_abo_staging
3. Click "Import" tab
4. Upload file: database/drop_all_tables.sql
5. Click "Go"
```

#### File 2: Create Database Schema (38 Tables + 3 Views)
```
1. Still in phpMyAdmin → [cpanel_user]_abo_staging database
2. Click "Import" tab
3. Upload file: database/schema.sql
4. Click "Go"
5. Wait for import to complete (30-60 seconds)
6. Verify: 38 tables + 3 views created successfully
```

**Tables Created**: globals, godinas, gamtas, gurmus, users, user_assignments, user_roles, positions, individual_responsibilities, shared_responsibilities, responsibilities, responsibility_assignments, tasks, meetings, events, finances, donations, courses, notifications, audit_logs, etc.

**Views Created**: 
- `hierarchy_view` - Active organizational hierarchy
- `user_summary_view` - User info with primary position (uses user_assignments)
- `active_tasks_view` - Current active tasks

#### File 3: Import Organizational Data
```
1. Still in phpMyAdmin → [cpanel_user]_abo_staging database
2. Click "Import" tab
3. Upload file: database/comprehensive_data_insertion.sql
4. Click "Go"
5. Wait for import to complete (20-30 seconds)
```

**Data Inserted**:
- 1 Global organization
- 6 Godinas, 20 Gamtas, 48 Gurmus
- 7 Executive positions
- 75 Total responsibilities (35 individual + 5 shared + 35 general)
- **1 System Admin**: admin@abo-wbo.org / admin123 (user_type='system_admin')

### 6.2 Verify Import Success

Run these verification queries in phpMyAdmin SQL tab:

```sql
-- Check table count (should be 38)
SELECT COUNT(*) as table_count 
FROM information_schema.tables 
WHERE table_schema = '[cpanel_user]_abo_staging' 
AND table_type = 'BASE TABLE';

-- Verify organizational hierarchy
SELECT 
    (SELECT COUNT(*) FROM globals) as globals,
    (SELECT COUNT(*) FROM godinas) as godinas,
    (SELECT COUNT(*) FROM gamtas) as gamtas,
    (SELECT COUNT(*) FROM gurmus) as gurmus;

-- Verify system admin user
SELECT id, email, user_type, level_scope, status 
FROM users 
WHERE email = 'admin@j-abo-wbo.org';

-- Test user_summary_view (should work without errors)
SELECT * FROM user_summary_view LIMIT 5;
```

**Expected Results**:
- ✅ 38 tables created
- ✅ 1 global, 6 godinas, 20 gamtas, 48 gurmus
- ✅ 1 system admin with user_type='system_admin'
- ✅ All views return data without errors

### 6.3 Common Issues & Solutions

**Error: "Unknown column 'u.position_id' in field list"**
- **Cause**: Old view definition trying to use removed position_id column
- **Solution**: This is FIXED in latest schema.sql - user_summary_view now uses user_assignments table
- **Action**: Re-import schema.sql (it will DROP and recreate views)

**Error: Foreign key constraint fails**
- **Cause**: Tables imported out of order
- **Solution**: Import drop_all_tables.sql first, then schema.sql, then data insertion
- **Action**: Start fresh with clean database

---

## 🌐 STEP 7: Configure Web Server

### 7.1 Set Document Root (if needed)
```
1. In cPanel, go to "Subdomains"
2. Find staging.j-abo-wbo.org
3. Ensure Document Root points to: /public_html/staging/public
4. If not, click "Modify" and update
```

### 7.2 Create .htaccess (if not exists)
```
1. Navigate to /public_html/staging/public/
2. Create file: .htaccess
3. Add content:

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Hide sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

---

## 🧪 STEP 8: Test Staging Deployment

### 8.1 Initial Testing
```
1. Visit: https://staging.j-abo-wbo.org
2. Check if homepage loads
3. Test login functionality
4. Test one module (e.g., Tasks)
```

### 8.2 Debug Common Issues
```
500 Error:
- Check .env file syntax
- Verify database connection
- Check file permissions

Database Connection Error:
- Verify database name, username, password
- Check if database user has proper privileges

File Not Found:
- Verify document root points to /public/
- Check .htaccess rules
```

---

## 🚀 STEP 9: Production Deployment (After Staging Success)

### 9.1 Prepare Production Files
```
1. Switch to main branch locally:
   git checkout main
   
2. Create production archive (same process as staging)
3. Upload to /public_html/app/
```

### 9.2 Production Environment File
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://j-abo-wbo.org/app

DB_DATABASE=[cpanel_user]_abo_production
# ... rest similar to staging but with production database
```

---

## 📊 STEP 10: Post-Deployment Setup

### 10.1 SSL Certificate
```
1. In cPanel, go to "SSL/TLS"
2. Install SSL for both:
   - staging.j-abo-wbo.org
   - j-abo-wbo.org
```

### 10.2 Regular Maintenance
```
1. Set up automated backups
2. Monitor error logs
3. Plan update strategy for future deployments
```

---

## ⚠️ SECURITY CHECKLIST

### Before Going Live:
- [ ] .env file has strong passwords
- [ ] Debug mode is OFF in production
- [ ] SSL certificates are installed
- [ ] File permissions are secure (644 for files, 755 for folders)
- [ ] Sensitive files are not accessible via web
- [ ] Database backups are scheduled

---

## 🎯 QUICK DEPLOYMENT SUMMARY

```bash
# HostGator Steps:
1. Create subdomain: staging.j-abo-wbo.org
2. Create databases: staging + production
3. Upload files (excluding .env, .git, logs)
4. Configure .env for each environment
5. Import database structure
6. Set permissions and .htaccess
7. Test functionality
8. Repeat for production when ready
```

## 🔗 Final URLs After Deployment:
- **Staging**: https://staging.j-abo-wbo.org
- **Production**: https://j-abo-wbo.org/app

---

**Ready to deploy! Let me know if you need clarification on any step or encounter issues.** 🚀