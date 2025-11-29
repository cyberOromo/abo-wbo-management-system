<?php
$pageTitle = 'Create New Gurmu';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="bi bi-people me-2"></i>
                    Create New Gurmu
                </h1>
                <a href="/hierarchy" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Hierarchy
                </a>
            </div>

            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- Main Form -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i>
                                Gurmu (Community) Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/hierarchy/create/gurmu" id="gurmuForm">
                                <?= csrf_field() ?>

                                <!-- Parent Selection -->
                                <div class="mb-3">
                                    <label for="godina_id" class="form-label">
                                        Godina <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="godina_id" required>
                                        <option value="">Select Godina first</option>
                                        <?php foreach ($godinas as $godina): ?>
                                            <option value="<?= $godina['id'] ?>" data-code="<?= $godina['code'] ?>">
                                                <?= htmlspecialchars($godina['name']) ?> (<?= $godina['code'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="gamta_id" class="form-label">
                                        Parent Gamta <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="gamta_id" name="gamta_id" required>
                                        <option value="">Select Godina first</option>
                                    </select>
                                    <small class="text-muted">Select the Gamta this Gurmu belongs to</small>
                                </div>

                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        Gurmu Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           placeholder="Enter community name (e.g., Toronto, Los Angeles, Melbourne)"
                                           required
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                    <div id="codePreview" class="mt-2 small text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Auto-generated code:</strong> <span id="previewCode" class="badge bg-secondary">Select Gamta first</span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="Enter description about this community"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>

                                <!-- Location Details -->
                                <h6 class="mt-4 mb-3">Location Details</h6>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Community Address</label>
                                    <textarea class="form-control" 
                                              id="address" 
                                              name="address" 
                                              rows="2"
                                              placeholder="Enter community meeting place or office address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                </div>

                                <!-- Contact Information -->
                                <h6 class="mt-4 mb-3">Contact Information</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_phone" class="form-label">Phone Number</label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="contact_phone" 
                                               name="contact_phone" 
                                               placeholder="Community contact phone"
                                               value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">Email Address</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="contact_email" 
                                               name="contact_email" 
                                               placeholder="Community email"
                                               value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="website" 
                                           name="website" 
                                           placeholder="https://community-website.com"
                                           value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="/hierarchy" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Create Gurmu
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Guidelines Sidebar -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>Guidelines
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3">Creating a Gurmu</h6>
                            <ul class="small">
                                <li class="mb-2">✓ Gurmu is a local community unit</li>
                                <li class="mb-2">✓ Belongs to a Gamta (sub-region)</li>
                                <li class="mb-2">✓ Examples: "Toronto", "Los Angeles", "Sydney"</li>
                                <li class="mb-2">✓ Code: {GOD}-{GAMTA}-{SUFFIX} (e.g., "USA-CAL-LA")</li>
                            </ul>

                            <h6 class="mt-4 mb-3">Required Fields</h6>
                            <ul class="small text-danger">
                                <li>★ Godina & Gamta</li>
                                <li>★ Community Name</li>
                            </ul>

                            <h6 class="mt-4 mb-3">Tips</h6>
                            <ul class="small">
                                <li class="mb-2">💡 Select Godina first, then Gamta</li>
                                <li class="mb-2">💡 Use city or locality names</li>
                                <li class="mb-2">💡 Add meeting address for members</li>
                                <li class="mb-2">💡 Code shows full hierarchy path</li>
                            </ul>

                            <div class="alert alert-success mt-3 small">
                                <i class="bi bi-magic me-1"></i>
                                <strong>Full Path Code:</strong> USA-CAL-LAX = USA (Godina) → California (Gamta) → Los Angeles (Gurmu)
                            </div>
                        </div>
                    </div>

                    <!-- Current Statistics -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $stats = $this->db->fetch("SELECT COUNT(*) as count FROM gurmus");
                            $gurmusCount = $stats['count'] ?? 0;
                            ?>
                            <p class="mb-2">
                                <span class="badge bg-info rounded-pill"><?= $gurmusCount ?></span>
                                Total Gurmus
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gamta data organized by Godina
const gamtasByGodina = <?= json_encode(array_reduce($gamtas, function($carry, $gamta) {
    $carry[$gamta['godina_id']][] = $gamta;
    return $carry;
}, [])) ?>;

// Update Gamta dropdown when Godina changes
document.getElementById('godina_id').addEventListener('change', function() {
    const godinaId = this.value;
    const gamtaSelect = document.getElementById('gamta_id');
    
    gamtaSelect.innerHTML = '<option value="">Select Gamta</option>';
    
    if (godinaId && gamtasByGodina[godinaId]) {
        gamtasByGodina[godinaId].forEach(function(gamta) {
            const option = document.createElement('option');
            option.value = gamta.id;
            option.textContent = `${gamta.name} (${gamta.code})`;
            option.setAttribute('data-code', gamta.code);
            gamtaSelect.appendChild(option);
        });
    }
    
    updateCodePreview();
});

// Update code preview
function updateCodePreview() {
    const gamtaSelect = document.getElementById('gamta_id');
    const nameInput = document.getElementById('name');
    const previewSpan = document.getElementById('previewCode');
    
    const selectedOption = gamtaSelect.options[gamtaSelect.selectedIndex];
    const gamtaCode = selectedOption.getAttribute('data-code');
    const gurmuName = nameInput.value.trim();
    
    if (gamtaCode && gurmuName) {
        // Create suffix from gurmu name
        const suffix = gurmuName.substring(0, 3).toUpperCase();
        const preview = gamtaCode + '-' + suffix;
        previewSpan.textContent = preview;
        previewSpan.className = 'badge bg-success';
    } else if (gamtaCode) {
        previewSpan.textContent = gamtaCode + '-???';
        previewSpan.className = 'badge bg-warning';
    } else {
        previewSpan.textContent = 'Select Gamta first';
        previewSpan.className = 'badge bg-secondary';
    }
}

document.getElementById('gamta_id').addEventListener('change', updateCodePreview);
document.getElementById('name').addEventListener('input', updateCodePreview);
</script>
