# ABO-WBO Management System - Development Prompt Guide

## 🎯 Current Project Context

You are working on the **ABO-WBO Management System**, a comprehensive digital platform for managing global Oromo organizational operations. The project is located at:

**Primary Directory:** `C:\Users\diwaj\devWorkSpace\ABO-WBO Management System`

## 🔧 Technical Environment

### Server Stack
- **XAMPP** (Apache 2.4.58, PHP 8.2.12, MySQL 8.0+)
- **Custom PHP MVC Framework**
- **Bootstrap 5.3+ Frontend**
- **Working URL:** `http://localhost/abo-wbo/public/index.php`

### Database
- **Name:** `abo_wbo_db`
- **Admin User:** `admin@abo-wbo.org` / `admin123`  
- **Status:** ✅ Migrated and functional

## 🚀 Current Status

### ✅ WORKING COMPONENTS
1. **Database Layer** - Complete schema with admin user
2. **MVC Framework** - Core classes implemented
3. **Authentication** - Login/register system ready
4. **UI/UX** - Bootstrap responsive design
5. **Demo Interface** - Fully functional at demo URL

### 🎯 ACTIVE WORK AREAS
- Complete MVC routing system integration
- User management interface
- Organizational hierarchy features
- Dashboard widgets and analytics

## 📋 Development Commands Reference

### Essential Commands
```bash
# Navigate to project
cd "C:/Users/diwaj/devWorkSpace/ABO-WBO Management System"

# View project structure
ls -la

# Run database migrations
"C:/xampp/php/php.exe" database/migrate-simple.php

# Copy changes to XAMPP
cp -r "C:/Users/diwaj/devWorkSpace/ABO-WBO Management System" "C:/xampp/htdocs/abo-wbo"

# Check Apache errors
tail -20 "C:/xampp/apache/logs/error.log"
```

### Testing URLs
<!-- - **Demo:** http://localhost/abo-wbo/public/demo.php -->
- **Main App:** http://localhost/abo-wbo/public/
- **Login:** http://localhost/abo-wbo/public/login

## 🏗️ Project Architecture

### Core Framework Files
```
app/Core/
├── Application.php    # Main application bootstrap
├── Router.php        # URL routing system  
├── Controller.php    # Base controller class
├── Model.php         # ORM base class
└── Database.php      # Database connection layer
```

### MVC Structure
```
app/
├── Controllers/      # Business logic
│   ├── AuthController.php
│   ├── HomeController.php
│   └── DashboardController.php
├── Models/          # Data models
│   ├── User.php
│   ├── Godina.php
│   └── Position.php
└── Middleware/      # Security layer
    ├── AuthMiddleware.php
    └── AdminMiddleware.php
```

## 🎯 Current Development Focus

### Immediate Tasks
1. **Complete MVC Integration** - Connect full routing system
2. **User Management** - CRUD operations for users
3. **Role Management** - Implement position-based permissions
4. **Dashboard Enhancement** - Add functional widgets

### Feature Priorities
1. **Organizational Hierarchy** (Godina → Gamta → Gurmu)
2. **Task Management** (Assignment and tracking)
3. **Meeting Scheduler** (Events and notifications)
4. **Donation Tracking** (Payment integration)

## 🔍 Debugging Guidelines

### Common Issue Patterns
1. **Server Errors** → Check Apache logs first
2. **Database Issues** → Verify connection and migrations
3. **Routing Problems** → Check .htaccess and Router.php
4. **Missing Files** → Ensure XAMPP htdocs sync

### Debug Workflow
```bash
# 1. Check current directory
pwd

# 2. Verify project structure
ls -la

# 3. Check Apache errors
tail -10 "C:/xampp/apache/logs/error.log"

# 4. Test database connection
"C:/xampp/php/php.exe" -r "new PDO('mysql:host=localhost;dbname=abo_wbo_db', 'root', '');"

# 5. Validate PHP syntax
"C:/xampp/php/php.exe" -l public/index.php
```

## 📝 Development Best Practices

### File Editing Workflow
1. **Edit in main project directory** (`C:\xampp\htdocs\abo-wbo`)
2. **Test locally** using XAMPP PHP server
3. **Sync to htdocs** for Apache testing
4. **Verify in browser** at demo URL

### Code Standards
- **PSR-4 autoloading** via Composer
- **MVC separation** of concerns
- **Security middleware** for protected routes
- **Bootstrap components** for UI consistency

## 🎯 Success Indicators

### Project Health Checks
- ✅ Demo page loads without errors
- ✅ Admin login works with test credentials
- ✅ Database queries execute successfully
- ✅ Apache error logs are clean
- ✅ Bootstrap UI renders properly

### Development Readiness
- ✅ All core MVC classes exist
- ✅ Database schema is complete
- ✅ Authentication system functional
- ✅ Basic routing working
- ✅ Asset pipeline operational

## 🚨 Critical Reminders

### Before Starting Work
1. **Navigate to project directory** first
2. **Check current working directory** with `pwd`
3. **Verify XAMPP services** are running
4. **Test demo URL** for baseline functionality

### During Development
1. **Edit files in main project** (not htdocs)
2. **Test changes locally** before deploying
3. **Check Apache logs** if errors occur
4. **Sync to htdocs** for full testing

### After Changes
1. **Copy updated files** to XAMPP htdocs
2. **Clear any caches** if needed
3. **Test all affected URLs**  
4. **Verify no new errors** in logs

## 📞 Quick Reference

### Key File Paths
- **Project Root:** `C:\xampp\htdocs\abo-wbo`
- **Web Root:** `C:/xampp/htdocs/abo-wbo/public/`
- **Config:** `.env`, `config/database.php`
- **Logs:** `C:/xampp/apache/logs/error.log`

### Test Credentials
- **Admin Email:** admin@abo-wbo.org
- **Admin Password:** admin123
- **Database:** abo_wbo_db (root/no password)

---

## 🎯 CURRENT MISSION

Continue development of the ABO-WBO Management System with focus on:
1. **Complete MVC routing** system integration
2. **User management** interface development  
3. **Organizational hierarchy** features
4. **Dashboard functionality** enhancement

**Status:** ✅ READY FOR DEVELOPMENT  
**Next Step:** Choose priority feature and begin implementation

---

*Last Updated: October 26, 2025*  
*Project Status: FUNCTIONAL & READY*