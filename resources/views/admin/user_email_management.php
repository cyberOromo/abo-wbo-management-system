<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'User Email Management' ?> - ABO-WBO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-home me-2"></i>ABO-WBO Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/dashboard">Dashboard</a>
                <a class="nav-link" href="/auth/logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-envelope me-2"></i><?= $title ?></h1>
                    <div>
                        <button class="btn btn-primary" onclick="generateMissingEmails()">
                            <i class="fas fa-magic me-2"></i>Generate Missing Emails
                        </button>
                        <a href="/dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5><?= $stats['total_users'] ?></h5>
                                        <p class="mb-0">Total Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5><?= $stats['has_internal_email'] ?></h5>
                                        <p class="mb-0">Has Internal Email</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5><?= $stats['missing_internal_email'] ?></h5>
                                        <p class="mb-0">Missing Internal Email</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5><?= $stats['has_assignments'] ?></h5>
                                        <p class="mb-0">Has Assignments</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-briefcase fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">User Email Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Personal Email</th>
                                        <th>Internal Email</th>
                                        <th>Positions</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($user['personal_email']) ?></td>
                                        <td>
                                            <?php if (!empty($user['internal_email'])): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-check me-1"></i>
                                                    <?= htmlspecialchars($user['internal_email']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Not Generated
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['positions'])): ?>
                                                <?= htmlspecialchars($user['positions']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">No assignments</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['level_scope'])): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($user['level_scope']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'warning' ?>">
                                                <?= htmlspecialchars($user['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['positions'])): ?>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="regenerateEmail(<?= $user['id'] ?>)"
                                                        title="Regenerate Email">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">No positions</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast for notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('notificationToast');
            const toastBodyEl = document.getElementById('toastMessage');
            
            toastBodyEl.textContent = message;
            toastEl.className = `toast bg-${type === 'success' ? 'success' : 'danger'} text-white`;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        function generateMissingEmails() {
            if (!confirm('Generate internal emails for all users missing them?')) {
                return;
            }

            fetch('/user-email/generate-missing', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Error generating emails: ' + error.message, 'error');
            });
        }

        function regenerateEmail(userId) {
            if (!confirm('Regenerate internal email for this user?')) {
                return;
            }

            fetch(`/user-email/regenerate/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Error regenerating email: ' + error.message, 'error');
            });
        }
    </script>
</body>
</html>