<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$currentPage = 'reports';
$quickStats = $quick_stats ?? [];
$availableReports = $available_reports ?? [];
$recentReports = $recent_reports ?? [];
$userScope = $user_scope ?? [];

$reportUi = [
    'tasks' => ['icon' => 'bi-list-task', 'theme' => 'theme-tasks', 'label' => 'Activities'],
    'meetings' => ['icon' => 'bi-calendar-event', 'theme' => 'theme-meetings', 'label' => 'Activities'],
    'events' => ['icon' => 'bi-calendar2-week', 'theme' => 'theme-events', 'label' => 'Activities'],
    'donations' => ['icon' => 'bi-cash-coin', 'theme' => 'theme-donations', 'label' => 'Financial'],
    'users' => ['icon' => 'bi-people', 'theme' => 'theme-users', 'label' => 'Membership'],
    'hierarchy' => ['icon' => 'bi-diagram-3', 'theme' => 'theme-hierarchy', 'label' => 'Hierarchy'],
    'courses' => ['icon' => 'bi-mortarboard', 'theme' => 'theme-courses', 'label' => 'Education'],
];

$quickExportTypes = array_values(array_intersect(['donations', 'users', 'tasks'], array_keys($availableReports)));
?>

<div class="module-surface theme-reports">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-bar-chart-line"></i> Leader Analytics</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-graph-up-arrow me-2"></i>Reports & Analytics</h1>
                    <p class="module-subtitle">A unified reporting workspace with direct report routes, scoped exports, and a richer detail renderer for leader-facing modules.</p>
                </div>
                <div class="module-actions">
                    <a href="/reports" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
                    <?php if (in_array('tasks', $quickExportTypes, true)): ?>
                        <a href="/reports/export/tasks?format=csv" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Export Tasks</a>
                    <?php endif; ?>
                    <?php if (in_array('donations', $quickExportTypes, true)): ?>
                        <a href="/reports/export/donations?format=csv" class="btn btn-primary"><i class="bi bi-download me-1"></i>Export Donations</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($userScope['scope_name'] ?? 'Current hierarchy scope') ?></span>
                <span class="module-chip"><i class="bi bi-layers"></i><?= htmlspecialchars(ucfirst((string) ($userScope['level_scope'] ?? 'all'))) ?> level</span>
                <span class="module-chip"><i class="bi bi-grid"></i><?= number_format(count($availableReports)) ?> accessible report types</span>
            </div>
        </div>
    </section>

    <div class="module-callout">
        <strong>Supported analytics path:</strong> use the direct report cards and builder below to open stabilized report routes. The detail pages now render structured metrics and tables rather than raw debug JSON.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($quickStats['total_members'] ?? 0)) ?></div><div class="stat-label">Total Members</div></div><span class="stat-icon"><i class="bi bi-people"></i></span></div><div class="stat-footnote">Members visible inside this leader scope.</div></div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value">ETB <?= number_format((float) ($quickStats['monthly_donations'] ?? 0)) ?></div><div class="stat-label">Monthly Donations</div></div><span class="stat-icon"><i class="bi bi-cash-coin"></i></span></div><div class="stat-footnote">Current month donation volume in the visible scope.</div></div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($quickStats['active_tasks'] ?? 0)) ?></div><div class="stat-label">Active Tasks</div></div><span class="stat-icon"><i class="bi bi-list-task"></i></span></div><div class="stat-footnote">Open work items requiring leadership attention.</div></div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="module-stat-card"><div class="stat-topline"><div><div class="stat-value"><?= number_format((int) ($quickStats['upcoming_meetings'] ?? 0)) ?></div><div class="stat-label">Upcoming Meetings</div></div><span class="stat-icon"><i class="bi bi-calendar-event"></i></span></div><div class="stat-footnote">Meetings scheduled ahead within the current scope.</div></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-sliders me-2"></i>Report Builder</h2>
                </div>
                <div class="module-panel-body">
                    <div class="module-form-grid mb-3">
                        <div>
                            <label for="reportType">Report Type</label>
                            <select class="form-select" id="reportType">
                                <option value="">Choose a report</option>
                                <?php foreach ($availableReports as $key => $report): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($report['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="timePeriod">Time Window</label>
                            <select class="form-select" id="timePeriod">
                                <option value="7_days">Last 7 days</option>
                                <option value="30_days" selected>Last 30 days</option>
                                <option value="90_days">Last 90 days</option>
                                <option value="this_year">This year</option>
                                <option value="all">All available data</option>
                            </select>
                        </div>
                    </div>

                    <p class="module-muted-note">The builder opens the supported report route directly. Module-specific filters remain owned by each report endpoint.</p>

                    <div class="d-grid gap-2 mt-3">
                        <button type="button" class="btn btn-primary" onclick="openScopedReport()"><i class="bi bi-arrow-right-circle me-1"></i>Open Report</button>
                        <a href="/reports/donations" class="btn btn-outline-secondary"><i class="bi bi-stars me-1"></i>Open Donation Analytics</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-grid-3x3-gap me-2"></i>Accessible Reports</h2>
                    <span class="module-soft-badge"><i class="bi bi-check2-circle"></i>Direct routes only</span>
                </div>
                <div class="module-panel-body">
                    <div class="module-card-grid">
                        <?php foreach ($availableReports as $key => $report): ?>
                            <?php $meta = $reportUi[$key] ?? ['icon' => 'bi-file-earmark-text', 'theme' => 'theme-reports', 'label' => 'Report']; ?>
                            <a class="module-link-card <?= htmlspecialchars($meta['theme']) ?>" href="/reports/<?= htmlspecialchars($key) ?>">
                                <span class="module-link-icon"><i class="bi <?= htmlspecialchars($meta['icon']) ?>"></i></span>
                                <div class="module-row-title mb-2"><?= htmlspecialchars($report['title']) ?></div>
                                <div class="module-caption mb-3"><?= htmlspecialchars($report['description']) ?></div>
                                <span class="module-soft-badge"><?= htmlspecialchars($meta['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="module-panel">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-clock-history me-2"></i>Recent Reports</h2>
                </div>
                <div class="module-panel-body p-0">
                    <div class="table-responsive">
                        <table class="module-table">
                            <thead>
                                <tr>
                                    <th>Report</th>
                                    <th>Category</th>
                                    <th>Generated By</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentReports)): ?>
                                    <?php foreach ($recentReports as $report): ?>
                                        <?php $reportType = $report['type'] ?? $report['category'] ?? 'tasks'; ?>
                                        <tr>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars($report['name'] ?? 'Untitled Report') ?></div>
                                                <div class="module-row-meta"><?= htmlspecialchars($report['description'] ?? '') ?></div>
                                            </td>
                                            <td><span class="module-status status-neutral"><?= htmlspecialchars(ucfirst((string) ($report['category'] ?? 'report'))) ?></span></td>
                                            <td>
                                                <div class="module-row-title"><?= htmlspecialchars($report['generated_by_name'] ?? 'System') ?></div>
                                            </td>
                                            <td>
                                                <div class="module-row-title"><?= !empty($report['created_at']) ? htmlspecialchars(date('M j, Y', strtotime((string) $report['created_at']))) : 'Recently' ?></div>
                                            </td>
                                            <td>
                                                <span class="module-status <?= (($report['status'] ?? 'completed') === 'completed') ? 'status-success' : 'status-warning' ?>">
                                                    <?= htmlspecialchars(ucfirst((string) ($report['status'] ?? 'completed'))) ?>
                                                </span>
                                            </td>
                                            <td><a href="/reports/<?= htmlspecialchars($reportType) ?>" class="btn btn-sm btn-outline-primary">Open</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="module-empty py-4">
                                                <i class="bi bi-inbox"></i>
                                                <p class="mb-0 mt-2">No reports generated yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="module-panel h-100">
                <div class="module-panel-header">
                    <h2 class="module-panel-title"><i class="bi bi-download me-2"></i>Quick Exports</h2>
                </div>
                <div class="module-panel-body">
                    <?php if (!empty($quickExportTypes)): ?>
                        <div class="d-grid gap-2">
                            <?php foreach ($quickExportTypes as $type): ?>
                                <a href="/reports/export/<?= htmlspecialchars($type) ?>?format=csv" class="btn btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i><?= htmlspecialchars(ucfirst($type)) ?> CSV
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="module-muted-note mb-0">No scoped quick exports are currently available for this role.</p>
                    <?php endif; ?>

                    <div class="module-callout mt-3 mb-0">
                        <strong>Export note:</strong> only report types backed by live export helpers are shown here so this dashboard stays aligned with current staging behavior.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openScopedReport() {
    const type = document.getElementById('reportType').value;
    if (!type) {
        alert('Choose a supported report first.');
        return;
    }

    const params = new URLSearchParams();
    const dateRange = document.getElementById('timePeriod').value;
    if (dateRange) {
        params.set('date_range', dateRange);
    }

    window.location.href = `/reports/${encodeURIComponent(type)}?${params.toString()}`;
}
</script>