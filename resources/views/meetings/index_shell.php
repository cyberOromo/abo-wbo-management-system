<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$pageTitle = $title ?? 'Meeting Management';
$meetings = $meetings ?? [];
$stats = $stats ?? [];
$scope = $scope ?? [];
$canCreateMeeting = $can_create_meeting ?? false;

$formatStatusClass = static function (?string $value): string {
    return match ((string) $value) {
        'completed' => 'status-success',
        'in_progress' => 'status-info',
        'scheduled', 'postponed' => 'status-warning',
        'cancelled' => 'status-danger',
        default => 'status-neutral',
    };
};

$agendaItems = array_slice($meetings, 0, 5);
?>

<div class="module-surface theme-meetings">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-compass"></i> Leader Workspace</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-calendar-event me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <p class="module-subtitle">A consistent meeting surface for scoped visibility, upcoming agenda review, and direct access to the stabilized meeting report route.</p>
                </div>
                <div class="module-actions">
                    <a href="/meetings" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
                    <a href="/reports/meetings" class="btn btn-outline-primary"><i class="bi bi-graph-up me-1"></i>Meeting Report</a>
                    <a href="/reports" class="btn btn-primary"><i class="bi bi-grid-1x2 me-1"></i>Reports Hub</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($scope['scope_name'] ?? 'Current executive scope') ?></span>
                <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) ($scope['level_scope'] ?? 'all'))) ?> level</span>
                <span class="module-chip"><i class="bi bi-shield-lock"></i><?= $canCreateMeeting ? 'Managed meeting workspace' : 'Read-only meeting surface' ?></span>
            </div>
        </div>
    </section>

    <?php if (!$canCreateMeeting): ?>
        <div class="module-callout warning">
            <strong>Current staging behavior:</strong> scheduling and editing remain disabled for this executive scope. This surface focuses on reliable meeting visibility and direct access to analytics.
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['total'] ?? 0)) ?></div><div class="stat-label">Total Meetings</div></div><span class="stat-icon"><i class="bi bi-collection"></i></span></div>
                <div class="stat-footnote"><?= number_format((int) ($stats['recent'] ?? 0)) ?> recent meeting records.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['scheduled'] ?? 0)) ?></div><div class="stat-label">Scheduled</div></div><span class="stat-icon"><i class="bi bi-clock-history"></i></span></div>
                <div class="stat-footnote">Awaiting execution or attendance.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['in_progress'] ?? 0)) ?></div><div class="stat-label">In Progress</div></div><span class="stat-icon"><i class="bi bi-broadcast-pin"></i></span></div>
                <div class="stat-footnote">Active meetings in the live dataset.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($stats['completed'] ?? 0)) ?></div><div class="stat-label">Completed</div></div><span class="stat-icon"><i class="bi bi-check2-circle"></i></span></div>
                <div class="stat-footnote">Finished records retained for reporting.</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-people me-2"></i>Scoped Meeting Register</h2>
                    <span class="module-soft-badge"><i class="bi bi-eye"></i><?= number_format(count($meetings)) ?> loaded</span>
                </div>
                <div class="module-panel-body p-0">
                    <?php if (!empty($meetings)): ?>
                        <div class="table-responsive">
                            <table class="module-table">
                                <thead>
                                    <tr>
                                        <th>Meeting</th>
                                        <th>Status</th>
                                        <th>Schedule</th>
                                        <th>Location</th>
                                        <th>Visible Owner</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($meetings as $meeting): ?>
                                        <?php
                                        $status = (string) ($meeting['status'] ?? 'scheduled');
                                        $platform = (string) ($meeting['platform'] ?? 'in_person');
                                        $startAt = !empty($meeting['start_datetime']) ? strtotime((string) $meeting['start_datetime']) : false;
                                        $location = trim((string) ($meeting['location'] ?? ''));
                                        $locationLabel = $location !== '' ? $location : ucfirst(str_replace('_', ' ', $platform));
                                        $owner = trim((string) ($meeting['created_by_name'] ?? ''));
                                        if ($owner === '') {
                                            $owner = ucfirst(str_replace('_', ' ', $platform));
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars($meeting['title'] ?? 'Untitled meeting') ?></div>
                                                <div class="module-row-meta"><?= htmlspecialchars($meeting['agenda'] ?? 'Agenda details are not populated for this record.') ?></div>
                                            </td>
                                            <td><span class="module-status <?= $formatStatusClass($status) ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?></span></td>
                                            <td>
                                                <div class="module-row-title"><?= $startAt ? htmlspecialchars(date('M j, Y', $startAt)) : 'TBD' ?></div>
                                                <div class="module-row-meta"><?= $startAt ? htmlspecialchars(date('g:i A', $startAt)) : 'Pending time' ?></div>
                                            </td>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars($locationLabel) ?></div>
                                                <div class="module-row-meta"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $platform))) ?> meeting context</div>
                                            </td>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars($owner) ?></div>
                                                <div class="module-row-meta">Resolved from available schema columns</div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="module-empty">
                            <i class="bi bi-calendar-x"></i>
                            <h3 class="h5 mt-3">No scoped meetings are currently visible</h3>
                            <p class="mb-0">There are no meeting records available to the current executive scope.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel mb-4">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-calendar3 me-2"></i>Upcoming Agenda</h2>
                </div>
                <div class="module-panel-body">
                    <?php if (!empty($agendaItems)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($agendaItems as $meeting): ?>
                                <?php $startAt = !empty($meeting['start_datetime']) ? strtotime((string) $meeting['start_datetime']) : false; ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars($meeting['title'] ?? 'Untitled meeting') ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars(trim((string) ($meeting['location'] ?? '')) ?: ucfirst(str_replace('_', ' ', (string) ($meeting['platform'] ?? 'in_person')))) ?></div>
                                    </div>
                                    <div class="module-stack-value"><?= $startAt ? htmlspecialchars(date('M j', $startAt)) : 'TBD' ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No upcoming agenda items are visible in this scope.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="module-panel mb-4">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Scope Summary</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row"><span class="module-key-label">Scope</span><span class="module-key-value"><?= htmlspecialchars($scope['scope_name'] ?? 'Current executive scope') ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Visible records</span><span class="module-key-value"><?= number_format((int) ($stats['total'] ?? 0)) ?></span></div>
                        <div class="module-key-row"><span class="module-key-label">Reporting path</span><span class="module-key-value">/reports/meetings</span></div>
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
                                <div class="module-row-title">Schema-tolerant reports</div>
                                <div class="module-row-meta">The meeting report route now handles missing optional staging columns such as creator and platform data.</div>
                            </div>
                        </div>
                        <div class="module-stack-item">
                            <div>
                                <div class="module-row-title">Supported actions only</div>
                                <div class="module-row-meta">Scheduling, editing, and template flows remain intentionally hidden until their full UI paths are implemented.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>