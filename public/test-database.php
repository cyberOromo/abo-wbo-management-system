<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}",
        $config['user'],
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "=== DATABASE CHECK ===\n";
    
    // Check Godinas
    $stmt = $pdo->query("SELECT id, name, code, status FROM godinas ORDER BY name LIMIT 5");
    $godinas = $stmt->fetchAll();
    echo "Godinas found: " . count($godinas) . "\n";
    foreach ($godinas as $godina) {
        echo "  ID: {$godina['id']}, Name: {$godina['name']}, Code: {$godina['code']}, Status: {$godina['status']}\n";
    }
    
    echo "\n";
    
    // Check Gamtas
    $stmt = $pdo->query("SELECT id, name, code, godina_id, status FROM gamtas ORDER BY name LIMIT 5");
    $gamtas = $stmt->fetchAll();
    echo "Gamtas found: " . count($gamtas) . "\n";
    foreach ($gamtas as $gamta) {
        echo "  ID: {$gamta['id']}, Name: {$gamta['name']}, Code: {$gamta['code']}, Godina: {$gamta['godina_id']}, Status: {$gamta['status']}\n";
    }
    
    echo "\n";
    
    // Check Gurmus
    $stmt = $pdo->query("SELECT id, name, code, gamta_id, status FROM gurmus ORDER BY name LIMIT 5");
    $gurmus = $stmt->fetchAll();
    echo "Gurmus found: " . count($gurmus) . "\n";
    foreach ($gurmus as $gurmu) {
        echo "  ID: {$gurmu['id']}, Name: {$gurmu['name']}, Code: {$gurmu['code']}, Gamta: {$gurmu['gamta_id']}, Status: {$gurmu['status']}\n";
    }
    
    // Test the specific query from getGamtasByGodina method
    if (!empty($godinas)) {
        echo "\n=== TESTING AJAX QUERY ===\n";
        $testGodinaId = $godinas[0]['id'];
        echo "Testing with Godina ID: $testGodinaId\n";
        
        $stmt = $pdo->prepare("SELECT id, name, code, description FROM gamtas WHERE godina_id = ? AND status = 'active' ORDER BY name");
        $stmt->execute([$testGodinaId]);
        $results = $stmt->fetchAll();
        echo "Gamtas for Godina $testGodinaId: " . count($results) . "\n";
        foreach ($results as $result) {
            echo "  - {$result['name']} ({$result['code']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>