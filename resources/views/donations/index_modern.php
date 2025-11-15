<?php
$pageTitle = $title ?? 'Donations Management';
$layout = 'modern';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-heart me-2"></i>
            Donations Management
        </h1>
        <p class="text-muted mb-0">Track donations, manage campaigns, and analyze fundraising performance</p>
    </div>
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                <i class="bi bi-plus-circle me-1"></i>
                Create Campaign
            </button>
        </div>
        <div class="btn-group">
            <a href="/donations/report" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-bar-graph me-1"></i>
                Generate Report
            </a>
            <a href="/donations/export" class="btn btn-outline-info">
                <i class="bi bi-download me-1"></i>
                Export Data
            </a>
        </div>
    </div>
</div>

<!-- Donation Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0">$<?= number_format($stats['total_raised'] ?? 45750) ?></h3>
                    <p class="text-muted mb-0">Total Raised</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        +$<?= number_format($stats['this_month'] ?? 5420) ?> This Month
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_donors'] ?? 238) ?></h3>
                    <p class="text-muted mb-0">Total Donors</p>
                    <small class="text-info">
                        <i class="bi bi-person-plus"></i>
                        +<?= $stats['new_donors'] ?? 18 ?> New This Month
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['campaigns_active'] ?? 5) ?></h3>
                    <p class="text-muted mb-0">Active Campaigns</p>
                    <small class="text-warning">
                        <i class="bi bi-clock"></i>
                        <?= $stats['campaigns_ending'] ?? 2 ?> Ending Soon
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0">$<?= number_format($stats['avg_donation'] ?? 192) ?></h3>
                    <p class="text-muted mb-0">Avg Donation</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        +12% from last month
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Donation & Recent Activity -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="quickDonation()">
                            <div class="action-icon bg-success text-white mb-2">
                                <i class="bi bi-credit-card"></i>
                            </div>
                            <span class="fw-bold">Quick Donation</span>
                            <small class="text-muted">Process payment</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                            <div class="action-icon bg-primary text-white mb-2">
                                <i class="bi bi-megaphone"></i>
                            </div>
                            <span class="fw-bold">New Campaign</span>
                            <small class="text-muted">Create fundraiser</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='/donors'">
                            <div class="action-icon bg-info text-white mb-2">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="fw-bold">Donor Management</span>
                            <small class="text-muted">Manage donors</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="window.location.href='/donations/analytics'">
                            <div class="action-icon bg-warning text-white mb-2">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="fw-bold">Analytics</span>
                            <small class="text-muted">View insights</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Donations
                </h6>
            </div>
            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                <?php 
                $recentDonations = [
                    ['donor' => 'Anonymous', 'amount' => 500, 'campaign' => 'Emergency Relief Fund', 'time' => '5 min ago'],
                    ['donor' => 'Sarah Johnson', 'amount' => 100, 'campaign' => 'Education Scholarship', 'time' => '1 hour ago'],
                    ['donor' => 'Michael Chen', 'amount' => 250, 'campaign' => 'Community Center', 'time' => '2 hours ago'],
                    ['donor' => 'Anonymous', 'amount' => 50, 'campaign' => 'Youth Programs', 'time' => '3 hours ago'],
                    ['donor' => 'Dr. Patricia Williams', 'amount' => 1000, 'campaign' => 'Healthcare Initiative', 'time' => '5 hours ago']
                ];
                
                foreach ($recentDonations as $donation): 
                ?>
                    <div class="donation-item d-flex justify-content-between align-items-start mb-3 p-2 rounded">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= htmlspecialchars($donation['donor']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($donation['campaign']) ?></small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-success">$<?= number_format($donation['amount']) ?></span>
                            <br>
                            <small class="text-muted"><?= $donation['time'] ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Active Campaigns -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-megaphone me-2"></i>
            Active Fundraising Campaigns
        </h5>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary active" data-view="grid">
                <i class="bi bi-grid"></i> Grid
            </button>
            <button type="button" class="btn btn-outline-secondary" data-view="list">
                <i class="bi bi-list"></i> List
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row" id="campaigns-grid">
            <?php 
            $campaigns = [
                [
                    'id' => 1,
                    'title' => 'Emergency Relief Fund',
                    'description' => 'Supporting families affected by natural disasters in our community',
                    'goal' => 25000,
                    'raised' => 18750,
                    'donors' => 125,
                    'days_left' => 15,
                    'category' => 'emergency',
                    'featured' => true,
                    'image' => 'relief.jpg'
                ],
                [
                    'id' => 2,
                    'title' => 'Education Scholarship Program',
                    'description' => 'Providing educational opportunities for underprivileged youth',
                    'goal' => 15000,
                    'raised' => 8200,
                    'donors' => 67,
                    'days_left' => 45,
                    'category' => 'education',
                    'featured' => false,
                    'image' => 'education.jpg'
                ],
                [
                    'id' => 3,
                    'title' => 'Community Center Renovation',
                    'description' => 'Upgrading facilities to better serve our growing community',
                    'goal' => 50000,
                    'raised' => 32500,
                    'donors' => 89,
                    'days_left' => 60,
                    'category' => 'infrastructure',
                    'featured' => true,
                    'image' => 'renovation.jpg'
                ],
                [
                    'id' => 4,
                    'title' => 'Healthcare Initiative',
                    'description' => 'Improving access to healthcare services for community members',
                    'goal' => 20000,
                    'raised' => 12800,
                    'donors' => 56,
                    'days_left' => 30,
                    'category' => 'healthcare',
                    'featured' => false,
                    'image' => 'healthcare.jpg'
                ],
                [
                    'id' => 5,
                    'title' => 'Youth Sports Program',
                    'description' => 'Equipment and training for youth sports activities',
                    'goal' => 8000,
                    'raised' => 4500,
                    'donors' => 34,
                    'days_left' => 25,
                    'category' => 'sports',
                    'featured' => false,
                    'image' => 'sports.jpg'
                ]
            ];
            
            foreach ($campaigns as $campaign): 
                $progressPercent = ($campaign['raised'] / $campaign['goal']) * 100;
                $categoryClass = [
                    'emergency' => 'danger',
                    'education' => 'primary',
                    'infrastructure' => 'warning',
                    'healthcare' => 'success',
                    'sports' => 'info'
                ][$campaign['category']] ?? 'secondary';
                
                $urgency = $campaign['days_left'] <= 7 ? 'urgent' : ($campaign['days_left'] <= 30 ? 'moderate' : 'normal');
            ?>
                <div class="col-lg-4 col-md-6 mb-4 campaign-card">
                    <div class="card campaign-item h-100 <?= $campaign['featured'] ? 'featured-campaign' : '' ?>">
                        <?php if ($campaign['featured']): ?>
                            <div class="featured-badge">
                                <i class="bi bi-star-fill"></i> Featured
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($urgency === 'urgent'): ?>
                            <div class="urgency-badge bg-danger text-white">
                                <i class="bi bi-exclamation-triangle"></i> Urgent
                            </div>
                        <?php endif; ?>
                        
                        <div class="campaign-image">
                            <div class="placeholder-image bg-<?= $categoryClass ?> bg-opacity-20 d-flex align-items-center justify-content-center">
                                <i class="bi bi-<?= [
                                    'emergency' => 'exclamation-triangle',
                                    'education' => 'book',
                                    'infrastructure' => 'building',
                                    'healthcare' => 'heart-pulse',
                                    'sports' => 'trophy'
                                ][$campaign['category']] ?? 'heart' ?> text-<?= $categoryClass ?>" style="font-size: 3rem;"></i>
                            </div>
                            <div class="campaign-badges">
                                <span class="badge bg-<?= $categoryClass ?>"><?= ucfirst($campaign['category']) ?></span>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($campaign['title']) ?></h5>
                            <p class="text-muted mb-3 flex-grow-1"><?= htmlspecialchars($campaign['description']) ?></p>
                            
                            <!-- Progress Section -->
                            <div class="progress-section mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="h5 text-<?= $categoryClass ?>">$<?= number_format($campaign['raised']) ?></span>
                                        <small class="text-muted">raised of $<?= number_format($campaign['goal']) ?> goal</small>
                                    </div>
                                    <span class="badge bg-<?= $categoryClass ?> bg-opacity-20 text-<?= $categoryClass ?>"><?= round($progressPercent) ?>%</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $categoryClass ?>" style="width: <?= $progressPercent ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <small><i class="bi bi-people me-1"></i><?= $campaign['donors'] ?> donors</small>
                                    <small><i class="bi bi-calendar me-1"></i><?= $campaign['days_left'] ?> days left</small>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button class="btn btn-<?= $categoryClass ?> flex-grow-1" onclick="donateToCampaign(<?= $campaign['id'] ?>)">
                                    <i class="bi bi-heart me-1"></i>
                                    Donate Now
                                </button>
                                <div class="btn-group">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="/campaigns/<?= $campaign['id'] ?>">
                                            <i class="bi bi-eye me-2"></i>View Details
                                        </a></li>
                                        <li><a class="dropdown-item" href="/campaigns/<?= $campaign['id'] ?>/share">
                                            <i class="bi bi-share me-2"></i>Share Campaign
                                        </a></li>
                                        <li><a class="dropdown-item" href="/campaigns/<?= $campaign['id'] ?>/edit">
                                            <i class="bi bi-pencil me-2"></i>Edit Campaign
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/campaigns/<?= $campaign['id'] ?>/analytics">
                                            <i class="bi bi-graph-up me-2"></i>View Analytics
                                        </a></li>
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

