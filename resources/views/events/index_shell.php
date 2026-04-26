<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$title = $title ?? 'Events Management';
$events = $events ?? [];
$stats = $stats ?? [];
$scope = $scope ?? [];

$formatStatusClass = static function (?string $value): string {
    return match ((string) $value) {
        'completed' => 'status-success',
        'ongoing', 'in_progress' => 'status-info',
        'upcoming', 'open_registration' => 'status-warning',
        'cancelled' => 'status-danger',
        default => 'status-neutral',
    };
};

$featuredEvents = array_slice($events, 0, 4);
?>

<div class="module-surface theme-events">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-compass"></i> Leader Workspace</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-calendar2-week me-2"></i><?= htmlspecialchars($title) ?></h1>
                    <p class="module-subtitle">A consistent event workspace for scoped visibility, schedule review, and event reporting without exposing unsupported edit or export paths.</p>
                </div>
                <div class="module-actions">
                    <a href="/events" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
                    <a href="/reports/events" class="btn btn-outline-primary"><i class="bi bi-graph-up me-1"></i>Event Report</a>
                    <a href="/reports" class="btn btn-primary"><i class="bi bi-grid-1x2 me-1"></i>Reports Hub</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($scope['scope_name'] ?? 'Current hierarchy scope') ?></span>
                <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) ($scope['level_scope'] ?? 'all'))) ?> level</span>
                <span class="module-chip"><i class="bi bi-shield-lock"></i>Read-only event surface</span>
            </div>
        </div>
    </section>

    <div class="module-callout warning">
        <strong>Current staging behavior:</strong> this page shows real event records and reporting access. Create, calendar, export, and event-detail actions remain hidden until their backing handlers are complete.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['total'] ?? 0)) ?></div><div class="stat-label">Total Events</div></div><span class="stat-icon"><i class="bi bi-collection"></i></span></div>
                <div class="stat-footnote">Events visible in the current scope.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['upcoming'] ?? 0)) ?></div><div class="stat-label">Upcoming</div></div><span class="stat-icon"><i class="bi bi-calendar-event"></i></span></div>
                <div class="stat-footnote">Scheduled records approaching execution.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['ongoing'] ?? 0)) ?></div><div class="stat-label">Ongoing</div></div><span class="stat-icon"><i class="bi bi-broadcast"></i></span></div>
                <div class="stat-footnote">Events currently active in the dataset.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['completed'] ?? 0)) ?></div><div class="stat-label">Completed</div></div><span class="stat-icon"><i class="bi bi-check2-circle"></i></span></div>
                <div class="stat-footnote"><?= number_format((int) ($stats['recent'] ?? 0)) ?> created in the last 30 days.</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-calendar-range me-2"></i>Scoped Event Register</h2>
                    <span class="module-soft-badge"><i class="bi bi-eye"></i><?= number_format(count($events)) ?> loaded</span>
                </div>
                <div class="module-panel-body p-0">
                    <?php if (!empty($events)): ?>
                        <div class="table-responsive">
                            <table class="module-table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Schedule</th>
                                        <th>Location</th>
                                        <th>Created By</th>
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
                                                <div class="module-row-title"><?= htmlspecialchars($event['title'] ?? 'Untitled event') ?></div>
                                                <div class="module-row-meta"><?= htmlspecialchars($event['description'] ?? 'No description provided.') ?></div>
                                            </td>
                                            <td><span class="module-status <?= $formatStatusClass((string) ($event['status'] ?? 'upcoming')) ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($event['status'] ?? 'upcoming')))) ?></span></td>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars($dateLabel) ?></div>
                                                <div class="module-row-meta">Primary visible schedule</div>
                                            </td>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars($event['location'] ?? 'TBD') ?></div>
                                                <div class="module-row-meta">Resolved venue or fallback</div>
                                            </td>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars((string) $creator) ?></div>
                                                <div class="module-row-meta">Visible creator attribution</div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="module-empty">
                            <i class="bi bi-calendar-x"></i>
                            <h3 class="h5 mt-3">No scoped events are currently visible</h3>
                            <p class="mb-0">There are no event records available to the current leader scope.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-stars me-2"></i>Event Snapshot</h2>
                </div>
                <div class="module-panel-body">
                    <?php if (!empty($featuredEvents)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($featuredEvents as $event): ?>
                                <?php $when = !empty($event['start_datetime']) ? date('M j', strtotime((string) $event['start_datetime'])) : 'TBD'; ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars($event['title'] ?? 'Untitled event') ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars($event['location'] ?? 'TBD') ?></div>
                                    </div>
                                    <div class="module-stack-value"><?= htmlspecialchars($when) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No recent event highlights are available in the current scope.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Scope Summary</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Scope level</span><span class="module-key-value"><?= htmlspecialchars(ucfirst((string) ($scope['level_scope'] ?? 'all'))) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Recent records</span><span class="module-key-value"><?= number_format((int) ($stats['recent'] ?? 0)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Reporting path</span><span class="module-key-value">/reports/events</span></div>
                    </div>
                </div>
            </div>

            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-info-circle me-2"></i>Build Notes</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-stack-list">
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Schema-aware reads</div>
                                <div class="module-row-meta">The event workspace and deeper report routes now tolerate missing optional columns in staging.</div>
                            </div>
                        </div>
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Supported actions only</div>
                                <div class="module-row-meta">Non-functional create, calendar, export, and detail affordances remain hidden until implemented.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>