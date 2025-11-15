<?php
/**
 * Reports Index View Template
 * Comprehensive analytics dashboard with data visualization and export functionality
 */

// Page metadata
$pageTitle = __('reports.title');
$pageDescription = __('reports.description');
$bodyClass = 'reports-page';

// Report data
$reportCategories = $reportCategories ?? [];
$quickStats = $quickStats ?? [];
$recentReports = $recentReports ?? [];
$dashboardData = $dashboardData ?? [];

// User permissions
$canViewReports = $permissions['can_view_reports'] ?? false;
$canCreateReports = $permissions['can_create_reports'] ?? false;
$canExportReports = $permissions['can_export_reports'] ?? false;

// Report categories with icons and colors
$categories = [
    'financial' => [
        'name' => __('reports.financial'),
        'color' => 'success',
        'icon' => 'currency-dollar',
        'description' => __('reports.financial_description')
    ],
    'membership' => [
        'name' => __('reports.membership'),
        'color' => 'primary',
        'icon' => 'people',
        'description' => __('reports.membership_description')
    ],
    'activities' => [
        'name' => __('reports.activities'),
        'color' => 'info',
        'icon' => 'calendar-event',
        'description' => __('reports.activities_description')
    ],
    'performance' => [
        'name' => __('reports.performance'),
        'color' => 'warning',
        'icon' => 'graph-up',
        'description' => __('reports.performance_description')
    ],
    'compliance' => [
        'name' => __('reports.compliance'),
        'color' => 'danger',
        'icon' => 'shield-check',
        'description' => __('reports.compliance_description')
    ],
    'custom' => [
        'name' => __('reports.custom'),
        'color' => 'secondary',
        'icon' => 'gear',
        'description' => __('reports.custom_description')
    ]
];

// Predefined reports
$predefinedReports = [
    'membership_growth' => [
        'name' => __('reports.membership_growth'),
        'category' => 'membership',
        'description' => __('reports.membership_growth_desc'),
        'icon' => 'graph-up-arrow'
    ],
    'financial_summary' => [
        'name' => __('reports.financial_summary'),
        'category' => 'financial',
        'description' => __('reports.financial_summary_desc'),
        'icon' => 'cash-stack'
    ],
    'event_attendance' => [
        'name' => __('reports.event_attendance'),
        'category' => 'activities',
        'description' => __('reports.event_attendance_desc'),
        'icon' => 'calendar-check'
    ],
    'donation_analytics' => [
        'name' => __('reports.donation_analytics'),
        'category' => 'financial',
        'description' => __('reports.donation_analytics_desc'),
        'icon' => 'gift'
    ],
    'task_completion' => [
        'name' => __('reports.task_completion'),
        'category' => 'performance',
        'description' => __('reports.task_completion_desc'),
        'icon' => 'check-circle'
    ],
    'user_activity' => [
        'name' => __('reports.user_activity'),
        'category' => 'performance',
        'description' => __('reports.user_activity_desc'),
        'icon' => 'activity'
    ]
];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('reports.analytics_dashboard') ?></h1>
        <p class="text-muted mb-0"><?= __('reports.comprehensive_insights') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canCreateReports): ?>
            <a href="/reports/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?= __('reports.create_custom') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> <?= __('reports.export') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/reports/export/dashboard?format=pdf">
                    <i class="bi bi-file-earmark-pdf me-2"></i><?= __('reports.export_dashboard_pdf') ?>
                </a></li>
                <li><a class="dropdown-item" href="/reports/export/dashboard?format=excel">
                    <i class="bi bi-file-earmark-excel me-2"></i><?= __('reports.export_dashboard_excel') ?>
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/reports/scheduled">
                    <i class="bi bi-clock me-2"></i><?= __('reports.scheduled_reports') ?>
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Quick Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('reports.total_members') ?></h5>
                        <h2 class="mb-0"><?= number_format($quickStats['total_members'] ?? 0) ?></h2>
                        <small class="opacity-75">
                            <i class="bi bi-arrow-up"></i> 
                            <?= $quickStats['members_growth'] ?? 0 ?>% <?= __('reports.this_month') ?>
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-people fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-gradient-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('reports.total_revenue') ?></h5>
                        <h2 class="mb-0"><?= format_currency($quickStats['total_revenue'] ?? 0) ?></h2>
                        <small class="opacity-75">
                            <i class="bi bi-arrow-up"></i> 
                            <?= $quickStats['revenue_growth'] ?? 0 ?>% <?= __('reports.this_month') ?>
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-currency-dollar fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-gradient-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('reports.active_projects') ?></h5>
                        <h2 class="mb-0"><?= number_format($quickStats['active_projects'] ?? 0) ?></h2>
                        <small class="opacity-75">
                            <?= $quickStats['completed_projects'] ?? 0 ?> <?= __('reports.completed') ?>
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-briefcase fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('reports.engagement_rate') ?></h5>
                        <h2 class="mb-0"><?= number_format($quickStats['engagement_rate'] ?? 0) ?>%</h2>
                        <small class="opacity-75">
                            <?= __('reports.avg_activity_level') ?>
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-activity fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Dashboard Content -->
<div class="row g-4 mb-4">
    <!-- Membership Analytics -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><?= __('reports.membership_analytics') ?></h6>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="period" id="period-month" checked>
                    <label class="btn btn-outline-primary" for="period-month"><?= __('reports.month') ?></label>
                    <input type="radio" class="btn-check" name="period" id="period-quarter">
                    <label class="btn btn-outline-primary" for="period-quarter"><?= __('reports.quarter') ?></label>
                    <input type="radio" class="btn-check" name="period" id="period-year">
                    <label class="btn btn-outline-primary" for="period-year"><?= __('reports.year') ?></label>
                </div>
            </div>
            <div class="card-body">
                <canvas id="membershipChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0"><?= __('reports.recent_activity') ?></h6>
            </div>
            <div class="card-body">
                <div class="activity-timeline">
                    <?php if (!empty($recentReports)): ?>
                        <?php foreach (array_slice($recentReports, 0, 8) as $activity): ?>
                            <div class="activity-item d-flex align-items-start mb-3">
                                <div class="activity-icon me-3">
                                    <div class="icon-circle bg-<?= $activity['type_color'] ?>">
                                        <i class="bi bi-<?= $activity['icon'] ?> text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                                    <small class="text-muted d-block"><?= htmlspecialchars($activity['description']) ?></small>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= time_ago($activity['created_at']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-activity display-6"></i>
                            <p class="mt-2"><?= __('reports.no_recent_activity') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Overview -->
