<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Simple auth check
if (!isset($_SESSION['user_id'])) {
    // Show login form
    if ($_POST && isset($_POST['email'], $_POST['password'])) {
        try {
            $config = require '../config/database.php';
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}",
                $config['user'],
                $config['pass'],
                $config['options']
            );
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$_POST['email']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($_POST['password'], $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role'];
                header('Location: hierarchy-manager.php');
                exit;
            } else {
                $loginError = "Invalid credentials";
            }
        } catch (Exception $e) {
            $loginError = "Database error: " . $e->getMessage();
        }
    }
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hierarchy Manager - Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container">
            <div class="row justify-content-center" style="min-height: 100vh; align-items: center;">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">🏢 ABO-WBO Hierarchy Manager</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($loginError)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email:</label>
                                    <input type="email" name="email" class="form-control" value="admin@abo-wbo.org" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password:</label>
                                    <input type="password" name="password" class="form-control" value="admin123" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Database connection
try {
    $config = require '../config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}",
        $config['user'],
        $config['pass'],
        $config['options']
    );
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: hierarchy-manager.php');
    exit;
}

// Get hierarchy data
$godinas = $pdo->query("SELECT * FROM godinas ORDER BY name")->fetchAll();
$gamtas = $pdo->query("SELECT g.*, gd.name as godina_name FROM gamtas g JOIN godinas gd ON g.godina_id = gd.id ORDER BY gd.name, g.name")->fetchAll();
$gurmus = $pdo->query("SELECT gu.*, ga.name as gamta_name, gd.name as godina_name 
                       FROM gurmus gu 
                       JOIN gamtas ga ON gu.gamta_id = ga.id 
                       JOIN godinas gd ON ga.godina_id = gd.id 
                       ORDER BY gd.name, ga.name, gu.name")->fetchAll();
$positions = $pdo->query("SELECT * FROM positions ORDER BY name")->fetchAll();

// Get statistics
$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM godinas) as godinas,
    (SELECT COUNT(*) FROM gamtas) as gamtas, 
    (SELECT COUNT(*) FROM gurmus) as gurmus,
    (SELECT COUNT(*) FROM positions) as positions,
    (SELECT COUNT(*) FROM users) as users
