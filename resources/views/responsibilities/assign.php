<?php
$title = $title ?? 'Assign Responsibilities';
$section = $section ?? 'responsibilities';
$positions = $positions ?? [];
$responsibilities = $responsibilities ?? [];
$organizational_units = $organizational_units ?? [];
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-plus-circle text-primary me-2"></i>
                        Assign Responsibilities & Tasks
                    </h1>
                    <p class="text-muted mb-0">Assign shared and individual responsibilities to position holders</p>
                </div>
                <div>
                    <a href="/responsibilities" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="/responsibilities/assignments" class="btn btn-outline-primary">
                        <i class="bi bi-list-check"></i> View Assignments
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Assignment Form -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="assignmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-assignment" type="button" role="tab">
                                <i class="bi bi-collection me-2"></i>
                                Bulk Assignment by Position
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual-assignment" type="button" role="tab">
                                <i class="bi bi-person-plus me-2"></i>
                                Individual Assignment
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="assignmentTabsContent">
                        <!-- Bulk Assignment Tab -->
                        <div class="tab-pane fade show active" id="bulk-assignment" role="tabpanel">
                            <form method="POST" action="/responsibilities/assign" id="bulkAssignmentForm">
                                <input type="hidden" name="assignment_type" value="bulk_position">
                                
                                <div class="row">
                                    <!-- Position Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="position_id" class="form-label">
                                            <i class="bi bi-person-badge text-primary me-1"></i>
                                            Executive Position *
                                        </label>
                                        <select class="form-select" id="position_id" name="position_id" required onchange="updatePositionPreview()">
                                            <option value="">Select Position</option>
                                            <?php foreach ($positions as $position): ?>
                                                <option value="<?= $position['id'] ?>" 
                                                        data-key="<?= htmlspecialchars($position['key_name']) ?>"
                                                        data-name-en="<?= htmlspecialchars($position['name_en']) ?>"
                                                        data-name-om="<?= htmlspecialchars($position['name_om']) ?>"
                                                        data-description="<?= htmlspecialchars($position['description_en'] ?? '') ?>">
                                                    <?= htmlspecialchars($position['name_en']) ?> 
                                                    (<?= htmlspecialchars($position['name_om']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Level Scope -->
                                    <div class="col-md-6 mb-3">
                                        <label for="level_scope" class="form-label">
                                            <i class="bi bi-diagram-3 text-success me-1"></i>
                                            Organizational Level *
                                        </label>
                                        <select class="form-select" id="level_scope" name="level_scope" required>
                                            <option value="">Select Level</option>
                                            <option value="global">Global Level</option>
                                            <option value="godina">Godina Level</option>
                                            <option value="gamta">Gamta Level</option>
                                            <option value="gurmu">Gurmu Level</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Responsibility Types -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="bi bi-check2-square text-info me-1"></i>
                                        Responsibility Types to Assign *
                                    </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="assign_shared" name="assign_shared" value="1" checked>
                                                        <label class="form-check-label" for="assign_shared">
                                                            <strong>Shared Responsibilities</strong>
                                                            <br>
                                                            <small class="text-muted">5 Core Areas (Applied to ALL positions)</small>
                                                        </label>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-primary d-block">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Includes: Qaboo Ya'ii, Karoora, Gabaasa, Projectoota, Gamaggama
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-success">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="assign_individual" name="assign_individual" value="1" checked>
                                                        <label class="form-check-label" for="assign_individual">
                                                            <strong>Individual Responsibilities</strong>
                                                            <br>
                                                            <small class="text-muted">Position-specific responsibilities</small>
                                                        </label>
                                                    </div>
                                                    <div class="mt-2" id="individualPreview">
                                                        <small class="text-success d-block">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Select a position to see specific responsibilities
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment Options -->
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="priority" class="form-label">
                                            <i class="bi bi-flag text-warning me-1"></i>
                                            Priority Level
                                        </label>
                                        <select class="form-select" id="priority" name="priority">
                                            <option value="1">Low Priority</option>
                                            <option value="2" selected>Medium Priority</option>
                                            <option value="3">High Priority</option>
                                            <option value="4">Critical Priority</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="due_date" class="form-label">
                                            <i class="bi bi-calendar text-danger me-1"></i>
                                            Due Date (Optional)
                                        </label>
                                        <input type="date" class="form-control" id="due_date" name="due_date" 
                                               min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="auto_assign" class="form-label">
                                            <i class="bi bi-gear text-secondary me-1"></i>
                                            Assignment Mode
                                        </label>
                                        <select class="form-select" id="auto_assign" name="auto_assign">
                                            <option value="1" selected>Auto-assign to all position holders</option>
                                            <option value="0">Manual assignment required</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Assignment Preview -->
                                <div id="assignmentPreview" class="alert alert-info d-none">
                                    <h6><i class="bi bi-eye me-2"></i>Assignment Preview</h6>
                                    <div id="previewContent"></div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" onclick="previewAssignment()">
                                        <i class="bi bi-eye"></i> Preview Assignment
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Assign Responsibilities
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Individual Assignment Tab -->
                        <div class="tab-pane fade" id="individual-assignment" role="tabpanel">
                            <form method="POST" action="/responsibilities/assign" id="individualAssignmentForm">
                                <input type="hidden" name="assignment_type" value="individual">
                                
                                <div class="row">
                                    <!-- User Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="user_id" class="form-label">
                                            <i class="bi bi-person text-primary me-1"></i>
                                            Select User *
                                        </label>
                                        <select class="form-select" id="user_id" name="user_id" required onchange="loadUserPositions()">
                                            <option value="">Choose User</option>
                                            <!-- Users will be loaded dynamically -->
                                        </select>
                                    </div>

                                    <!-- Position Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="user_position_id" class="form-label">
                                            <i class="bi bi-person-badge text-success me-1"></i>
                                            User's Position *
                                        </label>
                                        <select class="form-select" id="user_position_id" name="position_id" required disabled>
                                            <option value="">Select user first</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Responsibility Selection -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-list-check text-info me-1"></i>
                                        Select Responsibilities *
                                    </label>
                                    <div class="card">
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            <div id="responsibilityList">
                                                <p class="text-muted text-center">Select a user and position to see available responsibilities</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Individual Assignment Options -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="individual_priority" class="form-label">Priority Level</label>
                                        <select class="form-select" id="individual_priority" name="priority">
                                            <option value="1">Low Priority</option>
                                            <option value="2" selected>Medium Priority</option>
                                            <option value="3">High Priority</option>
                                            <option value="4">Critical Priority</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="individual_due_date" class="form-label">Due Date</label>
                                        <input type="date" class="form-control" id="individual_due_date" name="due_date" 
                                               min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mb-3">
                                    <label for="assignment_notes" class="form-label">Assignment Notes (Optional)</label>
                                    <textarea class="form-control" id="assignment_notes" name="notes" rows="3" 
                                              placeholder="Any specific instructions or notes for this assignment..."></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Assign Selected Responsibilities
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Reference -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        Assignment Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="guideAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sharedGuide">
                                    Shared Responsibilities (5 Core Areas)
                                </button>
                            </h2>
                            <div id="sharedGuide" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                                <div class="accordion-body small">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <strong>Qaboo Ya'ii</strong> - Meetings Management
                                            <br><small class="text-muted">Organize and coordinate meetings</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Karoora</strong> - Planning & Strategic Development
                                            <br><small class="text-muted">Develop and implement plans</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Gabaasa</strong> - Reporting & Documentation
                                            <br><small class="text-muted">Prepare reports and maintain records</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Projectoota</strong> - Projects & Initiatives
                                            <br><small class="text-muted">Lead and manage projects</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Gamaggama</strong> - Evaluation & Assessment
                                            <br><small class="text-muted">Evaluate performance and effectiveness</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#positionGuide">
                                    Executive Positions (7 Roles)
                                </button>
                            </h2>
                            <div id="positionGuide" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                                <div class="accordion-body small">
                                    <ul class="list-unstyled">
                                        <?php foreach ($positions as $position): ?>
                                            <li class="mb-1">
                                                <strong><?= htmlspecialchars($position['name_en']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($position['name_om']) ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Tips -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        Assignment Tips
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border-start border-primary border-3">
                        <h6 class="alert-heading">Bulk Assignment</h6>
                        <p class="mb-0 small">Use bulk assignment to assign all relevant responsibilities to all holders of a specific position at once.</p>
                    </div>
                    
                    <div class="alert alert-light border-start border-success border-3">
                        <h6 class="alert-heading">Individual Assignment</h6>
                        <p class="mb-0 small">Use individual assignment for specific responsibilities or when you need custom settings for particular users.</p>
                    </div>
                    
                    <div class="alert alert-light border-start border-info border-3">
                        <h6 class="alert-heading">Priority Levels</h6>
                        <ul class="mb-0 small">
                            <li><strong>Critical:</strong> Immediate attention required</li>
                            <li><strong>High:</strong> Important, time-sensitive</li>
                            <li><strong>Medium:</strong> Standard priority</li>
                            <li><strong>Low:</strong> Can be delayed if needed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Position data for JavaScript
const positionData = <?= json_encode($positions) ?>;
const responsibilityData = <?= json_encode($responsibilities) ?>;

// Update position preview
function updatePositionPreview() {
    const select = document.getElementById('position_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const positionKey = selectedOption.dataset.key;
        const individualResp = responsibilityData.filter(r => 
            r.responsibility_type === 'individual' && r.position_scope === positionKey
        );
        
        const preview = document.getElementById('individualPreview');
        if (individualResp.length > 0) {
            preview.innerHTML = `
                <small class="text-success d-block">
                    <i class="bi bi-check-circle me-1"></i>
                    ${individualResp.length} individual responsibilities available
                </small>
            `;
        } else {
            preview.innerHTML = `
                <small class="text-warning d-block">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    No individual responsibilities defined for this position
                </small>
            `;
        }
    } else {
        document.getElementById('individualPreview').innerHTML = `
            <small class="text-success d-block">
                <i class="bi bi-info-circle me-1"></i>
                Select a position to see specific responsibilities
            </small>
        `;
    }
}

// Preview assignment
function previewAssignment() {
    const form = document.getElementById('bulkAssignmentForm');
    const formData = new FormData(form);
    
    if (!formData.get('position_id') || !formData.get('level_scope')) {
        alert('Please select position and level scope first');
        return;
    }
    
    // Show preview section
    const preview = document.getElementById('assignmentPreview');
    const content = document.getElementById('previewContent');
    
    const positionSelect = document.getElementById('position_id');
    const positionName = positionSelect.options[positionSelect.selectedIndex].text;
    const levelScope = formData.get('level_scope');
    const assignShared = formData.get('assign_shared');
    const assignIndividual = formData.get('assign_individual');
    
    let previewHtml = `
        <p><strong>Position:</strong> ${positionName}</p>
        <p><strong>Level:</strong> ${levelScope.charAt(0).toUpperCase() + levelScope.slice(1)}</p>
        <p><strong>Assignment Types:</strong></p>
        <ul>
    `;
    
    if (assignShared) {
        previewHtml += '<li>Shared Responsibilities (5 Core Areas)</li>';
    }
    
    if (assignIndividual) {
        previewHtml += '<li>Individual Position Responsibilities</li>';
    }
    
    previewHtml += '</ul>';
    
    content.innerHTML = previewHtml;
    preview.classList.remove('d-none');
}

// Load users for individual assignment
async function loadUsers() {
    try {
        const response = await fetch('/api/users');
        const users = await response.json();
        
        const select = document.getElementById('user_id');
        select.innerHTML = '<option value="">Choose User</option>';
        
        users.forEach(user => {
            select.innerHTML += `
                <option value="${user.id}" data-name="${user.full_name}">
                    ${user.full_name} - ${user.email}
                </option>
            `;
        });
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

// Load user positions
async function loadUserPositions() {
    const userId = document.getElementById('user_id').value;
    const positionSelect = document.getElementById('user_position_id');
    
    if (!userId) {
        positionSelect.innerHTML = '<option value="">Select user first</option>';
        positionSelect.disabled = true;
        return;
    }
    
    try {
        const response = await fetch(`/api/users/${userId}/positions`);
        const positions = await response.json();
        
        positionSelect.innerHTML = '<option value="">Select Position</option>';
        positions.forEach(pos => {
            positionSelect.innerHTML += `
                <option value="${pos.position_id}" 
                        data-org-unit="${pos.organizational_unit_id}"
                        data-level="${pos.level_scope}">
                    ${pos.position_name} - ${pos.organizational_unit_name}
                </option>
            `;
        });
        
        positionSelect.disabled = false;
        positionSelect.onchange = loadUserResponsibilities;
    } catch (error) {
        console.error('Error loading user positions:', error);
    }
}

// Load available responsibilities for user
async function loadUserResponsibilities() {
    const userId = document.getElementById('user_id').value;
    const positionSelect = document.getElementById('user_position_id');
    const selectedOption = positionSelect.options[positionSelect.selectedIndex];
    
    if (!userId || !selectedOption.value) {
        return;
    }
    
    const positionId = selectedOption.value;
    const levelScope = selectedOption.dataset.level;
    
    try {
        const response = await fetch(`/api/responsibilities/available?user_id=${userId}&position_id=${positionId}&level_scope=${levelScope}`);
        const data = await response.json();
        
        const container = document.getElementById('responsibilityList');
        let html = '';
        
        if (data.shared && data.shared.length > 0) {
            html += '<h6 class="text-primary">Shared Responsibilities (5 Core Areas)</h6>';
            data.shared.forEach(resp => {
                html += `
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="responsibility_ids[]" 
                               value="${resp.id}" id="resp_${resp.id}">
                        <label class="form-check-label" for="resp_${resp.id}">
                            <strong>${resp.name_en}</strong>
                            <br><small class="text-muted">${resp.name_om}</small>
                        </label>
                    </div>
                `;
            });
        }
        
        if (data.individual && data.individual.length > 0) {
            html += '<h6 class="text-success mt-3">Individual Responsibilities</h6>';
            data.individual.forEach(resp => {
                html += `
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="responsibility_ids[]" 
                               value="${resp.id}" id="resp_${resp.id}">
                        <label class="form-check-label" for="resp_${resp.id}">
                            <strong>${resp.name_en}</strong>
                            <br><small class="text-muted">${resp.name_om}</small>
                        </label>
                    </div>
                `;
            });
        }
        
        if (!html) {
            html = '<p class="text-muted text-center">No available responsibilities found</p>';
        }
        
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading responsibilities:', error);
    }
}

// Initialize individual assignment tab
document.getElementById('individual-tab').addEventListener('shown.bs.tab', function () {
    loadUsers();
});

// Form validation
document.getElementById('bulkAssignmentForm').addEventListener('submit', function(e) {
    const assignShared = document.getElementById('assign_shared').checked;
    const assignIndividual = document.getElementById('assign_individual').checked;
    
    if (!assignShared && !assignIndividual) {
        e.preventDefault();
        alert('Please select at least one responsibility type to assign');
    }
});
</script>