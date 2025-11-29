<?php
$pageTitle = 'Create New Gamta';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="bi bi-building me-2"></i>
                    Create New Gamta
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
                                <i class="bi bi-building me-2"></i>
                                Gamta Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/hierarchy/create/gamta" id="gamtaForm">
                                <?= csrf_field() ?>

                                <!-- Parent Godina -->
                                <div class="mb-3">
                                    <label for="godina_id" class="form-label">
                                        Parent Godina <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="godina_id" name="godina_id" required>
                                        <option value="">Select Godina</option>
                                        <?php foreach ($godinas as $godina): ?>
                                            <option value="<?= $godina['id'] ?>" 
                                                    data-code="<?= $godina['code'] ?>"
                                                    <?= ($_POST['godina_id'] ?? '') == $godina['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($godina['name']) ?> (<?= $godina['code'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Select the Godina this Gamta belongs to</small>
                                </div>

                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        Gamta Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           placeholder="Enter gamta name (e.g., East Coast, California, Ontario)"
                                           required
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                    <div id="codePreview" class="mt-2 small text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Auto-generated code:</strong> <span id="previewCode" class="badge bg-secondary">Select Godina first</span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="Enter description or notes about this gamta"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>

                                <!-- Location Details -->
                                <h6 class="mt-4 mb-3">Location Details</h6>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="address" 
                                           name="address" 
                                           placeholder="Enter full address"
                                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
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
                                               placeholder="Enter phone number"
                                               value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">Email Address</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="contact_email" 
                                               name="contact_email" 
                                               placeholder="Enter email address"
                                               value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="website" 
                                           name="website" 
                                           placeholder="https://example.com"
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
                                        Create Gamta
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
                            <h6 class="mb-3">Creating a Gamta</h6>
                            <ul class="small">
                                <li class="mb-2">✓ Gamta is a sub-region within a Godina</li>
                                <li class="mb-2">✓ Can contain multiple Gurmus (communities)</li>
                                <li class="mb-2">✓ Examples: "East Coast", "California", "Ontario"</li>
                                <li class="mb-2">✓ Code format: {GODINA}-{SUFFIX} (e.g., "USA-CAL")</li>
                            </ul>

                            <h6 class="mt-4 mb-3">Required Fields</h6>
                            <ul class="small text-danger">
                                <li>★ Parent Godina</li>
                                <li>★ Gamta Name</li>
                            </ul>

                            <h6 class="mt-4 mb-3">Tips</h6>
                            <ul class="small">
                                <li class="mb-2">💡 Select the parent Godina first</li>
                                <li class="mb-2">💡 Use geographical or regional names</li>
                                <li class="mb-2">💡 Code auto-generates based on Godina</li>
                            </ul>

                            <div class="alert alert-success mt-3 small">
                                <i class="bi bi-magic me-1"></i>
                                <strong>Smart Code:</strong> Format {GODINA_CODE}-{SUFFIX}. Example: USA-EAST, CAN-WEST
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
                            $stats = $this->db->fetch("SELECT COUNT(*) as count FROM gamtas");
                            $gamtasCount = $stats['count'] ?? 0;
                            ?>
                            <p class="mb-2">
                                <span class="badge bg-success rounded-pill"><?= $gamtasCount ?></span>
                                Total Gamtas
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show real-time code preview
function updateCodePreview() {
    const godinaSelect = document.getElementById('godina_id');
    const nameInput = document.getElementById('name');
    const previewSpan = document.getElementById('previewCode');
    
    const selectedOption = godinaSelect.options[godinaSelect.selectedIndex];
    const godinaCode = selectedOption.getAttribute('data-code');
    const gamtaName = nameInput.value.trim();
    
    if (godinaCode && gamtaName) {
        // Create suffix from gamta name
        const words = gamtaName.split(/[\s\-_]+/).filter(w => w.length > 0);
        let suffix = '';
        
        if (words.length >= 2) {
            suffix = words.slice(0, 2).map(w => w.substring(0, 2)).join('').toUpperCase();
        } else {
            suffix = gamtaName.substring(0, 4).toUpperCase();
        }
        
        const preview = godinaCode + '-' + suffix;
        previewSpan.textContent = preview;
        previewSpan.className = 'badge bg-success';
    } else if (godinaCode) {
        previewSpan.textContent = godinaCode + '-???';
        previewSpan.className = 'badge bg-warning';
    } else {
        previewSpan.textContent = 'Select Godina first';
        previewSpan.className = 'badge bg-secondary';
    }
}

document.getElementById('godina_id').addEventListener('change', updateCodePreview);
document.getElementById('name').addEventListener('input', updateCodePreview);
</script>
