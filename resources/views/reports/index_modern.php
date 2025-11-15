<?php
$currentPage = 'reports';
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
                <div class="kpi-value" style="color: var(--primary-green);"><?= $kpi_data['total_members'] ?? 0 ?></div>
                <div class="kpi-label">Total Members</div>
                <div class="kpi-trend positive">
                    <i class="bi bi-trend-up"></i> +12% this month
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-metric">
                <div class="kpi-value" style="color: var(--primary-red);">ETB <?= number_format($kpi_data['total_revenue'] ?? 0) ?></div>
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-trend positive">
                    <i class="bi bi-trend-up"></i> +8% this month
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-metric">
                <div class="kpi-value" style="color: #3b82f6;"><?= $kpi_data['active_projects'] ?? 0 ?></div>
                <div class="kpi-label">Active Projects</div>
                <div class="kpi-trend neutral">
                    <i class="bi bi-dash"></i> No change
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-metric">
                <div class="kpi-value" style="color: #8b5cf6;"><?= $kpi_data['completion_rate'] ?? 0 ?>%</div>
                <div class="kpi-label">Task Completion</div>
                <div class="kpi-trend positive">
                    <i class="bi bi-trend-up"></i> +5% this month
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
                <option value="">All Reports</option>
                <option value="financial">💰 Financial</option>
                <option value="membership">👥 Membership</option>
                <option value="activities">📋 Activities</option>
                <option value="performance">📊 Performance</option>
                <option value="compliance">📋 Compliance</option>
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
    <div class="col-lg-4 col-md-6">
        <div class="report-card report-financial" onclick="selectReportCategory('financial')">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="report-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <span class="report-category-badge category-financial">Financial</span>
                </div>
                <h5 class="card-title fw-600 mb-2">Financial Reports</h5>
                <p class="card-text text-muted mb-3">Revenue, expenses, donations, and budget analysis</p>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-muted">Available Reports</span>
                    <span class="fw-600">12</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="report-card report-membership" onclick="selectReportCategory('membership')">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="report-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <span class="report-category-badge category-membership">Membership</span>
                </div>
                <h5 class="card-title fw-600 mb-2">Membership Reports</h5>
                <p class="card-text text-muted mb-3">Member statistics, registrations, and demographics</p>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-muted">Available Reports</span>
                    <span class="fw-600">8</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="report-card report-activities" onclick="selectReportCategory('activities')">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="report-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <span class="report-category-badge category-activities">Activities</span>
                </div>
                <h5 class="card-title fw-600 mb-2">Activities Reports</h5>
                <p class="card-text text-muted mb-3">Events, meetings, tasks, and project progress</p>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-muted">Available Reports</span>
                    <span class="fw-600">15</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="report-card report-performance" onclick="selectReportCategory('performance')">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="report-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <span class="report-category-badge category-performance">Performance</span>
                </div>
                <h5 class="card-title fw-600 mb-2">Performance Reports</h5>
                <p class="card-text text-muted mb-3">KPIs, targets, achievements, and analytics</p>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-muted">Available Reports</span>
                    <span class="fw-600">10</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="report-card report-compliance" onclick="selectReportCategory('compliance')">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="report-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <span class="report-category-badge category-compliance">Compliance</span>
                </div>
                <h5 class="card-title fw-600 mb-2">Compliance Reports</h5>
                <p class="card-text text-muted mb-3">Regulatory compliance and audit reports</p>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-muted">Available Reports</span>
                    <span class="fw-600">6</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="report-card" onclick="showCustomReportModal()">
            <div class="card-body p-4 text-center">
                <div class="report-preview">
                    <div class="report-icon">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <h5 class="fw-600 mb-2">Custom Report</h5>
                    <p class="text-muted mb-0">Create a custom report with specific parameters</p>
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
                                    <a href="/reports/<?= $report['id'] ?>/view" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="/reports/<?= $report['id'] ?>/download" class="btn btn-outline-secondary">
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
            <a href="/reports/export/financial?format=pdf" class="export-button">
                <i class="bi bi-filetype-pdf"></i> Financial PDF
            </a>
            <a href="/reports/export/membership?format=excel" class="export-button secondary">
                <i class="bi bi-filetype-xlsx"></i> Membership Excel
            </a>
            <a href="/reports/export/activities?format=csv" class="export-button">
                <i class="bi bi-filetype-csv"></i> Activities CSV
            </a>
            <button class="export-button secondary" onclick="showCustomReportModal()">
                <i class="bi bi-gear"></i> Custom Export
            </button>
        </div>
    </div>
</div>

<!-- Custom Report Modal -->
<div class="modal fade" id="customReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Custom Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/reports/create-custom">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Report Name *</label>
                            <input type="text" name="report_name" class="form-control" required placeholder="Enter report name">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Report Category</label>
                            <select name="category" class="form-select">
                                <option value="financial">💰 Financial</option>
                                <option value="membership">👥 Membership</option>
                                <option value="activities">📋 Activities</option>
                                <option value="performance">📊 Performance</option>
                                <option value="compliance">📋 Compliance</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Output Format</label>
                            <select name="format" class="form-select">
                                <option value="pdf">📄 PDF Report</option>
                                <option value="excel">📊 Excel Spreadsheet</option>
                                <option value="csv">📋 CSV Data</option>
                                <option value="json">💾 JSON Data</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Date Range From</label>
                            <input type="date" name="date_from" class="form-control" value="<?= date('Y-m-01') ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Date Range To</label>
                            <input type="date" name="date_to" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Data Sources</label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="data_sources[]" value="members" id="srcMembers" checked>
                                        <label class="form-check-label" for="srcMembers">Members Data</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="data_sources[]" value="donations" id="srcDonations" checked>
                                        <label class="form-check-label" for="srcDonations">Donations Data</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="data_sources[]" value="events" id="srcEvents">
                                        <label class="form-check-label" for="srcEvents">Events Data</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="data_sources[]" value="meetings" id="srcMeetings">
                                        <label class="form-check-label" for="srcMeetings">Meetings Data</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="data_sources[]" value="tasks" id="srcTasks">
                                        <label class="form-check-label" for="srcTasks">Tasks Data</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="data_sources[]" value="finances" id="srcFinances" checked>
                                        <label class="form-check-label" for="srcFinances">Financial Data</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Hierarchy Scope</label>
                            <select name="hierarchy_scope" class="form-select">
                                <option value="all">All Levels</option>
                                <option value="godina">Godina Level Only</option>
                                <option value="gamta">Gamta Level Only</option>
                                <option value="gurmu">Gurmu Level Only</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Any specific requirements or notes for this report..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-plus"></i> Generate Report
                    </button>
                </div>
            </form>
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
    updateReportOptions();
    // Scroll to filters
    document.querySelector('.report-filter-panel').scrollIntoView({ behavior: 'smooth' });
}

function updateReportOptions() {
    const reportType = document.getElementById('reportType').value;
    // Update available options based on report type
    console.log('Updating options for:', reportType);
}

function generateReport() {
    const formData = {
        type: document.getElementById('reportType').value,
        period: document.getElementById('timePeriod').value,
        from: document.getElementById('fromDate').value,
        to: document.getElementById('toDate').value,
        hierarchy: document.getElementById('hierarchyLevel').value
    };
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating...';
    btn.disabled = true;
    
    // Simulate report generation
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Report generated successfully!');
    }, 2000);
}

function showCustomReportModal() {
    new bootstrap.Modal(document.getElementById('customReportModal')).show();
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