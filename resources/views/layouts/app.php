<!DOCTYPE html>
<html lang="<?= APP_LANGUAGE ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= isset($title) ? $title . ' - ' : '' ?>ABO-WBO Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons - Updated to latest stable version -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap Icons Fallback -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet" onerror="this.onerror=null; this.href='https://unpkg.com/bootstrap-icons@1.11.1/font/bootstrap-icons.css'">
    <!-- Custom CSS -->
    <style>
        :root {
            --abo-primary: #dc3545;
            --abo-secondary: #28a745;
            --abo-dark: #343a40;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--abo-primary) !important;
        }
        
        .btn-primary {
            background-color: var(--abo-primary);
            border-color: var(--abo-primary);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .sidebar .nav-link {
            color: var(--abo-dark);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0.5rem;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--abo-primary);
            color: white;
        }
        
        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c757d !important;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .card-stats {
            border-left: 4px solid var(--abo-primary);
        }
        
        .footer {
            background-color: var(--abo-dark);
            color: white;
            padding: 1rem 0;
            margin-top: auto;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="bi bi-people-fill me-2"></i>
                ABO-WBO Management System
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (auth_check()): ?>
                        <?php 
                        $user = auth_user(); 
                        $userRole = $user['role'] ?? 'member';
                        $isAdmin = $userRole === 'admin';
                        $isExecutive = $userRole === 'executive';
                        $isMember = $userRole === 'member';
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        
                        <!-- Organization dropdown - Different content based on role -->
                        <?php if ($isAdmin): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-diagram-3 me-1"></i>Organization
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/hierarchy">Hierarchy Management</a></li>
                                    <li><a class="dropdown-item" href="/positions">Position Management</a></li>
                                    <li><a class="dropdown-item" href="/users">User Management</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/responsibilities">
                                        <i class="bi bi-diagram-3 me-2 text-primary"></i>
                                        Shared Responsibilities
                                        <small class="d-block text-muted">5 Core Areas</small>
                                    </a></li>
                                </ul>
                            </li>
                        <?php elseif ($isExecutive): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-people me-1"></i>Leadership
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/responsibilities">My Responsibilities</a></li>
                                    <li><a class="dropdown-item" href="/member-registration">Register Members</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/reports">Reports & Analytics</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-calendar-event me-1"></i>Activities
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/tasks">My Tasks</a></li>
                                <li><a class="dropdown-item" href="/meetings">My Meetings</a></li>
                                <li><a class="dropdown-item" href="/events">Community Events</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/donations">
                                <i class="bi bi-heart me-1"></i>Donations
                            </a>
                        </li>
                                <i class="bi bi-heart me-1"></i>Donations
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (auth_check()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell me-1"></i>
                                <span class="badge bg-danger">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><a class="dropdown-item text-muted">No new notifications</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/notifications">View All</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= auth_user()['first_name'] ?? 'User' ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/users/profile/edit">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="/settings">
                                    <i class="bi bi-gear me-2"></i>Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="/auth/logout" class="m-0">
                                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/auth/login">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/auth/register">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid flex-grow-1">
        <div class="row">
            <?php if (auth_check()): ?>
                <?php 
                $user = auth_user(); 
                $userRole = $user['role'] ?? 'member';
                $isAdmin = $userRole === 'admin';
                $isExecutive = $userRole === 'executive';
                $isMember = $userRole === 'member';
                ?>
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            <!-- Dashboard - Available to all authenticated users -->
                            <li class="nav-item">
                                <a class="nav-link" href="/dashboard">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                            
                            <!-- My Activities - Available to all users -->
                            <li class="nav-item mb-2">
                                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                    <span>My Activities</span>
                                </h6>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/tasks">
                                    <i class="bi bi-check-square me-2"></i>My Tasks
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/meetings">
                                    <i class="bi bi-camera-video me-2"></i>My Meetings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/events">
                                    <i class="bi bi-calendar-event me-2"></i>Community Events
                                </a>
                            </li>
                            
                            <!-- Donations - Available to all users -->
                            <li class="nav-item">
                                <a class="nav-link" href="/donations">
                                    <i class="bi bi-heart me-2"></i>My Donations
                                </a>
                            </li>
                            
                            <!-- Executive/Leadership Functions - Only for executives and admins -->
                            <?php if ($isExecutive || $isAdmin): ?>
                                <li class="nav-item mb-2">
                                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                        <span>Leadership</span>
                                    </h6>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/responsibilities">
                                        <i class="bi bi-diagram-3 me-2"></i>Responsibilities
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/member-registration">
                                        <i class="bi bi-person-plus-fill me-2"></i>Register Members
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/reports">
                                        <i class="bi bi-graph-up me-2"></i>Reports
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- System Administration - Admin only -->
                            <?php if ($isAdmin): ?>
                                <li class="nav-item mb-2">
                                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                        <span>Administration</span>
                                    </h6>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/users">
                                        <i class="bi bi-people me-2"></i>User Management
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/hierarchy">
                                        <i class="bi bi-diagram-3 me-2"></i>Hierarchy Management
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/positions">
                                        <i class="bi bi-briefcase me-2"></i>Position Management
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin">
                                        <i class="bi bi-gear me-2"></i>System Settings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/settings">
                                        <i class="bi bi-gear me-2"></i>Settings
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>

                <!-- Main content area -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php else: ?>
                <!-- Full width for non-authenticated users -->
                <main class="col-12">
            <?php endif; ?>
                
                <!-- Flash Messages -->
                <?php if (session_has('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= session_get('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (session_has('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= session_get('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (session_has('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <?= session_get('info') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <div class="main-content">
                    <?= $content ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> ABO-WBO Management System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Version 1.0.0 | Built with ❤️ for the Oromo Community</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // CSRF Token for AJAX requests
        window.csrfToken = '<?= csrf_token() ?>';
        
        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000); // Hide after 5 seconds
            });
        });
    </script>
</body>
</html>