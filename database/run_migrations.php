<?php
/**
 * Comprehensive Migration Runner
 * Runs all new migrations including Global model and Finance management
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
    
    // List of migrations to run
    $migrations = [
        '009_create_globals_table.sql',
        '010_create_finance_management_tables.sql'
    ];
    
    foreach ($migrations as $migration) {
        $migrationFile = __DIR__ . '/migrations/' . $migration;
        
        if (file_exists($migrationFile)) {
            echo "🔄 Running migration: {$migration}\n";
            
            $sql = file_get_contents($migrationFile);
            
            // Split by semicolon and execute each statement
            $statements = explode(';', $sql);
            $executedCount = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !str_starts_with($statement, '--') && !str_starts_with($statement, '/*')) {
                    try {
                        $pdo->exec($statement);
                        $executedCount++;
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            echo "⚠️  Warning in {$migration}: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
            
            echo "✅ Migration {$migration} completed ({$executedCount} statements executed)\n";
        } else {
            echo "❌ Migration file not found: {$migrationFile}\n";
        }
    }
    
    // Verify tables were created
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
            echo sprintf("%-30s: %d records\n", $description, $result['count']);
        } catch (PDOException $e) {
            echo sprintf("%-30s: ❌ Table not found\n", $description);
        }
    }
    
    echo "\n🎉 All migrations completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}