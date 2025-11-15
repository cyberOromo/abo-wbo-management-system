<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ABO-WBO Management System' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #28a745;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="fas fa-users text-success me-2"></i>
                ABO-WBO Management System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/auth/login">
                    <i class="fas fa-sign-in-alt me-1"></i>
                    Login
                </a>
                <a class="nav-link" href="/auth/register">
                    <i class="fas fa-user-plus me-1"></i>
                    Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">
                Welcome to ABO-WBO Management System
            </h1>
            <p class="lead mb-5">
                A comprehensive digital platform for managing global Oromo organizational operations across all hierarchy levels.
            </p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="d-grid gap-2 d-md-block">
                        <a href="/auth/login" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login to System
                        </a>
                        <a href="/auth/register" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>
                            Register Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">System Features</h2>
                    <p class="lead text-muted">Comprehensive tools for organizational management</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h4>Hierarchy Management</h4>
                    <p class="text-muted">
                        Manage 4-tier organizational structure: Global → Godina → Gamta → Gurmu levels
                    </p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h4>Position Management</h4>
                    <p class="text-muted">
                        7 executive positions with 5 shared responsibilities across all organizational levels
                    </p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h4>Task Management</h4>
                    <p class="text-muted">
                        Cross-functional task assignment, collaboration, and progress tracking
                    </p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h4>Meeting Management</h4>
                    <p class="text-muted">
                        Schedule meetings, manage participants, and track meeting minutes
                    </p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-donate"></i>
                    </div>
                    <h4>Finance Management</h4>
                    <p class="text-muted">
                        Donation tracking, budget management, and financial reporting
                    </p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>Education & Training</h4>
                    <p class="text-muted">
                        Learning management system with courses and progress tracking
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Organizational Hierarchy -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Organizational Structure</h2>
                    <p class="lead text-muted">4-Tier Global Hierarchy</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-center mb-4">
                                <div class="text-center">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 80px; height: 80px;">
                                        <i class="fas fa-globe fa-2x"></i>
                                    </div>
                                    <h5>Global (Waliigalaa)</h5>
                                    <small class="text-muted">Executive Board</small>
                                </div>
                            </div>
                            
                            <div class="text-center mb-3">
                                <i class="fas fa-arrow-down text-success fa-2x"></i>
                            </div>
                            
                            <div class="d-flex justify-content-around mb-4">
                                <div class="text-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                                        <i class="fas fa-map fa-lg"></i>
                                    </div>
                                    <h6>Godina</h6>
                                    <small class="text-muted">6 Continental Regions</small>
                                </div>
                                <div class="text-center">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                                        <i class="fas fa-flag fa-lg"></i>
                                    </div>
                                    <h6>Gamta</h6>
                                    <small class="text-muted">Country Groups</small>
                                </div>
                                <div class="text-center">
                                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                                        <i class="fas fa-home fa-lg"></i>
                                    </div>
                                    <h6>Gurmu</h6>
                                    <small class="text-muted">Local Groups</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p class="mb-0">
                &copy; <?= date('Y') ?> ABO-WBO Management System. 
                <span class="text-success">Version 1.0.0</span>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>