<?php

require_once __DIR__ . '/../config/database.php';

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}", $config['user'], $config['pass'], $config['options']);

echo "=== User ID Investigation ===\n\n";

echo "Users with assignments:\n";
$stmt = $pdo->query("SELECT u.id as user_id, u.first_name, u.last_name, ua.user_id as assignment_user_id, ua.id as assignment_id FROM users u LEFT JOIN user_assignments ua ON u.id = ua.user_id WHERE ua.status = 'active' ORDER BY u.id");
while ($row = $stmt->fetch()) {
    echo "User ID: {$row['user_id']}, Assignment refers to: {$row['assignment_user_id']}, Name: {$row['first_name']} {$row['last_name']}\n";
}

echo "\nAll Users:\n";
$stmt = $pdo->query("SELECT id, first_name, last_name FROM users ORDER BY id");
while ($row = $stmt->fetch()) {
    echo "User ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}\n";
}

echo "\nChecking specific users from the failed operation:\n";
$userIds = [9, 10, 11, 12, 13, 14, 15, 16];
foreach ($userIds as $id) {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        echo "✅ User ID {$id}: {$user['first_name']} {$user['last_name']}\n";
    } else {
        echo "❌ User ID {$id}: NOT FOUND\n";
    }
}

?>