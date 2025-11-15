<?php 
$pageTitle = $title ?? 'Registration Management';
$activePage = 'hybrid-registration';
include '../layouts/admin-header.php'; 
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user-plus me-3"></i>
                    Registration Management
                </h1>
                <p class="page-subtitle">Manage hybrid registration system and approval workflows</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#systemConfigModal">
                    <i class="fas fa-cog me-2"></i>
                    System Config
                </button>
                <button class="btn btn-primary" id="refreshData">
                    <i class="fas fa-sync-alt me-2"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-primary">
                <div class="stat-icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['pending_registrations'] ?? 0 ?></div>
                    <div class="stat-label">Pending Registrations</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-success">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['approved_today'] ?? 0 ?></div>
                    <div class="stat-label">Approved Today</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-warning">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= count($pending_approvals) ?></div>
                    <div class="stat-label">Your Pending Approvals</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-info">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['internal_emails_created'] ?? 0 ?></div>
                    <div class="stat-label">Internal Emails</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Alerts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <!-- Your Pending Approvals -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        Your Pending Approvals
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_approvals)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h6>All Caught Up!</h6>
                            <p class="text-muted">You have no pending approval requests.</p>
                        </div>
                    <?php else: ?>
                        <div class="approval-list">
                            <?php foreach ($pending_approvals as $approval): ?>
                                <div class="approval-item" data-workflow-id="<?= $approval['workflow_id'] ?>">
                                    <div class="approval-avatar">
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($approval['first_name'], 0, 1) . substr($approval['last_name'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="approval-info">
                                        <h6 class="approval-name">
                                            <?= htmlspecialchars($approval['first_name'] . ' ' . $approval['last_name']) ?>
                                        </h6>
                                        <p class="approval-email"><?= htmlspecialchars($approval['personal_email']) ?></p>
                                        <small class="approval-meta">
                                            <i class="fas fa-clock me-1"></i>
                                            Submitted <?= date('M j, Y g:i A', strtotime($approval['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="approval-actions">
                                        <button class="btn btn-sm btn-success me-2" 
                                                onclick="processApproval(<?= $approval['workflow_id'] ?>, 'approved')">
                                            <i class="fas fa-check me-1"></i>
                                            Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger me-2" 
                                                onclick="processApproval(<?= $approval['workflow_id'] ?>, 'rejected')">
                                            <i class="fas fa-times me-1"></i>
                                            Reject
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="viewRegistrationDetails(<?= $approval['registration_id'] ?>)">
                                            <i class="fas fa-eye me-1"></i>
                                            View
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- System Health -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-heartbeat me-2"></i>
                        System Health
                    </h6>
                </div>
                <div class="card-body">
                    <div class="health-metric">
                        <div class="health-label">Email Queue</div>
                        <div class="health-status">
                            <span class="badge bg-success">Healthy</span>
                            <small class="health-count">12 pending</small>
                        </div>
                    </div>
                    <div class="health-metric">
                        <div class="health-label">Approval Workflows</div>
                        <div class="health-status">
                            <span class="badge bg-success">Healthy</span>
                            <small class="health-count"><?= count($pending_approvals) ?> active</small>
                        </div>
                    </div>
                    <div class="health-metric">
                        <div class="health-label">Internal Emails</div>
                        <div class="health-status">
                            <span class="badge bg-warning">Attention</span>
                            <small class="health-count">2 failed</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="quick-stat">
                        <div class="quick-stat-label">This Week</div>
                        <div class="quick-stat-value"><?= $stats['registrations_this_week'] ?? 0 ?> registrations</div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-label">Average Approval Time</div>
                        <div class="quick-stat-value"><?= $stats['avg_approval_time'] ?? '2.5' ?> hours</div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-label">Success Rate</div>
                        <div class="quick-stat-value"><?= $stats['success_rate'] ?? '94' ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Management Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="registrationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-pane" 
                            type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>
                        Pending Registrations
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-pane" 
                            type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>
                        Approved
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-pane" 
                            type="button" role="tab">
                        <i class="fas fa-times-circle me-2"></i>
                        Rejected
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="emails-tab" data-bs-toggle="tab" data-bs-target="#emails-pane" 
                            type="button" role="tab">
                        <i class="fas fa-envelope me-2"></i>
                        Internal Emails
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="registrationTabContent">
                <!-- Pending Registrations -->
                <div class="tab-pane fade show active" id="pending-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pendingRegistrationsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Target Level</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Approved Registrations -->
                <div class="tab-pane fade" id="approved-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover" id="approvedRegistrationsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Internal Email</th>
                                    <th>Hierarchy</th>
                                    <th>Position</th>
                                    <th>Approved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Rejected Registrations -->
                <div class="tab-pane fade" id="rejected-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover" id="rejectedRegistrationsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Target Level</th>
                                    <th>Rejected</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Internal Emails -->
                <div class="tab-pane fade" id="emails-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover" id="internalEmailsTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Internal Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Registration Details Modal -->
<div class="modal fade" id="registrationDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registration Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="registrationDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">Process Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approvalForm">
                <div class="modal-body">
                    <input type="hidden" id="approvalWorkflowId" name="workflow_id">
                    <input type="hidden" id="approvalDecision" name="decision">
                    
                    <div class="mb-3">
                        <label for="approvalComments" class="form-label">Comments</label>
                        <textarea class="form-control" id="approvalComments" name="comments" rows="3" 
                                  placeholder="Add comments about your decision (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="approvalSubmitBtn">Process</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- System Configuration Modal -->
<div class="modal fade" id="systemConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">System Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Email Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">Internal Email Domain</label>
                            <input type="text" class="form-control" value="abo-wbo.org" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default Email Quota (MB)</label>
                            <input type="number" class="form-control" value="1024">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Approval Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">Auto-approval Timeout (hours)</label>
                            <input type="number" class="form-control" value="72">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notification Frequency</label>
                            <select class="form-select">
                                <option>Immediate</option>
                                <option>Hourly</option>
                                <option>Daily</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/admin-footer.php'; ?>

<script>
// Page-specific JavaScript
$(document).ready(function() {
    // Initialize datatables
    initializeDataTables();
    
    // Load initial data
    loadPendingRegistrations();
    
    // Set up event listeners
    setupEventListeners();
});

function initializeDataTables() {
    $('#pendingRegistrationsTable, #approvedRegistrationsTable, #rejectedRegistrationsTable, #internalEmailsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[3, 'desc']], // Sort by date column
        language: {
            emptyTable: "No data available",
            search: "Search registrations:"
        }
    });
}

function loadPendingRegistrations() {
    // AJAX call to load data
    // Implementation would go here
}

function setupEventListeners() {
    // Tab change events
    $('#registrationTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).data('bs-target');
        switch(target) {
            case '#approved-pane':
                loadApprovedRegistrations();
                break;
            case '#rejected-pane':
                loadRejectedRegistrations();
                break;
            case '#emails-pane':
                loadInternalEmails();
                break;
        }
    });
    
    // Approval form submission
    $('#approvalForm').on('submit', function(e) {
        e.preventDefault();
        submitApproval();
    });
}

function processApproval(workflowId, decision) {
    $('#approvalWorkflowId').val(workflowId);
    $('#approvalDecision').val(decision);
    $('#approvalModalTitle').text(decision === 'approved' ? 'Approve Registration' : 'Reject Registration');
    $('#approvalSubmitBtn').text(decision === 'approved' ? 'Approve' : 'Reject');
    $('#approvalSubmitBtn').removeClass('btn-primary btn-danger').addClass(decision === 'approved' ? 'btn-success' : 'btn-danger');
    $('#approvalModal').modal('show');
}

function viewRegistrationDetails(registrationId) {
    // Load registration details via AJAX
    $('#registrationDetailsModal').modal('show');
}

function submitApproval() {
    // Submit approval decision via AJAX
    // Implementation would go here
}
</script>