<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0"><?= __('reports.revenue_breakdown') ?></h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0"><?= __('reports.geographical_distribution') ?></h6>
            </div>
            <div class="card-body">
                <div id="geographicalMap" style="height: 250px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Report Categories -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0"><?= __('reports.report_categories') ?></h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($categories as $categoryKey => $category): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="report-category-card h-100" data-category="<?= $categoryKey ?>">
                        <div class="card border-<?= $category['color'] ?>">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="category-icon me-3">
                                        <div class="icon-circle bg-<?= $category['color'] ?>">
                                            <i class="bi bi-<?= $category['icon'] ?> text-white fs-4"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1"><?= $category['name'] ?></h5>
                                        <small class="text-muted"><?= $category['description'] ?></small>
                                    </div>
                                </div>
                                
                                <div class="category-stats mb-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-6">
                                            <div class="stat-box">
                                                <div class="stat-value text-<?= $category['color'] ?>">
                                                    <?= $reportCategories[$categoryKey]['count'] ?? 0 ?>
                                                </div>
                                                <small class="text-muted"><?= __('reports.reports') ?></small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat-box">
                                                <div class="stat-value text-<?= $category['color'] ?>">
                                                    <?= $reportCategories[$categoryKey]['recent'] ?? 0 ?>
                                                </div>
                                                <small class="text-muted"><?= __('reports.recent') ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="/reports/category/<?= $categoryKey ?>" 
                                       class="btn btn-outline-<?= $category['color'] ?> btn-sm flex-fill">
                                        <?= __('reports.view_reports') ?>
                                    </a>
                                    <?php if ($canCreateReports): ?>
                                        <a href="/reports/create?category=<?= $categoryKey ?>" 
                                           class="btn btn-<?= $category['color'] ?> btn-sm">
                                            <i class="bi bi-plus"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Predefined Reports -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0"><?= __('reports.predefined_reports') ?></h6>
        <button class="btn btn-sm btn-outline-primary" id="refreshReports">
            <i class="bi bi-arrow-clockwise"></i> <?= __('reports.refresh') ?>
        </button>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($predefinedReports as $reportKey => $report): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card report-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="report-icon me-3">
                                    <i class="bi bi-<?= $report['icon'] ?> fs-3 text-<?= $categories[$report['category']]['color'] ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1"><?= $report['name'] ?></h6>
                                    <small class="text-muted"><?= $report['description'] ?></small>
                                </div>
                            </div>
                            
                            <div class="report-actions d-flex gap-2">
                                <button class="btn btn-primary btn-sm flex-fill generate-report" 
                                        data-report="<?= $reportKey ?>">
                                    <i class="bi bi-play-circle"></i> <?= __('reports.generate') ?>
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item schedule-report" href="#" 
                                               data-report="<?= $reportKey ?>">
                                            <i class="bi bi-clock me-2"></i><?= __('reports.schedule') ?>
                                        </a></li>
                                        <li><a class="dropdown-item customize-report" href="#" 
                                               data-report="<?= $reportKey ?>">
                                            <i class="bi bi-gear me-2"></i><?= __('reports.customize') ?>
                                        </a></li>
                                        <?php if ($canExportReports): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item export-report" href="#" 
                                                   data-report="<?= $reportKey ?>" data-format="pdf">
                                                <i class="bi bi-file-earmark-pdf me-2"></i><?= __('reports.export_pdf') ?>
                                            </a></li>
                                            <li><a class="dropdown-item export-report" href="#" 
                                                   data-report="<?= $reportKey ?>" data-format="excel">
                                                <i class="bi bi-file-earmark-excel me-2"></i><?= __('reports.export_excel') ?>
                                            </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Report Generation Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('reports.generate_report') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportGenerationForm">
                    <input type="hidden" id="report_type" name="report_type">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= __('reports.date_range') ?></label>
                            <select class="form-select" name="date_range" id="dateRange">
                                <option value="last_7_days"><?= __('reports.last_7_days') ?></option>
                                <option value="last_30_days" selected><?= __('reports.last_30_days') ?></option>
                                <option value="last_3_months"><?= __('reports.last_3_months') ?></option>
                                <option value="last_6_months"><?= __('reports.last_6_months') ?></option>
                                <option value="last_year"><?= __('reports.last_year') ?></option>
                                <option value="custom"><?= __('reports.custom_range') ?></option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __('reports.format') ?></label>
                            <select class="form-select" name="format">
                                <option value="html"><?= __('reports.html_view') ?></option>
                                <option value="pdf"><?= __('reports.pdf_download') ?></option>
                                <option value="excel"><?= __('reports.excel_download') ?></option>
                                <option value="csv"><?= __('reports.csv_download') ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3" id="customDateRange" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label"><?= __('reports.start_date') ?></label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __('reports.end_date') ?></label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                    
                    <div id="reportParameters">
                        <!-- Dynamic parameters will be loaded based on report type -->
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_charts" 
                                   value="1" id="include_charts" checked>
                            <label class="form-check-label" for="include_charts">
                                <?= __('reports.include_charts') ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="email_report" 
                                   value="1" id="email_report">
                            <label class="form-check-label" for="email_report">
                                <?= __('reports.email_when_ready') ?>
                            </label>
                        </div>
                    </div>
                </form>
                
                <div id="reportProgress" style="display: none;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="spinner-border spinner-border-sm me-3" role="status"></div>
                        <span><?= __('reports.generating_report') ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= __('common.cancel') ?>
                </button>
                <button type="button" class="btn btn-primary" id="generateReportBtn">
                    <i class="bi bi-play-circle"></i> <?= __('reports.generate') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reports Styles -->
