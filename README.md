# 🌍 ABO-WBO Management System

**Advanced Organizational Hierarchy Management Platform**

A comprehensive enterprise-level management system designed for the Oromo community organization, featuring multi-level hierarchical structure, user management, and administrative capabilities.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-green.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)
![License](https://img.shields.io/badge/license-Private-red.svg)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 🌍 Project Overview

The ABO-WBO Global Organization Management System is a comprehensive web application designed to manage global Oromo organizational operations across a 4-tier hierarchy (Global → Godina → Gamta → Gurmu) serving members worldwide.

### Key Features
- **Hierarchical Member Management** across 4 organizational levels
- **Multi-language Support** (English & Afaan Oromoo)
- **Integrated Meeting System** with Zoom SDK
- **Donation & Financial Management** with PayPal/Stripe
- **Task & Project Management** with cross-level collaboration
- **Education & Training Platform** (LMS)
- **Event Management** with participation tracking
- **Advanced Reporting & Analytics**
- **Multi-channel Notifications** (Email, SMS, In-app)

---

## 🏗️ System Architecture

### Technology Stack

#### Backend
- **PHP 8.2+** - Core programming language
- **MySQL 8.0+** - Primary database with UTF-8mb4 support
- **PDO** - Database abstraction layer with prepared statements
- **Composer** - Dependency management
- **Custom MVC Framework** - Handcrafted Model-View-Controller pattern

#### Frontend
- **Bootstrap 5.3+** - Responsive CSS framework
- **Vanilla JavaScript ES6+** - Client-side functionality
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with custom properties

#### Architecture Patterns
- **Custom MVC Framework** - Clean separation of concerns
- **RESTful API Design** - Standardized endpoint structure
- **Component-based Views** - Reusable UI components
- **Service Layer Pattern** - Business logic abstraction
- **Repository Pattern** - Data access abstraction

#### Security
- **bcrypt Password Hashing** - Secure authentication
- **CSRF Protection** - Token-based security
- **SQL Injection Prevention** - Prepared statements throughout
- **XSS Prevention** - Input sanitization and output encoding
- **Session Security** - Secure session management

#### Development & Deployment
- **XAMPP Compatible** - Local development environment
- **Shared Hosting Ready** - Apache/Nginx support
- **Environment Configuration** - .env file management
- **Git Version Control** - Source code management

---

## 📁 Project Structure

```
abo-wbo-management/
├── .env.example                 # Environment configuration template
├── .gitignore                  # Git ignore rules
├── .htaccess                   # Apache URL rewriting
├── composer.json               # PHP dependencies
├── composer.lock               # Dependency lock file
├── README.md                   # This file
├── LICENSE                     # Project license
├── 
├── public/                     # Web root directory
│   ├── index.php              # Application entry point
│   ├── .htaccess              # Public directory access rules
│   ├── assets/                # Static assets
│   │   ├── css/               # Compiled stylesheets
│   │   ├── js/                # JavaScript files
│   │   ├── images/            # Image assets
│   │   ├── fonts/             # Web fonts
│   │   └── uploads/           # User uploaded files
│   └── build/                 # Built assets
│
├── app/                       # Application core
│   ├── Config/                # Configuration files
│   │   ├── Database.php       # Database configuration
│   │   ├── App.php            # Application configuration
│   │   ├── Mail.php           # Email configuration
│   │   └── Services.php       # Third-party services config
│   │
│   ├── Controllers/           # MVC Controllers
│   │   ├── BaseController.php # Base controller class
│   │   ├── AuthController.php # Authentication controller
│   │   ├── DashboardController.php # Dashboard management
│   │   ├── UserController.php # User management
│   │   ├── HierarchyController.php # Organizational structure
│   │   ├── TaskController.php # Task management
│   │   ├── MeetingController.php # Meeting system
│   │   ├── DonationController.php # Financial management
│   │   ├── EventController.php # Event management
│   │   ├── CourseController.php # Education system
│   │   ├── ReportController.php # Reporting system
│   │   └── NotificationController.php # Notifications
│   │
│   ├── Models/                # Data models
│   │   ├── BaseModel.php      # Base model class
│   │   ├── User.php           # User model
│   │   ├── Godina.php         # Godina model
│   │   ├── Gamta.php          # Gamta model
│   │   ├── Gurmu.php          # Gurmu model
│   │   ├── Task.php           # Task model
│   │   ├── Meeting.php        # Meeting model
│   │   ├── Donation.php       # Donation model
│   │   ├── Event.php          # Event model
│   │   ├── Course.php         # Course model
│   │   └── Notification.php   # Notification model
│   │
│   ├── Views/                 # View templates
│   │   ├── layouts/           # Layout templates
│   │   │   ├── app.php        # Main application layout
│   │   │   ├── auth.php       # Authentication layout
│   │   │   └── admin.php      # Admin panel layout
│   │   ├── auth/              # Authentication views
│   │   ├── dashboard/         # Dashboard views
│   │   ├── users/             # User management views
│   │   ├── tasks/             # Task management views
│   │   ├── meetings/          # Meeting system views
│   │   ├── donations/         # Financial management views
│   │   ├── events/            # Event management views
│   │   ├── courses/           # Education system views
│   │   ├── reports/           # Reporting views
│   │   └── components/        # Reusable components
│   │
│   ├── Services/              # Business logic services
│   │   ├── AuthService.php    # Authentication service
│   │   ├── UserService.php    # User management service
│   │   ├── TaskService.php    # Task management service
│   │   ├── MeetingService.php # Meeting service with Zoom integration
│   │   ├── DonationService.php # Financial service with PayPal/Stripe
│   │   ├── EventService.php   # Event management service
│   │   ├── CourseService.php  # Education service
│   │   ├── NotificationService.php # Multi-channel notifications
│   │   ├── ReportService.php  # Reporting and analytics
│   │   ├── FileService.php    # File upload and management
│   │   └── LanguageService.php # Multilingual support
│   │
│   ├── Middleware/            # Request middleware
│   │   ├── AuthMiddleware.php # Authentication middleware
│   │   ├── RoleMiddleware.php # Role-based access control
│   │   ├── CsrfMiddleware.php # CSRF protection
│   │   ├── ValidationMiddleware.php # Input validation
│   │   └── LanguageMiddleware.php # Language detection
│   │
│   ├── Repositories/          # Data access layer
│   │   ├── BaseRepository.php # Base repository class
│   │   ├── UserRepository.php # User data access
│   │   ├── TaskRepository.php # Task data access
│   │   ├── MeetingRepository.php # Meeting data access
│   │   ├── DonationRepository.php # Donation data access
│   │   └── EventRepository.php # Event data access
│   │
│   ├── Validators/            # Input validation
│   │   ├── BaseValidator.php  # Base validation class
│   │   ├── UserValidator.php  # User input validation
│   │   ├── TaskValidator.php  # Task input validation
│   │   ├── DonationValidator.php # Donation validation
│   │   └── EventValidator.php # Event validation
│   │
│   ├── Utils/                 # Utility classes
│   │   ├── Database.php       # Database connection
│   │   ├── Router.php         # URL routing
│   │   ├── Request.php        # HTTP request handling
│   │   ├── Response.php       # HTTP response handling
│   │   ├── Session.php        # Session management
│   │   ├── CSRF.php           # CSRF token management
│   │   ├── Validator.php      # Validation utilities
│   │   ├── FileUpload.php     # File upload handling
│   │   ├── PDFGenerator.php   # PDF generation
│   │   ├── EmailSender.php    # Email utilities
│   │   ├── SMSSender.php      # SMS utilities
│   │   └── Logger.php         # Application logging
│   │
│   └── Core/                  # Core framework classes
│       ├── Application.php    # Main application class
│       ├── Controller.php     # Base controller
│       ├── Model.php          # Base model
│       ├── View.php           # View renderer
│       └── ServiceContainer.php # Dependency injection
│
├── database/                  # Database related files
│   ├── migrations/            # Database migrations
│   │   ├── 001_create_hierarchy_tables.sql
│   │   ├── 002_create_users_table.sql
│   │   ├── 003_create_tasks_table.sql
│   │   ├── 004_create_meetings_table.sql
│   │   ├── 005_create_donations_table.sql
│   │   ├── 006_create_events_table.sql
│   │   ├── 007_create_courses_table.sql
│   │   └── 008_create_notifications_table.sql
│   ├── seeds/                 # Database seeders
│   │   ├── HierarchySeeder.sql
│   │   ├── PositionsSeeder.sql
│   │   ├── AdminUserSeeder.sql
│   │   └── TestDataSeeder.sql
│   └── schema.sql             # Complete database schema
│
├── lang/                      # Internationalization
│   ├── en/                    # English translations
│   │   ├── common.php         # Common translations
│   │   ├── auth.php           # Authentication messages
│   │   ├── dashboard.php      # Dashboard translations
│   │   ├── tasks.php          # Task management
│   │   ├── meetings.php       # Meeting system
│   │   └── donations.php      # Financial system
│   └── om/                    # Afaan Oromoo translations
│       ├── common.php
│       ├── auth.php
│       ├── dashboard.php
│       ├── tasks.php
│       ├── meetings.php
│       └── donations.php
│
├── storage/                   # Storage directory
│   ├── logs/                  # Application logs
│   ├── cache/                 # Application cache
│   ├── sessions/              # Session files
│   ├── uploads/               # User uploads
│   │   ├── documents/         # Document uploads
│   │   ├── images/            # Image uploads
│   │   └── receipts/          # Generated receipts
│   └── backups/               # Database backups
│
├── tests/                     # Test files
│   ├── Unit/                  # Unit tests
│   ├── Integration/           # Integration tests
│   └── Feature/               # Feature tests
│
├── docs/                      # Documentation
│   ├── api.md                 # API documentation
│   ├── deployment.md          # Deployment guide
│   ├── database.md            # Database documentation
│   └── user-guide.md          # User manual
│
└── scripts/                   # Utility scripts
    ├── setup.php              # Initial setup script
    ├── migrate.php            # Database migration script
    ├── seed.php               # Database seeding script
    └── backup.php             # Backup script
```

---

## 🚀 Quick Start Guide

### Prerequisites

- **PHP 8.2+** with extensions: PDO, MySQL, GD, cURL, OpenSSL, mbstring
- **MySQL 8.0+** or MariaDB 10.4+
- **Composer** for dependency management
- **Node.js & npm** (optional, for asset compilation)
- **Git** for version control

### Local Development Setup

#### 1. Clone the Repository
```bash
git clone https://github.com/your-organization/abo-wbo-management.git
cd abo-wbo-management
```

#### 2. Install Dependencies
```bash
composer install
```

#### 3. Environment Configuration
```bash
cp .env.example .env
```

Edit `.env` file with your configuration:
```env
# Application Configuration
APP_NAME="ABO-WBO Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/abo-wbo-management/public
APP_TIMEZONE=UTC

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abo_wbo_management
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@abo-wbo.org
MAIL_FROM_NAME="ABO-WBO Management"

# SMS Configuration (Twilio)
TWILIO_SID=your-twilio-sid
TWILIO_TOKEN=your-twilio-token
TWILIO_FROM=+1234567890

# Zoom API Configuration
ZOOM_API_KEY=your-zoom-api-key
ZOOM_API_SECRET=your-zoom-api-secret
ZOOM_WEBHOOK_SECRET=your-webhook-secret

# Payment Gateway Configuration
PAYPAL_CLIENT_ID=your-paypal-client-id
PAYPAL_CLIENT_SECRET=your-paypal-client-secret
PAYPAL_MODE=sandbox

STRIPE_PUBLIC_KEY=your-stripe-public-key
STRIPE_SECRET_KEY=your-stripe-secret-key

# File Upload Configuration
MAX_FILE_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx

# Security Configuration
JWT_SECRET=your-jwt-secret-key
CSRF_TOKEN_NAME=_token
SESSION_LIFETIME=120
```

#### 4. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE abo_wbo_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php scripts/migrate.php

# Seed initial data
php scripts/seed.php
```

#### 5. File Permissions
```bash
chmod -R 755 storage/
chmod -R 755 public/assets/uploads/
```

#### 6. Virtual Host Configuration (Apache)
Create a virtual host in your Apache configuration:
```apache
<VirtualHost *:80>
    ServerName abo-wbo.local
    DocumentRoot /path/to/abo-wbo-management/public
    
    <Directory /path/to/abo-wbo-management/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/abo-wbo-error.log
    CustomLog ${APACHE_LOG_DIR}/abo-wbo-access.log combined
</VirtualHost>
```

Add to your hosts file:
```
127.0.0.1 abo-wbo.local
```

#### 7. Access the Application
Open your browser and navigate to: `http://abo-wbo.local`

**Default Admin Credentials:**
- Email: `admin@abo-wbo.org`
- Password: `AdminPass123!`

---

## 🛠️ Development Workflow

### GitHub Copilot Integration Prompts

#### Setting Up the Development Environment
```
Create a comprehensive PHP 8.2 MVC framework for the ABO-WBO management system with the following requirements:
1. Custom MVC architecture with clean URL routing
2. PDO database abstraction with prepared statements
3. Service layer pattern for business logic
4. Repository pattern for data access
5. Middleware system for authentication and authorization
6. Multi-language support for English and Afaan Oromoo
7. Integration with Zoom SDK, PayPal, and Stripe APIs
8. Comprehensive error handling and logging
9. CSRF protection and XSS prevention
10. Session management with security best practices
```

#### Database Schema Creation
```
Generate MySQL 8.0 database schema for a hierarchical organization management system with:
1. 4-tier hierarchy: Global → Godina → Gamta → Gurmu
2. User management with role-based access control
3. Task management with multi-level assignments
4. Meeting management with Zoom integration
5. Donation tracking with receipt generation
6. Event management with participation tracking
7. Education system with course and lesson management
8. Multi-channel notification system
9. Comprehensive audit logging
10. Support for UTF-8mb4 character set for multilingual content
```

#### Authentication System
```
Create a secure authentication system for PHP with:
1. User registration with email verification
2. Multi-tier approval workflow (Gurmu → Gamta → Godina → Global)
3. Role-based access control with hierarchical permissions
4. Password hashing with bcrypt
5. Session management with secure cookies
6. CSRF protection for all forms
7. Rate limiting for login attempts
8. Password reset functionality
9. Two-factor authentication support
10. Activity logging for security audits
```

#### User Interface Components
```
Design responsive Bootstrap 5.3 components for organizational management with:
1. Hierarchical dashboard with role-based views
2. Task management Kanban board with drag-and-drop
3. Meeting scheduler with calendar integration
4. Donation form with payment gateway integration
5. Event management with RSVP functionality
6. Course catalog with progress tracking
7. Multi-language toggle (English/Afaan Oromoo)
8. Mobile-first responsive design
9. Accessibility compliance (WCAG 2.1)
10. Dark mode support
```

#### API Development
```
Build RESTful API endpoints for the management system with:
1. Authentication endpoints with JWT tokens
2. CRUD operations for all entities
3. Hierarchical data filtering based on user scope
4. File upload endpoints with validation
5. Reporting endpoints with custom filters
6. Webhook endpoints for third-party integrations
7. Real-time notification endpoints
8. Data export endpoints (PDF, Excel, CSV)
9. Bulk operations for administrative tasks
10. API rate limiting and throttling
```

### Git Workflow Best Practices

#### Branch Strategy
```bash
# Main branches
main                 # Production-ready code
develop             # Integration branch
staging             # Pre-production testing

# Feature branches
feature/auth-system
feature/task-management
feature/meeting-integration
feature/donation-system

# Hotfix branches
hotfix/security-patch
hotfix/critical-bug
```

#### Commit Message Convention
```
feat: add user authentication system
fix: resolve SQL injection vulnerability
docs: update API documentation
style: format code according to PSR-12
refactor: optimize database queries
test: add unit tests for user service
chore: update composer dependencies
```

### Code Quality Standards

#### PSR Standards Compliance
- **PSR-1**: Basic Coding Standard
- **PSR-4**: Autoloader Standard  
- **PSR-12**: Extended Coding Style Guide
- **PSR-7**: HTTP Message Interface

#### Security Checklist
- [ ] Input validation and sanitization
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] CSRF token validation
- [ ] Secure session configuration
- [ ] Password hashing with bcrypt
- [ ] File upload validation
- [ ] Error handling without information disclosure
- [ ] HTTPS enforcement
- [ ] Security headers implementation

