<?php
/**
 * Responsibilities Database Migration Script
 * ABO-WBO Management System - Shared Responsibilities & Tasks (5 Core Areas)
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
    // Connect to the database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Connected to database '{$config['dbname']}'\n";
    
    // Run responsibilities schema migration
    echo "🔄 Running Shared Responsibilities & Tasks schema migration...\n";
    $schemaFile = __DIR__ . '/migrations/responsibilities_schema.sql';
    if (file_exists($schemaFile)) {
        $schema = file_get_contents($schemaFile);
        
        // Split the schema into individual statements
        $statements = explode(';', $schema);
        $executedCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !str_starts_with($statement, '--') && strlen($statement) > 10) {
                try {
                    $pdo->exec($statement);
                    $executedCount++;
                    
                    // Check if this is a table creation
                    if (stripos($statement, 'CREATE TABLE') !== false) {
                        preg_match('/CREATE TABLE[^`]*`?([^`\s]+)`?/i', $statement, $matches);
                        if (isset($matches[1])) {
                            echo "  📋 Created table: {$matches[1]}\n";
                        }
                    } elseif (stripos($statement, 'INSERT') !== false) {
                        preg_match('/INSERT[^`]*INTO[^`]*`?([^`\s\(]+)`?/i', $statement, $matches);
                        if (isset($matches[1])) {
                            echo "  📝 Inserted data into: {$matches[1]}\n";
                        }
                    } elseif (stripos($statement, 'CREATE INDEX') !== false) {
                        echo "  🔍 Created index\n";
                    } elseif (stripos($statement, 'CREATE OR REPLACE VIEW') !== false) {
                        preg_match('/CREATE OR REPLACE VIEW[^`]*`?([^`\s]+)`?/i', $statement, $matches);
                        if (isset($matches[1])) {
                            echo "  👁  Created view: {$matches[1]}\n";
                        }
                    }
                    
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                        strpos($e->getMessage(), 'already exists') === false) {
                        echo "⚠️  Warning: " . $e->getMessage() . "\n";
                        $errorCount++;
                    }
                }
            }
        }
        
        echo "✅ Responsibilities schema migration completed ({$executedCount} statements executed, {$errorCount} warnings)\n";
    } else {
        echo "❌ Responsibilities schema file not found: {$schemaFile}\n";
        exit(1);
    }
    
    // Verify table creation
    echo "\n🔍 Verifying created tables...\n";
    $requiredTables = [
        'responsibilities',
        'responsibility_assignments', 
        'responsibility_tasks',
        'task_assignments',
        'activity_logs'
    ];
    
    foreach ($requiredTables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->fetch()) {
            // Get record count
            $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$table}");
            $countStmt->execute();
            $count = $countStmt->fetch()['count'];
            echo "  ✅ Table '{$table}' exists ({$count} records)\n";
        } else {
            echo "  ❌ Table '{$table}' not found!\n";
        }
    }
    
    // Verify views
    echo "\n👁  Verifying created views...\n";
    $requiredViews = [
        'v_shared_responsibilities',
        'v_position_responsibilities',
        'v_user_assignment_summary'
    ];
    
    foreach ($requiredViews as $view) {
        $stmt = $pdo->prepare("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_{$config['dbname']} = ?");
        $stmt->execute([$view]);
        if ($stmt->fetch()) {
            echo "  ✅ View '{$view}' exists\n";
        } else {
            echo "  ❌ View '{$view}' not found!\n";
        }
    }
    
    // Display summary of responsibilities data
    echo "\n📊 Shared Responsibilities & Tasks Summary:\n";
    echo "================================================\n";
    
    // Shared responsibilities (5 core areas)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM responsibilities WHERE is_shared = 1");
    $stmt->execute();
    $sharedCount = $stmt->fetch()['count'];
    echo sprintf("%-35s: %d records\n", "Shared Responsibilities (5 Core Areas)", $sharedCount);
    
    // Individual responsibilities
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM responsibilities WHERE is_shared = 0");
    $stmt->execute();
    $individualCount = $stmt->fetch()['count'];
    echo sprintf("%-35s: %d records\n", "Individual Position Responsibilities", $individualCount);
    
    // Tasks
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM responsibility_tasks");
    $stmt->execute();
    $tasksCount = $stmt->fetch()['count'];
    echo sprintf("%-35s: %d records\n", "Responsibility Tasks", $tasksCount);
    
    // List the 5 core shared responsibilities
    echo "\n🎯 5 Core Shared Responsibilities:\n";
    $stmt = $pdo->prepare("SELECT name_en, name_om FROM responsibilities WHERE is_shared = 1 ORDER BY id");
    $stmt->execute();
    $coreResponsibilities = $stmt->fetchAll();
    
    foreach ($coreResponsibilities as $i => $responsibility) {
        echo sprintf("%d. %-30s (%s)\n", 
            $i + 1, 
            $responsibility['name_en'], 
            $responsibility['name_om']
        );
    }
    
    echo "\n🎉 Shared Responsibilities & Tasks system setup completed successfully!\n";
    echo "🌐 Access the system at: {$_ENV['APP_URL']}/responsibilities\n";
    echo "📋 The 5 Core Areas are now ready for assignment to all positions\n";
    echo "💡 Use the web interface to assign responsibilities to position holders\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}