<!-- Donors & Analytics -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Donation Trends
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="donationChart" width="400" height="200"></canvas>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-4 text-center">
                        <h3 class="text-success">$<?= number_format($stats['monthly_avg'] ?? 3812) ?></h3>
                        <p class="text-muted mb-0">Monthly Average</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 class="text-primary"><?= number_format($stats['repeat_donors'] ?? 89) ?>%</h3>
                        <p class="text-muted mb-0">Repeat Donors</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 class="text-warning">$<?= number_format($stats['largest_donation'] ?? 2500) ?></h3>
                        <p class="text-muted mb-0">Largest Donation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Top Donors -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-trophy me-2"></i>
                    Top Donors
                </h6>
            </div>
            <div class="card-body">
                <?php 
                $topDonors = [
                    ['name' => 'Dr. Patricia Williams', 'amount' => 2500, 'donations' => 8, 'rank' => 1],
                    ['name' => 'Michael & Susan Chen', 'amount' => 1850, 'donations' => 12, 'rank' => 2],
                    ['name' => 'Anonymous Donor', 'amount' => 1500, 'donations' => 3, 'rank' => 3],
                    ['name' => 'Sarah Johnson', 'amount' => 1200, 'donations' => 15, 'rank' => 4],
                    ['name' => 'Ahmed Hassan', 'amount' => 950, 'donations' => 6, 'rank' => 5]
                ];
                
                foreach ($topDonors as $donor): 
                    $badgeClass = $donor['rank'] === 1 ? 'warning' : ($donor['rank'] === 2 ? 'secondary' : ($donor['rank'] === 3 ? 'danger' : 'primary'));
                ?>
                    <div class="top-donor-item d-flex align-items-center mb-3 p-2 rounded">
                        <div class="rank-badge bg-<?= $badgeClass ?> text-white me-3">
                            <?= $donor['rank'] ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><?= htmlspecialchars($donor['name']) ?></h6>
                            <small class="text-muted"><?= $donor['donations'] ?> donations</small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-success">$<?= number_format($donor['amount']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Payment Methods -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-credit-card me-2"></i>
                    Payment Methods
                </h6>
            </div>
            <div class="card-body">
                <div class="payment-method d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-credit-card text-primary me-2"></i>
                        <span class="small">Credit/Debit Cards</span>
                    </div>
                    <span class="small fw-bold">68%</span>
                </div>
                <div class="payment-method d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-paypal text-info me-2"></i>
                        <span class="small">PayPal</span>
                    </div>
                    <span class="small fw-bold">22%</span>
                </div>
                <div class="payment-method d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-bank text-success me-2"></i>
                        <span class="small">Bank Transfer</span>
                    </div>
                    <span class="small fw-bold">8%</span>
                </div>
                <div class="payment-method d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cash text-warning me-2"></i>
                        <span class="small">Other Methods</span>
                    </div>
                    <span class="small fw-bold">2%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-megaphone me-2"></i>
                    Create Fundraising Campaign
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCampaignForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="campaignTitle" class="form-label">Campaign Title</label>
                                <input type="text" class="form-control" id="campaignTitle" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="campaignCategory" class="form-label">Category</label>
                                <select class="form-select" id="campaignCategory" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="emergency">Emergency Relief</option>
                                    <option value="education">Education</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="infrastructure">Infrastructure</option>
                                    <option value="sports">Sports & Recreation</option>
                                    <option value="cultural">Cultural Programs</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="campaignDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="campaignDescription" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignGoal" class="form-label">Fundraising Goal ($)</label>
                                <input type="number" class="form-control" id="campaignGoal" name="goal" min="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignEndDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="campaignEndDate" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="featuredCampaign" name="featured">
                                <label class="form-check-label" for="featuredCampaign">
                                    <i class="bi bi-star"></i> Featured Campaign
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="allowAnonymous" name="allow_anonymous" checked>
                                <label class="form-check-label" for="allowAnonymous">
                                    Allow Anonymous Donations
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="campaignImage" class="form-label">Campaign Image</label>
                        <input type="file" class="form-control" id="campaignImage" name="image" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCampaign()">
                    <i class="bi bi-megaphone me-1"></i>
                    Create Campaign
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Donation Modal -->
<div class="modal fade" id="quickDonationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-heart me-2"></i>
                    Quick Donation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickDonationForm">
                    <div class="mb-3">
                        <label for="donationCampaign" class="form-label">Campaign</label>
                        <select class="form-select" id="donationCampaign" name="campaign" required>
                            <option value="">Select Campaign</option>
                            <option value="1">Emergency Relief Fund</option>
                            <option value="2">Education Scholarship Program</option>
                            <option value="3">Community Center Renovation</option>
                            <option value="4">Healthcare Initiative</option>
                            <option value="5">Youth Sports Program</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="donationAmount" class="form-label">Donation Amount ($)</label>
                        <input type="number" class="form-control" id="donationAmount" name="amount" min="1" step="0.01" required>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="setAmount(25)">$25</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="setAmount(50)">$50</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="setAmount(100)">$100</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAmount(250)">$250</button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="donorName" class="form-label">Donor Name</label>
                        <input type="text" class="form-control" id="donorName" name="donor_name" placeholder="Enter name or 'Anonymous'">
                    </div>
                    
                    <div class="mb-3">
                        <label for="donorEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="donorEmail" name="donor_email" placeholder="For receipt and updates">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="recurringDonation" name="recurring">
                        <label class="form-check-label" for="recurringDonation">
                            Make this a monthly recurring donation
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="processDonation()">
                    <i class="bi bi-credit-card me-1"></i>
                    Process Donation
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.campaign-item {
    transition: var(--abo-transition);
    position: relative;
    overflow: hidden;
}