---

## 🗄️ Database Architecture

### Entity Relationship Diagram
```
Global
├── Godina (Region)
│   ├── Gamta (District)
│   │   ├── Gurmu (Local Group)
│   │   │   ├── Users (Members)
│   │   │   ├── Tasks
│   │   │   ├── Meetings
│   │   │   ├── Events
│   │   │   └── Donations
│   │   └── ...
│   └── ...
└── ...
```

### Core Tables Structure

#### Hierarchy Tables
```sql
-- Godina (Regions)
CREATE TABLE godinas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    code VARCHAR(10) UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by)
);

-- Gamta (Districts)
CREATE TABLE gamtas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    godina_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (godina_id) REFERENCES godinas(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_gamta_per_godina (godina_id, name),
    INDEX idx_godina_id (godina_id),
    INDEX idx_status (status)
);

-- Gurmu (Local Groups)
CREATE TABLE gurmus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    gamta_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10),
    description TEXT,
    membership_fee DECIMAL(10,2) DEFAULT 0.00,
    meeting_schedule VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gamta_id) REFERENCES gamtas(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_gurmu_per_gamta (gamta_id, name),
    INDEX idx_gamta_id (gamta_id),
    INDEX idx_status (status)
);
```

#### User Management
```sql
-- Positions
CREATE TABLE positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key_name VARCHAR(50) UNIQUE NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_om VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_om TEXT,
    level_scope ENUM('global', 'godina', 'gamta', 'gurmu', 'all') NOT NULL,
    permissions JSON,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    profile_image VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    gurmu_id INT NOT NULL,
    position_id INT,
    level_scope ENUM('global', 'godina', 'gamta', 'gurmu') NOT NULL,
    language_preference ENUM('en', 'om') DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    status ENUM('pending', 'active', 'suspended', 'inactive') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    phone_verified BOOLEAN DEFAULT FALSE,
    phone_verified_at TIMESTAMP NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    last_login TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    created_by INT,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gurmu_id) REFERENCES gurmus(id) ON DELETE RESTRICT,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_gurmu_id (gurmu_id),
    INDEX idx_position_id (position_id),
    INDEX idx_status (status),
    INDEX idx_level_scope (level_scope),
    INDEX idx_created_at (created_at)
);
```

