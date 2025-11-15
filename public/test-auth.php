<?php
// Simple test in public directory
echo "Auth test working in public directory\n";
echo "Session status: " . session_status() . "\n";

// Start session
session_start();
echo "Session started, ID: " . session_id() . "\n";

// Check for user_id
echo "isset(\$_SESSION['user_id']): " . (isset($_SESSION['user_id']) ? 'TRUE' : 'FALSE') . "\n";
if (isset($_SESSION['user_id'])) {
    echo "user_id value: " . $_SESSION['user_id'] . "\n";
}
?>