<style>
    .hierarchy-stat-item, .email-stat-item {
        transition: all 0.3s ease;
        padding: 0.5rem;
        border-radius: 8px;
    }
    
    .hierarchy-stat-item:hover, .email-stat-item:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
    }
    
    .hierarchy-stat-item h4, .email-stat-item h4 {
        transition: all 0.2s ease;
    }
    
    .hierarchy-stat-item:hover h4, .email-stat-item:hover h4 {
        transform: scale(1.1);
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-shield-check me-2"></i>
        System Administration Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download me-1"></i>Export System Data
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-danger">
            <i class="bi bi-gear me-1"></i>
            System Settings
        </button>
    </div>
</div>

<!-- Admin Welcome Message -->
<div class="alert alert-warning border-0 rounded-3 mb-4" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7);">
    <div class="row align-items-center">
        <div class="col-auto">
            <i class="bi bi-shield-check fs-2"></i>
        </div>
        <div class="col">
            <h5 class="mb-1">System Administrator Access</h5>
            <p class="mb-0">You have full system access. Use these tools responsibly to manage the ABO-WBO organization.</p>
        </div>
    </div>
</div>

<!-- System Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-people text-primary fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">Total Users</div>
                        <div class="fs-4 fw-bold text-primary"><?= $total_users ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-calendar-event text-success fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">Total Meetings</div>
                        <div class="fs-4 fw-bold text-success"><?= $total_meetings ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-list-task text-info fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">Total Tasks</div>
                        <div class="fs-4 fw-bold text-info"><?= $total_tasks ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-currency-dollar text-warning fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">Total Donations</div>
                        <div class="fs-4 fw-bold text-warning"><?= $total_donations ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hierarchy Overview -->
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Organizational Hierarchy
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="border-end hierarchy-stat-item" onclick="window.location='/hierarchy?filter=godina'" style="cursor: pointer;" title="Click to view all Godinas">
                            <h4 class="text-primary"><?= $hierarchy_overview['total_godinas'] ?? 0 ?></h4>
                            <small class="text-muted">Godinas</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border-end hierarchy-stat-item" onclick="window.location='/hierarchy?filter=gamta'" style="cursor: pointer;" title="Click to view all Gamtas">
                            <h4 class="text-success"><?= $hierarchy_overview['total_gamtas'] ?? 0 ?></h4>
                            <small class="text-muted">Gamtas</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border-end hierarchy-stat-item" onclick="window.location='/hierarchy?filter=gurmu'" style="cursor: pointer;" title="Click to view all Gurmus">
                            <h4 class="text-info"><?= $hierarchy_overview['total_gurmus'] ?? 0 ?></h4>
                            <small class="text-muted">Gurmus</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="hierarchy-stat-item" onclick="window.location='/positions/assignments'" style="cursor: pointer;" title="Click to view all assignments">
                            <h4 class="text-warning"><?= $hierarchy_overview['total_assignments'] ?? 0 ?></h4>
                            <small class="text-muted">Assignments</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <a href="/hierarchy" class="btn btn-primary btn-sm">
                        <i class="bi bi-diagram-3 me-1"></i>Manage Hierarchy
                    </a>
                    <a href="/positions" class="btn btn-outline-primary btn-sm ms-2">
                        <i class="bi bi-person-badge me-1"></i>Manage Positions
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Internal Email Management -->
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-envelope-at me-2"></i>
                    Internal Email System
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="border-end email-stat-item" onclick="window.location='/user-emails'" style="cursor: pointer;" title="Click to view all emails">
                            <h4 class="text-primary"><?= $email_stats['total_emails'] ?? 0 ?></h4>
                            <small class="text-muted">Total Emails</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end email-stat-item" onclick="window.location='/user-emails?status=active'" style="cursor: pointer;" title="Click to view active emails">
                            <h4 class="text-success"><?= $email_stats['active_emails'] ?? 0 ?></h4>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="email-stat-item" onclick="window.location='/user-emails?status=inactive'" style="cursor: pointer;" title="Click to view inactive emails">
                            <h4 class="text-secondary"><?= $email_stats['inactive_emails'] ?? 0 ?></h4>
                            <small class="text-muted">Inactive</small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-light border mb-3">
                    <small><strong>Domain:</strong> j-abo-wbo.org</small><br>
                    <small><strong>Format:</strong> {position}.{hierarchy}.{firstname}.{lastname}@j-abo-wbo.org</small>
                </div>
                <div class="text-center">
                    <a href="/user-emails" class="btn btn-info btn-sm text-white">
                        <i class="bi bi-envelope-at me-1"></i>Manage Emails
                    </a>
                    <a href="/user-emails/create" class="btn btn-outline-info btn-sm ms-2">
                        <i class="bi bi-plus-circle me-1"></i>Create Email
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2"></i>
                    System Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <strong>Database Size:</strong>
                        <p class="text-muted"><?= $system_stats['database_size'] ?? '0 MB' ?></p>
                    </div>
                    <div class="col-6">
                        <strong>Storage Used:</strong>
                        <p class="text-muted"><?= $system_stats['storage_used'] ?? '0 MB' ?></p>
                    </div>
                    <div class="col-6">
                        <strong>Active Sessions:</strong>
                        <p class="text-muted"><?= $system_stats['active_sessions'] ?? 0 ?></p>
                    </div>
                    <div class="col-6">
                        <strong>Last Backup:</strong>
                        <p class="text-muted"><?= $system_stats['last_backup'] ?? 'Never' ?></p>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <a href="/admin/maintenance" class="btn btn-success btn-sm">
                        <i class="bi bi-tools me-1"></i>System Maintenance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Administration Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/users" class="btn btn-outline-primary d-block">
                            <i class="bi bi-people me-2"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/admin/user-registration" class="btn btn-outline-success d-block">
                            <i class="bi bi-person-plus me-2"></i>
                            Register New User
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/admin/global-settings" class="btn btn-outline-warning d-block">
                            <i class="bi bi-gear me-2"></i>
                            Global Settings
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="/reports" class="btn btn-outline-info d-block">
                            <i class="bi bi-graph-up me-2"></i>
                            System Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent System Activities (placeholder) -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent System Activities
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activities)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p>No recent system activities to display.</p>
                        <small>System activity monitoring will be implemented in future updates.</small>
                    </div>
                <?php else: ?>
                    <!-- Activities would be displayed here when implemented -->
                    <div class="list-group">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                                    <small><?= htmlspecialchars($activity['time']) ?></small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($activity['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>