#### Task Management
```sql
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    level_scope ENUM('global', 'godina', 'gamta', 'gurmu', 'cross_level') NOT NULL,
    scope_id INT NULL,
    parent_task_id INT NULL,
    event_id INT NULL,
    project_id INT NULL,
    meeting_id INT NULL,
    category ENUM('administrative', 'financial', 'educational', 'social', 'technical') DEFAULT 'administrative',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'under_review', 'completed', 'cancelled', 'on_hold') DEFAULT 'pending',
    start_date DATE,
    due_date DATE,
    completed_date DATE,
    estimated_hours INT,
    actual_hours INT,
    completion_percentage INT DEFAULT 0,
    tags JSON,
    attachments JSON,
    created_by INT NOT NULL,
    assigned_to JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_uuid (uuid),
    INDEX idx_level_scope (level_scope),
    INDEX idx_scope_id (scope_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_created_by (created_by),
    INDEX idx_parent_task_id (parent_task_id)
);
```

---

## 🔐 Security Implementation

### Authentication Security
```php
<?php
// Password hashing
class PasswordService {
    public static function hash(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }
    
    public static function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}

// CSRF Protection
class CSRFToken {
    public static function generate(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function verify(string $token): bool {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
}
```

### Input Validation
```php
<?php
class InputValidator {
    public static function email(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function phone(string $phone): bool {
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phone);
    }
    
    public static function sanitizeString(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeHtml(string $html): string {
        // Use HTMLPurifier or similar library
        return $html; // Placeholder
    }
}
```

