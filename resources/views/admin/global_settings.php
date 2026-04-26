<?php
$pageTitle = $title ?? 'Global Organization Settings';
$global = $global ?? [];
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Global Organization Settings</h1>
            <p class="text-muted mb-0">Manage the top-level organization profile used by system administration.</p>
        </div>
        <a href="/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="/admin/global-settings">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Organization Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($global['name'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="contact_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= htmlspecialchars($global['contact_email'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="contact_phone" class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($global['contact_phone'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="website" name="website" value="<?= htmlspecialchars($global['website'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label for="headquarters_address" class="form-label">Headquarters Address</label>
                        <input type="text" class="form-control" id="headquarters_address" name="headquarters_address" value="<?= htmlspecialchars($global['headquarters_address'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($global['description'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="mission_statement" class="form-label">Mission Statement</label>
                        <textarea class="form-control" id="mission_statement" name="mission_statement" rows="4"><?= htmlspecialchars($global['mission_statement'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="vision_statement" class="form-label">Vision Statement</label>
                        <textarea class="form-control" id="vision_statement" name="vision_statement" rows="4"><?= htmlspecialchars($global['vision_statement'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="fiscal_year_start" class="form-label">Fiscal Year Start</label>
                        <input type="date" class="form-control" id="fiscal_year_start" name="fiscal_year_start" value="<?= htmlspecialchars($global['fiscal_year_start'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="fiscal_year_end" class="form-label">Fiscal Year End</label>
                        <input type="date" class="form-control" id="fiscal_year_end" name="fiscal_year_end" value="<?= htmlspecialchars($global['fiscal_year_end'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="/dashboard" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Save Global Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>