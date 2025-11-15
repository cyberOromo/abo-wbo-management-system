<?php
/**
 * Database Migration Script
 * ABO-WBO Management System
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"');
        }
    }
}

// Database configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'dbname' => $_ENV['DB_NAME'] ?? 'abo_wbo_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4'
];

try {
    // Connect to MySQL (without database first)
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '{$config['dbname']}' created/verified\n";
    
    // Connect to the specific database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Connected to database '{$config['dbname']}'\n";
    
    // Run schema migration
    echo "🔄 Running database schema migration...\n";
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        $schema = file_get_contents($schemaFile);
        
        // Execute the entire schema using exec_multi
        try {
            $pdo->exec($schema);
            echo "✅ Schema executed successfully\n";
        } catch (PDOException $e) {
            echo "⚠️  Schema warning: " . $e->getMessage() . "\n";
        }
        
        $executedCount = 1;
        
        echo "✅ Schema migration completed ({$executedCount} statements executed)\n";
    } else {
        echo "❌ Schema file not found: {$schemaFile}\n";
        exit(1);
    }
    
    // Run seed data
    echo "🔄 Running seed data...\n";
    
    // 1. Hierarchy seed
    $hierarchyFile = __DIR__ . '/seeds/hierarchy-seed.sql';
    if (file_exists($hierarchyFile)) {
        $seedData = file_get_contents($hierarchyFile);
        $statements = explode(';', $seedData);
        $executedCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                try {
                    $pdo->exec($statement);
                    $executedCount++;
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        echo "⚠️  Warning in hierarchy seed: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "✅ Hierarchy seed data loaded ({$executedCount} statements executed)\n";
    }
    
    // 2. Positions seed
    $positionsFile = __DIR__ . '/seeds/positions-seed.sql';
    if (file_exists($positionsFile)) {
        $seedData = file_get_contents($positionsFile);
        $statements = explode(';', $seedData);
        $executedCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                try {
                    $pdo->exec($statement);
                    $executedCount++;
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        echo "⚠️  Warning in positions seed: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "✅ Positions seed data loaded ({$executedCount} statements executed)\n";
    }
    
    // Create admin user
    echo "🔄 Creating admin user...\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@abo-wbo.local']);
    
    if (!$stmt->fetch()) {
        // Get a sample Gurmu ID
        $stmt = $pdo->prepare("SELECT id FROM gurmus LIMIT 1");
        $stmt->execute();
        $gurmu = $stmt->fetch();
        $gurmuId = $gurmu ? $gurmu['id'] : null;
        
        // Get global chairperson position
        $stmt = $pdo->prepare("SELECT id FROM positions WHERE key_name = 'global_chairperson' LIMIT 1");
        $stmt->execute();
        $position = $stmt->fetch();
        $positionId = $position ? $position['id'] : null;
        
        $adminPassword = password_hash('admin123', PASSWORD_ARGON2ID);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (
                first_name, last_name, email, password, 
                gurmu_id, position_id, level_scope, 
                status, approval_status, 
                email_verified_at, approved_at,
                language_preference, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, NOW())
        ");
        
        $stmt->execute([
            'System',
            'Administrator', 
            'admin@abo-wbo.local',
            $adminPassword,
            $gurmuId,
            $positionId,
            'global',
            'active',
            'approved',
            'en'
        ]);
        
        echo "✅ Admin user created (email: admin@abo-wbo.local, password: admin123)\n";
    } else {
        echo "✅ Admin user already exists\n";
    }
    
    // Display summary
    echo "\n📊 Database Setup Summary:\n";
    echo "==========================================\n";
    
    $tables = [
        'godinas' => 'Regions',
        'gamtas' => 'Districts', 
        'gurmus' => 'Local Groups',
        'positions' => 'Organizational Positions',
        'users' => 'Users'
    ];
    
    foreach ($tables as $table => $description) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$table}");
        $stmt->execute();
        $result = $stmt->fetch();
        echo sprintf("%-25s: %d records\n", $description, $result['count']);
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "🌐 You can now access the application at: {$_ENV['APP_URL']}\n";
    echo "👤 Admin login: admin@abo-wbo.local / admin123\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}