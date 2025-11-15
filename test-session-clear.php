<?php
// Test session clearing and auth check
session_start();

// Clear any existing session
session_unset();
session_destroy();

// Start fresh session
session_start();

echo "1. Session cleared and restarted\n";
echo "2. Session ID: " . session_id() . "\n";
echo "3. SESSION array content: " . print_r($_SESSION, true) . "\n";

// Test if user_id is set
echo "4. isset(\$_SESSION['user_id']): " . (isset($_SESSION['user_id']) ? 'TRUE' : 'FALSE') . "\n";
echo "5. empty(\$_SESSION['user_id']): " . (empty($_SESSION['user_id']) ? 'TRUE' : 'FALSE') . "\n";

// Simulate auth_check logic
$auth_result = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
echo "6. Auth check result: " . ($auth_result ? 'TRUE' : 'FALSE') . "\n";
?>