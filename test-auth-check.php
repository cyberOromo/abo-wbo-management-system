<?php
// Start session to test auth_check function
session_start();

// Include the helpers to get auth_check function
require_once __DIR__ . '/app/helpers.php';

echo "Session status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Auth check result: " . (auth_check() ? 'TRUE' : 'FALSE') . "\n";

if (isset($_SESSION['user'])) {
    echo "Session user exists: YES\n";
    print_r($_SESSION['user']);
} else {
    echo "Session user exists: NO\n";
}

if (isset($_SESSION)) {
    echo "Session variables:\n";
    print_r($_SESSION);
} else {
    echo "No session variables\n";
}
?>