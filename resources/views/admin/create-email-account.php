<?php
$pageTitle = 'Create Internal Email Account';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-envelope-plus me-2"></i>
            Create Internal Email Account
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/user-emails" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Email Management
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        New Email Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/user-emails" class="needs-validation" novalidate>
                        <div class="alert alert-info border-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Primary Login:</strong> {firstname}.{lastInitial}@j-abo-wbo.org<br>
                            <strong>Optional Alias:</strong> {position}.{hierarchy}@j-abo-wbo.org
                        </div>

                        <div class="mb-3">
                            <label for="user_id" class="form-label required">
                                <i class="bi bi-person me-1"></i>
                                Select User
                            </label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">-- Select User --</option>
                                <?php foreach ($users ?? [] as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= (isset($old['user_id']) && $old['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> 
                                        (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                The email will be automatically generated based on the user's position and hierarchy.
                            </div>
                            <div class="invalid-feedback">Please select a user.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email_type" class="form-label required">
                                <i class="bi bi-tag me-1"></i>
                                Email Type
                            </label>
                            <select class="form-select" id="email_type" name="email_type" required>
                                <option value="primary" <?= (isset($old['email_type']) && $old['email_type'] === 'primary') ? 'selected' : '' ?>>
                                    Primary (Immutable login address)
                                </option>
                                <option value="alias" <?= (isset($old['email_type']) && $old['email_type'] === 'alias') ? 'selected' : '' ?>>
                                    Alias (Role or office email)
                                </option>
                                <option value="forwarding" <?= (isset($old['email_type']) && $old['email_type'] === 'forwarding') ? 'selected' : '' ?>>
                                    Forwarding (Address that forwards elsewhere)
                                </option>
                            </select>
                            <div class="invalid-feedback">Please select an email type.</div>
                        </div>

                        <div class="mb-3">
                            <label for="quota_mb" class="form-label">
                                <i class="bi bi-hdd me-1"></i>
                                Quota (MB)
                            </label>
                            <input type="number" class="form-control" id="quota_mb" name="quota_mb" 
                                   value="<?= htmlspecialchars($old['quota_mb'] ?? '500') ?>" 
                                   min="100" max="10000" step="100">
                            <div class="form-text">Storage quota in megabytes (100 MB to 10 GB). Default: 500 MB</div>
                        </div>

                        <div class="mb-3">
                            <label for="forwarding_address" class="form-label">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                Forwarding Address (Optional)
                            </label>
                            <input type="email" class="form-control" id="forwarding_address" name="forwarding_address" 
                                   value="<?= htmlspecialchars($old['forwarding_address'] ?? '') ?>" 
                                   placeholder="external@example.com">
                            <div class="form-text">Forward emails to an external address (optional)</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="bi bi-card-text me-1"></i>
                                Description (Optional)
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Additional notes or description"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="auto_generate_password" name="auto_generate_password" checked>
                            <label class="form-check-label" for="auto_generate_password">
                                Auto-generate secure password
                            </label>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="/user-emails" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                Create Email Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Email Account Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Each user can have multiple email accounts (primary, secondary, role-based)</li>
                        <li>Email addresses are automatically generated using the standard format</li>
                        <li>Default quota is 500 MB, adjustable based on role requirements</li>
                        <li>Initial passwords are sent to the user's registered email</li>
                        <li>Email forwarding can be configured for external access</li>
                        <li>Domain: <strong>j-abo-wbo.org</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>

<style>
    .required::after {
        content: " *";
        color: #dc3545;
    }
</style>
