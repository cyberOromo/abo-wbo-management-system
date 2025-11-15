<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-person-badge me-2"></i>
        <?= htmlspecialchars($user_scope['position_name'] ?? 'Executive') ?> Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download me-1"></i>Export Reports
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            New Task
        </button>
    </div>
</div>

<!-- Executive Welcome Message -->
<div class="alert alert-primary border-0 rounded-3 mb-4" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
    <div class="row align-items-center">
        <div class="col-auto">
            <i class="bi bi-person-badge fs-2"></i>
        </div>
        <div class="col">
            <h5 class="mb-1">Welcome, <?= htmlspecialchars($user['first_name'] ?? 'Executive') ?>!</h5>
            <p class="mb-0">
                <strong><?= htmlspecialchars($user_scope['position_name'] ?? 'Executive Position') ?></strong> - 
                <?= htmlspecialchars($user_scope['scope_name'] ?? 'Organization Scope') ?>
            </p>
            <small class="text-muted">Access Level: <?= ucfirst($user_scope['level_scope'] ?? 'executive') ?></small>
        </div>
    </div>
</div>

<!-- Executive Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-list-task text-primary fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">My Tasks</div>
                        <div class="fs-4 fw-bold text-primary"><?= count($my_tasks ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-calendar-event text-success fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">My Meetings</div>
                        <div class="fs-4 fw-bold text-success"><?= count($my_meetings ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-people text-info fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">Team Members</div>
                        <div class="fs-4 fw-bold text-info"><?= count($hierarchy_members ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-file-earmark-text text-warning fs-1"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-muted">Reports</div>
                        <div class="fs-4 fw-bold text-warning"><?= count($my_reports ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Position-Specific Data -->
<?php if (isset($media_data) && !empty($media_data)): ?>
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-camera-video me-2"></i>
                    Media & Public Relations
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Media management tools and public relations data will be displayed here.</p>
                <div class="text-center">
                    <a href="/events" class="btn btn-info btn-sm">
                        <i class="bi bi-megaphone me-1"></i>Manage Publications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($financial_data) && !empty($financial_data)): ?>
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-currency-dollar me-2"></i>
                    Financial Overview
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Financial data and budget information will be displayed here.</p>
                <div class="text-center">
                    <a href="/donations" class="btn btn-success btn-sm">
                        <i class="bi bi-cash-stack me-1"></i>Financial Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Responsibilities -->
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    Position Responsibilities
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($position_responsibilities)): ?>
                    <ul class="list-unstyled">
                        <?php foreach (array_slice($position_responsibilities, 0, 5) as $responsibility): ?>
                            <li class="mb-2">
                                <i class="bi bi-check text-success me-2"></i>
                                <?= htmlspecialchars($responsibility) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($position_responsibilities) > 5): ?>
                        <small class="text-muted">+<?= count($position_responsibilities) - 5 ?> more responsibilities</small>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">No specific responsibilities defined for this position.</p>
                <?php endif; ?>
                <hr>
                <div class="text-center">
                    <a href="/responsibilities" class="btn btn-primary btn-sm">
                        <i class="bi bi-list-check me-1"></i>View All Responsibilities
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Hierarchy Statistics
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($hierarchy_stats)): ?>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary"><?= $hierarchy_stats['total_members'] ?? 0 ?></h4>
                            <small class="text-muted">Total Members</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success"><?= $hierarchy_stats['active_tasks'] ?? 0 ?></h4>
                            <small class="text-muted">Active Tasks</small>
                        </div>
                        <div class="col-6 mt-3">
                            <h4 class="text-info"><?= $hierarchy_stats['upcoming_meetings'] ?? 0 ?></h4>
                            <small class="text-muted">Upcoming Meetings</small>
                        </div>
                        <div class="col-6 mt-3">
                            <h4 class="text-warning"><?= $hierarchy_stats['completed_events'] ?? 0 ?></h4>
                            <small class="text-muted">Completed Events</small>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Hierarchy statistics will be displayed here.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities and Quick Actions -->
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activities
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_activities)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($recent_activities, 0, 5) as $activity): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($activity['title'] ?? 'Activity') ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($activity['date'] ?? 'Recently') ?></small>
                                </div>
                                <p class="mb-1 text-muted"><?= htmlspecialchars($activity['description'] ?? 'No description') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p>No recent activities to display.</p>
                        <small>Activities will appear here as you work with tasks, meetings, and events.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/tasks/create" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>
                        Create New Task
                    </a>
                    <a href="/meetings/create" class="btn btn-success btn-sm">
                        <i class="bi bi-calendar-plus me-1"></i>
                        Schedule Meeting
                    </a>
                    <a href="/events/create" class="btn btn-info btn-sm">
                        <i class="bi bi-megaphone me-1"></i>
                        Create Event
                    </a>
                    <a href="/reports" class="btn btn-warning btn-sm">
                        <i class="bi bi-file-earmark-text me-1"></i>
                        View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>