### File Upload Security
```php
<?php
class FileUploadService {
    private const ALLOWED_TYPES = [
        'image' => ['jpg', 'jpeg', 'png', 'gif'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'video' => ['mp4', 'webm', 'ogg']
    ];
    
    private const MAX_FILE_SIZE = 10485760; // 10MB
    
    public function uploadFile(array $file, string $type): array {
        // Validate file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_TYPES[$type] ?? [])) {
            throw new InvalidArgumentException('Invalid file type');
        }
        
        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException('File too large');
        }
        
        // Generate secure filename
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadPath = UPLOAD_PATH . '/' . $type . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new RuntimeException('Failed to upload file');
        }
        
        return [
            'filename' => $filename,
            'path' => $uploadPath,
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }
}
```

---

## 🌐 API Documentation

### Authentication Endpoints
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh
POST /api/auth/forgot-password
POST /api/auth/reset-password
POST /api/auth/verify-email
POST /api/auth/resend-verification
```

### User Management Endpoints
```
GET    /api/users                    # List users with filters
POST   /api/users                    # Create new user
GET    /api/users/{id}               # Get user details
PUT    /api/users/{id}               # Update user
DELETE /api/users/{id}               # Deactivate user
PUT    /api/users/{id}/approve       # Approve user registration
PUT    /api/users/{id}/suspend       # Suspend user
PUT    /api/users/{id}/activate      # Activate user
PUT    /api/users/{id}/position      # Assign position
```

### Task Management Endpoints
```
GET    /api/tasks                    # List tasks with filters
POST   /api/tasks                    # Create new task
GET    /api/tasks/{id}               # Get task details
PUT    /api/tasks/{id}               # Update task
DELETE /api/tasks/{id}               # Delete task
POST   /api/tasks/{id}/assign        # Assign task to users
PUT    /api/tasks/{id}/status        # Update task status
POST   /api/tasks/{id}/comment       # Add task comment
GET    /api/tasks/{id}/history       # Get task history
```

### Meeting Management Endpoints
```
GET    /api/meetings                 # List meetings
POST   /api/meetings                 # Create meeting
GET    /api/meetings/{id}            # Get meeting details
PUT    /api/meetings/{id}            # Update meeting
DELETE /api/meetings/{id}            # Cancel meeting
POST   /api/meetings/{id}/invite     # Send invitations
POST   /api/meetings/{id}/join       # Join meeting
POST   /api/meetings/{id}/minutes    # Save meeting minutes
GET    /api/meetings/{id}/recording  # Get meeting recording
```

### Donation Management Endpoints
```
GET    /api/donations                # List donations
POST   /api/donations                # Create donation
GET    /api/donations/{id}           # Get donation details
PUT    /api/donations/{id}           # Update donation
POST   /api/donations/{id}/approve   # Approve donation
GET    /api/donations/{id}/receipt   # Generate receipt
POST   /api/donations/import         # Import donation data
```

---

## 🚀 Deployment Guide

### Production Environment Setup

#### Server Requirements
- **PHP 8.2+** with required extensions
- **MySQL 8.0+** or MariaDB 10.4+
- **Apache 2.4+** or **Nginx 1.18+**
- **SSL Certificate** (Let's Encrypt recommended)
- **Minimum 2GB RAM**, 20GB storage
- **Cron job** support for scheduled tasks

#### Environment Configuration
```env
# Production Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://j-abo-wbo.org

