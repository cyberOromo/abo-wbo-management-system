<?php
$pdo = new PDO('mysql:host=localhost;dbname=abo_wbo_db', 'root', '');

echo "Users table structure:\n";
$stmt = $pdo->query('DESCRIBE users');
while ($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\nPositions table structure:\n";
try {
    $stmt = $pdo->query('DESCRIBE positions');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Table does not exist\n";
}