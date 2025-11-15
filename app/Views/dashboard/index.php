<?php
/**
 * Dashboard Index View Template
 * Main dashboard with role-based analytics and widgets
 */

// Page metadata
$pageTitle = __('dashboard.title');
$pageDescription = __('dashboard.description');
$bodyClass = 'dashboard-page';

// Get user context
$user = $user ?? [];
$userRole = $user['role'] ?? 'member';
$userScope = $user['level_scope'] ?? 'gurmu';

// Dashboard data
$dashboardData = $dashboardData ?? [];
$stats = $dashboardData['stats'] ?? [];
$recentActivities = $dashboardData['recent_activities'] ?? [];
$upcomingEvents = $dashboardData['upcoming_events'] ?? [];
$pendingTasks = $dashboardData['pending_tasks'] ?? [];
$announcements = $dashboardData['announcements'] ?? [];

// Quick actions based on role
$quickActions = $dashboardData['quick_actions'] ?? [];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('dashboard.welcome_back') ?>, <?= htmlspecialchars($user['first_name'] ?? 'User') ?>!</h1>
        <p class="text-muted mb-0">
            <?= __('dashboard.role') ?>: <span class="badge bg-primary"><?= __(ucfirst($userRole)) ?></span>
            <?= __('dashboard.level') ?>: <span class="badge bg-info"><?= __(ucfirst($userScope)) ?></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#notificationsModal">
            <i class="bi bi-bell"></i>
            <span class="badge bg-danger rounded-pill ms-1" id="notification-count">
                <?= $dashboardData['unread_notifications'] ?? 0 ?>
            </span>
        </button>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-plus-circle"></i> <?= __('dashboard.quick_actions') ?>
            </button>
            <ul class="dropdown-menu">
                <?php foreach ($quickActions as $action): ?>
                    <li>
                        <a class="dropdown-item" href="<?= htmlspecialchars($action['url']) ?>">
                            <i class="bi bi-<?= htmlspecialchars($action['icon']) ?> me-2"></i>
                            <?= htmlspecialchars($action['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Dashboard Stats -->
<div class="row g-3 mb-4">
    <?php if (isset($stats['total_members'])): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= __('dashboard.total_members') ?></h5>
                            <h2 class="mb-0"><?= number_format($stats['total_members']) ?></h2>
                            <?php if (isset($stats['members_growth'])): ?>
                                <small class="opacity-75">
                                    <i class="bi bi-trending-<?= $stats['members_growth'] >= 0 ? 'up' : 'down' ?>"></i>
                                    <?= abs($stats['members_growth']) ?>% <?= __('dashboard.this_month') ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-people fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($stats['active_tasks'])): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= __('dashboard.active_tasks') ?></h5>
                            <h2 class="mb-0"><?= number_format($stats['active_tasks']) ?></h2>
                            <?php if (isset($stats['completed_tasks'])): ?>
                                <small class="opacity-75">
                                    <?= number_format($stats['completed_tasks']) ?> <?= __('dashboard.completed') ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-list-task fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($stats['upcoming_meetings'])): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= __('dashboard.upcoming_meetings') ?></h5>
                            <h2 class="mb-0"><?= number_format($stats['upcoming_meetings']) ?></h2>
                            <?php if (isset($stats['meetings_this_week'])): ?>
                                <small class="opacity-75">
                                    <?= number_format($stats['meetings_this_week']) ?> <?= __('dashboard.this_week') ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar-event fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($stats['total_donations'])): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= __('dashboard.total_donations') ?></h5>
                            <h2 class="mb-0">$<?= number_format($stats['total_donations'], 2) ?></h2>
                            <?php if (isset($stats['donations_growth'])): ?>
                                <small class="opacity-75">
                                    <i class="bi bi-trending-<?= $stats['donations_growth'] >= 0 ? 'up' : 'down' ?>"></i>
                                    <?= abs($stats['donations_growth']) ?>% <?= __('dashboard.this_month') ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-currency-dollar fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Main Dashboard Content -->