# Security
SESSION_SECURE=true
SESSION_HTTPONLY=true
FORCE_HTTPS=true

# Database - Use separate DB server in production
DB_HOST=your-db-server.com
DB_DATABASE=abo_wbo_production
DB_USERNAME=secure_db_user
DB_PASSWORD=strong_password

# Email - Use transactional email service
MAIL_DRIVER=ses
MAIL_HOST=email-smtp.region.amazonaws.com

# File Storage - Use CDN for static assets
CDN_URL=https://cdn.j-abo-wbo.org
```

#### SSL/HTTPS Configuration
```apache
# Apache Virtual Host
<VirtualHost *:443>
    ServerName j-abo-wbo.org
    DocumentRoot /var/www/abo-wbo/public
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    <Directory /var/www/abo-wbo/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Database Optimization
```sql
-- Performance Optimization
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL query_cache_size = 268435456; -- 256MB
SET GLOBAL max_connections = 200;

-- Create indexes for better performance
CREATE INDEX idx_users_level_status ON users(level_scope, status);
CREATE INDEX idx_tasks_assignee_status ON tasks((JSON_EXTRACT(assigned_to, '$')), status);
CREATE INDEX idx_donations_date_status ON donations(created_at, payment_status);
```

#### Cron Jobs Setup
```bash
# Add to crontab
# Send notification digests
0 9 * * * php /var/www/abo-wbo/scripts/send-daily-digest.php

# Generate reports
0 2 * * 1 php /var/www/abo-wbo/scripts/generate-weekly-reports.php

# Backup database
0 3 * * * php /var/www/abo-wbo/scripts/backup-database.php

# Clean up temporary files
0 4 * * * php /var/www/abo-wbo/scripts/cleanup-temp-files.php
```

