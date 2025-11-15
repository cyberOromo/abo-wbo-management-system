<!DOCTYPE html>
<html lang="<?= APP_LANGUAGE ?? 'en' ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= isset($title) ? $title . ' - ' : '' ?>ABO-WBO Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Modern ABO-WBO Theme -->
    <style>
        :root {
            /* Primary Colors - Organization Brand */
            --abo-primary: #b91c1c;        /* Dark Red */
            --abo-primary-light: #dc2626;   /* Medium Red */
            --abo-primary-dark: #7f1d1d;    /* Darker Red */
            --abo-secondary: #166534;       /* Dark Green */
            --abo-secondary-light: #16a34a;  /* Medium Green */
            --abo-secondary-dark: #0f172a;  /* Deep Dark Green */
            
            /* Neutral Colors */
            --abo-white: #ffffff;
            --abo-black: #000000;
            --abo-gray-50: #f8fafc;
            --abo-gray-100: #f1f5f9;
            --abo-gray-200: #e2e8f0;
            --abo-gray-300: #cbd5e1;
            --abo-gray-400: #94a3b8;
            --abo-gray-500: #64748b;
            --abo-gray-600: #475569;
            --abo-gray-700: #334155;
            --abo-gray-800: #1e293b;
            --abo-gray-900: #0f172a;
            
            /* Accent Colors */
            --abo-accent-blue: #2563eb;
            --abo-accent-amber: #f59e0b;
            --abo-accent-emerald: #10b981;
            --abo-accent-rose: #f43f5e;
            
            /* Semantic Colors */
            --abo-success: var(--abo-secondary-light);
            --abo-warning: var(--abo-accent-amber);
            --abo-danger: var(--abo-primary);
            --abo-info: var(--abo-accent-blue);
            
            /* Shadows */
            --abo-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --abo-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --abo-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --abo-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --abo-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            
            /* Border Radius */
            --abo-radius-sm: 0.375rem;
            --abo-radius: 0.5rem;
            --abo-radius-lg: 0.75rem;
            --abo-radius-xl: 1rem;
            
            /* Transitions */
            --abo-transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --abo-transition-slow: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Global Styles */
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--abo-gray-50);
            color: var(--abo-gray-900);
            line-height: 1.6;
        }
        
        /* Bootstrap Override - Primary Colors */
        .btn-primary {
            background-color: var(--abo-primary);
            border-color: var(--abo-primary);
            font-weight: 500;
            transition: var(--abo-transition);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--abo-primary-dark);
            border-color: var(--abo-primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--abo-shadow-md);
        }
        
        .btn-success {
            background-color: var(--abo-secondary);
            border-color: var(--abo-secondary);
            font-weight: 500;
        }
        
        .btn-success:hover, .btn-success:focus {
            background-color: var(--abo-secondary-dark);
            border-color: var(--abo-secondary-dark);
            transform: translateY(-1px);
            box-shadow: var(--abo-shadow-md);
        }
        
        /* Modern Card Styles */
        .card {
            border: none;
            border-radius: var(--abo-radius-lg);
            box-shadow: var(--abo-shadow);
            transition: var(--abo-transition);
            background-color: var(--abo-white);
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--abo-shadow-lg);
        }
        
        .card-header {
            background-color: var(--abo-gray-50);
            border-bottom: 1px solid var(--abo-gray-200);
            padding: 1.5rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-footer {
            background-color: var(--abo-gray-50);
            border-top: 1px solid var(--abo-gray-200);
            padding: 1rem 1.5rem;
        }
        
        /* Stats Cards */
        .stats-card {
            position: relative;
            overflow: hidden;
            border-radius: var(--abo-radius-xl);
            transition: var(--abo-transition);
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, var(--abo-primary), var(--abo-secondary));
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--abo-shadow-xl);
        }
        
        .stats-card .stats-icon {
            width: 3rem;
            height: 3rem;
            border-radius: var(--abo-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Navigation Styles */
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--abo-primary) !important;
            text-decoration: none;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--abo-white) 0%, var(--abo-gray-50) 100%);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--abo-gray-200);
            box-shadow: var(--abo-shadow-sm);
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, var(--abo-white) 0%, var(--abo-gray-50) 100%);
            border-right: 1px solid var(--abo-gray-200);
            box-shadow: var(--abo-shadow-sm);
            min-height: calc(100vh - 76px);
        }
        
        .sidebar .nav-link {
            color: var(--abo-gray-700);
            padding: 0.75rem 1.25rem;
            border-radius: var(--abo-radius);
            margin: 0.125rem 0.75rem;
            font-weight: 500;
            transition: var(--abo-transition);
            position: relative;
            overflow: hidden;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--abo-primary);
            color: var(--abo-white);
            transform: translateX(4px);
            box-shadow: var(--abo-shadow-md);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--abo-primary) 0%, var(--abo-primary-light) 100%);
            color: var(--abo-white);
            box-shadow: var(--abo-shadow-md);
        }
        
        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--abo-white);
        }
        
        /* Form Styles */
        .form-control, .form-select {
            border: 2px solid var(--abo-gray-200);
            border-radius: var(--abo-radius);
            padding: 0.75rem 1rem;
            transition: var(--abo-transition);
            background-color: var(--abo-white);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--abo-primary);
            box-shadow: 0 0 0 0.25rem rgb(185 28 28 / 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--abo-gray-700);
            margin-bottom: 0.5rem;
        }
        
        /* Action Cards */
        .action-card {
            border: 2px solid var(--abo-gray-200);
            border-radius: var(--abo-radius-xl);
            padding: 2rem;
            text-align: center;
            transition: var(--abo-transition);
            background: var(--abo-white);
            height: 100%;
        }
        
        .action-card:hover {
            border-color: var(--abo-primary);
            transform: translateY(-4px);
            box-shadow: var(--abo-shadow-xl);
            background: linear-gradient(135deg, var(--abo-white) 0%, var(--abo-gray-50) 100%);
        }
        
        .action-card .action-icon {
            width: 4rem;
            height: 4rem;
            border-radius: var(--abo-radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, var(--abo-primary) 0%, var(--abo-secondary) 100%);
            color: var(--abo-white);
        }
        
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, var(--abo-primary) 0%, var(--abo-secondary) 100%);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--abo-primary) 0%, var(--abo-secondary) 100%);
            border: 3px solid var(--abo-white);
            box-shadow: var(--abo-shadow-md);
        }
        
        /* Badge Styles */
        .badge {
            font-weight: 500;
            border-radius: var(--abo-radius);
            padding: 0.5rem 0.75rem;
        }
        
        .badge.bg-primary {
            background-color: var(--abo-primary) !important;
        }
        
        .badge.bg-success {
            background-color: var(--abo-secondary) !important;
        }
        
        /* Alert Styles */
        .alert {
            border: none;
            border-radius: var(--abo-radius-lg);
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: rgb(22 101 52 / 0.1);
            border-left-color: var(--abo-secondary);
            color: var(--abo-secondary-dark);
        }
        
        .alert-danger {
            background-color: rgb(185 28 28 / 0.1);
            border-left-color: var(--abo-primary);
            color: var(--abo-primary-dark);
        }
        
        /* Dropdown Styles */
        .dropdown-menu {
            border: none;
            border-radius: var(--abo-radius-lg);
            box-shadow: var(--abo-shadow-lg);
            border: 1px solid var(--abo-gray-200);
        }
        
        .dropdown-item {
            padding: 0.75rem 1.25rem;
            transition: var(--abo-transition);
        }
        
        .dropdown-item:hover {
            background-color: var(--abo-primary);
            color: var(--abo-white);
        }
        
        /* Utilities */
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--abo-primary) 0%, var(--abo-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
        }
        
        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <i class="bi bi-diagram-3 me-2 fs-4"></i>
                <span class="gradient-text">ABO-WBO Management System</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="bi bi-speedometer2 me-1"></i>
                            Dashboard
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2"></i>
                            <?= auth_user()['first_name'] ?? 'User' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile">
                                <i class="bi bi-person me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="/settings">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Area -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar fade-in">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'active' : '' ?>" href="/dashboard">
                                <i class="bi bi-house me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/hierarchy') === 0 ? 'active' : '' ?>" href="/hierarchy">
                                <i class="bi bi-diagram-3 me-2"></i>
                                Hierarchy
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/users') === 0 ? 'active' : '' ?>" href="/users">
                                <i class="bi bi-people me-2"></i>
                                User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/positions') === 0 ? 'active' : '' ?>" href="/positions">
                                <i class="bi bi-award me-2"></i>
                                Positions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/responsibilities') === 0 ? 'active' : '' ?>" href="/responsibilities">
                                <i class="bi bi-list-task me-2"></i>
                                Responsibilities
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/meetings') === 0 ? 'active' : '' ?>" href="/meetings">
                                <i class="bi bi-calendar-event me-2"></i>
                                Meetings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/tasks') === 0 ? 'active' : '' ?>" href="/tasks">
                                <i class="bi bi-check-square me-2"></i>
                                Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/donations') === 0 ? 'active' : '' ?>" href="/donations">
                                <i class="bi bi-currency-dollar me-2"></i>
                                Donations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/reports') === 0 ? 'active' : '' ?>" href="/reports">
                                <i class="bi bi-graph-up me-2"></i>
                                Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="fade-in">
                    <?= $content ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Modern Enhancement Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation classes to elements as they come into view
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('slide-in-right');
                    }
                });
            }, observerOptions);
            
            // Observe all cards for animation
            document.querySelectorAll('.card, .stats-card, .action-card').forEach(card => {
                observer.observe(card);
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Enhanced tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>
