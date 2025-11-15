<?php
/**
 * Admin Hierarchy Management View
 * Comprehensive CRUD interface for System Admins
 */

$title = $title ?? 'Hierarchy Management';
$hierarchy_data = $hierarchy_data ?? [];
$hierarchy_stats = $hierarchy_stats ?? [];
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">🏢 Organizational Hierarchy Management</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGodinaModal">
                        <i class="bi bi-plus-circle"></i> Add Godina
                    </button>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createGamtaModal">
                        <i class="bi bi-plus-circle"></i> Add Gamta
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createGurmuModal">
                        <i class="bi bi-plus-circle"></i> Add Gurmu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $hierarchy_stats['total_godinas'] ?? 0 ?></h4>
                            <p class="mb-0">Godinas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-diagram-3 fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $hierarchy_stats['total_gamtas'] ?? 0 ?></h4>
                            <p class="mb-0">Gamtas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-diagram-2 fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $hierarchy_stats['total_gurmus'] ?? 0 ?></h4>
                            <p class="mb-0">Gurmus</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $hierarchy_stats['total_members'] ?? 0 ?></h4>
                            <p class="mb-0">Members</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hierarchy Tree View -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Organizational Structure</h5>
                </div>
                <div class="card-body">
                    <div class="hierarchy-tree" id="hierarchyTree">
                        <?php if (!empty($hierarchy_data)): ?>
                            <?php foreach ($hierarchy_data as $godina): ?>
                                <div class="godina-node mb-4" data-godina-id="<?= $godina['id'] ?>">
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary text-white rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-diagram-3 me-2"></i>
                                            <strong><?= htmlspecialchars($godina['name']) ?> (<?= htmlspecialchars($godina['code']) ?>)</strong>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-light" onclick="editGodina(<?= $godina['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-light" onclick="deleteGodina(<?= $godina['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <?php if (!empty($godina['gamtas'])): ?>
                                        <div class="gamtas-container mt-2 ms-4">
                                            <?php foreach ($godina['gamtas'] as $gamta): ?>
                                                <div class="gamta-node mb-3" data-gamta-id="<?= $gamta['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-center p-2 bg-info text-white rounded">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-diagram-2 me-2"></i>
                                                            <strong><?= htmlspecialchars($gamta['name']) ?> (<?= htmlspecialchars($gamta['code']) ?>)</strong>
                                                        </div>
                                                        <div class="btn-group">
                                                            <button class="btn btn-sm btn-outline-light" onclick="editGamta(<?= $gamta['id'] ?>)">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-light" onclick="deleteGamta(<?= $gamta['id'] ?>)">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($gamta['gurmus'])): ?>
                                                        <div class="gurmus-container mt-2 ms-4">
                                                            <?php foreach ($gamta['gurmus'] as $gurmu): ?>
                                                                <div class="gurmu-node mb-2" data-gurmu-id="<?= $gurmu['id'] ?>">
                                                                    <div class="d-flex justify-content-between align-items-center p-2 bg-success text-white rounded">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="bi bi-people me-2"></i>
                                                                            <?= htmlspecialchars($gurmu['name']) ?> (<?= htmlspecialchars($gurmu['code']) ?>)
                                                                        </div>
                                                                        <div class="btn-group">
                                                                            <button class="btn btn-sm btn-outline-light" onclick="editGurmu(<?= $gurmu['id'] ?>)">
                                                                                <i class="bi bi-pencil"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-light" onclick="deleteGurmu(<?= $gurmu['id'] ?>)">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-diagram-3 display-1 text-muted"></i>
                                <h4 class="text-muted">No Hierarchy Data</h4>
                                <p class="text-muted">Start by creating your first Godina</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Godina Modal -->
<div class="modal fade" id="createGodinaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Godina</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createGodinaForm">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?? '' ?>">
                    <input type="hidden" name="operation" value="create">
                    
                    <div class="mb-3">
                        <label for="godina_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="godina_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="godina_code" class="form-label">Code *</label>
                        <input type="text" class="form-control" id="godina_code" name="code" required maxlength="50">
                    </div>
                    
                    <div class="mb-3">
                        <label for="godina_description" class="form-label">Description</label>
                        <textarea class="form-control" id="godina_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="godina_contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="godina_contact_email" name="contact_email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="godina_contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" id="godina_contact_phone" name="contact_phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="godina_address" class="form-label">Address</label>
                        <textarea class="form-control" id="godina_address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="godina_website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="godina_website" name="website">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Godina</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Gamta Modal -->
<div class="modal fade" id="createGamtaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Gamta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createGamtaForm">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?? '' ?>">
                    <input type="hidden" name="operation" value="create">
                    
                    <div class="mb-3">
                        <label for="gamta_godina_id" class="form-label">Godina *</label>
                        <select class="form-control" id="gamta_godina_id" name="godina_id" required>
                            <option value="">Select Godina</option>
                            <?php foreach ($hierarchy_data as $godina): ?>
                                <option value="<?= $godina['id'] ?>"><?= htmlspecialchars($godina['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gamta_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="gamta_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gamta_code" class="form-label">Code *</label>
                        <input type="text" class="form-control" id="gamta_code" name="code" required maxlength="50">
                    </div>
                    
                    <div class="mb-3">
                        <label for="gamta_description" class="form-label">Description</label>
                        <textarea class="form-control" id="gamta_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gamta_contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="gamta_contact_email" name="contact_email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gamta_contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" id="gamta_contact_phone" name="contact_phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gamta_timezone" class="form-label">Timezone</label>
                        <input type="text" class="form-control" id="gamta_timezone" name="timezone" placeholder="e.g., America/New_York">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Create Gamta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Gurmu Modal -->
<div class="modal fade" id="createGurmuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Gurmu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createGurmuForm">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?? '' ?>">
                    <input type="hidden" name="operation" value="create">
                    
                    <div class="mb-3">
                        <label for="gurmu_gamta_id" class="form-label">Gamta *</label>
                        <select class="form-control" id="gurmu_gamta_id" name="gamta_id" required>
                            <option value="">Select Gamta</option>
                            <?php foreach ($hierarchy_data as $godina): ?>
                                <?php if (!empty($godina['gamtas'])): ?>
                                    <optgroup label="<?= htmlspecialchars($godina['name']) ?>">
                                        <?php foreach ($godina['gamtas'] as $gamta): ?>
                                            <option value="<?= $gamta['id'] ?>"><?= htmlspecialchars($gamta['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gurmu_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="gurmu_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gurmu_code" class="form-label">Code *</label>
                        <input type="text" class="form-control" id="gurmu_code" name="code" required maxlength="50">
                    </div>
                    
                    <div class="mb-3">
                        <label for="gurmu_description" class="form-label">Description</label>
                        <textarea class="form-control" id="gurmu_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gurmu_membership_fee" class="form-label">Membership Fee</label>
                                <input type="number" class="form-control" id="gurmu_membership_fee" name="membership_fee" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gurmu_currency" class="form-label">Currency</label>
                                <select class="form-control" id="gurmu_currency" name="currency">
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                    <option value="CAD">CAD</option>
                                    <option value="AUD">AUD</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gurmu_meeting_schedule" class="form-label">Meeting Schedule</label>
                        <input type="text" class="form-control" id="gurmu_meeting_schedule" name="meeting_schedule" placeholder="e.g., Every Sunday 3:00 PM">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Gurmu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CSRF token
    if (!window.csrfToken) {
        window.csrfToken = '<?= $_SESSION['_token'] ?? '' ?>';
    }
    
    // Handle form submissions
    ['createGodinaForm', 'createGamtaForm', 'createGurmuForm'].forEach(formId => {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const endpoint = formId.includes('Godina') ? '/admin/godina-management' : 
                           formId.includes('Gamta') ? '/admin/gamta-management' : 
                           '/admin/gurmu-management';
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});

// Edit functions
function editGodina(id) {
    // Implementation for editing Godina
    console.log('Edit Godina:', id);
}

function editGamta(id) {
    // Implementation for editing Gamta
    console.log('Edit Gamta:', id);
}

function editGurmu(id) {
    // Implementation for editing Gurmu
    console.log('Edit Gurmu:', id);
}

// Delete functions
function deleteGodina(id) {
    if (confirm('Are you sure you want to delete this Godina? This action cannot be undone.')) {
        deleteEntity('godina', id, '/admin/godina-management');
    }
}

function deleteGamta(id) {
    if (confirm('Are you sure you want to delete this Gamta? This action cannot be undone.')) {
        deleteEntity('gamta', id, '/admin/gamta-management');
    }
}

function deleteGurmu(id) {
    if (confirm('Are you sure you want to delete this Gurmu? This action cannot be undone.')) {
        deleteEntity('gurmu', id, '/admin/gurmu-management');
    }
}

function deleteEntity(type, id, endpoint) {
    const formData = new FormData();
    formData.append('_token', window.csrfToken);
    formData.append('operation', 'delete');
    formData.append('id', id);
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

<style>
.hierarchy-tree {
    font-family: monospace;
}

.godina-node, .gamta-node, .gurmu-node {
    transition: all 0.3s ease;
}

.godina-node:hover, .gamta-node:hover, .gurmu-node:hover {
    transform: translateX(5px);
}

.btn-group .btn {
    border: none;
}

.btn-outline-light:hover {
    background-color: rgba(255, 255, 255, 0.2);
}
</style>