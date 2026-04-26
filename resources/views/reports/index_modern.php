<?php
require __DIR__ . '/index_shell.php';
return;

$currentPage = 'reports';
$quick_stats = $quick_stats ?? [];
$available_reports = $available_reports ?? [];
$report_ui = [
    'tasks' => ['icon' => 'bi-list-task', 'badge' => 'category-activities', 'label' => 'Activities'],
    'meetings' => ['icon' => 'bi-calendar-event', 'badge' => 'category-activities', 'label' => 'Activities'],
    'events' => ['icon' => 'bi-calendar2-week', 'badge' => 'category-activities', 'label' => 'Activities'],
    'donations' => ['icon' => 'bi-cash-coin', 'badge' => 'category-financial', 'label' => 'Financial'],
    'users' => ['icon' => 'bi-people', 'badge' => 'category-membership', 'label' => 'Membership'],
    'hierarchy' => ['icon' => 'bi-diagram-3', 'badge' => 'category-performance', 'label' => 'Hierarchy'],
    'courses' => ['icon' => 'bi-mortarboard', 'badge' => 'category-compliance', 'label' => 'Education'],
];
$quick_export_types = array_values(array_intersect(['donations', 'users', 'tasks'], array_keys($available_reports)));
?>

<!-- Modern Reports Management Interface -->
<style>
    .report-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }
    
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -2px rgba(0, 0, 0, 0.15);
    }
    
    .report-financial {
        background: linear-gradient(135deg, rgba(139, 21, 56, 0.05) 0%, white 50%);
        border-left: 4px solid var(--primary-red);
    }
    
    .report-membership {
        border-left: 4px solid var(--primary-green);
    }
    
    .report-activities {
        border-left: 4px solid #3b82f6;
    }
    
    .report-performance {
        border-left: 4px solid #8b5cf6;
    }
    
    .report-compliance {
        border-left: 4px solid #f59e0b;
    }
    
    .chart-container {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        height: 400px;
    }
    
    .chart-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .chart-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .chart-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    
    .kpi-metric {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .kpi-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    
    .kpi-value {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    
    .kpi-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .kpi-trend {
        font-size: 0.75rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }
    
    .kpi-trend.positive {
        color: #10b981;
    }
    
    .kpi-trend.negative {
        color: #ef4444;
    }
    
    .kpi-trend.neutral {
        color: #6b7280;
    }
    
    .report-filter-panel {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .dashboard-section {
        background: #f8fafc;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .dashboard-section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .report-category-badge {
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .category-financial {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border-color: rgba(139, 21, 56, 0.2);
    }
    
    .category-membership {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border-color: rgba(45, 80, 22, 0.2);
    }
    
    .category-activities {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .category-performance {
        background: rgba(139, 92, 246, 0.1);
        color: #7c3aed;
        border-color: rgba(139, 92, 246, 0.2);
    }
    
    .category-compliance {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border-color: rgba(245, 158, 11, 0.2);
    }
    
    .export-button {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(45, 80, 22, 0.2);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .export-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .export-button.secondary {
        background: linear-gradient(135deg, #6b7280, #9ca3af);
        box-shadow: 0 4px 8px rgba(107, 114, 128, 0.2);
    }
    
    .export-button.secondary:hover {
        box-shadow: 0 8px 16px rgba(107, 114, 128, 0.3);
    }
    
    .report-preview {
        background: #f8fafc;
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .report-preview:hover {
        border-color: var(--primary-green);
        background: rgba(45, 80, 22, 0.05);
    }
    
    .report-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .table-modern {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .table-modern th {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #374151;
        border: none;
    }
    
    .table-modern td {
        padding: 1rem 1.5rem;
        border: none;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table-modern tbody tr:last-child td {
        border-bottom: none;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
    }
</style>

<div class="page-header">
    <h1 class="page-title">Reports & Analytics</h1>
    <p class="page-description">Comprehensive reporting dashboard with advanced analytics, data visualization, and export capabilities</p>
</div>

<!-- Key Performance Indicators -->
<div class="dashboard-section">
    <div class="dashboard-section-title">
        <i class="bi bi-graph-up-arrow"></i> Key Performance Indicators
    </div>
    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="kpi-metric">
                <div class="kpi-value" style="color: var(--primary-green);"><?= number_format($quick_stats['total_members'] ?? 0) ?></div>
                <div class="kpi-label">Total Members</div>
                <div class="kpi-trend neutral">
                    <i class="bi bi-people"></i> In current access scope
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-metric">
                <div class="kpi-value" style="color: var(--primary-red);">ETB <?= number_format($quick_stats['monthly_donations'] ?? 0) ?></div>
                <div class="kpi-label">Monthly Donations</div>
                <div class="kpi-trend neutral">
                    <i class="bi bi-cash-coin"></i> Current month total
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-metric">
                <div class="kpi-value" style="color: #3b82f6;"><?= number_format($quick_stats['active_tasks'] ?? 0) ?></div>
                <div class="kpi-label">Active Tasks</div>
                <div class="kpi-trend neutral">
                    <i class="bi bi-list-task"></i> Open work items
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-metric">
                <div class="kpi-value" style="color: #8b5cf6;"><?= number_format($quick_stats['upcoming_meetings'] ?? 0) ?></div>
                <div class="kpi-label">Upcoming Meetings</div>
                <div class="kpi-trend neutral">
                    <i class="bi bi-calendar-event"></i> Scheduled ahead
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Filters -->
<div class="report-filter-panel mb-4">
    <div class="row align-items-end g-3">
        <div class="col-md-2">
            <label class="form-label fw-500">Report Type</label>
            <select class="form-select" id="reportType" onchange="updateReportOptions()">
                <option value="">Choose a report</option>
                <?php foreach ($available_reports as $key => $report): ?>
                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($report['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-500">Time Period</label>
            <select class="form-select" id="timePeriod">
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="this_quarter">This Quarter</option>
                <option value="this_year">This Year</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-500">From Date</label>
            <input type="date" class="form-select" id="fromDate" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-500">To Date</label>
            <input type="date" class="form-select" id="toDate" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-500">Hierarchy Level</label>
            <select class="form-select" id="hierarchyLevel">
                <option value="">All Levels</option>
                <option value="godina">Godina Level</option>
                <option value="gamta">Gamta Level</option>
                <option value="gurmu">Gurmu Level</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" onclick="generateReport()">
                <i class="bi bi-search"></i> Generate
            </button>
        </div>
    </div>
</div>

<!-- Report Categories Grid -->
<div class="row g-4 mb-5" id="reportCategories">
    <?php foreach ($available_reports as $key => $report): ?>
        <?php $meta = $report_ui[$key] ?? ['icon' => 'bi-file-earmark-text', 'badge' => 'category-performance', 'label' => 'Report']; ?>
        <div class="col-lg-4 col-md-6">
            <a class="report-card d-block text-decoration-none" href="/reports/<?= htmlspecialchars($key) ?>">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="report-icon">
                            <i class="bi <?= htmlspecialchars($meta['icon']) ?>"></i>
                        </div>
                        <span class="report-category-badge <?= htmlspecialchars($meta['badge']) ?>"><?= htmlspecialchars($meta['label']) ?></span>
                    </div>
                    <h5 class="card-title fw-600 mb-2"><?= htmlspecialchars($report['title']) ?></h5>
                    <p class="card-text text-muted mb-3"><?= htmlspecialchars($report['description']) ?></p>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Open report</span>
                        <span class="fw-600"><i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
    <div class="col-lg-4 col-md-6">
        <div class="report-card" onclick="showReportSupportNotice()">
            <div class="card-body p-4 text-center">
                <div class="report-preview">
                    <div class="report-icon">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <h5 class="fw-600 mb-2">Filtered Report Builder</h5>
                    <p class="text-muted mb-0">Use the selector above to open a supported report with scoped filters.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Dashboard -->
<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Monthly Trends</h3>
                    <p class="chart-subtitle">Revenue, Members, and Activities over time</p>
                </div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="chartPeriod" id="chartMonthly" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary btn-sm" for="chartMonthly">Monthly</label>
                    
                    <input type="radio" class="btn-check" name="chartPeriod" id="chartQuarterly" autocomplete="off">
                    <label class="btn btn-outline-secondary btn-sm" for="chartQuarterly">Quarterly</label>
                    
                    <input type="radio" class="btn-check" name="chartPeriod" id="chartYearly" autocomplete="off">
                    <label class="btn btn-outline-secondary btn-sm" for="chartYearly">Yearly</label>
                </div>
            </div>
            <canvas id="trendsChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Member Distribution</h3>
                    <p class="chart-subtitle">By hierarchy level</p>
                </div>
            </div>
            <canvas id="distributionChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
</div>

<!-- Recent Reports Table -->
<div class="dashboard-section">
    <div class="dashboard-section-title">
        <i class="bi bi-clock-history"></i> Recent Reports
    </div>
    <div class="table-modern">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Report Name</th>
                    <th>Category</th>
                    <th>Generated By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_reports)): ?>
                    <?php foreach ($recent_reports as $report): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                    <div>
                                        <div class="fw-600"><?= htmlspecialchars($report['name'] ?? 'Untitled Report') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($report['description'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="report-category-badge category-<?= $report['category'] ?? 'financial' ?>">
                                    <?= ucfirst($report['category'] ?? 'financial') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($report['generated_by_name'] ?? 'System') ?></td>
                            <td><?= date('M j, Y', strtotime($report['created_at'] ?? '')) ?></td>
                            <td>
                                <span class="badge bg-<?= ($report['status'] ?? 'completed') === 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($report['status'] ?? 'completed') ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php $reportType = $report['type'] ?? $report['category'] ?? 'tasks'; ?>
                                    <a href="/reports/<?= htmlspecialchars($reportType) ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="/reports/export/<?= htmlspecialchars($reportType) ?>?format=pdf" class="btn btn-outline-secondary">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox"></i> No reports generated yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Export Panel -->
<div class="row g-3 mt-4">
    <div class="col-md-12">
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <?php foreach ($quick_export_types as $index => $type): ?>
                <?php $format = $index === 0 ? 'pdf' : ($index === 1 ? 'excel' : 'csv'); ?>
                <a href="/reports/export/<?= htmlspecialchars($type) ?>?format=<?= $format ?>" class="export-button<?= $index === 1 ? ' secondary' : '' ?>">
                    <i class="bi bi-download"></i> <?= htmlspecialchars(ucfirst($type)) ?> <?= strtoupper($format) ?>
                </a>
            <?php endforeach; ?>
            <button class="export-button secondary" onclick="showReportSupportNotice()">
                <i class="bi bi-info-circle"></i> Supported Exports Only
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeTrendsChart();
    initializeDistributionChart();
});

function selectReportCategory(category) {
    document.getElementById('reportType').value = category;
    // Scroll to filters
    document.querySelector('.report-filter-panel').scrollIntoView({ behavior: 'smooth' });
}

function updateReportOptions() {
    const reportType = document.getElementById('reportType').value;
    // Update available options based on report type
    console.log('Updating options for:', reportType);
}

function generateReport() {
    const type = document.getElementById('reportType').value;
    if (!type) {
        alert('Choose a supported report before generating.');
        return;
    }

    const params = new URLSearchParams();
    params.set('date_range', document.getElementById('timePeriod').value);
    params.set('from', document.getElementById('fromDate').value);
    params.set('to', document.getElementById('toDate').value);

    const hierarchy = document.getElementById('hierarchyLevel').value;
    if (hierarchy) {
        params.set('hierarchy_level', hierarchy);
    }

    window.location.href = `/reports/${encodeURIComponent(type)}?${params.toString()}`;
}

function showReportSupportNotice() {
    alert('This staging build currently supports direct report pages and export links only.');
}

function initializeTrendsChart() {
    // Chart.js implementation would go here
    console.log('Initializing trends chart...');
}

function initializeDistributionChart() {
    // Chart.js implementation would go here
    console.log('Initializing distribution chart...');
}
</script>