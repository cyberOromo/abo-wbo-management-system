<?php
/**
 * Modern Advanced Hierarchy Details View
 * Displays detailed information for Godina, Gamta, or Gurmu
 * Industry-standard UI/UX with interactive features
 */

$pageTitle = $title ?? 'Hierarchy Details';
$hierarchyType = $type ?? 'godina';
$unit = $unit ?? [];
$stats = $stats ?? [];

// Determine color scheme based on type
$colorScheme = match($hierarchyType) {
    'godina' => ['primary' => '#0d6efd', 'secondary' => '#0a58ca', 'light' => '#cfe2ff', 'name' => 'Godina'],
    'gamta' => ['primary' => '#198754', 'secondary' => '#146c43', 'light' => '#d1e7dd', 'name' => 'Gamta'],
    'gurmu' => ['primary' => '#fd7e14', 'secondary' => '#ca6510', 'light' => '#ffe5d0', 'name' => 'Gurmu'],
    default => ['primary' => '#6c757d', 'secondary' => '#5c636a', 'light' => '#e2e3e5', 'name' => 'Unit']
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - ABO-WBO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?= $colorScheme['primary'] ?>;
            --secondary-color: <?= $colorScheme['secondary'] ?>;
            --light-color: <?= $colorScheme['light'] ?>;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .hero-icon {
            font-size: 4rem;
            opacity: 0.9;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            color: var(--primary-color);
        }
        
        .info-card {
            border-left: 4px solid var(--primary-color);
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }
        
        .table-hover tbody tr:hover {
            background-color: var(--light-color);
            cursor: pointer;
        }
        
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
        }
        
        .action-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .breadcrumb-custom {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .timeline-item {
            border-left: 3px solid var(--primary-color);
            padding-left: 1.5rem;
            padding-bottom: 1.5rem;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
        }
        
        .progress-custom {
            height: 8px;
            border-radius: 10px;
            background: #e9ecef;
        }
        
        .progress-custom .progress-bar {
            border-radius: 10px;
        }
        
        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            padding: 1rem 1.5rem;
            transition: all 0.2s;
        }
        
        .nav-tabs-custom .nav-link:hover {
            color: var(--primary-color);
            background: var(--light-color);
        }
        
        .nav-tabs-custom .nav-link.active {
            color: var(--primary-color);
            background: white;
            border-bottom: 3px solid var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--primary-color);">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="bi bi-house-fill me-2"></i>
                ABO-WBO
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/hierarchy">
                            <i class="bi bi-diagram-3 me-1"></i>
                            Hierarchy Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="bi bi-speedometer2 me-1"></i>
                            Dashboard
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb text-white mb-3">
                            <li class="breadcrumb-item"><a href="/dashboard" class="text-white">Home</a></li>
                            <li class="breadcrumb-item"><a href="/hierarchy" class="text-white">Hierarchy</a></li>
                            <li class="breadcrumb-item active text-white"><?= htmlspecialchars($unit['name'] ?? 'Details') ?></li>
                        </ol>
                    </nav>
                    <h1 class="display-4 fw-bold mb-2">
                        <?= htmlspecialchars($unit['name'] ?? 'Unknown') ?>
                    </h1>
                    <p class="lead mb-0">
                        <span class="badge badge-custom bg-white text-dark">
                            <i class="bi bi-<?= $hierarchyType === 'godina' ? 'globe' : ($hierarchyType === 'gamta' ? 'map' : 'building') ?> me-2"></i>
                            <?= $colorScheme['name'] ?>
                        </span>
                        <?php if (!empty($unit['code'])): ?>
                            <span class="badge badge-custom bg-white bg-opacity-25 ms-2">
                                Code: <?= htmlspecialchars($unit['code']) ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="hero-icon">
                        <i class="bi bi-<?= $hierarchyType === 'godina' ? 'globe-americas' : ($hierarchyType === 'gamta' ? 'pin-map-fill' : 'buildings-fill') ?>"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="btn-group me-2" role="group">
                    <a href="/hierarchy/<?= $unit['id'] ?>/edit?type=<?= $hierarchyType ?>" class="action-btn btn btn-primary">
                        <i class="bi bi-pencil-square me-2"></i>
                        Edit Details
                    </a>
                    <button type="button" class="action-btn btn btn-outline-primary" onclick="printDetails()">
                        <i class="bi bi-printer me-2"></i>
                        Print
                    </button>
                    <button type="button" class="action-btn btn btn-outline-primary" onclick="exportData()">
                        <i class="bi bi-download me-2"></i>
                        Export
                    </button>
                </div>
                
                <?php if ($hierarchyType === 'godina'): ?>
                    <a href="/hierarchy/create?type=gamta&godina_id=<?= $unit['id'] ?>" class="action-btn btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Gamta
                    </a>
                <?php elseif ($hierarchyType === 'gamta'): ?>
                    <a href="/hierarchy/create?type=gurmu&gamta_id=<?= $unit['id'] ?>" class="action-btn btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Gurmu
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <?php if ($hierarchyType === 'godina'): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-map"></i>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="text-muted mb-1 small">Gamtas</h6>
                                    <div class="stat-number"><?= number_format($stats['total_gamtas'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="text-muted mb-1 small">Gurmus</h6>
                                    <div class="stat-number"><?= number_format($stats['total_gurmus'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($hierarchyType === 'gamta'): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="text-muted mb-1 small">Gurmus</h6>
                                    <div class="stat-number"><?= number_format($stats['total_gurmus'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="text-muted mb-1 small">Members</h6>
                                <div class="stat-number"><?= number_format($stats['total_members'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="text-muted mb-1 small">Executives</h6>
                                <div class="stat-number"><?= number_format($stats['executive_count'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="text-muted mb-1 small">Active Tasks</h6>
                                <div class="stat-number"><?= number_format($stats['active_tasks'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="detailsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                    <i class="bi bi-info-circle me-2"></i>Overview
                </button>
            </li>
            
            <?php if ($hierarchyType === 'godina' && !empty($gamtas)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="gamtas-tab" data-bs-toggle="tab" data-bs-target="#gamtas" type="button">
                        <i class="bi bi-map me-2"></i>Gamtas (<?= count($gamtas) ?>)
                    </button>
                </li>
            <?php endif; ?>
            
            <?php if ($hierarchyType === 'gamta' && !empty($gurmus)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="gurmus-tab" data-bs-toggle="tab" data-bs-target="#gurmus" type="button">
                        <i class="bi bi-building me-2"></i>Gurmus (<?= count($gurmus) ?>)
                    </button>
                </li>
            <?php endif; ?>
            
            <?php if (!empty($users)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button">
                        <i class="bi bi-people me-2"></i>Members (<?= count($users) ?>)
                    </button>
                </li>
            <?php endif; ?>
            
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                    <i class="bi bi-clock-history me-2"></i>Activity
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="detailsTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card info-card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-info-circle-fill me-2" style="color: var(--primary-color);"></i>
                                    Basic Information
                                </h5>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="200">Name:</th>
                                            <td><strong><?= htmlspecialchars($unit['name'] ?? 'N/A') ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Code:</th>
                                            <td><code><?= htmlspecialchars($unit['code'] ?? 'N/A') ?></code></td>
                                        </tr>
                                        <?php if (!empty($unit['description'])): ?>
                                            <tr>
                                                <th>Description:</th>
                                                <td><?= htmlspecialchars($unit['description']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($hierarchyType === 'gamta' && !empty($unit['godina_name'])): ?>
                                            <tr>
                                                <th>Parent Godina:</th>
                                                <td>
                                                    <a href="/hierarchy/<?= $unit['godina_id'] ?>?type=godina" class="text-decoration-none">
                                                        <i class="bi bi-globe me-1"></i>
                                                        <?= htmlspecialchars($unit['godina_name']) ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($hierarchyType === 'gurmu' && !empty($unit['gamta_name'])): ?>
                                            <tr>
                                                <th>Parent Gamta:</th>
                                                <td>
                                                    <a href="/hierarchy/<?= $unit['gamta_id'] ?>?type=gamta" class="text-decoration-none">
                                                        <i class="bi bi-map me-1"></i>
                                                        <?= htmlspecialchars($unit['gamta_name']) ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <span class="badge bg-<?= ($unit['status'] ?? 'inactive') === 'active' ? 'success' : 'secondary' ?> badge-custom">
                                                    <?= ucfirst($unit['status'] ?? 'Unknown') ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created:</th>
                                            <td><?= date('F d, Y', strtotime($unit['created_at'] ?? 'now')) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <?php if (!empty($unit['address']) || !empty($unit['contact_email']) || !empty($unit['contact_phone'])): ?>
                            <div class="card info-card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">
                                        <i class="bi bi-geo-alt-fill me-2" style="color: var(--primary-color);"></i>
                                        Contact Information
                                    </h5>
                                    <table class="table table-borderless">
                                        <tbody>
                                            <?php if (!empty($unit['address'])): ?>
                                                <tr>
                                                    <th width="200"><i class="bi bi-house-door me-2"></i>Address:</th>
                                                    <td><?= htmlspecialchars($unit['address']) ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($unit['contact_email'])): ?>
                                                <tr>
                                                    <th><i class="bi bi-envelope me-2"></i>Email:</th>
                                                    <td><a href="mailto:<?= htmlspecialchars($unit['contact_email']) ?>"><?= htmlspecialchars($unit['contact_email']) ?></a></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($unit['contact_phone'])): ?>
                                                <tr>
                                                    <th><i class="bi bi-telephone me-2"></i>Phone:</th>
                                                    <td><a href="tel:<?= htmlspecialchars($unit['contact_phone']) ?>"><?= htmlspecialchars($unit['contact_phone']) ?></a></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card info-card mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">Quick Stats</h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Member Activity</small>
                                        <small class="text-muted">78%</small>
                                    </div>
                                    <div class="progress-custom progress">
                                        <div class="progress-bar" style="width: 78%; background-color: var(--primary-color);"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Task Completion</small>
                                        <small class="text-muted">65%</small>
                                    </div>
                                    <div class="progress-custom progress">
                                        <div class="progress-bar bg-success" style="width: 65%;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Positions Filled</small>
                                        <small class="text-muted">85%</small>
                                    </div>
                                    <div class="progress-custom progress">
                                        <div class="progress-bar bg-info" style="width: 85%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card info-card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">Hierarchy Path</h6>
                                <div class="timeline-item">
                                    <small class="text-muted">Global</small>
                                    <div><strong>ABO-WBO</strong></div>
                                </div>
                                <?php if ($hierarchyType !== 'godina'): ?>
                                    <div class="timeline-item">
                                        <small class="text-muted">Godina</small>
                                        <div><strong><?= htmlspecialchars($unit['godina_name'] ?? 'N/A') ?></strong></div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($hierarchyType === 'gurmu'): ?>
                                    <div class="timeline-item">
                                        <small class="text-muted">Gamta</small>
                                        <div><strong><?= htmlspecialchars($unit['gamta_name'] ?? 'N/A') ?></strong></div>
                                    </div>
                                <?php endif; ?>
                                <div class="timeline-item" style="border-left-color: var(--primary-color); padding-bottom: 0;">
                                    <small class="text-muted"><?= $colorScheme['name'] ?></small>
                                    <div><strong style="color: var(--primary-color);"><?= htmlspecialchars($unit['name'] ?? 'Current') ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gamtas Tab (for Godina) -->
            <?php if ($hierarchyType === 'godina' && !empty($gamtas)): ?>
                <div class="tab-pane fade" id="gamtas" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-map me-2"></i>
                                Gamtas in <?= htmlspecialchars($unit['name']) ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Gurmus</th>
                                            <th>Members</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($gamtas as $gamta): ?>
                                            <tr onclick="window.location='/hierarchy/<?= $gamta['id'] ?>?type=gamta'">
                                                <td>
                                                    <i class="bi bi-map-fill text-success me-2"></i>
                                                    <strong><?= htmlspecialchars($gamta['name']) ?></strong>
                                                </td>
                                                <td><code><?= htmlspecialchars($gamta['code']) ?></code></td>
                                                <td><?= number_format($gamta['gurmu_count'] ?? 0) ?></td>
                                                <td><?= number_format($gamta['member_count'] ?? 0) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $gamta['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($gamta['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="/hierarchy/<?= $gamta['id'] ?>?type=gamta" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Gurmus Tab (for Gamta) -->
            <?php if ($hierarchyType === 'gamta' && !empty($gurmus)): ?>
                <div class="tab-pane fade" id="gurmus" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-building me-2"></i>
                                Gurmus in <?= htmlspecialchars($unit['name']) ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Members</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($gurmus as $gurmu): ?>
                                            <tr onclick="window.location='/hierarchy/<?= $gurmu['id'] ?>?type=gurmu'">
                                                <td>
                                                    <i class="bi bi-building-fill text-warning me-2"></i>
                                                    <strong><?= htmlspecialchars($gurmu['name']) ?></strong>
                                                </td>
                                                <td><code><?= htmlspecialchars($gurmu['code']) ?></code></td>
                                                <td><?= number_format($gurmu['member_count'] ?? 0) ?></td>
                                                <td><?= htmlspecialchars($gurmu['address'] ?? 'N/A') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $gurmu['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($gurmu['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="/hierarchy/<?= $gurmu['id'] ?>?type=gurmu" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Members Tab -->
            <?php if (!empty($users)): ?>
                <div class="tab-pane fade" id="members" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                Members
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Position</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <i class="bi bi-person-circle me-2"></i>
                                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= htmlspecialchars($user['position_name'] ?? 'N/A') ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= ucfirst($user['role'] ?? 'member') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($user['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Activity Tab -->
            <div class="tab-pane fade" id="activity" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Recent Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline-item">
                            <small class="text-muted">2 hours ago</small>
                            <div><strong>New member added</strong></div>
                            <p class="text-muted small mb-0">John Doe joined the organization</p>
                        </div>
                        <div class="timeline-item">
                            <small class="text-muted">1 day ago</small>
                            <div><strong>Meeting scheduled</strong></div>
                            <p class="text-muted small mb-0">Monthly executive meeting</p>
                        </div>
                        <div class="timeline-item" style="padding-bottom: 0;">
                            <small class="text-muted">3 days ago</small>
                            <div><strong>Report submitted</strong></div>
                            <p class="text-muted small mb-0">Quarterly financial report</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printDetails() {
            window.print();
        }
        
        function exportData() {
            alert('Export functionality coming soon!');
        }
    </script>
</body>
</html>
