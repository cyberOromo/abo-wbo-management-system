<?php
require __DIR__ . '/index_shell.php';
return;

$title = $title ?? 'Events Management';
$events = $events ?? [];
$stats = $stats ?? [];
$scope = $scope ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-calendar-event me-2"></i>
            <?= htmlspecialchars($title) ?>
        </h1>
        <p class="text-muted mb-0">Real event visibility for the current hierarchy scope.</p>
    </div>
    <div class="btn-toolbar gap-2">
        <a href="/events" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Refresh
        </a>
        <a href="/reports/events" class="btn btn-outline-primary">
            <i class="bi bi-graph-up me-1"></i>
            Event Reports
        </a>
    </div>
</div>

<div class="alert alert-warning border-0 rounded-3 mb-4" style="background: linear-gradient(135deg, #fff3cd, #ffe69c);">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-circle fs-4"></i>
        <div>
            <h5 class="mb-1">Current Staging Scope</h5>
            <p class="mb-1">This page shows real event records currently visible to your executive scope.</p>
            <p class="mb-0 small text-muted">Create, calendar, export, and event-detail actions are hidden until their backing views and handlers are implemented.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3"><div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($stats['total'] ?? 0) ?></h3><p class="text-muted mb-0">Total Events</p></div></div></div>
    <div class="col-lg-3 col-md-6 mb-3"><div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($stats['upcoming'] ?? 0) ?></h3><p class="text-muted mb-0">Upcoming</p></div></div></div>
    <div class="col-lg-3 col-md-6 mb-3"><div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($stats['ongoing'] ?? 0) ?></h3><p class="text-muted mb-0">Ongoing</p></div></div></div>
    <div class="col-lg-3 col-md-6 mb-3"><div class="card stats-card h-100"><div class="card-body"><h3 class="mb-1"><?= number_format($stats['completed'] ?? 0) ?></h3><p class="text-muted mb-0">Completed</p></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-calendar2-week me-2"></i>Scoped Events</h5>
                <span class="badge bg-light text-dark"><?= number_format(count($events)) ?> loaded</span>
            </div>
            <div class="card-body">
                <?php if (!empty($events)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                    <?php
                                    $dateLabel = 'No schedule';
                                    if (!empty($event['start_datetime'])) {
                                        $dateLabel = date('M j, Y g:i A', strtotime((string) $event['start_datetime']));
                                    } elseif (!empty($event['created_at'])) {
                                        $dateLabel = date('M j, Y', strtotime((string) $event['created_at']));
                                    }
                                    $creator = trim((string) (($event['first_name'] ?? '') . ' ' . ($event['last_name'] ?? '')));
                                    if ($creator === '') {
                                        $creator = $event['created_by_name'] ?? 'Unknown';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($event['title'] ?? 'Untitled event') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($event['description'] ?? 'No description provided.') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($event['status'] ?? 'upcoming')))) ?></td>
                                        <td><?= htmlspecialchars($dateLabel) ?></td>
                                        <td><?= htmlspecialchars($event['location'] ?? 'TBD') ?></td>
                                        <td><?= htmlspecialchars((string) $creator) ?></td>
                                        <td><span class="text-muted small">Read-only in current build</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No event records are visible</h5>
                        <p class="text-muted mb-0">There are no event entries available for your current hierarchy scope.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="card-title mb-0"><i class="bi bi-bullseye me-2"></i>Scope Summary</h6></div>
            <div class="card-body">
                <p class="mb-2"><strong>Level:</strong> <?= htmlspecialchars(ucfirst($scope['level_scope'] ?? 'all')) ?></p>
                <p class="mb-2"><strong>Recent records:</strong> <?= number_format($stats['recent'] ?? 0) ?></p>
                <p class="mb-0 text-muted small">Event actions are restricted to read-only visibility in this staging build.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Build Notes</h6></div>
            <div class="card-body">
                <ul class="small text-muted mb-0 ps-3">
                    <li>Events listed here are pulled from the live events table with schema-aware filtering.</li>
                    <li>Create, calendar, export, and event-detail affordances were removed because their views are not present in the current build.</li>
                    <li>Use event reports for deeper analysis until mutation flows are completed.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
