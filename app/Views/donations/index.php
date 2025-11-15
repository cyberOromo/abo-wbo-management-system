<?php
/**
 * Donations Index View Template
 * Comprehensive donation management with payment integration and financial reporting
 */

// Page metadata
$pageTitle = __('donations.title');
$pageDescription = __('donations.description');
$bodyClass = 'donations-page';

// Donation data
$donations = $donations ?? [];
$donationStats = $donationStats ?? [];
$recentDonations = $recentDonations ?? [];
$filters = $filters ?? [];

// User permissions
$canCreateDonations = $permissions['can_create_donations'] ?? false;
$canManageDonations = $permissions['can_manage_donations'] ?? false;
$canViewFinancials = $permissions['can_view_financials'] ?? false;

// Donation types
$donationTypes = [
    'monetary' => ['name' => __('donations.monetary'), 'color' => 'success', 'icon' => 'currency-dollar'],
    'in_kind' => ['name' => __('donations.in_kind'), 'color' => 'info', 'icon' => 'gift'],
    'service' => ['name' => __('donations.service'), 'color' => 'warning', 'icon' => 'tools'],
    'equipment' => ['name' => __('donations.equipment'), 'color' => 'primary', 'icon' => 'laptop'],
    'food' => ['name' => __('donations.food'), 'color' => 'danger', 'icon' => 'cup-straw']
];

// Donation statuses
$donationStatuses = [
    'pledged' => ['name' => __('donations.pledged'), 'color' => 'warning'],
    'received' => ['name' => __('donations.received'), 'color' => 'success'],
    'processing' => ['name' => __('donations.processing'), 'color' => 'info'],
    'completed' => ['name' => __('donations.completed'), 'color' => 'primary'],
    'cancelled' => ['name' => __('donations.cancelled'), 'color' => 'danger']
];

