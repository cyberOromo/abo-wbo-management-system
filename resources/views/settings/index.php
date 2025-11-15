<?php
$title = $title ?? 'System Settings';
$system_settings = $system_settings ?? [];
$system_status = $system_status ?? [];
$recent_activities = $recent_activities ?? [];
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-cogs text-secondary"></i>
                <?php echo htmlspecialchars($title); ?>
            </h1>
            <p class="text-muted mb-0">Configure system-wide settings and preferences</p>
        </div>
        
        <div>
            <button type="button" class="btn btn-outline-info" onclick="location.reload()">
                <i class="fas fa-sync"></i> Refresh Status
            </button>
        </div>
    </div>

    <!-- System Status Cards -->
    <?php if (!empty($system_status)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?php echo ($system_status['database_status'] ?? 'unknown') === 'healthy' ? 'success' : 'danger'; ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Database Status
                            </div>
                            <div class="h6 mb-0 font-weight-bold">
                                <?php echo ucfirst($system_status['database_status'] ?? 'Unknown'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?php echo ($system_status['storage_status'] ?? 'unknown') === 'healthy' ? 'success' : 'warning'; ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Storage Status
                            </div>
                            <div class="h6 mb-0 font-weight-bold">
                                <?php echo ucfirst($system_status['storage_status'] ?? 'Unknown'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?php echo ($system_status['email_status'] ?? 'unknown') === 'healthy' ? 'success' : 'warning'; ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Email System
                            </div>
                            <div class="h6 mb-0 font-weight-bold">
                                <?php echo ucfirst($system_status['email_status'] ?? 'Not Configured'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?php echo ($system_status['backup_status'] ?? 'unknown') === 'healthy' ? 'success' : 'info'; ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Backup Status
                            </div>
                            <div class="h6 mb-0 font-weight-bold">
                                <?php echo ucfirst($system_status['backup_status'] ?? 'Pending'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Settings Categories -->
    <div class="row">
        <!-- General Settings -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-cog fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">General Settings</h5>
                            <p class="card-text text-muted small mb-0">
                                Organization info, timezone, language preferences
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/settings/general" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-envelope fa-2x text-info"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Email Configuration</h5>
                            <p class="card-text text-muted small mb-0">
                                SMTP settings, email templates, notifications
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/settings/email" class="btn btn-info btn-sm">
                            <i class="fas fa-envelope-open"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-bell fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Notifications</h5>
                            <p class="card-text text-muted small mb-0">
                                System alerts, user notifications, preferences
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/settings/notifications" class="btn btn-warning btn-sm">
                            <i class="fas fa-bell-slash"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup & Restore -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-shield-alt fa-2x text-success"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Backup & Restore</h5>
                            <p class="card-text text-muted small mb-0">
                                Create backups, restore data, schedule automated backups
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/settings/backup" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Manage
                        </a>
                        <?php if (!empty($system_status['last_backup'])): ?>
                        <small class="text-muted d-block mt-1">
                            Last backup: <?php echo date('M j, Y', strtotime($system_status['last_backup'])); ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Mode -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-tools fa-2x text-danger"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">System Maintenance</h5>
                            <p class="card-text text-muted small mb-0">
                                Enable maintenance mode, system health checks
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/settings/maintenance" class="btn btn-danger btn-sm">
                            <i class="fas fa-wrench"></i> Manage
                        </a>
                        <?php if (($system_status['maintenance_mode'] ?? 0) == 1): ?>
                        <small class="text-danger d-block mt-1">
                            <i class="fas fa-exclamation-triangle"></i> Maintenance mode is ON
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-lock fa-2x text-dark"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Security Settings</h5>
                            <p class="card-text text-muted small mb-0">
                                Password policies, session management, access logs
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="/admin/security" class="btn btn-dark btn-sm">
                            <i class="fas fa-shield-alt"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <?php if (!empty($recent_activities)): ?>
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent System Activities</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>User</th>
                            <th>Date & Time</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($recent_activities, 0, 10) as $activity): ?>
                        <tr>
                            <td>
                                <?php echo ucwords(str_replace('_', ' ', $activity['action'] ?? 'Unknown')); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')); ?>
                            </td>
                            <td>
                                <?php echo date('M j, Y H:i:s', strtotime($activity['created_at'] ?? 'now')); ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php 
                                    $metadata = !empty($activity['metadata']) ? json_decode($activity['metadata'], true) : [];
                                    echo htmlspecialchars(implode(', ', array_map(function($k, $v) { 
                                        return "$k: $v"; 
                                    }, array_keys($metadata), array_values($metadata))));
                                    ?>
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Auto-refresh system status every 30 seconds
    setInterval(function() {
        // Add AJAX call to refresh system status
    }, 30000);
});
</script>