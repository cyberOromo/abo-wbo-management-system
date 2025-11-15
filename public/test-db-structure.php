<?php
// Quick database structure check
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

try {
    $config = require __DIR__ . '/../config/app.php';
    $dbConfig = $config['database'];
    
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}", 
        $dbConfig['user'], 
        $dbConfig['pass']
    );
    
    echo "=== CHECKING HIERARCHY TABLES ===\n";
    $tables = ['godinas', 'gamtas', 'gurmus', 'user_assignments'];
    
    foreach ($tables as $table) {
        echo "\n--- $table TABLE ---\n";
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo sprintf("%-20s %-30s\n", $row['Field'], $row['Type']);
            }
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== SAMPLE HIERARCHY DATA ===\n";
    
    // Check Godinas
    try {
        $stmt = $pdo->query("SELECT id, name, code, status FROM godinas LIMIT 5");
        echo "GODINAS:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("  ID: %-3s Name: %-20s Code: %-10s Status: %s\n", 
                $row['id'], $row['name'], $row['code'], $row['status']);
        }
    } catch (Exception $e) {
        echo "GODINAS ERROR: " . $e->getMessage() . "\n";
    }
    
    // Check Gamtas
    try {
        $stmt = $pdo->query("SELECT id, name, code, godina_id, status FROM gamtas LIMIT 5");
        echo "\nGAMTAS:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("  ID: %-3s Name: %-20s Code: %-10s Godina: %-3s Status: %s\n", 
                $row['id'], $row['name'], $row['code'], $row['godina_id'], $row['status']);
        }
    } catch (Exception $e) {
        echo "GAMTAS ERROR: " . $e->getMessage() . "\n";
    }
    
    // Check Gurmus
    try {
        $stmt = $pdo->query("SELECT id, name, code, gamta_id, status FROM gurmus LIMIT 5");
        echo "\nGURMUS:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("  ID: %-3s Name: %-20s Code: %-10s Gamta: %-3s Status: %s\n", 
                $row['id'], $row['name'], $row['code'], $row['gamta_id'], $row['status']);
        }
    } catch (Exception $e) {
        echo "GURMUS ERROR: " . $e->getMessage() . "\n";
    }
    
    // Check Positions
    try {
        $stmt = $pdo->query("SELECT id, name, hierarchy_type, status FROM positions WHERE status = 'active' LIMIT 10");
        echo "\nPOSITIONS:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("  ID: %-3s Name: %-30s Type: %-10s Status: %s\n", 
                $row['id'], $row['name'], $row['hierarchy_type'], $row['status']);
        }
    } catch (Exception $e) {
        echo "POSITIONS ERROR: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>