<style>
.stats-card {
    border: none;
    transition: transform 0.2s ease;
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark, #0056b3) 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.report-category-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.report-category-card:hover {
    transform: translateY(-2px);
}

.report-category-card:hover .card {
    border-width: 2px;
}

.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.activity-timeline {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item:last-child {
    margin-bottom: 0 !important;
}

.activity-icon .icon-circle {
    width: 32px;
    height: 32px;
}

.report-card {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
}

.report-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.stat-box {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1;
}

.category-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.category-filter {
    transition: all 0.2s ease;
}

.category-filter.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

#geographicalMap {
    background: #f8f9fa;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

@media (max-width: 768px) {
    .stats-card .card-body {
        padding: 1rem;
    }
    
    .activity-timeline {
        max-height: 300px;
    }
    
    .report-actions {
        flex-direction: column;
    }
    
    .report-actions .btn {
        width: 100%;
    }
}
</style>

<!-- Reports JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeMembershipChart();
    initializeRevenueChart();
    
    // Date range selector
    document.getElementById('dateRange').addEventListener('change', function() {
        const customRange = document.getElementById('customDateRange');
        if (this.value === 'custom') {
            customRange.style.display = 'block';
        } else {
            customRange.style.display = 'none';
        }
    });
    
    // Period change for membership chart
    document.querySelectorAll('input[name="period"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                updateMembershipChart(this.id.replace('period-', ''));
            }
        });
    });
    
    // Report generation
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('generate-report') || e.target.closest('.generate-report')) {
            e.preventDefault();
            const reportType = e.target.dataset.report || e.target.closest('.generate-report').dataset.report;
            openReportModal(reportType);
        }
        
        if (e.target.classList.contains('export-report') || e.target.closest('.export-report')) { 
            e.preventDefault();
            const reportType = e.target.dataset.report || e.target.closest('.export-report').dataset.report;
            const format = e.target.dataset.format || e.target.closest('.export-report').dataset.format;
            exportReport(reportType, format);
        }
        
        if (e.target.classList.contains('schedule-report') || e.target.closest('.schedule-report')) {
            e.preventDefault();
            const reportType = e.target.dataset.report || e.target.closest('.schedule-report').dataset.report;
            // Open schedule modal - implement as needed
            console.log('Schedule report:', reportType);
        }
    });
    
    // Generate report button
    document.getElementById('generateReportBtn').addEventListener('click', function() {
        const form = document.getElementById('reportGenerationForm');
        const formData = new FormData(form);
        generateReport(formData);
    });
    
    // Functions
    function initializeMembershipChart() {
        const ctx = document.getElementById('membershipChart');
        if (!ctx) return;
        
        const chartData = <?= json_encode($dashboardData['membership_data'] ?? []) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: '<?= __('reports.new_members') ?>',
                    data: chartData.new_members || [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: '<?= __('reports.total_members') ?>',
                    data: chartData.total_members || [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '<?= __('reports.number_of_members') ?>'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }
    
    function initializeRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;
        
        const chartData = <?= json_encode($dashboardData['revenue_data'] ?? []) ?>;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    data: chartData.values || [],
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#6f42c1',
                        '#fd7e14'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    function updateMembershipChart(period) {
        // Fetch new data based on period
        fetch(`/api/reports/membership-data?period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update chart with new data
                    // Implementation depends on Chart.js version
                    console.log('Update chart for period:', period);
                }
            })
            .catch(error => {
                console.error('Error updating chart:', error);
            });
    }
    
    function openReportModal(reportType) {
        document.getElementById('report_type').value = reportType;
        
        // Load report-specific parameters
        fetch(`/api/reports/parameters/${reportType}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.parameters) {
                    const container = document.getElementById('reportParameters');
                    container.innerHTML = data.parameters;
                }
            });
        
        const modal = new bootstrap.Modal(document.getElementById('reportModal'));
        modal.show();
    }
    
    function generateReport(formData) {
        const progressDiv = document.getElementById('reportProgress');
        const formDiv = document.getElementById('reportGenerationForm').parentElement;
        const generateBtn = document.getElementById('generateReportBtn');
        
        // Show progress
        formDiv.style.display = 'none';
        progressDiv.style.display = 'block';
        generateBtn.disabled = true;
        
        fetch('/api/reports/generate', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json();
            } else {
                // Handle file download
                return response.blob().then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `report_${Date.now()}.${formData.get('format')}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    return { success: true };
                });
            }
        })
        .then(data => {
            if (data.success) {
                // Hide modal and show success message
                bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                
                if (data.report_url) {
                    window.open(data.report_url, '_blank');
                }
            } else {
                alert(data.message || '<?= __('reports.generation_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('reports.generation_error') ?>');
        })
        .finally(() => {
            // Reset modal state
            formDiv.style.display = 'block';
            progressDiv.style.display = 'none';
            generateBtn.disabled = false;
        });
    }
    
    function exportReport(reportType, format) {
        const url = `/api/reports/export/${reportType}?format=${format}`;
        
        fetch(url, {
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${reportType}_report.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Export error:', error);
            alert('<?= __('reports.export_error') ?>');
        });
    }
    
    // Auto-refresh functionality
    document.getElementById('refreshReports').addEventListener('click', function() {
        location.reload();
    });
    
    // Real-time updates via WebSocket (if available)
    if (typeof io !== 'undefined') {
        const socket = io();
        
        socket.on('report_generated', function(data) {
            // Handle real-time report completion notifications
            console.log('Report generated:', data);
        });
        
        socket.on('stats_updated', function(data) {
            // Update dashboard stats in real-time
            updateDashboardStats(data);
        });
    }
    
    function updateDashboardStats(data) {
        // Update quick stats cards with new data
        if (data.total_members) {
            // Update member count
        }
        if (data.total_revenue) {
            // Update revenue
        }
        // Add more updates as needed
    }
});
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>