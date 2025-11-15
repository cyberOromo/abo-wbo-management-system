<?php
/**
 * Additional Database Tables Creation Script
 * Creates all missing tables for Task, Meeting, Event, Course, and Notification modules
 */

$config = require_once 'config/app.php';

// Database connection
try {
    $dsn = "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['name'] . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $config['database']['user'], $config['database']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "🔄 Creating additional database tables...\n\n";
    
    // Tasks table
    echo "📋 Creating tasks table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tasks (
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
            completed_date DATETIME,
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Task activities table
    echo "📝 Creating task_activities table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS task_activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            task_id INT NOT NULL,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_task_id (task_id),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Meetings table
    echo "🤝 Creating meetings table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS meetings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            uuid VARCHAR(36) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            meeting_type ENUM('regular', 'emergency', 'training', 'social', 'planning') DEFAULT 'regular',
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu', 'cross_level') NOT NULL,
            scope_id INT NULL,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            timezone VARCHAR(50) DEFAULT 'UTC',
            platform ENUM('zoom', 'in_person', 'hybrid') DEFAULT 'in_person',
            location TEXT,
            zoom_meeting_id VARCHAR(100),
            zoom_meeting_url TEXT,
            zoom_password VARCHAR(50),
            agenda JSON,
            recurring_pattern JSON,
            max_participants INT,
            is_public BOOLEAN DEFAULT 0,
            requires_approval BOOLEAN DEFAULT 0,
            status ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'postponed') DEFAULT 'scheduled',
            meeting_minutes JSON,
            recording_url TEXT,
            attachments JSON,
            created_by INT NOT NULL,
            moderators JSON,
            tags JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_uuid (uuid),
            INDEX idx_level_scope (level_scope),
            INDEX idx_scope_id (scope_id),
            INDEX idx_status (status),
            INDEX idx_start_datetime (start_datetime),
            INDEX idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Meeting participants table
    echo "👥 Creating meeting_participants table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS meeting_participants (
            id INT PRIMARY KEY AUTO_INCREMENT,
            meeting_id INT NOT NULL,
            user_id INT NOT NULL,
            status ENUM('invited', 'accepted', 'declined', 'attended', 'absent') DEFAULT 'invited',
            invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_meeting_participant (meeting_id, user_id),
            
            INDEX idx_meeting_id (meeting_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Meeting activities table
    echo "📋 Creating meeting_activities table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS meeting_activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            meeting_id INT NOT NULL,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_meeting_id (meeting_id),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Events table
    echo "🎉 Creating events table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT PRIMARY KEY AUTO_INCREMENT,
            uuid VARCHAR(36) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            event_type ENUM('cultural', 'educational', 'fundraising', 'social', 'political', 'memorial', 'celebration', 'conference') DEFAULT 'social',
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu', 'cross_level') NOT NULL,
            scope_id INT NULL,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            timezone VARCHAR(50) DEFAULT 'UTC',
            venue_name VARCHAR(255),
            venue_address TEXT,
            venue_city VARCHAR(100),
            venue_country VARCHAR(100),
            is_virtual BOOLEAN DEFAULT 0,
            virtual_link TEXT,
            registration_type ENUM('open', 'approval_required', 'invitation_only', 'closed') DEFAULT 'open',
            registration_start DATETIME,
            registration_end DATETIME,
            max_participants INT,
            min_participants INT DEFAULT 0,
            registration_fee DECIMAL(10,2) DEFAULT 0.00,
            currency VARCHAR(3) DEFAULT 'USD',
            requires_payment BOOLEAN DEFAULT 0,
            agenda JSON,
            speakers JSON,
            sponsors JSON,
            social_media_links JSON,
            banner_image VARCHAR(255),
            gallery_images JSON,
            requirements JSON,
            what_to_bring JSON,
            contact_email VARCHAR(255),
            contact_phone VARCHAR(20),
            status ENUM('planning', 'open_registration', 'registration_closed', 'in_progress', 'completed', 'cancelled', 'postponed') DEFAULT 'planning',
            tags JSON,
            custom_fields JSON,
            created_by INT NOT NULL,
            organizers JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_uuid (uuid),
            INDEX idx_level_scope (level_scope),
            INDEX idx_scope_id (scope_id),
            INDEX idx_status (status),
            INDEX idx_event_type (event_type),
            INDEX idx_start_datetime (start_datetime),
            INDEX idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Event participants table
    echo "🎫 Creating event_participants table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS event_participants (
            id INT PRIMARY KEY AUTO_INCREMENT,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            status ENUM('registered', 'confirmed', 'cancelled', 'attended', 'no_show', 'waitlisted') DEFAULT 'registered',
            registration_data JSON,
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_event_participant (event_id, user_id),
            
            INDEX idx_event_id (event_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Event activities table
    echo "📝 Creating event_activities table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS event_activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_event_id (event_id),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Courses table
    echo "📚 Creating courses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS courses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            uuid VARCHAR(36) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            course_type ENUM('language', 'leadership', 'technical', 'cultural', 'history', 'political', 'business', 'life_skills') DEFAULT 'cultural',
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu', 'cross_level') NOT NULL,
            scope_id INT NULL,
            difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
            instructor_id INT,
            co_instructors JSON,
            duration_hours INT,
            start_date DATE,
            end_date DATE,
            enrollment_type ENUM('open', 'approval_required', 'invitation_only', 'closed') DEFAULT 'open',
            enrollment_start DATETIME,
            enrollment_end DATETIME,
            max_students INT,
            prerequisites JSON,
            learning_objectives JSON,
            course_outline JSON,
            materials_needed JSON,
            assessment_criteria JSON,
            certification_available BOOLEAN DEFAULT 0,
            certificate_template VARCHAR(255),
            course_fee DECIMAL(10,2) DEFAULT 0.00,
            currency VARCHAR(3) DEFAULT 'USD',
            thumbnail_image VARCHAR(255),
            banner_image VARCHAR(255),
            video_trailer VARCHAR(255),
            language VARCHAR(10) DEFAULT 'en',
            tags JSON,
            status ENUM('draft', 'published', 'in_progress', 'completed', 'archived') DEFAULT 'draft',
            is_featured BOOLEAN DEFAULT 0,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_uuid (uuid),
            INDEX idx_level_scope (level_scope),
            INDEX idx_scope_id (scope_id),
            INDEX idx_status (status),
            INDEX idx_course_type (course_type),
            INDEX idx_instructor_id (instructor_id),
            INDEX idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Course enrollments table
    echo "🎓 Creating course_enrollments table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS course_enrollments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            user_id INT NOT NULL,
            status ENUM('pending', 'active', 'completed', 'dropped', 'suspended') DEFAULT 'pending',
            enrollment_data JSON,
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_course_enrollment (course_id, user_id),
            
            INDEX idx_course_id (course_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Course lessons table
    echo "📖 Creating course_lessons table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS course_lessons (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            content JSON,
            lesson_order INT NOT NULL,
            duration_minutes INT,
            is_published BOOLEAN DEFAULT 0,
            video_url VARCHAR(255),
            resources JSON,
            quiz_questions JSON,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_course_id (course_id),
            INDEX idx_lesson_order (lesson_order),
            INDEX idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Lesson progress table
    echo "📊 Creating lesson_progress table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lesson_progress (
            id INT PRIMARY KEY AUTO_INCREMENT,
            lesson_id INT NOT NULL,
            user_id INT NOT NULL,
            status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
            progress_percentage INT DEFAULT 0,
            time_spent_minutes INT DEFAULT 0,
            completed_at DATETIME NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_lesson_progress (lesson_id, user_id),
            
            INDEX idx_lesson_id (lesson_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Course activities table
    echo "📋 Creating course_activities table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS course_activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_course_id (course_id),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Course reviews table
    echo "⭐ Creating course_reviews table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS course_reviews (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            review_text TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_course_review (course_id, user_id),
            
            INDEX idx_course_id (course_id),
            INDEX idx_user_id (user_id),
            INDEX idx_rating (rating)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Notifications table
    echo "🔔 Creating notifications table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            uuid VARCHAR(36) UNIQUE NOT NULL,
            type ENUM('system', 'task', 'meeting', 'event', 'course', 'donation', 'user', 'announcement') NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu', 'user') NOT NULL,
            scope_id INT NULL,
            recipient_id INT NULL,
            sender_id INT NULL,
            channels JSON NOT NULL,
            priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
            scheduled_at DATETIME NULL,
            sent_at DATETIME NULL,
            read_at DATETIME NULL,
            status ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
            metadata JSON,
            action_url VARCHAR(500),
            action_text VARCHAR(100),
            template_id VARCHAR(50),
            template_data JSON,
            delivery_attempts INT DEFAULT 0,
            last_attempt_at DATETIME NULL,
            error_message TEXT,
            expires_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
            
            INDEX idx_uuid (uuid),
            INDEX idx_type (type),
            INDEX idx_level_scope (level_scope),
            INDEX idx_scope_id (scope_id),
            INDEX idx_recipient_id (recipient_id),
            INDEX idx_sender_id (sender_id),
            INDEX idx_status (status),
            INDEX idx_priority (priority),
            INDEX idx_scheduled_at (scheduled_at),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "\n✅ All additional database tables created successfully!\n\n";
    
    // Create summary
    echo "📊 Database Summary:\n";
    echo "==================\n";
    
    $tables = [
        'tasks' => 'Task management with hierarchical scope',
        'task_activities' => 'Task activity logging',
        'meetings' => 'Meeting management with Zoom support',
        'meeting_participants' => 'Meeting participation tracking',
        'meeting_activities' => 'Meeting activity logging',
        'events' => 'Event management with registration',
        'event_participants' => 'Event participation tracking',
        'event_activities' => 'Event activity logging',
        'courses' => 'Education/training platform',
        'course_enrollments' => 'Course enrollment management',
        'course_lessons' => 'Course lesson content',
        'lesson_progress' => 'Student progress tracking',
        'course_activities' => 'Course activity logging',
        'course_reviews' => 'Course rating and reviews',
        'notifications' => 'Multi-channel notification system'
    ];
    
    foreach ($tables as $table => $description) {
        echo "• {$table}: {$description}\n";
    }
    
    echo "\n🎉 Complete module infrastructure is now ready!\n";
    echo "📝 Next steps:\n";
    echo "   1. Create controllers for each module\n";
    echo "   2. Build view templates\n";
    echo "   3. Implement API endpoints\n";
    echo "   4. Add sample data for testing\n\n";
    
} catch (PDOException $e) {
    die("❌ Database Error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>