---

## 📊 Monitoring & Analytics

### Application Monitoring
```php
<?php
// Performance monitoring
class PerformanceMonitor {
    private static $startTime;
    
    public static function start(): void {
        self::$startTime = microtime(true);
    }
    
    public static function end(): float {
        return microtime(true) - self::$startTime;
    }
    
    public static function logSlowQuery(string $query, float $time): void {
        if ($time > 1.0) { // Log queries taking more than 1 second
            error_log("Slow query ({$time}s): {$query}");
        }
    }
}

// Error tracking
class ErrorTracker {
    public static function logError(\Throwable $exception): void {
        $error = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        error_log(json_encode($error));
    }
}
```

### Health Check Endpoint
```php
<?php
// /api/health endpoint
class HealthCheckController {
    public function check(): array {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'checks' => []
        ];
        
        // Database check
        try {
            $db = Database::getInstance();
            $db->query('SELECT 1');
            $health['checks']['database'] = 'healthy';
        } catch (Exception $e) {
            $health['checks']['database'] = 'unhealthy';
            $health['status'] = 'unhealthy';
        }
        
        // File system check
        $health['checks']['storage'] = is_writable(STORAGE_PATH) ? 'healthy' : 'unhealthy';
        
        // Memory usage
        $health['checks']['memory'] = [
            'used' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ];
        
        return $health;
    }
}
```

---

## 🧪 Testing Strategy

