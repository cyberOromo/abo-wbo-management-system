<?php
require __DIR__ . '/index_shell.php';
return;

$pageTitle = $title ?? 'Meeting Management';
$layout = 'modern';
$meetings = $meetings ?? [];
$stats = $stats ?? [];
$canCreateMeeting = $can_create_meeting ?? false;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-calendar-event me-2"></i>
            Meeting Management
        </h1>
        <p class="text-muted mb-0">Schedule, organize, and track organizational meetings</p>
    </div>
    <div class="btn-toolbar">
        <div class="btn-group">
            <a href="/reports/meetings" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i>
                Meeting Reports
            </a>
        </div>
    </div>
</div>

<?php if (!$canCreateMeeting): ?>
<div class="alert alert-info mb-4" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    Scheduling and editing flows are not enabled for the current executive scope in this staging build. This page is currently limited to scoped meeting visibility and reporting.
</div>
<?php endif; ?>

<!-- Meeting Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Meetings</p>
                    <small class="text-info">
                        <i class="bi bi-clock-history"></i>
                        <?= number_format($stats['recent'] ?? 0) ?> Recent
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['scheduled'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Scheduled</p>
                    <small class="text-info">
                        <i class="bi bi-calendar-check"></i>
                        Awaiting execution
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['in_progress'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">In Progress</p>
                    <small class="text-success">
                        <i class="bi bi-play-circle"></i>
                        Active right now
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-camera-video"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['completed'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Completed</p>
                    <small class="text-muted">
                        <i class="bi bi-check-circle"></i>
                        Closed records
                    </small>
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
                    <i class="bi bi-lightning-charge me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" href="/meetings">
                            <div class="action-icon bg-primary text-white mb-2">
                                <i class="bi bi-arrow-clockwise"></i>
                            </div>
                            <span class="fw-bold">Refresh Meetings</span>
                            <small class="text-muted">Reload scoped meeting data</small>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" type="button" data-view="list">
                            <div class="action-icon bg-success text-white mb-2">
                                <i class="bi bi-list-ul"></i>
                            </div>
                            <span class="fw-bold">List View</span>
                            <small class="text-muted">Current scoped records</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" type="button" data-view="calendar">
                            <div class="action-icon bg-info text-white mb-2">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <span class="fw-bold">Agenda View</span>
                            <small class="text-muted">Grouped by upcoming date</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <a class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" href="/reports/meetings">
                            <div class="action-icon bg-warning text-white mb-2">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="fw-bold">Meeting Reports</span>
                            <small class="text-muted">Analytics & insights</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Meetings List & Calendar -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list me-2"></i>
                    Meeting Records
                </h5>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary active" data-view="list">
                        <i class="bi bi-list"></i> List
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="calendar">
                        <i class="bi bi-calendar3"></i> Calendar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="meetings-list-view">
                    <?php if (!empty($meetings)): ?>
                    <?php foreach ($meetings as $meeting): ?>
                    <?php
                        $status = $meeting['status'] ?? 'scheduled';
                        $statusClass = [
                            'scheduled' => 'warning',
                            'in_progress' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'postponed' => 'secondary'
                        ][$status] ?? 'secondary';
                        $platform = $meeting['platform'] ?? 'in_person';
                        $isVirtual = $platform === 'zoom' || $platform === 'hybrid';
                        $startAt = !empty($meeting['start_datetime']) ? strtotime($meeting['start_datetime']) : false;
                        $location = trim((string) ($meeting['location'] ?? ''));
                        $locationLabel = $location !== '' ? $location : ucfirst(str_replace('_', ' ', $platform));
                    ?>
                        <div class="meeting-item border rounded p-3 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="meeting-icon bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> me-3">
                                            <i class="bi bi-<?= $isVirtual ? 'camera-video' : 'building' ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($meeting['title'] ?? 'Untitled Meeting') ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?= htmlspecialchars($locationLabel) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <div class="fw-bold"><?= $startAt ? date('M j', $startAt) : 'TBD' ?></div>
                                        <small class="text-muted"><?= $startAt ? date('g:i A', $startAt) : 'Pending time' ?></small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucwords(str_replace('_', ' ', $status)) ?></span>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="bi bi-tag me-1"></i>
                                                <?= htmlspecialchars($meeting['created_by_name'] ?: ucfirst(str_replace('_', ' ', $platform))) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-end">
                                        <span class="badge text-bg-light border">Read-only in current build</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                            <p class="mt-3 mb-0">No meetings are available in your current scope.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div id="meetings-calendar-view" style="display: none;">
                    <div class="calendar-view">
                        <h6 class="mb-3">Upcoming Agenda</h6>
                        <?php if (!empty($meetings)): ?>
                            <?php foreach ($meetings as $meeting): ?>
                                <?php $startAt = !empty($meeting['start_datetime']) ? strtotime($meeting['start_datetime']) : false; ?>
                                <div class="schedule-item d-flex align-items-center p-2 rounded mb-2 bg-light">
                                    <div class="time-badge bg-primary text-white me-3"><?= $startAt ? date('M j', $startAt) : 'TBD' ?></div>
                                    <div>
                                        <h6 class="mb-0 small"><?= htmlspecialchars($meeting['title'] ?? 'Untitled Meeting') ?></h6>
                                        <small class="text-muted"><?= $startAt ? date('g:i A', $startAt) : 'Pending schedule' ?> · <?= htmlspecialchars(trim((string) ($meeting['location'] ?? '')) ?: ucfirst(str_replace('_', ' ', $meeting['platform'] ?? 'in_person'))) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">No upcoming agenda items are available in your current scope.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Meeting Sidebar -->
    <div class="col-lg-4">
        <!-- Scope Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-bullseye me-2"></i>
                    Scope Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="schedule-item d-flex align-items-center p-2 rounded mb-2 bg-primary bg-opacity-10">
                    <div class="time-badge bg-primary text-white me-3"><?= strtoupper(substr((string) ($scope['level_scope'] ?? 'all'), 0, 3)) ?></div>
                    <div>
                        <h6 class="mb-0 small"><?= htmlspecialchars($scope['scope_name'] ?? 'Current executive scope') ?></h6>
                        <small class="text-muted"><?= number_format($stats['total'] ?? 0) ?> meeting records visible</small>
                    </div>
                </div>
                
                <div class="text-center py-3">
                    <i class="bi bi-eye text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 small">This panel reflects only records reachable in your current hierarchy scope.</p>
                </div>
            </div>
        </div>
        
        <!-- Status Breakdown -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Status Breakdown
                </h6>
            </div>
            <div class="card-body">
                <div class="meeting-type-item d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-warning me-2"></div>
                        <span class="small">Scheduled</span>
                    </div>
                    <span class="badge bg-secondary"><?= number_format($stats['scheduled'] ?? 0) ?></span>
                </div>
                <div class="meeting-type-item d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-info me-2"></div>
                        <span class="small">In Progress</span>
                    </div>
                    <span class="badge bg-secondary"><?= number_format($stats['in_progress'] ?? 0) ?></span>
                </div>
                <div class="meeting-type-item d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-success me-2"></div>
                        <span class="small">Completed</span>
                    </div>
                    <span class="badge bg-secondary"><?= number_format($stats['completed'] ?? 0) ?></span>
                </div>
                <div class="meeting-type-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-primary me-2"></div>
                        <span class="small">Recent Records</span>
                    </div>
                    <span class="badge bg-secondary"><?= number_format($stats['recent'] ?? 0) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Build Notes -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-tools me-2"></i>
                    Build Notes
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">The staging build currently exposes read-only meeting visibility for this executive scope.</p>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Scheduling, editing, and template flows are intentionally hidden until their backing views and handlers are complete.</li>
                    <li>Meeting reports remain available through the reports module.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.meeting-item {
    transition: var(--abo-transition);
    background: var(--abo-white);
}

.meeting-item:hover {
    transform: translateX(4px);
    box-shadow: var(--abo-shadow-md);
}

.meeting-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--abo-radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.calendar-day-full {
    height: 80px;
    border: 1px solid var(--abo-gray-200);
    border-radius: var(--abo-radius);
    padding: 0.25rem;
    position: relative;
    background: var(--abo-white);
    transition: var(--abo-transition);
}

.calendar-day-full:hover {
    background-color: var(--abo-gray-50);
}

.calendar-day-full.has-meeting {
    background-color: var(--abo-primary);
    background-opacity: 0.1;
    border-color: var(--abo-primary);
}

.calendar-day-full.empty {
    background-color: var(--abo-gray-50);
    border-color: transparent;
}

.day-number {
    font-weight: 600;
    font-size: 0.875rem;
}

.meeting-dot {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 8px;
    height: 8px;
    background-color: var(--abo-primary);
    border-radius: 50%;
}

.schedule-item {
    transition: var(--abo-transition);
}

.schedule-item:hover {
    transform: scale(1.02);
}

.time-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: var(--abo-radius);
    min-width: 60px;
    text-align: center;
}

.meeting-type-item {
    padding: 0.25rem;
    border-radius: var(--abo-radius);
    transition: var(--abo-transition);
}

.meeting-type-item:hover {
    background-color: var(--abo-gray-50);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const viewButtons = document.querySelectorAll('[data-view]');
    const listView = document.getElementById('meetings-list-view');
    const calendarView = document.getElementById('meetings-calendar-view');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide views
            if (viewType === 'list') {
                listView.style.display = 'block';
                calendarView.style.display = 'none';
            } else if (viewType === 'calendar') {
                listView.style.display = 'none';
                calendarView.style.display = 'block';
            }
        });
    });
});
</script>
