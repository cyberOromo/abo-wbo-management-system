<?php
$pageTitle = 'Email Account Details';
$email = $email ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-envelope-open me-2"></i>
            Email Account Details
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="resetPassword()">
                    <i class="bi bi-key me-1"></i>
                    Reset Password
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="updateQuota()">
                    <i class="bi bi-hdd me-1"></i>
                    Update Quota
                </button>
                <?php if (($email['status'] ?? 'active') === 'active'): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deactivateEmail()">
                        <i class="bi bi-pause-circle me-1"></i>
                        Deactivate
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="reactivateEmail()">
                        <i class="bi bi-play-circle me-1"></i>
                        Reactivate
                    </button>
                <?php endif; ?>
            </div>
            <a href="/user-emails" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Email Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="200">Email Address:</th>
                                <td>
                                    <strong><?= htmlspecialchars($email['email_address'] ?? 'N/A') ?></strong>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= htmlspecialchars($email['email_address'] ?? '') ?>')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>User:</th>
                                <td><?= htmlspecialchars($email['user_name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Email Type:</th>
                                <td>
                                    <span class="badge bg-<?= ($email['email_type'] ?? 'primary') === 'primary' ? 'primary' : (($email['email_type'] ?? '') === 'secondary' ? 'secondary' : 'info') ?>">
                                        <?= ucfirst($email['email_type'] ?? 'primary') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-<?= ($email['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($email['status'] ?? 'active') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Quota:</th>
                                <td>
                                    <?= number_format($email['quota_mb'] ?? 500) ?> MB
                                    <div class="progress mt-2" style="height: 20px;">
                                        <?php 
                                        $used = $email['used_mb'] ?? 0;
                                        $quota = $email['quota_mb'] ?? 500;
                                        $percentage = $quota > 0 ? ($used / $quota) * 100 : 0;
                                        $bgColor = $percentage > 90 ? 'bg-danger' : ($percentage > 70 ? 'bg-warning' : 'bg-success');
                                        ?>
                                        <div class="progress-bar <?= $bgColor ?>" style="width: <?= min($percentage, 100) ?>%">
                                            <?= number_format($used, 2) ?> MB / <?= number_format($quota) ?> MB
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php if (!empty($email['forwarding_address'])): ?>
                                <tr>
                                    <th>Forwarding To:</th>
                                    <td><?= htmlspecialchars($email['forwarding_address']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($email['description'])): ?>
                                <tr>
                                    <th>Description:</th>
                                    <td><?= htmlspecialchars($email['description']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Created:</th>
                                <td><?= date('F d, Y H:i', strtotime($email['created_at'] ?? 'now')) ?></td>
                            </tr>
                            <tr>
                                <th>Last Modified:</th>
                                <td><?= date('F d, Y H:i', strtotime($email['updated_at'] ?? 'now')) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($email['forwarding_address'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-arrow-right-circle me-2"></i>
                            Email Forwarding
                        </h6>
                    </div>
                    <div class="card-body">
                        <p>Emails are being forwarded to: <strong><?= htmlspecialchars($email['forwarding_address']) ?></strong></p>
                        <button class="btn btn-sm btn-danger" onclick="removeForwarding()">
                            <i class="bi bi-x-circle me-1"></i>
                            Remove Forwarding
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-activity me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="resetPassword()">
                            <i class="bi bi-key me-2"></i>
                            Reset Password
                        </button>
                        <button class="btn btn-outline-warning" onclick="updateQuota()">
                            <i class="bi bi-hdd me-2"></i>
                            Update Quota
                        </button>
                        <?php if (empty($email['forwarding_address'])): ?>
                            <button class="btn btn-outline-info" onclick="setupForwarding()">
                                <i class="bi bi-arrow-right-circle me-2"></i>
                                Setup Forwarding
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-danger" onclick="deleteEmail()">
                            <i class="bi bi-trash me-2"></i>
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Email Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Incoming Server (IMAP):</strong><br>
                        mail.j-abo-wbo.org<br>
                        Port: 993 (SSL)<br><br>
                        
                        <strong>Outgoing Server (SMTP):</strong><br>
                        mail.j-abo-wbo.org<br>
                        Port: 465 (SSL)<br><br>
                        
                        <strong>Webmail:</strong><br>
                        <a href="https://webmail.j-abo-wbo.org" target="_blank">webmail.j-abo-wbo.org</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const emailId = <?= $email['id'] ?? 0 ?>;
    
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Email address copied to clipboard!');
        });
    }
    
    function resetPassword() {
        if (confirm('Reset password for this email account? A new password will be generated and sent to the user.')) {
            fetch(`/user-emails/${emailId}/reset-password`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            }).then(response => response.json())
              .then(data => {
                  alert(data.message || 'Password reset successfully!');
                  location.reload();
              }).catch(() => alert('Error resetting password'));
        }
    }
    
    function updateQuota() {
        const newQuota = prompt('Enter new quota in MB:', <?= $email['quota_mb'] ?? 500 ?>);
        if (newQuota && !isNaN(newQuota)) {
            fetch(`/user-emails/${emailId}/update-quota`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({quota_mb: parseInt(newQuota)})
            }).then(() => location.reload());
        }
    }
    
    function setupForwarding() {
        const address = prompt('Enter forwarding email address:');
        if (address) {
            fetch(`/user-emails/${emailId}/setup-forwarding`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({forwarding_address: address})
            }).then(() => location.reload());
        }
    }
    
    function removeForwarding() {
        if (confirm('Remove email forwarding?')) {
            fetch(`/user-emails/${emailId}/remove-forwarding`, {
                method: 'DELETE'
            }).then(() => location.reload());
        }
    }
    
    function deactivateEmail() {
        if (confirm('Deactivate this email account? The user will not be able to send or receive emails.')) {
            fetch(`/user-emails/${emailId}/deactivate`, {
                method: 'POST'
            }).then(() => location.reload());
        }
    }
    
    function reactivateEmail() {
        if (confirm('Reactivate this email account?')) {
            fetch(`/user-emails/${emailId}/reactivate`, {
                method: 'POST'
            }).then(() => location.reload());
        }
    }
    
    function deleteEmail() {
        if (confirm('DELETE this email account permanently? This action cannot be undone!')) {
            if (confirm('Are you absolutely sure? All emails will be lost!')) {
                fetch(`/user-emails/${emailId}`, {
                    method: 'DELETE'
                }).then(() => window.location.href = '/user-emails');
            }
        }
    }
</script>