### Unit Testing Setup
```php
<?php
// PHPUnit configuration
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase {
    private UserService $userService;
    
    protected function setUp(): void {
        $this->userService = new UserService();
    }
    
    public function testCreateUser(): void {
        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'gurmu_id' => 1
        ];
        
        $user = $this->userService->createUser($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(password_verify('password123', $user->password_hash));
    }
    
    public function testValidateUserInput(): void {
        $invalidData = [
            'first_name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ];
        
        $this->expectException(ValidationException::class);
        $this->userService->validateUserData($invalidData);
    }
}
```

### Integration Testing
```php
<?php
class TaskIntegrationTest extends TestCase {
    private Database $db;
    
    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->db->beginTransaction();
    }
    
    protected function tearDown(): void {
        $this->db->rollBack();
    }
    
    public function testCreateTaskWithAssignments(): void {
        // Create test users
        $creator = $this->createTestUser();
        $assignee = $this->createTestUser();
        
        // Create task
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => [$assignee->id],
            'due_date' => '2025-12-31'
        ];
        
        $task = TaskService::createTask($taskData, $creator->id);
        
        $this->assertNotNull($task->id);
        $this->assertEquals('Test Task', $task->title);
        
        // Verify assignment
        $assignments = TaskService::getTaskAssignments($task->id);
        $this->assertCount(1, $assignments);
        $this->assertEquals($assignee->id, $assignments[0]->user_id);
    }
}
```

---

## 📚 GitHub Copilot Development Prompts

### Complete Application Scaffold
```
Create a complete PHP 8.2 MVC application for organizational management with:

STRUCTURE:
- Custom MVC framework with clean architecture
- Service layer for business logic
- Repository pattern for data access
- Middleware for authentication and authorization
- Component-based view system

FEATURES:
1. Multi-tier user authentication (Global → Godina → Gamta → Gurmu)
2. Role-based access control with hierarchical permissions
3. Task management with cross-level assignments
4. Meeting system with Zoom SDK integration
5. Donation processing with PayPal/Stripe
6. Event management with participation tracking
7. Education platform with course management
8. Multi-channel notifications (Email, SMS, In-app)
9. Advanced reporting and analytics
10. Multi-language support (English/Afaan Oromoo)

SECURITY:
- CSRF protection on all forms
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Secure session management
- Password hashing with bcrypt
- File upload validation
- Rate limiting for API endpoints

DATABASE:
- MySQL 8.0 with UTF-8mb4 support
- Hierarchical organization structure
- Audit logging for all operations
- Optimized indexes for performance

INTEGRATIONS:
- Zoom SDK for meeting management
- PayPal and Stripe for payment processing
- Email service for notifications
- SMS service for alerts
- File storage with security validation

Please create the complete application structure with all necessary files, configurations, and documentation.
```

### Authentication System
```
Build a comprehensive authentication system for PHP with:

FEATURES:
1. User registration with email verification
2. Multi-tier approval workflow (Gurmu → Gamta → Godina → Global)
3. Login with remember me functionality
4. Password reset with secure tokens
5. Two-factor authentication support
6. Role-based access control
7. Session management with security
8. Activity logging for audit trails

SECURITY REQUIREMENTS:
- Password hashing with Argon2ID
- CSRF protection for all auth forms
- Rate limiting for login attempts
- Secure session configuration
- Email verification tokens
- Password complexity validation
- Account lockout after failed attempts
- Audit logging for all auth events

DATABASE TABLES:
- users (with hierarchy assignment)
- user_sessions (secure session tracking)
- password_resets (secure token management)
- user_activities (audit logging)
- login_attempts (rate limiting)

MIDDLEWARE:
- Authentication middleware
- Authorization middleware
- Rate limiting middleware
- CSRF protection middleware

Please provide complete implementation with security best practices.
```

### Database Architecture
```
Design a comprehensive MySQL database schema for hierarchical organization management:

HIERARCHY STRUCTURE:
- Global (top level)
- Godina (regional level)
- Gamta (district level)  
- Gurmu (local group level)

MAIN ENTITIES:
1. Users with role-based permissions
2. Tasks with multi-level assignments
3. Meetings with Zoom integration
4. Donations with receipt tracking
5. Events with participation management
6. Courses with lesson tracking
7. Notifications with multi-channel delivery

REQUIREMENTS:
- UTF-8mb4 character set for multilingual support
- Proper foreign key relationships
- Cascade rules for data integrity
- Optimized indexes for performance
- Audit trail tables for compliance
- JSON columns for flexible data storage
- Full-text search capabilities
- Partitioning for large tables

SECURITY:
- Soft deletes for important data
- Audit logging for all changes
- Data encryption for sensitive fields
- Access control at database level

Please create complete SQL schema with:
- Table creation statements
- Index definitions
- Foreign key constraints
- Sample data for testing
- Migration scripts
- Performance optimization queries
```

