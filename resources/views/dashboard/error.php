<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Error - ABO-WBO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Dashboard Configuration Issue
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <h5>Unable to Load Dashboard</h5>
                            <p class="mb-0"><?= htmlspecialchars($error_message ?? 'An unknown error occurred while loading your dashboard.') ?></p>
                        </div>
                        
                        <?php if (isset($user)): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Your Account Information:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Name:</strong> <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?></li>
                                    <li><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></li>
                                    <li><strong>Role:</strong> <?= htmlspecialchars($user['role'] ?? '') ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Next Steps:</h6>
                                <ul>
                                    <li>Contact your system administrator</li>
                                    <li>Verify your position assignment</li>
                                    <li>Check your hierarchy placement</li>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="/auth/logout" class="btn btn-secondary me-2">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                Logout
                            </a>
                            <a href="/dashboard" class="btn btn-primary">
                                <i class="fas fa-refresh me-1"></i>
                                Try Again
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        ABO-WBO Management System v1.0.0
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>