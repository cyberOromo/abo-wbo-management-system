# ABO-WBO Management System - Fresh Project Development Guide

## 🎯 Project Overview

**Project Name:** ABO-WBO Management System  
**Location:** `C:\Users\diwaj\devWorkSpace\ABO-WBO Management System`  
**Environment:** XAMPP (PHP 8.2.12, Apache, MySQL)  
**Framework:** Custom PHP MVC Framework  
**Frontend:** Bootstrap 5.3+ with Responsive Design

## 🏗️ Project Architecture

### Directory Structure
```
ABO-WBO Management System/
├── app/                    # Application logic
│   ├── Controllers/        # MVC Controllers
│   ├── Models/            # Data models
│   ├── Middleware/        # Authentication & security
│   └── Core/              # Framework core classes
├── config/                # Configuration files
├── database/              # Database migrations & seeds
├── public/                # Web accessible files
│   ├── assets/           # CSS, JS, images
│   ├── index.php         # Main entry point
│   └── demo.php          # Working demo
├── resources/views/       # Template files
├── routes/               # Route definitions
├── storage/              # Logs, cache, uploads
└── vendor/               # Composer dependencies
```

### Technology Stack
- **Backend:** PHP 8.2+ Custom MVC Framework
- **Database:** MySQL 8.0+ with PDO
- **Frontend:** Bootstrap 5.3, jQuery, HTML5
- **Server:** Apache (XAMPP) with mod_rewrite
- **Dependencies:** Composer for autoloading

## 🚀 Current Status

### ✅ Completed Components

1. **Database Layer**
   - Complete schema with 11 core tables
   - Admin user created: `admin@abo-wbo.org` / `admin123`
   - Migration system working

2. **MVC Framework Core**
   - Application.php (bootstrap)
   - Router.php (URL routing)
   - Controller.php (base controller)
   - Model.php (ORM base)
   - Database.php (connection layer)

3. **Authentication System**
   - AuthController with login/register
   - Middleware for protection
   - Session management
   - Password hashing

4. **User Interface**
   - Responsive Bootstrap design
   - Navigation system
   - Dashboard layout
   - Forms and components

5. **Working Demo**
   - Accessible at: `http://localhost/abo-wbo/public/demo.php`
   - Homepage, login, dashboard pages
   - Administrative interface

### 🔧 Technical Configuration

#### Environment Files
- `.env` with database credentials
- `.env.example` template
- Composer autoloading configured

#### Server Configuration
- Apache `.htaccess` files configured
- URL rewriting enabled
- Security headers implemented
- File protection rules

#### Database Connection
```php
Host: localhost
Database: abo_wbo_db
Username: root
Password: (empty)
```

## 🎯 Development Workflow

### Access Points
1. **Main Application:** `http://localhost/abo-wbo/public/`
2. **Working Demo:** `http://localhost/abo-wbo/public/demo.php`
3. **Admin Login:** Use `admin@abo-wbo.org` / `admin123`

### Development Commands
```bash
# Navigate to project
cd "C:/Users/diwaj/devWorkSpace/ABO-WBO Management System"

# Run migrations
"C:/xampp/php/php.exe" database/migrate-simple.php

# Start development server (alternative)
"C:/xampp/php/php.exe" -S localhost:8000 -t public/

# Check project structure
ls -la
```

### File Modification Workflow
1. Edit files in main project directory
2. Copy changes to XAMPP htdocs if needed:
   ```bash
   cp -r "C:/Users/diwaj/devWorkSpace/ABO-WBO Management System" "C:/xampp/htdocs/abo-wbo"
   ```
3. Test in browser at `http://localhost/abo-wbo/public/`

## 🔄 Next Development Steps

### Priority 1: Core Functionality
- [ ] Complete MVC routing integration
- [ ] User management interface
- [ ] Role-based permissions
- [ ] Dashboard widgets

### Priority 2: Organizational Features
- [ ] Hierarchy management (Godinas, Gamtas)
- [ ] Position assignments
- [ ] Task management system
- [ ] Meeting scheduler

### Priority 3: Advanced Features
- [ ] Donation tracking
- [ ] Report generation
- [ ] Notification system
- [ ] File upload handling

## 🐛 Troubleshooting

### Common Issues

1. **Internal Server Error**
   - Check Apache error logs: `tail -20 "C:/xampp/apache/logs/error.log"`
   - Verify `.htaccess` syntax (no `<Directory>` in .htaccess)
   - Ensure PHP syntax is correct

2. **Database Connection Issues**
   - Verify MySQL is running in XAMPP
   - Check `.env` configuration
   - Run migration script

3. **File Not Found (404)**
   - Ensure files are copied to XAMPP htdocs
   - Check `.htaccess` rewrite rules
   - Verify file permissions

### Debug Commands
```bash
# Check Apache error logs
tail -20 "C:/xampp/apache/logs/error.log"

# Test database connection
"C:/xampp/php/php.exe" -r "try { $pdo = new PDO('mysql:host=localhost;dbname=abo_wbo_db', 'root', ''); echo 'Connected!'; } catch(Exception $e) { echo $e->getMessage(); }"

# Test PHP syntax
"C:/xampp/php/php.exe" -l filename.php
```

## 📁 Important File Locations

### Configuration
- Main config: `config/database.php`
- Environment: `.env`
- Apache: `public/.htaccess`

### Entry Points
- Main app: `public/index.php`
- Demo: `public/demo.php`
- Routes: `routes/web.php`

### Core Classes
- Application: `app/Core/Application.php`
- Router: `app/Core/Router.php`
- Database: `app/Core/Database.php`

## 🎯 Success Metrics

- ✅ Project accessible via web browser
- ✅ Database connection working
- ✅ Admin login functional
- ✅ Basic navigation working
- ✅ No server errors in logs

## 📞 Support Information

For development issues:
1. Check this guide first
2. Review Apache error logs
3. Verify database connectivity
4. Test with simple PHP scripts

---

*Last Updated: October 26, 2025*  
*Status: ✅ FUNCTIONAL - Demo Ready*