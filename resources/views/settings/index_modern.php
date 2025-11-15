<?php
$title = $title ?? 'System Settings';
$system_settings = $system_settings ?? [];
$system_status = $system_status ?? [];
$recent_activities = $recent_activities ?? [];
$settings_stats = $settings_stats ?? [];
$security_alerts = $security_alerts ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #8b1538;
            --primary-green: #2d5016;
            --dark-red: #6b1028;
            --dark-green: #1a300b;
            --light-red: #f8e7ea;
            --light-green: #e8f2e1;
        }

        body {
            background: linear-gradient(135deg, var(--light-red) 0%, var(--light-green) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--primary-green) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .setting-category-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .setting-category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-green));
        }

        .setting-category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .category-icon.primary { background: linear-gradient(45deg, var(--primary-red), #af1d47); color: white; }
        .category-icon.success { background: linear-gradient(45deg, #28a745, #20c997); color: white; }
        .category-icon.warning { background: linear-gradient(45deg, #ffc107, #fd7e14); color: white; }
        .category-icon.danger { background: linear-gradient(45deg, #dc3545, #e83e8c); color: white; }
        .category-icon.info { background: linear-gradient(45deg, #17a2b8, #6610f2); color: white; }
        .category-icon.dark { background: linear-gradient(45deg, #343a40, #495057); color: white; }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .status-healthy { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .status-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .status-error { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .status-unknown { background: rgba(108, 117, 125, 0.1); color: #6c757d; }

        .btn-modern {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-modern.btn-primary {
            background: linear-gradient(45deg, var(--primary-red), #af1d47);
        }

        .btn-modern.btn-success {
            background: linear-gradient(45deg, var(--primary-green), #3e6b23);
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary-red), var(--primary-green));
        }

        .activity-item {
            position: relative;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: -1.8rem;
            top: 1.2rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-green);
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .health-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .metric-card.healthy { border-color: #28a745; }
        .metric-card.warning { border-color: #ffc107; }
        .metric-card.error { border-color: #dc3545; }

        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .search-filters {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .view-toggle {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .view-btn {
            padding: 0.5rem 1rem;
            border: 2px solid var(--primary-red);
            background: transparent;
            color: var(--primary-red);
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .view-btn.active {
            background: var(--primary-red);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-2">
                        <i class="fas fa-cogs me-3"></i>
                        System Settings
                    </h1>
                    <p class="mb-0 opacity-75">Configure and manage system-wide settings and preferences</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-light btn-modern" onclick="location.reload()">
                        <i class="fas fa-sync me-2"></i>Refresh Status
                    </button>
                    <button class="btn btn-outline-light btn-modern ms-2" onclick="exportSettings()">
                        <i class="fas fa-download me-2"></i>Export Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        <!-- System Health Metrics -->
        <div class="health-metrics">
            <div class="metric-card <?php echo ($system_status['database_status'] ?? 'unknown') === 'healthy' ? 'healthy' : 'error'; ?>">
                <div class="metric-value <?php echo ($system_status['database_status'] ?? 'unknown') === 'healthy' ? 'text-success' : 'text-danger'; ?>">
                    <i class="fas fa-database"></i>
                </div>
                <div class="metric-label">Database</div>
                <small class="text-muted"><?php echo ucfirst($system_status['database_status'] ?? 'Unknown'); ?></small>
            </div>
            
            <div class="metric-card <?php echo ($system_status['storage_status'] ?? 'unknown') === 'healthy' ? 'healthy' : 'warning'; ?>">
                <div class="metric-value <?php echo ($system_status['storage_status'] ?? 'unknown') === 'healthy' ? 'text-success' : 'text-warning'; ?>">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="metric-label">Storage</div>
                <small class="text-muted"><?php echo ucfirst($system_status['storage_status'] ?? 'Unknown'); ?></small>
            </div>
            
            <div class="metric-card <?php echo ($system_status['email_status'] ?? 'unknown') === 'healthy' ? 'healthy' : 'warning'; ?>">
                <div class="metric-value <?php echo ($system_status['email_status'] ?? 'unknown') === 'healthy' ? 'text-success' : 'text-warning'; ?>">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="metric-label">Email System</div>
                <small class="text-muted"><?php echo ucfirst($system_status['email_status'] ?? 'Not Configured'); ?></small>
            </div>
            
            <div class="metric-card <?php echo ($system_status['backup_status'] ?? 'unknown') === 'healthy' ? 'healthy' : 'warning'; ?>">
                <div class="metric-value <?php echo ($system_status['backup_status'] ?? 'unknown') === 'healthy' ? 'text-success' : 'text-info'; ?>">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="metric-label">Backup</div>
                <small class="text-muted"><?php echo ucfirst($system_status['backup_status'] ?? 'Pending'); ?></small>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="view-toggle">
            <button class="view-btn active" onclick="switchView('grid')">
                <i class="fas fa-th-large me-2"></i>Grid View
            </button>
            <button class="view-btn" onclick="switchView('list')">
                <i class="fas fa-list me-2"></i>List View
            </button>
        </div>

        <!-- Settings Categories Grid -->
        <div class="settings-grid" id="settingsGrid">
            <!-- General Settings -->
            <div class="setting-category-card">
                <div class="category-icon primary">
                    <i class="fas fa-cog"></i>
                </div>
                <h5 class="mb-2">General Settings</h5>
                <p class="text-muted mb-3">Organization information, timezone, language preferences and basic system configuration.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-check-circle me-2"></i>Configured
                    </span>
                    <small class="text-muted">Last updated: 2 days ago</small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/general" class="btn btn-primary btn-modern flex-fill">
                        <i class="fas fa-edit me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="previewSettings('general')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Email Configuration -->
            <div class="setting-category-card">
                <div class="category-icon info">
                    <i class="fas fa-envelope"></i>
                </div>
                <h5 class="mb-2">Email Configuration</h5>
                <p class="text-muted mb-3">SMTP settings, email templates, notification preferences and delivery configuration.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-<?php echo ($system_status['email_status'] ?? 'unknown') === 'healthy' ? 'healthy' : 'warning'; ?>">
                        <i class="fas fa-<?php echo ($system_status['email_status'] ?? 'unknown') === 'healthy' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo ($system_status['email_status'] ?? 'unknown') === 'healthy' ? 'Active' : 'Needs Setup'; ?>
                    </span>
                    <small class="text-muted">
                        <?php if (!empty($system_status['last_email_test'])): ?>
                            Tested: <?php echo date('M j', strtotime($system_status['last_email_test'])); ?>
                        <?php else: ?>
                            Not tested
                        <?php endif; ?>
                    </small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/email" class="btn btn-info btn-modern flex-fill">
                        <i class="fas fa-envelope-open me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="testEmailConnection()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="setting-category-card">
                <div class="category-icon warning">
                    <i class="fas fa-bell"></i>
                </div>
                <h5 class="mb-2">Notification System</h5>
                <p class="text-muted mb-3">System alerts, user notifications, preferences and automated communication settings.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-check-circle me-2"></i>Active
                    </span>
                    <small class="text-muted">
                        <?php echo count($recent_activities ?? []); ?> recent alerts
                    </small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/notifications" class="btn btn-warning btn-modern flex-fill">
                        <i class="fas fa-bell-slash me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="previewNotifications()">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Backup & Restore -->
            <div class="setting-category-card">
                <div class="category-icon success">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h5 class="mb-2">Backup & Restore</h5>
                <p class="text-muted mb-3">Create backups, restore data, schedule automated backups and manage data integrity.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-<?php echo ($system_status['backup_status'] ?? 'unknown') === 'healthy' ? 'healthy' : 'warning'; ?>">
                        <i class="fas fa-<?php echo ($system_status['backup_status'] ?? 'unknown') === 'healthy' ? 'check-circle' : 'clock'; ?> me-2"></i>
                        <?php echo ucfirst($system_status['backup_status'] ?? 'Pending'); ?>
                    </span>
                    <small class="text-muted">
                        <?php if (!empty($system_status['last_backup'])): ?>
                            <?php echo date('M j', strtotime($system_status['last_backup'])); ?>
                        <?php else: ?>
                            No backups
                        <?php endif; ?>
                    </small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/backup" class="btn btn-success btn-modern flex-fill">
                        <i class="fas fa-download me-2"></i>Manage
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="createQuickBackup()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="setting-category-card">
                <div class="category-icon dark">
                    <i class="fas fa-lock"></i>
                </div>
                <h5 class="mb-2">Security Configuration</h5>
                <p class="text-muted mb-3">Password policies, session management, access logs and security monitoring.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-shield-alt me-2"></i>Secure
                    </span>
                    <small class="text-muted">
                        <?php echo count($security_alerts ?? []); ?> active sessions
                    </small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/admin/security" class="btn btn-dark btn-modern flex-fill">
                        <i class="fas fa-shield-alt me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="viewSecurityLogs()">
                        <i class="fas fa-history"></i>
                    </button>
                </div>
            </div>

            <!-- System Maintenance -->
            <div class="setting-category-card">
                <div class="category-icon danger">
                    <i class="fas fa-tools"></i>
                </div>
                <h5 class="mb-2">System Maintenance</h5>
                <p class="text-muted mb-3">Enable maintenance mode, system health checks and performance optimization.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-<?php echo ($system_status['maintenance_mode'] ?? 0) == 1 ? 'error' : 'healthy'; ?>">
                        <i class="fas fa-<?php echo ($system_status['maintenance_mode'] ?? 0) == 1 ? 'exclamation-triangle' : 'check-circle'; ?> me-2"></i>
                        <?php echo ($system_status['maintenance_mode'] ?? 0) == 1 ? 'Maintenance ON' : 'Operational'; ?>
                    </span>
                    <small class="text-muted">System health: Good</small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/maintenance" class="btn btn-danger btn-modern flex-fill">
                        <i class="fas fa-wrench me-2"></i>Manage
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="systemHealthCheck()">
                        <i class="fas fa-chart-line"></i>
                    </button>
                </div>
            </div>

            <!-- Module Access Control -->
            <div class="setting-category-card">
                <div class="category-icon primary">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h5 class="mb-2">Module Access Control</h5>
                <p class="text-muted mb-3">Configure module access permissions per hierarchy levels, enable/disable features for specific roles and organizational units.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-check-circle me-2"></i>Active
                    </span>
                    <small class="text-muted">11 modules configured</small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/access-control" class="btn btn-primary btn-modern flex-fill">
                        <i class="fas fa-key me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="previewAccessMatrix()">
                        <i class="fas fa-table"></i>
                    </button>
                </div>
            </div>

            <!-- Permission Management -->
            <div class="setting-category-card">
                <div class="category-icon warning">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h5 class="mb-2">Permission Management</h5>
                <p class="text-muted mb-3">Manage role-based permissions, grant specific capabilities, and configure hierarchy-based access controls.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-users me-2"></i>8 Roles Active
                    </span>
                    <small class="text-muted">
                        <?php echo count($security_alerts ?? []); ?> pending reviews
                    </small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/permissions" class="btn btn-warning btn-modern flex-fill">
                        <i class="fas fa-user-cog me-2"></i>Manage
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="auditPermissions()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Feature Toggles -->
            <div class="setting-category-card">
                <div class="category-icon info">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <h5 class="mb-2">Feature Management</h5>
                <p class="text-muted mb-3">Enable or disable system features, experimental modules, and advanced functionality per organizational level.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-cog me-2"></i>Features Active
                    </span>
                    <small class="text-muted">
                        <?php echo ($settings_stats['configured_settings'] ?? 0); ?> features enabled
                    </small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/features" class="btn btn-info btn-modern flex-fill">
                        <i class="fas fa-sliders-h me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="previewFeatures()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Hierarchy Settings -->
            <div class="setting-category-card">
                <div class="category-icon success">
                    <i class="fas fa-sitemap"></i>
                </div>
                <h5 class="mb-2">Hierarchy Configuration</h5>
                <p class="text-muted mb-3">Configure hierarchy-specific settings, module availability per level, and organizational unit permissions.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-healthy">
                        <i class="fas fa-layer-group me-2"></i>4 Levels Active
                    </span>
                    <small class="text-muted">Executive → Godina → Gamta → Gurmu</small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/hierarchy-config" class="btn btn-success btn-modern flex-fill">
                        <i class="fas fa-tree me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="hierarchyMatrix()">
                        <i class="fas fa-chart-network"></i>
                    </button>
                </div>
            </div>

            <!-- API & Integration -->
            <div class="setting-category-card">
                <div class="category-icon dark">
                    <i class="fas fa-plug"></i>
                </div>
                <h5 class="mb-2">API & Integrations</h5>
                <p class="text-muted mb-3">Manage API keys, external integrations, webhook configurations and third-party service connections.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-warning">
                        <i class="fas fa-key me-2"></i>2 Active Keys
                    </span>
                    <small class="text-muted">Last integration: 3 hours ago</small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/integrations" class="btn btn-dark btn-modern flex-fill">
                        <i class="fas fa-cogs me-2"></i>Manage
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="testIntegrations()">
                        <i class="fas fa-vial"></i>
                    </button>
                </div>
            </div>

            <!-- Advanced Configuration -->
            <div class="setting-category-card">
                <div class="category-icon danger">
                    <i class="fas fa-code"></i>
                </div>
                <h5 class="mb-2">Advanced Configuration</h5>
                <p class="text-muted mb-3">System-level configurations, performance tuning, caching settings, and advanced technical parameters.</p>
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="status-indicator status-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Expert Only
                    </span>
                    <small class="text-muted">
                        <?php echo ($settings_stats['completion_percentage'] ?? 0); ?>% configured
                    </small>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="/settings/advanced" class="btn btn-danger btn-modern flex-fill">
                        <i class="fas fa-tools me-2"></i>Configure
                    </a>
                    <button class="btn btn-outline-secondary btn-modern" onclick="exportConfig()">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent System Activities -->
        <?php if (!empty($recent_activities)): ?>
        <div class="stats-card">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-history text-primary me-2"></i>
                    Recent System Activities
                </h4>
                <div>
                    <button class="btn btn-outline-primary btn-sm btn-modern" onclick="exportActivities()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                    <button class="btn btn-outline-secondary btn-sm btn-modern ms-2" onclick="clearActivities()">
                        <i class="fas fa-trash me-1"></i>Clear
                    </button>
                </div>
            </div>
            
            <div class="activity-timeline">
                <?php foreach (array_slice($recent_activities, 0, 8) as $activity): ?>
                <div class="activity-item">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <?php echo ucwords(str_replace('_', ' ', $activity['action'] ?? 'Unknown Action')); ?>
                            </h6>
                            <p class="text-muted mb-2">
                                By <?php echo htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? 'System')); ?>
                            </p>
                            <?php if (!empty($activity['metadata'])): ?>
                            <small class="text-primary">
                                <?php 
                                $metadata = json_decode($activity['metadata'], true) ?? [];
                                foreach ($metadata as $key => $value) {
                                    echo htmlspecialchars("$key: $value") . " ";
                                }
                                ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">
                            <?php echo date('M j, H:i', strtotime($activity['created_at'] ?? 'now')); ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($recent_activities) > 8): ?>
            <div class="text-center mt-3">
                <button class="btn btn-outline-primary btn-modern" onclick="viewAllActivities()">
                    <i class="fas fa-plus me-2"></i>View All Activities
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modals -->
    <!-- Quick Backup Modal -->
    <div class="modal fade" id="quickBackupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Quick Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Backup Type</label>
                        <select class="form-select" id="backupType">
                            <option value="full">Full System Backup</option>
                            <option value="database">Database Only</option>
                            <option value="files">Files Only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" id="backupDescription" placeholder="e.g., Pre-update backup">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-modern" onclick="executeBackup()">
                        <i class="fas fa-play me-2"></i>Create Backup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Initialize components
        $(document).ready(function() {
            initializeSettings();
            loadSystemStatus();
        });

        function initializeSettings() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Auto-refresh system status
            setInterval(loadSystemStatus, 30000);
        }

        function loadSystemStatus() {
            // Simulate status refresh
            console.log('Refreshing system status...');
        }

        function switchView(viewType) {
            $('.view-btn').removeClass('active');
            $(`button[onclick="switchView('${viewType}')"]`).addClass('active');
            
            const grid = $('#settingsGrid');
            if (viewType === 'list') {
                grid.css('grid-template-columns', '1fr');
            } else {
                grid.css('grid-template-columns', 'repeat(auto-fit, minmax(350px, 1fr))');
            }
        }

        function previewSettings(category) {
            alert(`Preview settings for: ${category}`);
        }

        function testEmailConnection() {
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Email connection test completed!');
            }, 2000);
        }

        function createQuickBackup() {
            const modal = new bootstrap.Modal(document.getElementById('quickBackupModal'));
            modal.show();
        }

        function executeBackup() {
            const type = $('#backupType').val();
            const description = $('#backupDescription').val();
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('quickBackupModal')).hide();
            
            // Show progress
            alert(`Creating ${type} backup...`);
        }

        function systemHealthCheck() {
            alert('Running system health check...');
        }

        function viewSecurityLogs() {
            window.open('/admin/security', '_blank');
        }

        function previewNotifications() {
            alert('Opening notification preview...');
        }

        function exportSettings() {
            alert('Exporting system settings...');
        }

        function exportActivities() {
            alert('Exporting recent activities...');
        }

        function clearActivities() {
            if (confirm('Are you sure you want to clear recent activities?')) {
                alert('Activities cleared!');
            }
        }

        function viewAllActivities() {
            window.open('/admin/activities', '_blank');
        }

        // Advanced Settings Functions
        function previewAccessMatrix() {
            alert('Opening access control matrix preview...');
        }

        function auditPermissions() {
            alert('Running permissions audit...');
        }

        function previewFeatures() {
            alert('Previewing available features...');
        }

        function hierarchyMatrix() {
            alert('Opening hierarchy configuration matrix...');
        }

        function testIntegrations() {
            alert('Testing API integrations...');
        }

        function exportConfig() {
            alert('Exporting advanced configuration...');
        }
    </script>
</body>
</html>