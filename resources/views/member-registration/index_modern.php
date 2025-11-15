<?php
$currentPage = 'member-registration';
?>

<!-- Modern Member Registration Interface -->
<style>
    .registration-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .registration-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -2px rgba(0, 0, 0, 0.15);
    }
    
    .registration-pending {
        border-left: 4px solid #fbbf24;
    }
    
    .registration-approved {
        border-left: 4px solid var(--primary-green);
    }
    
    .registration-rejected {
        border-left: 4px solid var(--primary-red);
    }
    
    .registration-processing {
        border-left: 4px solid #3b82f6;
    }
    
    .member-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.5rem;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .registration-status-badge {
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .status-pending {
        background: rgba(251, 191, 36, 0.1);
        color: #d97706;
        border-color: rgba(251, 191, 36, 0.2);
    }
    
    .status-approved {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border-color: rgba(45, 80, 22, 0.2);
    }
    
    .status-rejected {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border-color: rgba(139, 21, 56, 0.2);
    }
    
    .status-processing {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .hierarchy-badge {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .registration-wizard {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .wizard-header {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .wizard-steps {
        display: flex;
        justify-content: center;
        margin: 2rem 0;
        position: relative;
    }
    
    .wizard-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1;
        max-width: 150px;
    }
    
    .wizard-step::after {
        content: '';
        position: absolute;
        top: 20px;
        left: 70%;
        right: -50%;
        height: 2px;
        background: #e5e7eb;
        z-index: 1;
    }
    
    .wizard-step:last-child::after {
        display: none;
    }
    
    .wizard-step.active::after {
        background: var(--primary-green);
    }
    
    .wizard-step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        position: relative;
        z-index: 2;
        margin-bottom: 0.5rem;
    }
    
    .wizard-step.active .wizard-step-circle {
        background: var(--primary-green);
        color: white;
    }
    
    .wizard-step.completed .wizard-step-circle {
        background: var(--primary-green);
        color: white;
    }
    
    .wizard-step-label {
        font-size: 0.875rem;
        font-weight: 500;
        text-align: center;
        color: #6b7280;
    }
    
    .wizard-step.active .wizard-step-label {
        color: var(--primary-green);
    }
    
    .document-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f9fafb;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .document-upload-area:hover {
        border-color: var(--primary-green);
        background: rgba(45, 80, 22, 0.05);
    }
    
    .document-upload-area.dragover {
        border-color: var(--primary-green);
        background: rgba(45, 80, 22, 0.1);
    }
    
    .uploaded-document {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .document-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .approval-action-btn {
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .approve-btn {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        box-shadow: 0 2px 4px rgba(45, 80, 22, 0.2);
    }
    
    .approve-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .reject-btn {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        box-shadow: 0 2px 4px rgba(139, 21, 56, 0.2);
    }
    
    .reject-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(139, 21, 56, 0.3);
        color: white;
    }
    
    .review-btn {
        background: #f3f4f6;
        color: #374151;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .review-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }
    
    .registration-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .registration-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary-green), var(--primary-green-light));
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-item::before {
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
    
    .bulk-action-panel {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }
    
    .member-search-bar {
        background: white;
        border-radius: 25px;
        padding: 0.75rem 1.5rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .member-search-bar:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(45, 80, 22, 0.1);
    }
</style>

<div class="page-header">
    <h1 class="page-title">Member Registration</h1>
    <p class="page-description">Manage member registrations, approvals, and onboarding with streamlined workflow processes</p>
</div>

<!-- Registration Statistics -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #fbbf24;"><?= $registration_stats['pending'] ?? 0 ?></div>
            <div class="text-muted fw-500">Pending Review</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-green);"><?= $registration_stats['approved'] ?? 0 ?></div>
            <div class="text-muted fw-500">Approved</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-red);"><?= $registration_stats['rejected'] ?? 0 ?></div>
            <div class="text-muted fw-500">Rejected</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #3b82f6;"><?= $registration_stats['this_month'] ?? 0 ?></div>
            <div class="text-muted fw-500">This Month</div>
        </div>
    </div>
</div>

<!-- Control Panel -->
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
                        
                        <input type="radio" class="btn-check" name="viewMode" id="timelineView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="timelineView">
                            <i class="bi bi-clock-history"></i> Timeline
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <input type="text" class="form-control member-search-bar" placeholder="🔍 Search registrations..." id="memberSearch">
            </div>
            
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">⏳ Pending</option>
                    <option value="processing">🔄 Processing</option>
                    <option value="approved">✅ Approved</option>
                    <option value="rejected">❌ Rejected</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="showRegistrationModal()">
                        <i class="bi bi-person-plus"></i> Register
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/member-registration/export?format=excel">📊 Excel Export</a></li>
                            <li><a class="dropdown-item" href="/member-registration/export?format=pdf">📄 PDF Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Panel -->
<div class="bulk-action-panel" id="bulkActionsPanel" style="display: none;">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <span class="fw-600"><span id="selectedCount">0</span> registrations selected</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-success" onclick="bulkApprove()">
                <i class="bi bi-check-circle"></i> Bulk Approve
            </button>
            <button class="btn btn-sm btn-danger" onclick="bulkReject()">
                <i class="bi bi-x-circle"></i> Bulk Reject
            </button>
            <button class="btn btn-sm btn-secondary" onclick="clearSelection()">
                <i class="bi bi-x"></i> Clear
            </button>
        </div>
    </div>
</div>

<!-- Card View -->
<div id="cardViewContainer">
    <div class="row g-4" id="registrationsGrid">
        <?php if (!empty($registrations)): ?>
            <?php foreach ($registrations as $registration): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 registration-item" 
                     data-status="<?= $registration['status'] ?? 'pending' ?>"
                     data-search="<?= strtolower(($registration['first_name'] ?? '') . ' ' . ($registration['last_name'] ?? '') . ' ' . ($registration['email'] ?? '')) ?>">
                    <div class="registration-card registration-<?= $registration['status'] ?? 'pending' ?>">
                        <!-- Selection Checkbox -->
                        <div class="position-absolute top-0 end-0 p-3">
                            <div class="form-check">
                                <input class="form-check-input registration-checkbox" type="checkbox" 
                                       value="<?= $registration['id'] ?>" onchange="updateBulkActions()">
                            </div>
                        </div>
                        
                        <!-- Registration Header -->
                        <div class="card-header bg-transparent border-0 p-4 pb-0">
                            <div class="d-flex align-items-start gap-3">
                                <div class="member-avatar">
                                    <?= substr($registration['first_name'] ?? 'M', 0, 1) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 fw-600">
                                        <?= htmlspecialchars(($registration['first_name'] ?? '') . ' ' . ($registration['last_name'] ?? '')) ?>
                                    </h5>
                                    <div class="text-muted mb-2"><?= htmlspecialchars($registration['email'] ?? '') ?></div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="registration-status-badge status-<?= $registration['status'] ?? 'pending' ?>">
                                            <?= getRegistrationStatusIcon($registration['status'] ?? 'pending') ?> 
                                            <?= ucfirst($registration['status'] ?? 'pending') ?>
                                        </span>
                                        <?php if (isset($registration['hierarchy_level'])): ?>
                                            <span class="hierarchy-badge">
                                                <?= ucfirst($registration['hierarchy_level']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Registration Content -->
                        <div class="card-body p-4 pt-2">
                            <!-- Personal Information -->
                            <div class="mb-3">
                                <div class="row g-2 text-sm">
                                    <div class="col-6">
                                        <div class="text-muted">Phone</div>
                                        <div class="fw-500"><?= htmlspecialchars($registration['phone'] ?? 'Not provided') ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Date of Birth</div>
                                        <div class="fw-500"><?= isset($registration['date_of_birth']) ? date('M j, Y', strtotime($registration['date_of_birth'])) : 'Not provided' ?></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted">Address</div>
                                        <div class="fw-500"><?= htmlspecialchars($registration['address'] ?? 'Not provided') ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hierarchy Assignment -->
                            <?php if (isset($registration['assigned_gurmu']) || isset($registration['assigned_gamta']) || isset($registration['assigned_godina'])): ?>
                                <div class="mb-3">
                                    <div class="text-muted mb-1">Assigned to</div>
                                    <div class="text-sm">
                                        <?php if (isset($registration['assigned_gurmu'])): ?>
                                            <div><strong>Gurmu:</strong> <?= htmlspecialchars($registration['assigned_gurmu']) ?></div>
                                        <?php endif; ?>
                                        <?php if (isset($registration['assigned_gamta'])): ?>
                                            <div><strong>Gamta:</strong> <?= htmlspecialchars($registration['assigned_gamta']) ?></div>
                                        <?php endif; ?>
                                        <?php if (isset($registration['assigned_godina'])): ?>
                                            <div><strong>Godina:</strong> <?= htmlspecialchars($registration['assigned_godina']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Registration Footer -->
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('M j, Y', strtotime($registration['created_at'] ?? '')) ?>
                                </small>
                                
                                <div class="d-flex gap-1">
                                    <button class="approval-action-btn review-btn" onclick="viewRegistration(<?= $registration['id'] ?>)" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if (($registration['status'] ?? 'pending') === 'pending'): ?>
                                        <button class="approval-action-btn approve-btn" onclick="approveRegistration(<?= $registration['id'] ?>)" title="Approve">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button class="approval-action-btn reject-btn" onclick="rejectRegistration(<?= $registration['id'] ?>)" title="Reject">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-person-plus" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Registrations Found</h4>
                    <p class="text-muted mb-4">No member registration requests at the moment</p>
                    <button class="btn btn-primary" onclick="showRegistrationModal()">
                        <i class="bi bi-person-plus"></i> Register First Member
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Timeline View (Hidden by default) -->
<div id="timelineViewContainer" style="display: none;">
    <div class="registration-timeline">
        <?php if (!empty($registrations)): ?>
            <?php foreach ($registrations as $registration): ?>
                <div class="timeline-item registration-item" 
                     data-status="<?= $registration['status'] ?? 'pending' ?>"
                     data-search="<?= strtolower(($registration['first_name'] ?? '') . ' ' . ($registration['last_name'] ?? '') . ' ' . ($registration['email'] ?? '')) ?>">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="member-avatar" style="width: 48px; height: 48px; font-size: 1.1rem;">
                                <?= substr($registration['first_name'] ?? 'M', 0, 1) ?>
                            </div>
                            <div>
                                <h6 class="fw-600 mb-1">
                                    <?= htmlspecialchars(($registration['first_name'] ?? '') . ' ' . ($registration['last_name'] ?? '')) ?>
                                </h6>
                                <div class="text-muted"><?= htmlspecialchars($registration['email'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="registration-status-badge status-<?= $registration['status'] ?? 'pending' ?>">
                                <?= getRegistrationStatusIcon($registration['status'] ?? 'pending') ?> 
                                <?= ucfirst($registration['status'] ?? 'pending') ?>
                            </span>
                            <small class="text-muted">
                                <?= date('M j, Y', strtotime($registration['created_at'] ?? '')) ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <small class="text-muted">Phone</small>
                            <div><?= htmlspecialchars($registration['phone'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Date of Birth</small>
                            <div><?= isset($registration['date_of_birth']) ? date('M j, Y', strtotime($registration['date_of_birth'])) : 'Not provided' ?></div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Address</small>
                            <div><?= htmlspecialchars($registration['address'] ?? 'Not provided') ?></div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewRegistration(<?= $registration['id'] ?>)">
                            View Details
                        </button>
                        <?php if (($registration['status'] ?? 'pending') === 'pending'): ?>
                            <button class="btn btn-sm btn-success" onclick="approveRegistration(<?= $registration['id'] ?>)">
                                Approve
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectRegistration(<?= $registration['id'] ?>)">
                                Reject
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content registration-wizard">
            <div class="wizard-header">
                <h3 class="mb-2">New Member Registration</h3>
                <p class="mb-0 opacity-90">Complete the registration process step by step</p>
            </div>
            
            <!-- Wizard Steps -->
            <div class="wizard-steps">
                <div class="wizard-step active">
                    <div class="wizard-step-circle">1</div>
                    <div class="wizard-step-label">Personal Info</div>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step-circle">2</div>
                    <div class="wizard-step-label">Contact Details</div>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step-circle">3</div>
                    <div class="wizard-step-label">Documents</div>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step-circle">4</div>
                    <div class="wizard-step-label">Assignment</div>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step-circle">5</div>
                    <div class="wizard-step-label">Review</div>
                </div>
            </div>
            
            <form method="POST" action="/member-registration/create" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Step 1: Personal Information -->
                    <div class="wizard-content" id="step1">
                        <h5 class="mb-4">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-500">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required placeholder="Enter first name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required placeholder="Enter last name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Date of Birth *</label>
                                <input type="date" name="date_of_birth" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Marital Status</label>
                                <select name="marital_status" class="form-select">
                                    <option value="">Select status</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="widowed">Widowed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Occupation</label>
                                <input type="text" name="occupation" class="form-control" placeholder="Enter occupation">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Contact Details -->
                    <div class="wizard-content" id="step2" style="display: none;">
                        <h5 class="mb-4">Contact Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-500">Email Address *</label>
                                <input type="email" name="email" class="form-control" required placeholder="member@email.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" required placeholder="+251...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-500">Address *</label>
                                <textarea name="address" class="form-control" rows="3" required placeholder="Enter complete address"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" placeholder="Emergency contact name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" class="form-control" placeholder="Emergency contact phone">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3: Documents -->
                    <div class="wizard-content" id="step3" style="display: none;">
                        <h5 class="mb-4">Required Documents</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-500">ID Card/Passport</label>
                                <div class="document-upload-area" onclick="document.getElementById('idUpload').click()">
                                    <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--primary-green);"></i>
                                    <div class="mt-2">
                                        <strong>Click to upload ID Card/Passport</strong>
                                        <div class="text-muted">PNG, JPG, PDF up to 5MB</div>
                                    </div>
                                </div>
                                <input type="file" id="idUpload" name="id_document" class="d-none" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-500">Photo</label>
                                <div class="document-upload-area" onclick="document.getElementById('photoUpload').click()">
                                    <i class="bi bi-camera" style="font-size: 2rem; color: var(--primary-green);"></i>
                                    <div class="mt-2">
                                        <strong>Click to upload Photo</strong>
                                        <div class="text-muted">PNG, JPG up to 2MB</div>
                                    </div>
                                </div>
                                <input type="file" id="photoUpload" name="photo" class="d-none" accept=".jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 4: Hierarchy Assignment -->
                    <div class="wizard-content" id="step4" style="display: none;">
                        <h5 class="mb-4">Hierarchy Assignment</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-500">Godina *</label>
                                <select name="godina_id" class="form-select" required onchange="loadGamtas(this.value)">
                                    <option value="">Select Godina</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-500">Gamta *</label>
                                <select name="gamta_id" class="form-select" required onchange="loadGurmus(this.value)">
                                    <option value="">Select Gamta</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-500">Gurmu *</label>
                                <select name="gurmu_id" class="form-select" required>
                                    <option value="">Select Gurmu</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-500">Position (Optional)</label>
                                <select name="position_id" class="form-select">
                                    <option value="">Select position (if applicable)</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 5: Review -->
                    <div class="wizard-content" id="step5" style="display: none;">
                        <h5 class="mb-4">Review & Submit</h5>
                        <div id="reviewContent">
                            <!-- Populated via JavaScript -->
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="previousStep()" style="display: none;">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">Next</button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                        <i class="bi bi-person-plus"></i> Submit Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 5;

document.addEventListener('DOMContentLoaded', function() {
    // View switching functionality
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const timelineView = document.getElementById('timelineView');
    const cardContainer = document.getElementById('cardViewContainer');
    const timelineContainer = document.getElementById('timelineViewContainer');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'block';
            timelineContainer.style.display = 'none';
        }
    });
    
    timelineView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            timelineContainer.style.display = 'block';
        }
    });
    
    // Search functionality
    const searchInput = document.getElementById('memberSearch');
    const statusFilter = document.getElementById('statusFilter');
    
    function applyFilters() {
        const searchValue = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        
        document.querySelectorAll('.registration-item').forEach(item => {
            const matchesSearch = !searchValue || item.dataset.search.includes(searchValue);
            const matchesStatus = !statusValue || item.dataset.status === statusValue;
            item.style.display = matchesSearch && matchesStatus ? 'block' : 'none';
        });
    }
    
    searchInput.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
});

function showRegistrationModal() {
    new bootstrap.Modal(document.getElementById('registrationModal')).show();
}

function nextStep() {
    if (currentStep < totalSteps) {
        document.getElementById(`step${currentStep}`).style.display = 'none';
        document.querySelector(`.wizard-step:nth-child(${currentStep})`).classList.remove('active');
        document.querySelector(`.wizard-step:nth-child(${currentStep})`).classList.add('completed');
        
        currentStep++;
        
        document.getElementById(`step${currentStep}`).style.display = 'block';
        document.querySelector(`.wizard-step:nth-child(${currentStep})`).classList.add('active');
        
        updateWizardButtons();
        
        if (currentStep === totalSteps) {
            populateReview();
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        document.getElementById(`step${currentStep}`).style.display = 'none';
        document.querySelector(`.wizard-step:nth-child(${currentStep})`).classList.remove('active');
        
        currentStep--;
        
        document.getElementById(`step${currentStep}`).style.display = 'block';
        document.querySelector(`.wizard-step:nth-child(${currentStep})`).classList.remove('completed');
        document.querySelector(`.wizard-step:nth-child(${currentStep})`).classList.add('active');
        
        updateWizardButtons();
    }
}

function updateWizardButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
    
    if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-block';
    } else {
        nextBtn.style.display = 'inline-block';
        submitBtn.style.display = 'none';
    }
}