// Payment methods
$paymentMethods = [
    'cash' => __('donations.cash'),
    'bank_transfer' => __('donations.bank_transfer'),
    'mobile_money' => __('donations.mobile_money'),
    'card' => __('donations.card'),
    'check' => __('donations.check'),
    'online' => __('donations.online')
];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('donations.donation_management') ?></h1>
        <p class="text-muted mb-0"><?= __('donations.manage_organization_donations') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canCreateDonations): ?>
            <a href="/donations/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?= __('donations.record_donation') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> <?= __('donations.export') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/donations/export?format=excel">
                    <i class="bi bi-file-earmark-excel me-2"></i><?= __('donations.export_excel') ?>
                </a></li>
                <li><a class="dropdown-item" href="/donations/export?format=pdf">
                    <i class="bi bi-file-earmark-pdf me-2"></i><?= __('donations.export_pdf') ?>
                </a></li>
                <li><a class="dropdown-item" href="/donations/receipts">
                    <i class="bi bi-receipt me-2"></i><?= __('donations.bulk_receipts') ?>
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Donation Statistics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('donations.total_raised') ?></h5>
                        <h2 class="mb-0"><?= format_currency($donationStats['total_amount'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-currency-dollar fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('donations.total_donations') ?></h5>
                        <h2 class="mb-0"><?= number_format($donationStats['total_count'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-gift fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('donations.this_month') ?></h5>
                        <h2 class="mb-0"><?= format_currency($donationStats['this_month'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-calendar-month fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('donations.avg_donation') ?></h5>
                        <h2 class="mb-0"><?= format_currency($donationStats['average_amount'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-graph-up fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and Analytics -->
<div class="row g-3 mb-4">
    <!-- Recent Donations -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><?= __('donations.recent_donations') ?></h6>
                <a href="/donations" class="btn btn-sm btn-outline-primary"><?= __('donations.view_all') ?></a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentDonations)): ?>
                    <div class="recent-donations-list">
                        <?php foreach (array_slice($recentDonations, 0, 5) as $donation): ?>
                            <div class="donation-item d-flex align-items-center mb-3">
                                <div class="donation-avatar me-3">
                                    <img src="<?= $donation['donor_avatar'] ?? '/assets/images/default-avatar.svg' ?>" 
                                         alt="<?= htmlspecialchars($donation['donor_name']) ?>"
                                         class="rounded-circle" width="40" height="40">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($donation['donor_name']) ?></h6>
                                    <small class="text-muted">
                                        <?= format_currency($donation['amount']) ?> • 
                                        <?= format_date($donation['date']) ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?= $donationTypes[$donation['type']]['color'] ?>">
                                    <?= $donationTypes[$donation['type']]['name'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-gift display-6"></i>
                        <p class="mt-2"><?= __('donations.no_recent_donations') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Donation Chart -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0"><?= __('donations.donation_trends') ?></h6>
            </div>
            <div class="card-body">
                <canvas id="donationChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Filters and View Toggle -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- View Toggle -->
            <div class="btn-group view-toggle" role="group">
                <input type="radio" class="btn-check" name="view-mode" id="card-view" checked>
                <label class="btn btn-outline-primary" for="card-view">
                    <i class="bi bi-grid"></i> <?= __('donations.card_view') ?>
                </label>
                <input type="radio" class="btn-check" name="view-mode" id="table-view">
                <label class="btn btn-outline-primary" for="table-view">
                    <i class="bi bi-list-ul"></i> <?= __('donations.table_view') ?>
                </label>
            </div>
            
            <!-- Quick Filters -->
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel"></i> <?= __('donations.filters') ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 350px;">
                        <form class="filters-form">
                            <div class="mb-3">
                                <label class="form-label"><?= __('donations.search') ?></label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                                       placeholder="<?= __('donations.search_placeholder') ?>">
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('donations.type') ?></label>
                                    <select class="form-select" name="type">
                                        <option value=""><?= __('donations.all_types') ?></option>
                                        <?php foreach ($donationTypes as $type => $config): ?>
                                            <option value="<?= $type ?>" 
                                                    <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('donations.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value=""><?= __('donations.all_statuses') ?></option>
                                        <?php foreach ($donationStatuses as $status => $config): ?>
                                            <option value="<?= $status ?>" 
                                                    <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('donations.amount_min') ?></label>
                                    <input type="number" class="form-control" name="amount_min" 
                                           value="<?= $filters['amount_min'] ?? '' ?>" step="0.01">
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('donations.amount_max') ?></label>
                                    <input type="number" class="form-control" name="amount_max" 
                                           value="<?= $filters['amount_max'] ?? '' ?>" step="0.01">
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('donations.date_from') ?></label>
                                    <input type="date" class="form-control" name="date_from" 
                                           value="<?= $filters['date_from'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('donations.date_to') ?></label>
                                    <input type="date" class="form-control" name="date_to" 
                                           value="<?= $filters['date_to'] ?? '' ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><?= __('donations.payment_method') ?></label>
                                <select class="form-select" name="payment_method">
                                    <option value=""><?= __('donations.all_methods') ?></option>
                                    <?php foreach ($paymentMethods as $method => $name): ?>
                                        <option value="<?= $method ?>" 
                                                <?= ($filters['payment_method'] ?? '') === $method ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <?= __('donations.apply_filters') ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-filters">
                                    <?= __('donations.clear') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card View -->
<div id="card-view-content">
    <?php if (empty($donations)): ?>
        <div class="card">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-gift display-1 text-muted"></i>
                    <h4 class="text-muted mt-3"><?= __('donations.no_donations_found') ?></h4>
                    <p class="text-muted"><?= __('donations.no_donations_description') ?></p>
                    <?php if ($canCreateDonations): ?>
                        <a href="/donations/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> <?= __('donations.record_first_donation') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($donations as $donation): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card donation-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $donation['donor_avatar'] ?? '/assets/images/default-avatar.svg' ?>" 
                                         alt="<?= htmlspecialchars($donation['donor_name']) ?>"
                                         class="rounded-circle me-3" width="48" height="48">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($donation['donor_name']) ?></h6>
                                        <small class="text-muted"><?= format_date($donation['date']) ?></small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>">
                                            <i class="bi bi-eye me-2"></i><?= __('donations.view') ?>
                                        </a></li>
                                        <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>/receipt">
                                            <i class="bi bi-receipt me-2"></i><?= __('donations.receipt') ?>
                                        </a></li>
                                        <?php if ($canManageDonations): ?>
                                            <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>/edit">
                                                <i class="bi bi-pencil me-2"></i><?= __('donations.edit') ?>
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger delete-donation" href="#" 
                                                   data-donation-id="<?= $donation['id'] ?>">
                                                <i class="bi bi-trash me-2"></i><?= __('donations.delete') ?>
                                            </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="donation-amount text-center mb-3">
                                <h3 class="text-success mb-1"><?= format_currency($donation['amount']) ?></h3>
                                <div class="d-flex justify-content-center gap-2">
                                    <span class="badge bg-<?= $donationTypes[$donation['type']]['color'] ?>">
                                        <i class="bi bi-<?= $donationTypes[$donation['type']]['icon'] ?> me-1"></i>
                                        <?= $donationTypes[$donation['type']]['name'] ?>
                                    </span>
                                    <span class="badge bg-<?= $donationStatuses[$donation['status']]['color'] ?>">
                                        <?= $donationStatuses[$donation['status']]['name'] ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($donation['description'])): ?>
                                <p class="text-muted small mb-3">
                                    <?= htmlspecialchars(substr($donation['description'], 0, 100)) ?>
                                    <?= strlen($donation['description']) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="donation-details">
                                <?php if (!empty($donation['payment_method'])): ?>
                                    <div class="detail-item mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-credit-card me-1"></i>
                                            <?= $paymentMethods[$donation['payment_method']] ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($donation['reference_number'])): ?>
                                    <div class="detail-item mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-hash me-1"></i>
                                            <?= htmlspecialchars($donation['reference_number']) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($donation['campaign'])): ?>
                                    <div class="detail-item mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-flag me-1"></i>
                                            <?= htmlspecialchars($donation['campaign']) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($donation['status'] === 'pledged'): ?>
                            <div class="card-footer bg-light">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-success flex-fill mark-received" 
                                            data-donation-id="<?= $donation['id'] ?>">
                                        <i class="bi bi-check-circle"></i> <?= __('donations.mark_received') ?>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Table View -->
<div id="table-view-content" style="display: none;">
    <div class="card">
        <div class="card-body">
            <?php if (!empty($donations)): ?>
                <div class="table-responsive">
                    <table class="table table-hover donations-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-donations">
                                    </div>
                                </th>
                                <th><?= __('donations.donor') ?></th>
                                <th><?= __('donations.amount') ?></th>
                                <th><?= __('donations.type') ?></th>
                                <th><?= __('donations.status') ?></th>
                                <th><?= __('donations.payment_method') ?></th>
                                <th><?= __('donations.date') ?></th>
                                <th style="width: 120px;"><?= __('donations.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                                <tr class="donation-row" data-donation-id="<?= $donation['id'] ?>">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input donation-checkbox" type="checkbox" 
                                                   value="<?= $donation['id'] ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $donation['donor_avatar'] ?? '/assets/images/default-avatar.svg' ?>" 
                                                 alt="<?= htmlspecialchars($donation['donor_name']) ?>"
                                                 class="rounded-circle me-2" width="32" height="32">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($donation['donor_name']) ?></h6>
                                                <?php if (!empty($donation['donor_email'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($donation['donor_email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?= format_currency($donation['amount']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $donationTypes[$donation['type']]['color'] ?>">
                                            <i class="bi bi-<?= $donationTypes[$donation['type']]['icon'] ?> me-1"></i>
                                            <?= $donationTypes[$donation['type']]['name'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $donationStatuses[$donation['status']]['color'] ?>">
                                            <?= $donationStatuses[$donation['status']]['name'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($donation['payment_method'])): ?>
                                            <?= $paymentMethods[$donation['payment_method']] ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= format_date($donation['date']) ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i><?= __('donations.view') ?>
                                                </a></li>
                                                <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>/receipt">
                                                    <i class="bi bi-receipt me-2"></i><?= __('donations.receipt') ?>
                                                </a></li>
                                                <?php if ($canManageDonations): ?>
                                                    <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>/edit">
                                                        <i class="bi bi-pencil me-2"></i><?= __('donations.edit') ?>
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger delete-donation" href="#" 
                                                           data-donation-id="<?= $donation['id'] ?>">
                                                        <i class="bi bi-trash me-2"></i><?= __('donations.delete') ?>
                                                    </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('donations.bulk_actions') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionsForm">
                    <div class="mb-3">
                        <label class="form-label"><?= __('donations.select_action') ?></label>
                        <select class="form-select" name="action" required>
                            <option value=""><?= __('donations.choose_action') ?></option>
                            <option value="mark_received"><?= __('donations.mark_as_received') ?></option>
                            <option value="generate_receipts"><?= __('donations.generate_receipts') ?></option>
                            <option value="export_selected"><?= __('donations.export_selected') ?></option>
                            <?php if ($canManageDonations): ?>
                                <option value="delete_selected"><?= __('donations.delete_selected') ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="selected-count">0</span> <?= __('donations.donations_selected') ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= __('common.cancel') ?>
                </button>
                <button type="button" class="btn btn-primary" id="executeBulkAction">
                    <?= __('donations.execute_action') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Donation Styles -->
<style>
.stats-card {
    border: none;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.donation-card {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
}

.donation-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.donation-amount h3 {
    font-weight: 700;
}

.view-toggle .btn {
    border-radius: 0;
}

.view-toggle .btn:first-child {
    border-radius: 0.375rem 0 0 0.375rem;
}

.view-toggle .btn:last-child {
    border-radius: 0 0.375rem 0.375rem 0;
}

.filters-form .form-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.recent-donations-list {
    max-height: 300px;
    overflow-y: auto;
}

.donation-item:last-child {
    margin-bottom: 0 !important;
}

.detail-item {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .donation-card .card-body {
        padding: 1rem;
    }
    
    .donation-amount h3 {
        font-size: 1.5rem;
    }
}
</style>

<!-- Donations JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize donation chart
    initializeDonationChart();
    
    // View switching
    const cardView = document.getElementById('card-view');
    const tableView = document.getElementById('table-view');
    const cardContent = document.getElementById('card-view-content');
    const tableContent = document.getElementById('table-view-content');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContent.style.display = 'block';
            tableContent.style.display = 'none';
        }
    });
    
    tableView.addEventListener('change', function() {
        if (this.checked) {
            cardContent.style.display = 'none';
            tableContent.style.display = 'block';
        }
    });
    
    // Filter form handling
    document.querySelector('.filters-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/donations?' + params.toString();
    });
    
    document.querySelector('.clear-filters').addEventListener('click', function() {
        window.location.href = '/donations';
    });
    
    // Donation actions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('mark-received') || e.target.closest('.mark-received')) {
            e.preventDefault();
            const donationId = e.target.dataset.donationId || e.target.closest('.mark-received').dataset.donationId;
            markDonationReceived(donationId);
        }
        
        if (e.target.classList.contains('delete-donation') || e.target.closest('.delete-donation')) {
            e.preventDefault();
            const donationId = e.target.dataset.donationId || e.target.closest('.delete-donation').dataset.donationId;
            deleteDonation(donationId);
        }
    });
    
    // Bulk actions
    const selectAllCheckbox = document.getElementById('select-all-donations');
    const donationCheckboxes = document.querySelectorAll('.donation-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            donationCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsButton();
        });
    }
    
    donationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionsButton);
    });
    
    document.getElementById('executeBulkAction').addEventListener('click', function() {
        const form = document.getElementById('bulkActionsForm');
        const formData = new FormData(form);
        const selectedDonations = Array.from(document.querySelectorAll('.donation-checkbox:checked')).map(cb => cb.value);
        
        if (selectedDonations.length === 0) {
            alert('<?= __('donations.no_donations_selected') ?>');
            return;
        }
        
        formData.append('donation_ids', JSON.stringify(selectedDonations));
        
        fetch('/api/donations/bulk-action', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '<?= __('donations.bulk_action_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('donations.bulk_action_error') ?>');
        });
    });
    
    // Functions
    function initializeDonationChart() {
        const ctx = document.getElementById('donationChart');
        if (!ctx) return;
        
        const chartData = <?= json_encode($donationStats['chart_data'] ?? []) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: '<?= __('donations.amount') ?>',
                    data: chartData.amounts || [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: '<?= __('donations.count') ?>',
                    data: chartData.counts || [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: '<?= __('donations.amount') ?>'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '<?= __('donations.count') ?>'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
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
    
    function markDonationReceived(donationId) {
        if (confirm('<?= __('donations.confirm_mark_received') ?>')) {
            fetch(`/api/donations/${donationId}/mark-received`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?= __('donations.mark_received_failed') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('donations.mark_received_error') ?>');
            });
        }
    }
    
    function deleteDonation(donationId) {
        if (confirm('<?= __('donations.confirm_delete') ?>')) {
            fetch(`/api/donations/${donationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?= __('donations.delete_failed') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('donations.delete_error') ?>');
            });
        }
    }
    
    function updateBulkActionsButton() {
        const selectedCount = document.querySelectorAll('.donation-checkbox:checked').length;
        const countElement = document.getElementById('selected-count');
        
        if (countElement) {
            countElement.textContent = selectedCount;
        }
        
        if (selectedCount > 0 && !document.getElementById('bulk-actions-btn')) {
            addBulkActionsButton();
        } else if (selectedCount === 0) {
            removeBulkActionsButton();
        }
    }
    
    function addBulkActionsButton() {
        const existingBtn = document.getElementById('bulk-actions-btn');
        if (existingBtn) return;
        
        const headerDiv = document.querySelector('.d-flex.gap-2');
        const bulkBtn = document.createElement('button');
        bulkBtn.id = 'bulk-actions-btn';
        bulkBtn.className = 'btn btn-outline-primary';
        bulkBtn.innerHTML = '<i class="bi bi-check2-square"></i> <?= __('donations.bulk_actions') ?>';
        bulkBtn.setAttribute('data-bs-toggle', 'modal');
        bulkBtn.setAttribute('data-bs-target', '#bulkActionsModal');
        
        headerDiv.insertBefore(bulkBtn, headerDiv.firstChild);
    }
    
    function removeBulkActionsButton() {
        const existingBtn = document.getElementById('bulk-actions-btn');
        if (existingBtn) {
            existingBtn.remove();
        }
    }
});
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>