")->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABO-WBO Hierarchy Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hierarchy-card { margin-bottom: 1rem; }
        .stat-card { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .tab-content { margin-top: 1rem; }
        .table th { background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-sitemap me-2"></i>
                ABO-WBO Hierarchy Manager
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?>
                </span>
                <a class="nav-link" href="?logout=1">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-globe fa-2x mb-2"></i>
                        <h4><?= $stats['godinas'] ?></h4>
                        <small>Godinas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-map fa-2x mb-2"></i>
                        <h4><?= $stats['gamtas'] ?></h4>
                        <small>Gamtas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-building fa-2x mb-2"></i>
                        <h4><?= $stats['gurmus'] ?></h4>
                        <small>Gurmus</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-2x mb-2"></i>
                        <h4><?= $stats['positions'] ?></h4>
                        <small>Positions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?= $stats['users'] ?></h4>
                        <small>Users</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4>READY</h4>
                        <small>System Status</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="hierarchyTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    <i class="fas fa-eye me-1"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="godinas-tab" data-bs-toggle="tab" data-bs-target="#godinas-content" type="button" role="tab">
                    <i class="fas fa-globe me-1"></i>Godinas (<?= count($godinas) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="gamtas-tab" data-bs-toggle="tab" data-bs-target="#gamtas-content" type="button" role="tab">
                    <i class="fas fa-map me-1"></i>Gamtas (<?= count($gamtas) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="gurmus-tab" data-bs-toggle="tab" data-bs-target="#gurmus-content" type="button" role="tab">
                    <i class="fas fa-building me-1"></i>Gurmus (<?= count($gurmus) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="positions-tab" data-bs-toggle="tab" data-bs-target="#positions-content" type="button" role="tab">
                    <i class="fas fa-user-tie me-1"></i>Positions (<?= count($positions) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="hierarchyTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-sitemap me-2"></i>Organizational Hierarchy Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>4-Tier Structure:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-globe text-primary me-2"></i><strong>Global</strong> → <strong>Godina</strong> (<?= $stats['godinas'] ?> regions)</li>
                                    <li class="ms-3"><i class="fas fa-arrow-down text-muted me-2"></i><strong>Godina</strong> → <strong>Gamta</strong> (<?= $stats['gamtas'] ?> country groups)</li>
                                    <li class="ms-4"><i class="fas fa-arrow-down text-muted me-2"></i><strong>Gamta</strong> → <strong>Gurmu</strong> (<?= $stats['gurmus'] ?> local groups)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Executive Positions:</h6>
                                <div class="row">
                                    <?php foreach($positions as $position): ?>
                                        <div class="col-12 mb-1">
                                            <small><i class="fas fa-user-tie text-success me-1"></i><?= htmlspecialchars($position['name']) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Godinas Tab -->
            <div class="tab-pane fade" id="godinas-content" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-globe me-2"></i>Godinas (Continental Regions)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($godinas as $godina): ?>
                                        <tr>
                                            <td><?= $godina['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($godina['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($godina['code']) ?></code></td>
                                            <td><?= htmlspecialchars($godina['description']) ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= ucfirst($godina['status']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gamtas Tab -->
            <div class="tab-pane fade" id="gamtas-content" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-map me-2"></i>Gamtas (Country Groups)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Godina</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($gamtas as $gamta): ?>
                                        <tr>
                                            <td><?= $gamta['id'] ?></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($gamta['godina_name']) ?></small></td>
                                            <td><strong><?= htmlspecialchars($gamta['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($gamta['code']) ?></code></td>
                                            <td><?= htmlspecialchars($gamta['description']) ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= ucfirst($gamta['status']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gurmus Tab -->
            <div class="tab-pane fade" id="gurmus-content" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-building me-2"></i>Gurmus (Local Groups)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hierarchy</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($gurmus as $gurmu): ?>
                                        <tr>
                                            <td><?= $gurmu['id'] ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($gurmu['godina_name']) ?> → 
                                                    <?= htmlspecialchars($gurmu['gamta_name']) ?>
                                                </small>
                                            </td>
                                            <td><strong><?= htmlspecialchars($gurmu['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($gurmu['code']) ?></code></td>
                                            <td><?= htmlspecialchars($gurmu['description']) ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= ucfirst($gurmu['status']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Positions Tab -->
            <div class="tab-pane fade" id="positions-content" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-tie me-2"></i>Executive Positions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Key Name</th>
                                        <th>Code</th>
                                        <th>Level</th>
                                        <th>Executive</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($positions as $position): ?>
                                        <tr>
                                            <td><?= $position['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($position['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($position['key_name']) ?></code></td>
                                            <td><code><?= htmlspecialchars($position['code']) ?></code></td>
                                            <td>
                                                <span class="badge bg-info"><?= htmlspecialchars($position['hierarchy_type']) ?></span>
                                            </td>
                                            <td>
                                                <?php if($position['is_executive']): ?>
                                                    <i class="fas fa-check-circle text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-minus-circle text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= ucfirst($position['status']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Links -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-link me-2"></i>System Navigation</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="login-test.php" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-sign-in-alt me-1"></i>Login Test
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/abo-wbo/" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-home me-1"></i>MVC Home
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/abo-wbo/auth/login" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-shield-alt me-1"></i>MVC Login
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/abo-wbo/dashboard" class="btn btn-outline-warning w-100 mb-2">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-success text-white text-center py-3 mt-5">
        <p class="mb-0">
            ABO-WBO Management System - Hierarchy Manager v1.0.0 | 
            Database: <?= $stats['godinas'] ?> Godinas, <?= $stats['gamtas'] ?> Gamtas, <?= $stats['gurmus'] ?> Gurmus
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>