<?php
/**
 * ABO-WBO System Status & Configuration Check
 * Comprehensive system verification page
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment
require_once '../vendor/autoload.php';
require_once '../app/helpers.php';

// Start session for testing
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABO-WBO System Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-good { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
        .config-section { margin-bottom: 2rem; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-cogs me-2"></i>
                ABO-WBO System Status
            </span>
            <span class="navbar-text">
                <?= date('Y-m-d H:i:s') ?>
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <!-- Server Configuration -->
                <div class="card config-section">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-server me-2"></i>Server Configuration</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td>PHP Version</td>
                                <td><span class="status-good"><?= PHP_VERSION ?></span></td>
                            </tr>
                            <tr>
                                <td>Server Software</td>
                                <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                            </tr>
                            <tr>
                                <td>Document Root</td>
                                <td><code><?= $_SERVER['DOCUMENT_ROOT'] ?></code></td>
                            </tr>
                            <tr>
                                <td>Server Name</td>
                                <td><strong><?= $_SERVER['SERVER_NAME'] ?></strong></td>
                            </tr>
                            <tr>
                                <td>Request URI</td>
                                <td><code><?= $_SERVER['REQUEST_URI'] ?></code></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Database Status -->
                <div class="card config-section">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-database me-2"></i>Database Status</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $config = require '../config/database.php';
                            $pdo = new PDO(
                                "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}",
                                $config['user'],
                                $config['pass'],
                                $config['options']
                            );
                            
                            echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Database connection successful</div>';
                            
                            // Get database statistics
                            $stats = $pdo->query("SELECT 
                                (SELECT COUNT(*) FROM godinas) as godinas,
                                (SELECT COUNT(*) FROM gamtas) as gamtas, 
                                (SELECT COUNT(*) FROM gurmus) as gurmus,
                                (SELECT COUNT(*) FROM positions) as positions,
                                (SELECT COUNT(*) FROM users) as users
                            ")->fetch();
                            
                            echo '<table class="table table-sm">';
                            echo '<tr><td>Godinas</td><td><span class="badge bg-primary">' . $stats['godinas'] . '</span></td></tr>';
                            echo '<tr><td>Gamtas</td><td><span class="badge bg-primary">' . $stats['gamtas'] . '</span></td></tr>';
                            echo '<tr><td>Gurmus</td><td><span class="badge bg-primary">' . $stats['gurmus'] . '</span></td></tr>';
                            echo '<tr><td>Positions</td><td><span class="badge bg-primary">' . $stats['positions'] . '</span></td></tr>';
                            echo '<tr><td>Users</td><td><span class="badge bg-primary">' . $stats['users'] . '</span></td></tr>';
                            echo '</table>';
                            
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Environment Configuration -->
                <div class="card config-section">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-cog me-2"></i>Environment Configuration</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Load environment variables
                        $envFile = '../.env';
                        if (file_exists($envFile)) {
                            echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>.env file found and loaded</div>';
                            
                            $envVars = [
                                'APP_NAME' => env('APP_NAME'),
                                'APP_VERSION' => env('APP_VERSION'),
                                'APP_URL' => env('APP_URL'),
                                'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false',
                                'DB_HOST' => env('DB_HOST'),
                                'DB_NAME' => env('DB_NAME'),
                                'DB_USER' => env('DB_USER'),
                            ];
                            
                            echo '<table class="table table-sm">';
                            foreach ($envVars as $key => $value) {
                                echo '<tr><td>' . $key . '</td><td><code>' . htmlspecialchars($value) . '</code></td></tr>';
                            }
                            echo '</table>';
                        } else {
                            echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>.env file not found</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- System Links -->
                <div class="card config-section">
                    <div class="card-header bg-secondary text-white">
                        <h5><i class="fas fa-link me-2"></i>System Navigation</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/" class="btn btn-outline-primary">
                                <i class="fas fa-home me-1"></i>Home (MVC Welcome)
                            </a>
                            <a href="/debug" class="btn btn-outline-info">
                                <i class="fas fa-bug me-1"></i>Debug Route
                            </a>
                            <a href="/login" class="btn btn-outline-success">
                                <i class="fas fa-sign-in-alt me-1"></i>Simple Login
                            </a>
                            <a href="/auth/login" class="btn btn-outline-warning">
                                <i class="fas fa-shield-alt me-1"></i>Auth Controller Login
                            </a>
                            <a href="/hierarchy-manager.php" class="btn btn-primary">
                                <i class="fas fa-sitemap me-1"></i>Hierarchy Manager
                            </a>
                            <a href="/login-test.php" class="btn btn-outline-secondary">
                                <i class="fas fa-vials me-1"></i>Login Test
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MVC Routing Test -->
        <div class="card config-section">
            <div class="card-header bg-dark text-white">
                <h5><i class="fas fa-route me-2"></i>MVC Routing Test</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Virtual Host Status</h6>
                        <?php if ($_SERVER['SERVER_NAME'] === 'abo-wbo.local'): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Virtual host active: <strong>abo-wbo.local</strong>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Using: <strong><?= $_SERVER['SERVER_NAME'] ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <h6>URL Rewriting</h6>
                        <?php if (strpos($_SERVER['REQUEST_URI'], 'system-status.php') !== false): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Direct file access working
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                MVC routing active
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <h6>Session Status</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-id-card me-2"></i>
                            Session ID: <code><?= substr(session_id(), 0, 8) ?>...</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Login Test -->
        <div class="card config-section">
            <div class="card-header bg-danger text-white">
                <h5><i class="fas fa-user-shield me-2"></i>Quick Admin Test</h5>
            </div>
            <div class="card-body">
                <?php
                if ($_POST && isset($_POST['test_admin'])) {
                    try {
                        $config = require '../config/database.php';
                        $pdo = new PDO(
                            "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}",
                            $config['user'],
                            $config['pass'],
                            $config['options']
                        );
                        
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
                        $stmt->execute(['admin@abo-wbo.org']);
                        $user = $stmt->fetch();
                        
                        if ($user && password_verify('admin123', $user['password_hash'])) {
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-check-circle me-2"></i>';
                            echo '<strong>Admin credentials verified!</strong><br>';
                            echo 'User: ' . $user['first_name'] . ' ' . $user['last_name'] . '<br>';
                            echo 'Role: ' . $user['role'] . '<br>';
                            echo 'Status: ' . $user['status'];
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-danger">';
                            echo '<i class="fas fa-times-circle me-2"></i>';
                            echo 'Admin credentials failed verification';
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">';
                        echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                        echo 'Error: ' . htmlspecialchars($e->getMessage());
                        echo '</div>';
                    }
                }
                ?>
                <form method="POST">
                    <button type="submit" name="test_admin" class="btn btn-danger">
                        <i class="fas fa-vial me-1"></i>
                        Test Admin Login (admin@abo-wbo.org / admin123)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">
            ABO-WBO Management System - Configuration Check | 
            PHP <?= PHP_VERSION ?> | 
            <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown Server' ?>
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>