<?php
/**
 * Simple Migration Runner - Table Creation Only
 * Creates all required tables without complex triggers
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Database configuration
$config = [
    'host' => 'localhost',
    'port' => '3306',
    'dbname' => 'abo_wbo_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    // Connect to database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Connected to database '{$config['dbname']}'\n";
    
    // Create globals table
    echo "🔄 Creating globals table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS globals (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            mission_statement TEXT,
            vision_statement TEXT,
            established_date DATE,
            headquarters_address TEXT,
            contact_email VARCHAR(255),
            contact_phone VARCHAR(50),
            website VARCHAR(255),
            logo_path VARCHAR(500),
            total_godinas INT DEFAULT 0,
            total_gamtas INT DEFAULT 0,
            total_gurmus INT DEFAULT 0,
            total_members INT DEFAULT 0,
            total_donations DECIMAL(15,2) DEFAULT 0.00,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            metadata JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by INT,
            updated_by INT,
            INDEX idx_globals_status (status),
            INDEX idx_globals_code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Update existing tables to add global_id
    echo "🔄 Updating existing tables with global_id...\n";
    
    // Add global_id to godinas if not exists
    try {
        $pdo->exec("ALTER TABLE godinas ADD COLUMN global_id INT AFTER id");
        $pdo->exec("ALTER TABLE godinas ADD FOREIGN KEY (global_id) REFERENCES globals(id) ON DELETE CASCADE");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') === false) {
            echo "⚠️  Warning adding global_id to godinas: " . $e->getMessage() . "\n";
        }
    }
    
    // Create donors table
    echo "🔄 Creating donors table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS donors (
            id INT PRIMARY KEY AUTO_INCREMENT,
            donor_type ENUM('individual', 'group', 'organization', 'business') NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            group_name VARCHAR(255),
            organization_name VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(50),
            address TEXT,
            city VARCHAR(100),
            state_province VARCHAR(100),
            country VARCHAR(100),
            postal_code VARCHAR(20),
            date_of_birth DATE,
            gender ENUM('male', 'female', 'other'),
            occupation VARCHAR(255),
            employer VARCHAR(255),
            preferred_contact ENUM('email', 'phone', 'mail') DEFAULT 'email',
            total_donated DECIMAL(15,2) DEFAULT 0.00,
            donation_count INT DEFAULT 0,
            first_donation_date DATE,
            last_donation_date DATE,
            status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by INT,
            INDEX idx_donors_type (donor_type),
            INDEX idx_donors_email (email),
            INDEX idx_donors_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create donation_campaigns table
    echo "🔄 Creating donation_campaigns table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS donation_campaigns (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            campaign_type ENUM('general', 'emergency', 'project', 'annual', 'memorial', 'cultural') DEFAULT 'general',
            target_amount DECIMAL(15,2) NOT NULL,
            raised_amount DECIMAL(15,2) DEFAULT 0.00,
            currency VARCHAR(3) DEFAULT 'USD',
            start_date DATE NOT NULL,
            end_date DATE,
            status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
            visibility ENUM('public', 'members_only', 'private') DEFAULT 'public',
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu') DEFAULT 'global',
            global_id INT,
            allow_anonymous BOOLEAN DEFAULT TRUE,
            min_donation_amount DECIMAL(10,2) DEFAULT 1.00,
            auto_receipt BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by INT,
            metadata JSON,
            INDEX idx_campaigns_status (status),
            INDEX idx_campaigns_dates (start_date, end_date),
            INDEX idx_campaigns_scope (level_scope, global_id),
            FOREIGN KEY (global_id) REFERENCES globals(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Update donations table if exists, or create if not
    echo "🔄 Creating/updating donations table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS donations_new (
            id INT PRIMARY KEY AUTO_INCREMENT,
            donation_number VARCHAR(50) UNIQUE NOT NULL,
            donor_id INT,
            donor_type ENUM('individual', 'group', 'organization', 'business') NOT NULL,
            campaign_id INT,
            event_name VARCHAR(255),
            event_date DATE,
            amount DECIMAL(15,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            payment_method ENUM('cash', 'check', 'credit_card', 'bank_transfer', 'paypal', 'stripe', 'wire_transfer', 'money_order', 'cryptocurrency', 'mobile_payment', 'other') NOT NULL,
            payment_date DATE,
            payment_status ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu') DEFAULT 'global',
            global_id INT,
            submitted_by INT,
            status ENUM('pending_approval', 'approved', 'rejected', 'cancelled') DEFAULT 'pending_approval',
            donation_purpose VARCHAR(255),
            tax_year YEAR,
            receipt_number VARCHAR(50),
            receipt_generated_at TIMESTAMP NULL,
            approved_by INT,
            approved_at TIMESTAMP NULL,
            rejected_by INT,
            rejected_at TIMESTAMP NULL,
            rejection_reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            metadata JSON,
            INDEX idx_donations_donor (donor_id),
            INDEX idx_donations_campaign (campaign_id),
            INDEX idx_donations_status (status),
            INDEX idx_donations_payment_status (payment_status),
            INDEX idx_donations_date (payment_date),
            INDEX idx_donations_scope (level_scope, global_id),
            FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE SET NULL,
            FOREIGN KEY (campaign_id) REFERENCES donation_campaigns(id) ON DELETE SET NULL,
            FOREIGN KEY (global_id) REFERENCES globals(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Drop old donations table and rename new one
    try {
        $pdo->exec("DROP TABLE IF EXISTS donations");
        $pdo->exec("RENAME TABLE donations_new TO donations");
    } catch (PDOException $e) {
        echo "⚠️  Warning renaming donations table: " . $e->getMessage() . "\n";
    }
    
    // Create budgets table
    echo "🔄 Creating budgets table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS budgets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            budget_name VARCHAR(255) NOT NULL,
            budget_code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            fiscal_year YEAR NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu') DEFAULT 'global',
            global_id INT,
            total_budget DECIMAL(15,2) NOT NULL,
            spent_amount DECIMAL(15,2) DEFAULT 0.00,
            remaining_amount DECIMAL(15,2) GENERATED ALWAYS AS (total_budget - spent_amount) STORED,
            olf_support_allocation DECIMAL(15,2) DEFAULT 0.00,
            education_allocation DECIMAL(15,2) DEFAULT 0.00,
            healthcare_allocation DECIMAL(15,2) DEFAULT 0.00,
            community_development_allocation DECIMAL(15,2) DEFAULT 0.00,
            emergency_relief_allocation DECIMAL(15,2) DEFAULT 0.00,
            cultural_programs_allocation DECIMAL(15,2) DEFAULT 0.00,
            youth_programs_allocation DECIMAL(15,2) DEFAULT 0.00,
            women_programs_allocation DECIMAL(15,2) DEFAULT 0.00,
            administrative_allocation DECIMAL(15,2) DEFAULT 0.00,
            status ENUM('draft', 'active', 'approved', 'closed', 'cancelled') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by INT,
            metadata JSON,
            INDEX idx_budgets_fiscal_year (fiscal_year),
            INDEX idx_budgets_status (status),
            INDEX idx_budgets_scope (level_scope, global_id),
            FOREIGN KEY (global_id) REFERENCES globals(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create expenses table
    echo "🔄 Creating expenses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS expenses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            expense_number VARCHAR(50) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            expense_category ENUM('olf_support', 'education', 'healthcare', 'community_development', 'emergency_relief', 'cultural_programs', 'youth_programs', 'women_programs', 'administrative', 'travel', 'equipment', 'marketing', 'legal', 'other') NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            budget_id INT,
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu') DEFAULT 'global',
            global_id INT,
            status ENUM('pending_approval', 'approved', 'rejected', 'paid', 'cancelled') DEFAULT 'pending_approval',
            submitted_by INT,
            approved_by INT,
            approved_at TIMESTAMP NULL,
            rejected_by INT,
            rejected_at TIMESTAMP NULL,
            rejection_reason TEXT,
            expense_date DATE NOT NULL,
            payment_date DATE,
            payment_method ENUM('cash', 'check', 'bank_transfer', 'credit_card', 'wire_transfer', 'other'),
            receipt_path VARCHAR(500),
            vendor_name VARCHAR(255),
            invoice_number VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            metadata JSON,
            INDEX idx_expenses_status (status),
            INDEX idx_expenses_category (expense_category),
            INDEX idx_expenses_budget (budget_id),
            INDEX idx_expenses_date (expense_date),
            INDEX idx_expenses_scope (level_scope, global_id),
            FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE SET NULL,
            FOREIGN KEY (global_id) REFERENCES globals(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create payment_gateways table
    echo "🔄 Creating payment_gateways table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payment_gateways (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            type ENUM('paypal', 'stripe', 'square', 'bank_transfer', 'mobile_money', 'cryptocurrency', 'other') NOT NULL,
            status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
            configuration JSON,
            supported_currencies JSON,
            transaction_fee_percentage DECIMAL(5,4) DEFAULT 0.0000,
            transaction_fee_fixed DECIMAL(10,2) DEFAULT 0.00,
            minimum_amount DECIMAL(10,2) DEFAULT 1.00,
            maximum_amount DECIMAL(15,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_payment_gateways_type (type),
            INDEX idx_payment_gateways_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create user_assignments table
    echo "🔄 Creating user_assignments table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_assignments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            position_id INT NOT NULL,
            level_scope ENUM('global', 'godina', 'gamta', 'gurmu') NOT NULL,
            global_id INT,
            godina_id INT,
            gamta_id INT,
            gurmu_id INT,
            status ENUM('active', 'inactive', 'suspended', 'terminated') DEFAULT 'active',
            start_date DATE NOT NULL,
            end_date DATE,
            appointment_type ENUM('elected', 'appointed', 'volunteer', 'permanent') DEFAULT 'appointed',
            assigned_by INT,
            assignment_reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            metadata JSON,
            INDEX idx_user_assignments_user (user_id),
            INDEX idx_user_assignments_position (position_id),
            INDEX idx_user_assignments_scope (level_scope),
            INDEX idx_user_assignments_status (status),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (global_id) REFERENCES globals(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "\n📊 Database Tables Summary:\n";
    echo "==========================================\n";
    
    $tables = [
        'globals' => 'Global Organizations',
        'godinas' => 'Regional Organizations',
        'gamtas' => 'District Organizations', 
        'gurmus' => 'Local Groups',
        'positions' => 'Organizational Positions',
        'users' => 'Users',
        'user_assignments' => 'User Position Assignments',
        'donors' => 'Donors',
        'donation_campaigns' => 'Donation Campaigns',
        'donations' => 'Donations',
        'budgets' => 'Budgets',
        'expenses' => 'Expenses',
        'payment_gateways' => 'Payment Gateways'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$table}");
            $stmt->execute();
            $result = $stmt->fetch();
            echo sprintf("%-30s: ✅ %d records\n", $description, $result['count']);
        } catch (PDOException $e) {
            echo sprintf("%-30s: ❌ Table not found\n", $description);
        }
    }
    
    echo "\n🎉 All tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}