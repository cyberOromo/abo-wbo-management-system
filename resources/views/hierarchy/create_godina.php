<?php
$pageTitle = 'Create New Godina';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="bi bi-globe me-2"></i>
                    Create New Godina
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
                                <i class="bi bi-globe me-2"></i>
                                Godina Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/hierarchy/create/godina" id="godinaForm">
                                <?= csrf_field() ?>

                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           placeholder="Enter godina name (e.g., North America, Europe, Asia Pacific)"
                                           required
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                    <div id="codePreview" class="mt-2 small text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Auto-generated code:</strong> <span id="previewCode" class="badge bg-secondary">Will appear here</span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="Enter description or notes about this godina"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>

                                <!-- Location Details -->
                                <h6 class="mt-4 mb-3">Location Details</h6>
                                
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="address" 
                                               name="address" 
                                               placeholder="Enter full address"
                                               value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                                    </div>
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
                                        Create Godina
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
                            <h6 class="mb-3">Creating a Godina</h6>
                            <ul class="small">
                                <li class="mb-2">✓ Godina represents a regional unit</li>
                                <li class="mb-2">✓ Can contain multiple Gamtas</li>
                                <li class="mb-2">✓ Use descriptive names like "Kathmandu Region"</li>
                                <li class="mb-2">✓ Code auto-generates from name (e.g., "KAT")</li>
                            </ul>

                            <h6 class="mt-4 mb-3">Required Fields</h6>
                            <ul class="small text-danger">
                                <li>★ Name</li>
                            </ul>

                            <h6 class="mt-4 mb-3">Tips</h6>
                            <ul class="small">
                                <li class="mb-2">💡 Use consistent naming conventions</li>
                                <li class="mb-2">💡 Fill in contact details for communication</li>
                                <li class="mb-2">💡 Description helps with identification</li>
                            </ul>

                            <div class="alert alert-success mt-3 small">
                                <i class="bi bi-magic me-1"></i>
                                <strong>Smart Code Generation:</strong> The system automatically creates unique 3-letter codes from Godina names (e.g., "North America" → "NOR", "Europe" → "EUR").
                            </div>
                        </div>
                    </div>

                    <!-- Current Statistics -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>Current Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $stats = $this->db->fetch("SELECT COUNT(*) as count FROM godinas");
                            $godinasCount = $stats['count'] ?? 0;
                            ?>
                            <p class="mb-2">
                                <span class="badge bg-primary rounded-pill"><?= $godinasCount ?></span>
                                Total Godinas
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show real-time code preview as user types name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value.trim();
    const previewSpan = document.getElementById('previewCode');
    
    if (name) {
        // Simulate the abbreviation logic: take first 3 letters or first letter of each word
        const words = name.split(/[\s\-_]+/).filter(w => w.length > 0);
        let preview = '';
        
        if (words.length >= 3) {
            // Multi-word: take first letter of each word
            preview = words.slice(0, 3).map(w => w[0]).join('').toUpperCase();
        } else if (words.length > 1) {
            // 2 words: take more letters
            preview = name.substring(0, 3).toUpperCase();
        } else {
            // Single word: take first 3 letters
            preview = name.substring(0, 3).toUpperCase();
        }
        
        previewSpan.textContent = preview || 'XXX';
        previewSpan.className = 'badge bg-success';
    } else {
        previewSpan.textContent = 'Will appear here';
        previewSpan.className = 'badge bg-secondary';
    }
});
</script>
