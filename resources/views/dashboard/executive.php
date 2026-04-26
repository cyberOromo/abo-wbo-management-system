<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$user = $user ?? [];
$userScope = $user_scope ?? [];
$myTasks = $my_tasks ?? [];
$myMeetings = $my_meetings ?? [];
$myReports = $my_reports ?? [];
$hierarchyMembers = $hierarchy_members ?? [];
$positionResponsibilities = $position_responsibilities ?? [];
$recentActivities = $recent_activities ?? [];
$hierarchyStats = $hierarchy_stats ?? [];

$featuredActivity = $recentActivities[0] ?? null;
$responsibilityPreview = array_slice($positionResponsibilities, 0, 5);

$positionSpecificCards = [];
if (!empty($financial_data ?? null)) {
    $positionSpecificCards[] = [
        'title' => 'Financial Oversight',
        'description' => 'Review donation movement and financial-facing reporting for your current hierarchy scope.',
        'icon' => 'bi-cash-stack',
        'href' => '/donations',
        'action' => 'Open Donations',
    ];
}
if (!empty($media_data ?? null)) {
    $positionSpecificCards[] = [
        'title' => 'Media & Publications',
        'description' => 'Track communication-facing events and outbound visibility inside the current scope.',
        'icon' => 'bi-megaphone',
        'href' => '/events',
        'action' => 'Open Events',
    ];
}
if (!empty($leadership_data ?? null)) {
    $positionSpecificCards[] = [
        'title' => 'Leadership Overview',
        'description' => 'Use reporting and responsibilities to coordinate leadership priorities across the current hierarchy.',
        'icon' => 'bi-diagram-3',
        'href' => '/reports',
        'action' => 'Open Reports',
    ];
}
?>