<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Recent Activities -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-activity me-2"></i><?= __('dashboard.recent_activities') ?>
                </h5>
                <a href="/activities" class="btn btn-sm btn-outline-primary">
                    <?= __('dashboard.view_all') ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivities)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2"><?= __('dashboard.no_recent_activities') ?></p>
                    </div>
                <?php else: ?>
                    <div class="activity-timeline">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon bg-<?= htmlspecialchars($activity['type_color'] ?? 'primary') ?>">
                                    <i class="bi bi-<?= htmlspecialchars($activity['icon'] ?? 'circle') ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <h6 class="activity-title"><?= htmlspecialchars($activity['title']) ?></h6>
                                    <p class="activity-description mb-1"><?= htmlspecialchars($activity['description']) ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <time datetime="<?= htmlspecialchars($activity['created_at']) ?>">
                                            <?= time_ago($activity['created_at']) ?>
                                        </time>
                                        <?php if (isset($activity['user_name'])): ?>
                                            <span class="ms-2">
                                                <i class="bi bi-person me-1"></i>
                                                <?= htmlspecialchars($activity['user_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pending Tasks -->
        <?php if (!empty($pendingTasks)): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-circle me-2"></i><?= __('dashboard.pending_tasks') ?>
                    </h5>
                    <a href="/tasks?status=pending" class="btn btn-sm btn-outline-primary">
                        <?= __('dashboard.view_all') ?>
                    </a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($pendingTasks, 0, 5) as $task): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="/tasks/<?= htmlspecialchars($task['id']) ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($task['title']) ?>
                                            </a>
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            <?= htmlspecialchars(substr($task['description'], 0, 100)) ?>
                                            <?= strlen($task['description']) > 100 ? '...' : '' ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?= __('dashboard.due') ?>: <?= format_date($task['due_date']) ?>
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary') ?> me-2">
                                            <?= __(ucfirst($task['priority'])) ?>
                                        </span>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i><?= __('common.view') ?>
                                                </a></li>
                                                <li><a class="dropdown-item" href="/tasks/<?= $task['id'] ?>/edit">
                                                    <i class="bi bi-pencil me-2"></i><?= __('common.edit') ?>
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Performance Chart -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i><?= __('dashboard.performance_overview') ?>
                </h5>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Announcements -->
        <?php if (!empty($announcements)): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-megaphone me-2"></i><?= __('dashboard.announcements') ?>
                    </h5>
                    <a href="/announcements" class="btn btn-sm btn-outline-primary">
                        <?= __('dashboard.view_all') ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php foreach (array_slice($announcements, 0, 3) as $announcement): ?>
                        <div class="announcement-item <?= $announcement === end($announcements) ? '' : 'border-bottom' ?> pb-3 mb-3">
                            <h6 class="announcement-title mb-2">
                                <?= htmlspecialchars($announcement['title']) ?>
                                <?php if ($announcement['is_urgent'] ?? false): ?>
                                    <span class="badge bg-danger ms-1"><?= __('dashboard.urgent') ?></span>
                                <?php endif; ?>
                            </h6>
                            <p class="announcement-content mb-2 small text-muted">
                                <?= htmlspecialchars(substr($announcement['content'], 0, 150)) ?>
                                <?= strlen($announcement['content']) > 150 ? '...' : '' ?>
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?= format_date($announcement['created_at']) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Upcoming Events -->
        <?php if (!empty($upcomingEvents)): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event me-2"></i><?= __('dashboard.upcoming_events') ?>
                    </h5>
                    <a href="/events" class="btn btn-sm btn-outline-primary">
                        <?= __('dashboard.view_all') ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php foreach (array_slice($upcomingEvents, 0, 4) as $event): ?>
                        <div class="event-item d-flex align-items-center <?= $event === end($upcomingEvents) ? '' : 'border-bottom' ?> pb-3 mb-3">
                            <div class="event-date-box bg-primary text-white rounded text-center me-3" style="min-width: 60px;">
                                <div class="fw-bold"><?= date('d', strtotime($event['event_date'])) ?></div>
                                <small><?= date('M', strtotime($event['event_date'])) ?></small>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="event-title mb-1">
                                    <a href="/events/<?= htmlspecialchars($event['id']) ?>" 
                                       class="text-decoration-none">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= format_time($event['start_time']) ?>
                                    <?php if (isset($event['location'])): ?>
                                        <br><i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($event['location']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-speedometer2 me-2"></i><?= __('dashboard.quick_stats') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if (isset($stats['task_completion_rate'])): ?>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="progress-circle" data-percentage="<?= $stats['task_completion_rate'] ?>">
                                    <div class="percentage"><?= $stats['task_completion_rate'] ?>%</div>
                                </div>
                                <small class="text-muted"><?= __('dashboard.task_completion') ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($stats['meeting_attendance'])): ?>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="progress-circle" data-percentage="<?= $stats['meeting_attendance'] ?>">
                                    <div class="percentage"><?= $stats['meeting_attendance'] ?>%</div>
                                </div>
                                <small class="text-muted"><?= __('dashboard.meeting_attendance') ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Additional Quick Stats -->
                <div class="mt-4">
                    <?php if (isset($stats['active_projects'])): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted"><?= __('dashboard.active_projects') ?></small>
                            <span class="badge bg-primary"><?= $stats['active_projects'] ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($stats['pending_approvals'])): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted"><?= __('dashboard.pending_approvals') ?></small>
                            <span class="badge bg-warning"><?= $stats['pending_approvals'] ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($stats['overdue_tasks'])): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?= __('dashboard.overdue_tasks') ?></small>
                            <span class="badge bg-danger"><?= $stats['overdue_tasks'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Styles -->
<style>
.stats-card {
    border: none;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.activity-timeline {
    position: relative;
}

.activity-item {
    display: flex;
    margin-bottom: 1rem;
    position: relative;
}

.activity-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 15px;
    top: 40px;
    width: 2px;
    height: calc(100% + 0.5rem);
    background-color: #e9ecef;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity-content {
    flex-grow: 1;
}

.activity-title {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.activity-description {
    font-size: 0.8125rem;
    color: #6c757d;
}

.event-date-box {
    padding: 0.5rem;
    line-height: 1.2;
}

.progress-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: conic-gradient(#007bff var(--percentage, 0%), #e9ecef 0%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    position: relative;
}

.progress-circle::before {
    content: '';
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progress-circle .percentage {
    position: relative;
    z-index: 1;
    font-size: 0.75rem;
    font-weight: bold;
    color: #007bff;
}

.announcement-item:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}

.event-item:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}
</style>

<!-- Dashboard JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize progress circles
    document.querySelectorAll('.progress-circle').forEach(circle => {
        const percentage = circle.dataset.percentage;
        circle.style.setProperty('--percentage', `${percentage * 3.6}deg`);
    });
    
    // Initialize performance chart
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dashboardData['chart_labels'] ?? []) ?>,
                datasets: [{
                    label: '<?= __('dashboard.tasks_completed') ?>',
                    data: <?= json_encode($dashboardData['chart_data'] ?? []) ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        fetch('/api/dashboard/refresh')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update notification count
                    const notificationCount = document.getElementById('notification-count');
                    if (notificationCount) {
                        notificationCount.textContent = data.unread_notifications || 0;
                        notificationCount.style.display = data.unread_notifications > 0 ? 'inline' : 'none';
                    }
                }
            })
            .catch(error => console.error('Dashboard refresh error:', error));
    }, 5 * 60 * 1000);
    
    // Real-time updates via WebSocket (if available)
    if (typeof io !== 'undefined') {
        const socket = io();
        
        socket.on('dashboard_update', function(data) {
            // Handle real-time dashboard updates
            if (data.type === 'new_notification') {
                const notificationCount = document.getElementById('notification-count');
                if (notificationCount) {
                    const current = parseInt(notificationCount.textContent) || 0;
                    notificationCount.textContent = current + 1;
                    notificationCount.style.display = 'inline';
                }
            }
        });
    }
});
</script>