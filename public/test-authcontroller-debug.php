<?php
// Debug the exact AuthController behavior

// Simulate the AuthController showLogin method
echo "=== AuthController::showLogin() DEBUG ===\n";

// Start session like auth_check() does
if (session_status() === PHP_SESSION_NONE) {
    echo "Starting session...\n";
    session_start();
} else {
    echo "Session already started\n";
}

echo "Session ID: " . session_id() . "\n";
echo "Session status: " . session_status() . "\n";

// Debug session contents
echo "\nSession contents:\n";
if (empty($_SESSION)) {
    echo "Session is EMPTY\n";
} else {
    print_r($_SESSION);
}

// Simulate auth_check() step by step
echo "\n=== auth_check() simulation ===\n";
$userIdSet = isset($_SESSION['user_id']);
$userIdNotEmpty = !empty($_SESSION['user_id']);
$authCheckResult = $userIdSet && $userIdNotEmpty;

echo "isset(\$_SESSION['user_id']): " . ($userIdSet ? 'TRUE' : 'FALSE') . "\n";
echo "!empty(\$_SESSION['user_id']): " . ($userIdNotEmpty ? 'TRUE' : 'FALSE') . "\n";
echo "auth_check() result: " . ($authCheckResult ? 'TRUE' : 'FALSE') . "\n";

// Simulate the AuthController decision
echo "\n=== AuthController decision ===\n";
if ($authCheckResult) {
    echo "WOULD REDIRECT to /dashboard (user is authenticated)\n";
} else {
    echo "WOULD RENDER login form (user NOT authenticated)\n";
}

// Additional debug
echo "\n=== Additional Debug ===\n";
echo "PHP Session save path: " . session_save_path() . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session cookie params: ";
print_r(session_get_cookie_params());
?>