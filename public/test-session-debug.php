<?php
session_start();

echo "=== SESSION DEBUG ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session status: " . session_status() . "\n";

echo "\n=== CURRENT SESSION DATA ===\n";
if (empty($_SESSION)) {
    echo "Session is EMPTY\n";
} else {
    echo "Session contains:\n";
    foreach ($_SESSION as $key => $value) {
        echo "  $key: " . print_r($value, true) . "\n";
    }
}

echo "\n=== AUTH CHECK SIMULATION ===\n";
$userIdExists = isset($_SESSION['user_id']);
$userIdNotEmpty = !empty($_SESSION['user_id']);
$authResult = $userIdExists && $userIdNotEmpty;

echo "isset(\$_SESSION['user_id']): " . ($userIdExists ? 'TRUE' : 'FALSE') . "\n";
echo "!empty(\$_SESSION['user_id']): " . ($userIdNotEmpty ? 'TRUE' : 'FALSE') . "\n";
echo "auth_check() result would be: " . ($authResult ? 'TRUE' : 'FALSE') . "\n";

// Clear session for testing
echo "\n=== CLEARING SESSION ===\n";
session_unset();
session_destroy();
echo "Session cleared!\n";

// Test with fresh session
session_start();
echo "New session ID: " . session_id() . "\n";
echo "New session empty: " . (empty($_SESSION) ? 'YES' : 'NO') . "\n";
?>