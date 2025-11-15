<?php
/**
 * Admin Hierarchy Management View
 * ABO-WBO Management System
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Hierarchy Management' ?> - ABO-WBO Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hierarchy-tree {
            font-family: monospace;
            line-height: 1.6;
        }
        .tree-item {
            margin: 5px 0;
            padding: 8px 15px;
            border-left: 3px solid #dee2e6;
            background: #f8f9fa;
        }
        .tree-godina {
            border-color: #28a745;
            background: #d4edda;
            font-weight: bold;
        }
        .tree-gamta {
            border-color: #007bff;
            background: #d1ecf1;
            margin-left: 20px;
        }
        .tree-gurmu {
            border-color: #ffc107;
            background: #fff3cd;
            margin-left: 40px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-users me-2"></i>
                ABO-WBO Management System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-dashboard me-1"></i>
                    Dashboard
                </a>
                <a class="nav-link" href="/auth/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
                <form id="logout-form" action="/auth/logout" method="POST" style="display: none;">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-6 fw-bold">
                            <i class="fas fa-sitemap text-success me-3"></i>
                            System Admin - Hierarchy Management
                        </h1>
                        <p class="lead text-muted mb-0">
                            Complete organizational hierarchy overview and management
                        </p>
                    </div>
                    <div>
                        <a href="/hierarchy" class="btn btn-outline-primary me-2">
                            <i class="fas fa-chart-line me-1"></i>
                            Hierarchy Dashboard
                        </a>
                        <a href="/hierarchy/create" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            Add New Unit
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <i class="fas fa-globe fa-2x mb-2"></i>
                        <h3 class="fw-bold"><?= count($godinas ?? []) ?></h3>
                        <p class="mb-0">Godinas (Regions)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <i class="fas fa-flag fa-2x mb-2"></i>
                        <h3 class="fw-bold"><?= count($gamtas ?? []) ?></h3>
                        <p class="mb-0">Gamtas (Countries)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white text-center">
                    <div class="card-body">
                        <i class="fas fa-home fa-2x mb-2"></i>
                        <h3 class="fw-bold"><?= count($gurmus ?? []) ?></h3>
                        <p class="mb-0">Gurmus (Local Groups)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h3 class="fw-bold">
                            <?= array_sum(array_column($godinas ?? [], 'gamta_count')) ?>
                        </h3>
                        <p class="mb-0">Total Units</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hierarchy Tree -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="fas fa-tree me-2"></i>
                            Complete Organizational Hierarchy Tree
                        </h4>
                        <small class="text-muted">4-Tier Structure: Global → Godina → Gamta → Gurmu</small>
                    </div>
                    <div class="card-body">
                        <div class="hierarchy-tree">
                            <!-- Global Level -->
                            <div class="tree-item tree-godina">
                                <i class="fas fa-globe me-2"></i>
                                <strong>Global (Waliigalaa Global) - Executive Board</strong>
                            </div>

                            <!-- Godina Level -->
                            <?php if (!empty($godinas)): ?>
                                <?php foreach ($godinas as $godina): ?>
                                    <div class="tree-item tree-godina">
                                        <i class="fas fa-map me-2"></i>
                                        <strong><?= htmlspecialchars($godina['name']) ?></strong>
                                        <?php if (!empty($godina['description'])): ?>
                                            <small class="text-muted">- <?= htmlspecialchars($godina['description']) ?></small>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Gamta Level -->
                                    <?php 
                                    $godinaGamtas = array_filter($gamtas ?? [], fn($g) => $g['godina_id'] == $godina['id']);
                                    foreach ($godinaGamtas as $gamta): 
                                    ?>
                                        <div class="tree-item tree-gamta">
                                            <i class="fas fa-flag me-2"></i>
                                            <strong><?= htmlspecialchars($gamta['name']) ?></strong>
                                            
                                            <!-- Gurmu Level -->
                                            <?php 
                                            $gamtaGurmus = array_filter($gurmus ?? [], fn($g) => $g['gamta_id'] == $gamta['id']);
                                            foreach ($gamtaGurmus as $gurmu): 
                                            ?>
                                                <div class="tree-item tree-gurmu">
                                                    <i class="fas fa-home me-2"></i>
                                                    <?= htmlspecialchars($gurmu['name']) ?>
                                                    <?php if (!empty($gurmu['location'])): ?>
                                                        <small class="text-muted">- <?= htmlspecialchars($gurmu['location']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hierarchy data found. Please set up the organizational structure.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Management Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6><i class="fas fa-plus-circle text-success me-2"></i>Add New Units</h6>
                                <div class="d-grid gap-2">
                                    <a href="/hierarchy/godinas/create" class="btn btn-outline-success btn-sm">Add Godina</a>
                                    <a href="/hierarchy/gamtas/create" class="btn btn-outline-primary btn-sm">Add Gamta</a>
                                    <a href="/hierarchy/gurmus/create" class="btn btn-outline-warning btn-sm">Add Gurmu</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-users-cog text-primary me-2"></i>Position Management</h6>
                                <div class="d-grid gap-2">
                                    <a href="/positions" class="btn btn-outline-primary btn-sm">Manage Positions</a>
                                    <a href="/positions/assignments" class="btn btn-outline-info btn-sm">View Assignments</a>
                                    <a href="/responsibilities" class="btn btn-outline-secondary btn-sm">Responsibilities</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-chart-bar text-info me-2"></i>Reports & Analytics</h6>
                                <div class="d-grid gap-2">
                                    <a href="/reports/hierarchy" class="btn btn-outline-info btn-sm">Hierarchy Report</a>
                                    <a href="/hierarchy/export" class="btn btn-outline-success btn-sm">Export Data</a>
                                    <a href="/hierarchy/statistics" class="btn btn-outline-warning btn-sm">Statistics</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-3 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                &copy; <?= date('Y') ?> ABO-WBO Management System - Hierarchy Management Module
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>