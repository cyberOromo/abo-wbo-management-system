<?php
$currentPage = 'donations';
?>

<!-- Modern Donations Management Interface -->
<style>
    .donation-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .donation-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -2px rgba(0, 0, 0, 0.15);
    }
    
    .donation-amount-badge {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 20px;
        font-size: 1.1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 8px rgba(139, 21, 56, 0.2);
    }
    
    .donation-type-monetary {
        border-left: 4px solid var(--primary-green);
    }
    
    .donation-type-inkind {
        border-left: 4px solid #3b82f6;
    }
    
    .donation-type-service {
        border-left: 4px solid #8b5cf6;
    }
    
    .donation-status-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }
    
    .donation-status-completed {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    
    .donation-status-processing {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #3b82f6;
    }
    
    .donation-status-cancelled {
        background: #fecaca;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
    
    .financial-chart {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .donation-goal-progress {
        background: #f3f4f6;
        border-radius: 20px;
        height: 12px;
        overflow: hidden;
        position: relative;
    }
    
    .donation-goal-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-green), var(--primary-green-light));
        border-radius: 20px;
        transition: width 0.8s ease;
        position: relative;
    }
    
    .donation-goal-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .donor-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .donor-badge {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .donation-category-badge {
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .category-general {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border-color: rgba(45, 80, 22, 0.2);
    }
    
    .category-education {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .category-health {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border-color: rgba(239, 68, 68, 0.2);
    }
    
    .category-infrastructure {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
        border-color: rgba(107, 114, 128, 0.2);
    }
    
    .category-emergency {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border-color: rgba(139, 21, 56, 0.2);
    }
    
    .financial-metric {
        text-align: center;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .financial-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    
    .financial-amount {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    
    .financial-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .donation-receipt-btn {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(45, 80, 22, 0.2);
    }
    
    .donation-receipt-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .top-donors-list {
        background: white;
        border-radius: 16px;
        overflow: hidden;
    }
    
    .top-donor-item {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: background 0.2s ease;
    }
    
    .top-donor-item:hover {
        background: #f9fafb;
    }
    
    .top-donor-item:last-child {
        border-bottom: none;
    }
    
    .donor-rank {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
    }
    
    .donation-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .donation-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary-green), var(--primary-green-light));
    }
    
    .timeline-donation {
        position: relative;
        margin-bottom: 2rem;
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-donation::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 1.5rem;
        width: 12px;
        height: 12px;
        background: var(--primary-green);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--primary-green);
    }
    
    .campaign-card {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .campaign-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        transform: rotate(45deg);
    }
    
    .export-options {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
</style>

<div class="page-header">
    <h1 class="page-title">Donations Management</h1>
    <p class="page-description">Track, manage, and analyze community donations with comprehensive financial reporting and donor management</p>
</div>

<!-- Financial Overview Dashboard -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric">
            <div class="financial-amount" style="color: var(--primary-green);">$ <?= number_format($donation_stats['total_amount'] ?? 0) ?></div>
            <div class="financial-label">Total Donations</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric">
            <div class="financial-amount" style="color: #3b82f6;"><?= $donation_stats['total_donors'] ?? 0 ?></div>
            <div class="financial-label">Total Donors</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric">
            <div class="financial-amount" style="color: #fbbf24;"><?= $donation_stats['this_month'] ?? 0 ?></div>
            <div class="financial-label">This Month</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="financial-metric">
            <div class="financial-amount" style="color: var(--primary-red);">$ <?= number_format($donation_stats['avg_donation'] ?? 0) ?></div>
            <div class="financial-label">Average Donation</div>
        </div>
    </div>
</div>

<!-- Campaign Progress -->
<?php if (isset($active_campaign)): ?>
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="campaign-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">🎯 <?= htmlspecialchars($active_campaign['title'] ?? 'Active Campaign') ?></h3>
                        <p class="mb-3 opacity-90"><?= htmlspecialchars($active_campaign['description'] ?? '') ?></p>
                        
                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-600">Progress</span>
                                <span class="fw-600"><?= $active_campaign['progress'] ?? 0 ?>%</span>
                            </div>
                            <div class="donation-goal-progress" style="background: rgba(255,255,255,0.2);">
                                <div class="donation-goal-bar" style="width: <?= $active_campaign['progress'] ?? 0 ?>%; background: white;"></div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-4">
                            <div>
                                <div class="fw-600">Raised</div>
                                <div>$ <?= number_format($active_campaign['raised'] ?? 0) ?></div>
                            </div>
                            <div>
                                <div class="fw-600">Goal</div>
                                <div>$ <?= number_format($active_campaign['goal'] ?? 0) ?></div>
                            </div>
                            <div>
                                <div class="fw-600">Days Left</div>
                                <div><?= $active_campaign['days_left'] ?? 0 ?> days</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-light btn-lg">
                            <i class="bi bi-heart-fill"></i> Donate Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Advanced Control Panel -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="view-toggle">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="cardView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary" for="cardView">
                            <i class="bi bi-grid-3x3-gap"></i> Cards
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="listView">
                            <i class="bi bi-list-ul"></i> List
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="analyticsView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="analyticsView">
                            <i class="bi bi-graph-up"></i> Analytics
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="d-flex gap-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="completed">✅ Completed</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="processing">🔄 Processing</option>
                        <option value="cancelled">❌ Cancelled</option>
                    </select>
                    
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="general">🏘️ General</option>
                        <option value="education">📚 Education</option>
                        <option value="health">🏥 Health</option>
                        <option value="infrastructure">🏗️ Infrastructure</option>
                        <option value="emergency">🚨 Emergency</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="d-flex gap-2 justify-content-end">
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showRecordDonationModal()">
                            <i class="bi bi-plus-heart"></i> Record Donation
                        </button>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/donations/export?format=pdf">📄 PDF Report</a></li>
                            <li><a class="dropdown-item" href="/donations/export?format=excel">📊 Excel Export</a></li>
                            <li><a class="dropdown-item" href="/donations/tax-receipts">🧾 Tax Receipts</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card View -->
<div id="cardViewContainer">
    <div class="row g-4" id="donationsGrid">
        <?php if (!empty($donations)): ?>
            <?php foreach ($donations as $donation): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 donation-item" 
                     data-status="<?= $donation['status'] ?? 'completed' ?>" 
                     data-category="<?= $donation['category'] ?? 'general' ?>">
                    <div class="donation-card donation-type-<?= $donation['type'] ?? 'monetary' ?>">
                        <!-- Donation Header -->
                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start p-3">
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <span class="donation-amount-badge">
                                    <i class="bi bi-currency-dollar"></i>
                                    $ <?= number_format($donation['amount'] ?? 0) ?>
                                </span>
                                <span class="donation-category-badge category-<?= $donation['category'] ?? 'general' ?>">
                                    <?= getDonationCategoryIcon($donation['category'] ?? 'general') ?> 
                                    <?= ucfirst($donation['category'] ?? 'general') ?>
                                </span>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>">
                                        <i class="bi bi-eye"></i> View Details
                                    </a></li>
                                    <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>/receipt">
                                        <i class="bi bi-receipt"></i> Generate Receipt
                                    </a></li>
                                    <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>/edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/donations/<?= $donation['id'] ?>/thank-you">
                                        <i class="bi bi-envelope-heart"></i> Send Thank You
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Donation Content -->
                        <div class="card-body p-3 pt-0">
                            <!-- Donor Information -->
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="donor-avatar">
                                    <?= substr($donation['donor_name'] ?? $donation['first_name'] ?? 'A', 0, 1) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-600"><?= htmlspecialchars($donation['donor_name'] ?? $donation['first_name'] . ' ' . $donation['last_name'] ?? 'Anonymous') ?></h6>
                                    <small class="text-muted">
                                        <?= isset($donation['donor_email']) ? htmlspecialchars($donation['donor_email']) : 'No email provided' ?>
                                    </small>
                                    <?php if (isset($donation['is_recurring']) && $donation['is_recurring']): ?>
                                        <span class="donor-badge ms-2">Recurring</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Donation Details -->
                            <?php if (isset($donation['purpose'])): ?>
                                <p class="card-text text-muted mb-3">
                                    <strong>Purpose:</strong> <?= htmlspecialchars(substr($donation['purpose'], 0, 100)) ?>
                                    <?= strlen($donation['purpose']) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Donation Meta -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-3 mb-2 text-muted">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-calendar3 text-primary"></i>
                                        <small><?= date('M j, Y', strtotime($donation['donation_date'] ?? $donation['created_at'] ?? '')) ?></small>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-credit-card text-success"></i>
                                        <small><?= ucfirst($donation['payment_method'] ?? 'cash') ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Donation Footer -->
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge donation-status-<?= $donation['status'] ?? 'completed' ?> px-3 py-2">
                                    <?= getDonationStatusIcon($donation['status'] ?? 'completed') ?> 
                                    <?= ucfirst($donation['status'] ?? 'completed') ?>
                                </span>
                                
                                <?php if (($donation['status'] ?? 'completed') === 'completed'): ?>
                                    <button class="donation-receipt-btn" onclick="generateReceipt(<?= $donation['id'] ?>)">
                                        <i class="bi bi-receipt"></i> Receipt
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-heart" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Donations Recorded</h4>
                    <p class="text-muted mb-4">Start tracking community contributions and build transparent financial records</p>
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showRecordDonationModal()">
                            <i class="bi bi-plus-heart"></i> Record First Donation
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Analytics View (Hidden by default) -->
<div id="analyticsViewContainer" style="display: none;">
    <div class="row g-4">
        <!-- Top Donors -->
        <div class="col-md-6">
            <div class="top-donors-list">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-600">🏆 Top Donors</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($top_donors ?? [])): ?>
                        <?php foreach (array_slice($top_donors, 0, 5) as $index => $donor): ?>
                            <div class="top-donor-item">
                                <div class="donor-rank"><?= $index + 1 ?></div>
                                <div class="donor-avatar">
                                    <?= substr($donor['name'] ?? 'D', 0, 1) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-600"><?= htmlspecialchars($donor['name'] ?? 'Anonymous') ?></h6>
                                    <small class="text-muted"><?= $donor['total_donations'] ?? 0 ?> donations</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-600">$ <?= number_format($donor['total_amount'] ?? 0) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-people"></i> No donor data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Monthly Trends -->
        <div class="col-md-6">
            <div class="financial-chart">
                <h5 class="mb-3 fw-600">📈 Monthly Trends</h5>
                <canvas id="donationsChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
        
        <!-- Category Breakdown -->
        <div class="col-md-12">
            <div class="financial-chart">
                <h5 class="mb-3 fw-600">🎯 Donation Categories</h5>
                <div class="row g-3">
                    <?php if (!empty($category_stats ?? [])): ?>
                        <?php foreach ($category_stats as $category => $stats): ?>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="donation-category-badge category-<?= $category ?> d-block mb-2">
                                        <?= getDonationCategoryIcon($category) ?> <?= ucfirst($category) ?>
                                    </div>
                                    <div class="fw-600">$ <?= number_format($stats['amount'] ?? 0) ?></div>
                                    <small class="text-muted"><?= $stats['count'] ?? 0 ?> donations</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Record Donation Modal -->
<div class="modal fade" id="recordDonationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record New Donation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/donations/create">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Donor Information -->
                        <div class="col-12">
                            <h6 class="fw-600 mb-3">👤 Donor Information</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Donor Name *</label>
                            <input type="text" name="donor_name" class="form-control" required placeholder="Full name of donor">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Email Address</label>
                            <input type="email" name="donor_email" class="form-control" placeholder="donor@email.com">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Phone Number</label>
                            <input type="tel" name="donor_phone" class="form-control" placeholder="+251...">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Organization (Optional)</label>
                            <input type="text" name="donor_organization" class="form-control" placeholder="Company or organization name">
                        </div>
                        
                        <!-- Donation Details -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-600 mb-3">💰 Donation Details</h6>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Donation Type *</label>
                            <select name="type" class="form-select" onchange="toggleDonationFields(this.value)">
                                <option value="monetary">💰 Monetary</option>
                                <option value="inkind">📦 In-Kind</option>
                                <option value="service">🤝 Service</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4" id="amountField">
                            <label class="form-label fw-500">Amount ($) *</label>
                            <input type="number" name="amount" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                        
                        <div class="col-md-4" id="valueField" style="display: none;">
                            <label class="form-label fw-500">Estimated Value ($)</label>
                            <input type="number" name="estimated_value" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Category</label>
                            <select name="category" class="form-select">
                                <option value="general">🏘️ General</option>
                                <option value="education">📚 Education</option>
                                <option value="health">🏥 Health</option>
                                <option value="infrastructure">🏗️ Infrastructure</option>
                                <option value="emergency">🚨 Emergency</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">💵 Cash</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                                <option value="mobile_money">📱 Mobile Money</option>
                                <option value="check">📝 Check</option>
                                <option value="online">🌐 Online Payment</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Donation Date *</label>
                            <input type="date" name="donation_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="col-12" id="itemsField" style="display: none;">
                            <label class="form-label fw-500">Items/Services Donated</label>
                            <textarea name="items_description" class="form-control" rows="3" 
                                      placeholder="Describe the items or services donated..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Purpose/Notes</label>
                            <textarea name="purpose" class="form-control" rows="3" 
                                      placeholder="Specify the purpose of donation or any special notes..."></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurring">
                                <label class="form-check-label fw-500" for="isRecurring">
                                    This is a recurring donation
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="isAnonymous">
                                <label class="form-check-label fw-500" for="isAnonymous">
                                    Donor wishes to remain anonymous
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-heart"></i> Record Donation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switching functionality
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const analyticsView = document.getElementById('analyticsView');
    const cardContainer = document.getElementById('cardViewContainer');
    const analyticsContainer = document.getElementById('analyticsViewContainer');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'block';
            analyticsContainer.style.display = 'none';
        }
    });
    
    analyticsView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            analyticsContainer.style.display = 'block';
            // Initialize chart if needed
            initializeDonationsChart();
        }
    });
    
    // Advanced filtering
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    
    function applyFilters() {
        const statusValue = statusFilter.value;
        const categoryValue = categoryFilter.value;
        
        document.querySelectorAll('.donation-item').forEach(item => {
            const showStatus = !statusValue || item.dataset.status === statusValue;
            const showCategory = !categoryValue || item.dataset.category === categoryValue;
            item.style.display = showStatus && showCategory ? 'block' : 'none';
        });
    }
    
    statusFilter.addEventListener('change', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
});