<div class="module-surface theme-executive">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-person-badge"></i> Executive Workspace</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-person-badge me-2"></i><?= htmlspecialchars($userScope['position_name'] ?? 'Executive') ?> Dashboard</h1>
                    <p class="module-subtitle">A unified command surface for leadership activity, scoped operations, and direct access to the modernized task, meeting, event, donation, and reporting modules.</p>
                </div>
                <div class="module-actions">
                    <a href="/reports" class="btn btn-outline-secondary"><i class="bi bi-graph-up me-1"></i>Reports Hub</a>
                    <a href="/tasks" class="btn btn-primary"><i class="bi bi-list-task me-1"></i>Open Tasks</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-person"></i><?= htmlspecialchars($user['first_name'] ?? 'Executive') ?></span>
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($userScope['scope_name'] ?? 'Organization Scope') ?></span>
                <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) ($userScope['level_scope'] ?? 'executive'))) ?> level</span>
            </div>
        </div>
    </section>

    <div class="module-callout">
        <strong>Workspace alignment:</strong> the executive dashboard now uses the same surface language as the upgraded leader modules, so task, meeting, event, donation, and reporting entry points all feel like one connected system.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format(count($myTasks)) ?></div>
                        <div class="stat-label">My Tasks</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-list-task"></i></span>
                </div>
                <div class="stat-footnote">Visible tasks within your current executive scope.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format(count($myMeetings)) ?></div>
                        <div class="stat-label">My Meetings</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-calendar-event"></i></span>
                </div>
                <div class="stat-footnote">Meetings directly visible from this dashboard scope.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format(count($hierarchyMembers)) ?></div>
                        <div class="stat-label">Team Members</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-people"></i></span>
                </div>
                <div class="stat-footnote">Members reachable inside your current leadership span.</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card">
                <div class="stat-topline">
                    <div>
                        <div class="stat-value"><?= number_format(count($myReports)) ?></div>
                        <div class="stat-label">Reports</div>
                    </div>
                    <span class="stat-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                </div>
                <div class="stat-footnote">Available scoped reports and analytics routes.</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-lightning-charge me-2"></i>Executive Launchpad</h2>
                    <span class="module-soft-badge"><i class="bi bi-grid"></i>Unified module access</span>
                </div>
                <div class="module-panel-body">
                    <div class="module-card-grid">
                        <a class="module-link-card" href="/tasks">
                            <span class="module-link-icon"><i class="bi bi-list-task"></i></span>
                            <div class="module-row-title mb-2">Task Management</div>
                            <div class="module-caption mb-3">Move directly into the unified task workspace for scoped execution and delivery tracking.</div>
                            <span class="module-soft-badge"><?= number_format(count($myTasks)) ?> visible tasks</span>
                        </a>
                        <a class="module-link-card" href="/meetings">
                            <span class="module-link-icon"><i class="bi bi-calendar-event"></i></span>
                            <div class="module-row-title mb-2">Meeting Management</div>
                            <div class="module-caption mb-3">Review meeting visibility, agenda context, and the stabilized meeting analytics path.</div>
                            <span class="module-soft-badge"><?= number_format(count($myMeetings)) ?> visible meetings</span>
                        </a>
                        <a class="module-link-card" href="/events">
                            <span class="module-link-icon"><i class="bi bi-calendar2-week"></i></span>
                            <div class="module-row-title mb-2">Event Management</div>
                            <div class="module-caption mb-3">Track current event records and open the event analytics flow from the same visual system.</div>
                            <span class="module-soft-badge"><?= number_format((int) ($hierarchyStats['completed_events'] ?? 0)) ?> completed events</span>
                        </a>
                        <a class="module-link-card" href="/reports">
                            <span class="module-link-icon"><i class="bi bi-graph-up-arrow"></i></span>
                            <div class="module-row-title mb-2">Reports & Analytics</div>
                            <div class="module-caption mb-3">Open the structured analytics hub for tasks, meetings, events, donations, and hierarchy reporting.</div>
                            <span class="module-soft-badge"><?= number_format(count($myReports)) ?> report entries</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-bullseye me-2"></i>Scope Summary</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-key-grid">
                        <div class="module-key-row">
                            <span class="module-key-label">Position</span>
                            <span class="module-key-value"><?= htmlspecialchars($userScope['position_name'] ?? 'Executive Position') ?></span>
                        </div>
                        <div class="module-key-row">
                            <span class="module-key-label">Scope</span>
                            <span class="module-key-value"><?= htmlspecialchars($userScope['scope_name'] ?? 'Organization Scope') ?></span>
                        </div>
                        <div class="module-key-row">
                            <span class="module-key-label">Access level</span>
                            <span class="module-key-value"><?= htmlspecialchars(ucfirst((string) ($userScope['level_scope'] ?? 'executive'))) ?></span>
                        </div>
                    </div>

                    <?php if ($featuredActivity): ?>
                        <div class="module-callout mt-4 mb-0">
                            <strong>Latest visible activity:</strong>
                            <div class="module-row-title mt-2"><?= htmlspecialchars($featuredActivity['title'] ?? 'Activity') ?></div>
                            <div class="module-row-meta"><?= htmlspecialchars($featuredActivity['description'] ?? 'No description available.') ?></div>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mt-4 mb-0">Recent activities will appear here as your scoped modules accumulate more live records.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($positionSpecificCards)): ?>
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="module-panel">
                    <div class="module-panel-header">
                        <h2 class="module-panel-title"><i class="bi bi-stars me-2"></i>Position-Specific Focus</h2>
                    </div>
                    <div class="module-panel-body">
                        <div class="module-card-grid">
                            <?php foreach ($positionSpecificCards as $card): ?>
                                <a class="module-link-card" href="<?= htmlspecialchars($card['href']) ?>">
                                    <span class="module-link-icon"><i class="bi <?= htmlspecialchars($card['icon']) ?>"></i></span>
                                    <div class="module-row-title mb-2"><?= htmlspecialchars($card['title']) ?></div>
                                    <div class="module-caption mb-3"><?= htmlspecialchars($card['description']) ?></div>
                                    <span class="module-soft-badge"><?= htmlspecialchars($card['action']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-check2-square me-2"></i>Responsibilities</h2>
                    <a href="/responsibilities" class="btn btn-sm btn-outline-secondary">Open</a>
                </div>
                <div class="module-panel-body">
                    <?php if (!empty($responsibilityPreview)): ?>
                        <div class="module-stack-list">
                            <?php foreach ($responsibilityPreview as $responsibility): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars($responsibility) ?></div>
                                        <div class="module-row-meta">Leadership responsibility in the current position profile.</div>
                                    </div>
                                    <div class="module-stack-value"><i class="bi bi-check2-circle"></i></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($positionResponsibilities) > count($responsibilityPreview)): ?>
                            <p class="module-muted-note mt-3 mb-0">+<?= number_format(count($positionResponsibilities) - count($responsibilityPreview)) ?> more responsibilities are available in the full responsibilities module.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="module-empty py-4">
                            <i class="bi bi-inbox"></i>
                            <p class="mb-0 mt-2">No specific responsibilities are defined for this position yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-bar-chart me-2"></i>Hierarchy Statistics</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-metric-grid">
                        <div class="module-metric-card">
                            <div class="module-metric-label">Total Members</div>
                            <div class="module-metric-value"><?= number_format((int) ($hierarchyStats['total_members'] ?? 0)) ?></div>
                        </div>
                        <div class="module-metric-card">
                            <div class="module-metric-label">Active Tasks</div>
                            <div class="module-metric-value"><?= number_format((int) ($hierarchyStats['active_tasks'] ?? 0)) ?></div>
                        </div>
                        <div class="module-metric-card">
                            <div class="module-metric-label">Upcoming Meetings</div>
                            <div class="module-metric-value"><?= number_format((int) ($hierarchyStats['upcoming_meetings'] ?? 0)) ?></div>
                        </div>
                        <div class="module-metric-card">
                            <div class="module-metric-label">Completed Events</div>
                            <div class="module-metric-value"><?= number_format((int) ($hierarchyStats['completed_events'] ?? 0)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-clock-history me-2"></i>Recent Activities</h2>
                </div>
                <div class="module-panel-body">
                    <?php if (!empty($recentActivities)): ?>
                        <div class="module-stack-list">
                            <?php foreach (array_slice($recentActivities, 0, 5) as $activity): ?>
                                <div class="module-stack-item">
                                    <div>
                                        <div class="module-row-title"><?= htmlspecialchars($activity['title'] ?? 'Activity') ?></div>
                                        <div class="module-row-meta"><?= htmlspecialchars($activity['description'] ?? 'No description') ?></div>
                                    </div>
                                    <div class="module-stack-value"><?= htmlspecialchars($activity['date'] ?? 'Recently') ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="module-empty py-4">
                            <i class="bi bi-inbox"></i>
                            <p class="mb-0 mt-2">No recent activities are visible yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>