.campaign-item:hover {
    transform: translateY(-4px);
    box-shadow: var(--abo-shadow-lg);
}

.featured-campaign {
    border: 2px solid var(--abo-primary);
}

.featured-badge, .urgency-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 0.25rem 0.5rem;
    border-radius: var(--abo-radius);
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.urgency-badge {
    right: auto;
    left: 10px;
}

.campaign-image {
    height: 180px;
    position: relative;
    overflow: hidden;
}

.placeholder-image {
    width: 100%;
    height: 100%;
}

.campaign-badges {
    position: absolute;
    bottom: 10px;
    left: 10px;
    z-index: 2;
}

.donation-item {
    transition: var(--abo-transition);
    background: var(--abo-gray-50);
}

.donation-item:hover {
    background: var(--abo-gray-100);
    transform: scale(1.02);
}

.rank-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.top-donor-item {
    transition: var(--abo-transition);
}

.top-donor-item:hover {
    background-color: var(--abo-gray-50);
    transform: scale(1.02);
}

.payment-method {
    transition: var(--abo-transition);
    padding: 0.25rem;
    border-radius: var(--abo-radius);
}

.payment-method:hover {
    background-color: var(--abo-gray-50);
}

.chart-container {
    height: 300px;
    background: var(--abo-gray-50);
    border-radius: var(--abo-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed var(--abo-gray-300);
}

.progress-section .progress {
    border-radius: 10px;
}

.progress-section .progress-bar {
    border-radius: 10px;
}

@media (max-width: 768px) {
    .campaign-card {
        margin-bottom: 1rem !important;
    }
    
    .action-icon {
        width: 30px;
        height: 30px;
        font-size: 0.875rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    document.getElementById('campaignEndDate').min = new Date().toISOString().split('T')[0];
});

function quickDonation() {
    const modal = new bootstrap.Modal(document.getElementById('quickDonationModal'));
    modal.show();
}

function createCampaign() {
    const form = document.getElementById('createCampaignForm');
    const formData = new FormData(form);
    
    // Here you would normally send the data to the server
    console.log('Creating campaign with data:', Object.fromEntries(formData));
    
    // Simulate success
    alert('Campaign created successfully!');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('createCampaignModal'));
    modal.hide();
    form.reset();
    
    // Refresh page (in real app, you'd add the campaign to the DOM)
    window.location.reload();
}

function donateToCampaign(campaignId) {
    // Pre-select the campaign in quick donation modal
    const modal = new bootstrap.Modal(document.getElementById('quickDonationModal'));
    document.getElementById('donationCampaign').value = campaignId;
    modal.show();
}

function setAmount(amount) {
    document.getElementById('donationAmount').value = amount;
}

function processDonation() {
    const form = document.getElementById('quickDonationForm');
    const formData = new FormData(form);
    
    // Here you would normally process the payment
    console.log('Processing donation with data:', Object.fromEntries(formData));
    
    // Simulate success
    alert('Thank you for your donation! You will receive a confirmation email shortly.');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('quickDonationModal'));
    modal.hide();
    form.reset();
    
    // Refresh page to show updated statistics
    window.location.reload();
}

// Mock chart for demonstration
document.addEventListener('DOMContentLoaded', function() {
    const chartContainer = document.querySelector('.chart-container');
    chartContainer.innerHTML = `
        <div class="text-center">
            <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">Donation Analytics Chart</h5>
            <p class="text-muted">Interactive chart showing donation trends over time</p>
            <small class="text-muted">Chart.js integration would go here</small>
        </div>
    `;
});
</script>