### API Development
```
Create a comprehensive RESTful API for organizational management with:

ENDPOINTS:
1. Authentication (/api/auth/*)
2. User Management (/api/users/*)
3. Hierarchy Management (/api/hierarchy/*)
4. Task Management (/api/tasks/*)
5. Meeting System (/api/meetings/*)
6. Donation Processing (/api/donations/*)
7. Event Management (/api/events/*)
8. Education System (/api/courses/*)
9. Reporting (/api/reports/*)
10. Notifications (/api/notifications/*)

FEATURES:
- JWT token authentication
- Role-based access control
- Request validation
- Error handling with proper HTTP codes
- Pagination for large datasets
- Filtering and sorting
- File upload endpoints
- Webhook support for integrations
- Rate limiting and throttling
- API documentation with examples

SECURITY:
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- CSRF token validation
- Rate limiting per endpoint
- API key authentication for external services
- Request logging for audit trails

INTEGRATIONS:
- Zoom SDK for meeting management
- PayPal/Stripe for payment processing
- Email service for notifications
- SMS service for alerts
- File storage for uploads

Please provide complete API implementation with comprehensive documentation.
```

### Frontend Components
```
Create responsive Bootstrap 5.3 components for organizational management with:

COMPONENTS NEEDED:
1. Dashboard layouts (Global, Godina, Gamta, Gurmu levels)
2. User management interface with approval workflow
3. Task management with Kanban board
4. Meeting scheduler with calendar integration
5. Donation forms with payment gateway
6. Event management with RSVP system
7. Course catalog with progress tracking
8. Reporting dashboard with charts
9. Notification center with real-time updates
10. Multi-language toggle (English/Afaan Oromoo)

DESIGN REQUIREMENTS:
- Mobile-first responsive design
- Accessibility compliance (WCAG 2.1)
- Dark mode support
- Print-friendly layouts
- Touch-friendly interface
- Keyboard navigation support
- Screen reader compatibility

INTERACTIVE FEATURES:
- Drag-and-drop for task management
- Real-time notifications
- Auto-save functionality
- Progressive form submission
- Live search and filtering
- Infinite scroll for large lists
- Modal dialogs for quick actions
- Tooltip help system

PERFORMANCE:
- Lazy loading for images
- Code splitting for JavaScript
- CSS optimization
- Image optimization
- Caching strategies
- Progressive web app features

Please create complete component library with documentation and examples.
```

---

## 🤝 Contributing Guidelines

### Development Standards
1. **Follow PSR-12** coding standards
2. **Write comprehensive tests** for all features
3. **Document all public methods** with PHPDoc
4. **Use meaningful commit messages** following conventional commits
5. **Ensure backward compatibility** when making changes
6. **Review security implications** of all changes

### Pull Request Process
1. Fork the repository
2. Create a feature branch from `develop`
3. Write tests for new functionality
4. Ensure all tests pass
5. Update documentation as needed
6. Submit pull request with detailed description

### Code Review Checklist
- [ ] Code follows PSR-12 standards
- [ ] All tests pass
- [ ] Security vulnerabilities checked
- [ ] Performance implications considered
- [ ] Documentation updated
- [ ] Backward compatibility maintained

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📞 Support & Contact

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-organization/abo-wbo-management/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-organization/abo-wbo-management/discussions)
- **Email**: support@abo-wbo.org

---

## 🎯 Project Roadmap

### Phase 1: Foundation (Completed)
- [x] Core MVC framework
- [x] Authentication system
- [x] Database schema
- [x] Basic UI components

### Phase 2: Core Features (In Progress)
- [ ] Task management system
- [ ] Meeting integration
- [ ] Donation processing
- [ ] Event management
- [ ] Education platform

### Phase 3: Advanced Features (Planned)
- [ ] Advanced analytics
- [ ] Mobile app (React Native)
- [ ] API integrations
- [ ] Performance optimization
- [ ] Internationalization expansion

### Phase 4: Enterprise Features (Future)
- [ ] Advanced reporting
- [ ] Workflow automation
- [ ] Third-party integrations
- [ ] AI-powered insights
- [ ] Advanced security features

---

**Made with ❤️ for the Global Oromo Community**