function populateReview() {
    // Populate review content with form data
    console.log('Populating review content...');
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
    const count = checkboxes.length;
    const panel = document.getElementById('bulkActionsPanel');
    const countSpan = document.getElementById('selectedCount');
    
    countSpan.textContent = count;
    panel.style.display = count > 0 ? 'block' : 'none';
}

function clearSelection() {
    document.querySelectorAll('.registration-checkbox:checked').forEach(cb => cb.checked = false);
    updateBulkActions();
}

function bulkApprove() {
    if (confirm('Approve all selected registrations?')) {
        // Implement bulk approval
        console.log('Bulk approving...');
    }
}

function bulkReject() {
    if (confirm('Reject all selected registrations?')) {
        // Implement bulk rejection
        console.log('Bulk rejecting...');
    }
}

function viewRegistration(id) {
    window.location.href = `/member-registration/${id}`;
}

function approveRegistration(id) {
    if (confirm('Approve this registration?')) {
        fetch(`/member-registration/${id}/approve`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.ok ? location.reload() : alert('Error approving registration'))
        .catch(() => alert('Error approving registration'));
    }
}

function rejectRegistration(id) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        fetch(`/member-registration/${id}/reject`, {
            method: 'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.ok ? location.reload() : alert('Error rejecting registration'))
        .catch(() => alert('Error rejecting registration'));
    }
}

function loadGamtas(godinaId) {
    // Load gamtas for selected godina
    console.log('Loading gamtas for godina:', godinaId);
}

function loadGurmus(gamtaId) {
    // Load gurmus for selected gamta
    console.log('Loading gurmus for gamta:', gamtaId);
}
</script>

<?php
// Helper functions for UI
function getRegistrationStatusIcon($status) {
    return [
        'pending' => '⏳',
        'processing' => '🔄',
        'approved' => '✅',
        'rejected' => '❌'
    ][$status] ?? '⏳';
}
?>