function showRecordDonationModal() {
    new bootstrap.Modal(document.getElementById('recordDonationModal')).show();
}

function toggleDonationFields(type) {
    const amountField = document.getElementById('amountField');
    const valueField = document.getElementById('valueField');
    const itemsField = document.getElementById('itemsField');
    
    if (type === 'monetary') {
        amountField.style.display = 'block';
        valueField.style.display = 'none';
        itemsField.style.display = 'none';
    } else {
        amountField.style.display = 'none';
        valueField.style.display = 'block';
        itemsField.style.display = 'block';
    }
}

function generateReceipt(donationId) {
    window.open(`/donations/${donationId}/receipt`, '_blank');
}

function initializeDonationsChart() {
    // Chart.js implementation would go here
    console.log('Initializing donations chart...');
}
</script>

<?php
// Helper functions for UI
function getDonationCategoryIcon($category) {
    return [
        'general' => '🏘️',
        'education' => '📚',
        'health' => '🏥',
        'infrastructure' => '🏗️',
        'emergency' => '🚨'
    ][$category] ?? '🏘️';
}

function getDonationStatusIcon($status) {
    return [
        'completed' => '✅',
        'pending' => '⏳',
        'processing' => '🔄',
        'cancelled' => '❌'
    ][$status] ?? '✅';
}
?>