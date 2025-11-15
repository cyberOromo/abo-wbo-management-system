<!DOCTYPE html>
<html lang="<?= htmlspecialchars($this->lang ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($this->title ?? 'Admin Panel - ABO-WBO Management System') ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($this->description ?? 'Administrative control panel for ABO-WBO Global Organization Management System') ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= htmlspecialchars($this->csrfToken ?? '') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $this->asset('images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= $this->asset('images/apple-touch-icon.png') ?>">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <link href="<?= $this->asset('css/admin.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('css/themes.css') ?>" rel="stylesheet">
    
    <!-- Additional CSS -->
    <?php if (isset($this->additionalCss)): ?>
        <?php foreach ($this->additionalCss as $css): ?>
            <link href="<?= $this->asset($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Styles -->
    <?php if (isset($this->inlineStyles)): ?>
        <style><?= $this->inlineStyles ?></style>
    <?php endif; ?>
    
    <!-- Theme Detection -->
    <script>
        const theme = localStorage.getItem('theme') || 
                     (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
</head>

<body class="admin-body <?= htmlspecialchars($this->bodyClass ?? '') ?>">
    <!-- Skip Navigation -->
    <a href="#main-content" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 m-2" style="z-index: 9999;">Skip to main content</a>
    
    <!-- Admin Header -->
    <header class="admin-header">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
            <div class="container-fluid">
                <!-- Logo & Brand -->
                <a class="navbar-brand d-flex align-items-center" href="<?= $this->url('/admin') ?>">
                    <img src="<?= $this->asset('images/logo-white.png') ?>" alt="ABO-WBO Logo" height="32" class="me-2">
                    <span class="fw-bold">ABO-WBO Admin</span>
                </a>
                
                <!-- Mobile Toggle -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Top Navigation Items -->
                <div class="navbar-nav ms-auto d-flex flex-row">
                    <!-- Quick Actions -->
                    <div class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-lightning-fill"></i>
                            <span class="d-none d-md-inline ms-1">Quick Actions</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionsDropdown">
                            <li><h6 class="dropdown-header">Create New</h6></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/users/create') ?>"><i class="bi bi-person-plus me-2"></i>Add User</a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/tasks/create') ?>"><i class="bi bi-plus-circle me-2"></i>Create Task</a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/meetings/create') ?>"><i class="bi bi-calendar-plus me-2"></i>Schedule Meeting</a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/events/create') ?>"><i class="bi bi-calendar-event me-2"></i>Create Event</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Management</h6></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/hierarchy') ?>"><i class="bi bi-diagram-3 me-2"></i>Manage Hierarchy</a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/reports') ?>"><i class="bi bi-file-earmark-text me-2"></i>Generate Report</a></li>
                        </ul>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="nav-item dropdown me-2">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell-fill"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
                                <?= $this->notificationCount ?? 0 ?>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                                <a href="#" class="text-primary small">Mark all read</a>
                            </div>
                            <div class="notification-list" id="notification-list">
                                <!-- Notifications will be loaded here -->
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="<?= $this->url('/admin/notifications') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Profile -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= $this->currentUser['profile_image'] ?? $this->asset('images/default-avatar.png') ?>" 
                                 alt="Profile" class="rounded-circle me-2" width="32" height="32">
                            <span class="d-none d-md-inline"><?= htmlspecialchars($this->currentUser['first_name'] ?? 'Admin') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><h6 class="dropdown-header"><?= htmlspecialchars(($this->currentUser['first_name'] ?? '') . ' ' . ($this->currentUser['last_name'] ?? '')) ?></h6></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/profile') ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/admin/settings') ?>"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/dashboard') ?>"><i class="bi bi-house me-2"></i>User Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?= $this->url('/auth/logout') ?>" class="d-inline">
                                    <input type="hidden" name="_token" value="<?= $this->csrfToken ?>">
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Container -->
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="adminSidebarLabel">Admin Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
                </div>
                
                <div class="offcanvas-body">
                    <?php $this->include('components/admin-sidebar') ?>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main id="main-content" class="admin-main" role="main">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <!-- Breadcrumb -->
                    <?php if (isset($this->breadcrumbs) && !empty($this->breadcrumbs)): ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= $this->url('/admin') ?>">Admin</a></li>
                                <?php foreach ($this->breadcrumbs as $index => $breadcrumb): ?>
                                    <?php if ($index === count($this->breadcrumbs) - 1): ?>
                                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($breadcrumb['title']) ?></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item">
                                            <a href="<?= htmlspecialchars($breadcrumb['url']) ?>"><?= htmlspecialchars($breadcrumb['title']) ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>
                    
                    <!-- Page Header -->
                    <?php if (isset($this->pageTitle) || isset($this->pageActions)): ?>
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                            <div>
                                <h1 class="h2 mb-1"><?= htmlspecialchars($this->pageTitle ?? '') ?></h1>
                                <?php if (isset($this->pageDescription)): ?>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($this->pageDescription) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($this->pageActions)): ?>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <?= $this->pageActions ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Content Body -->
            <div class="content-body">
                <div class="container-fluid">
                    <!-- Flash Messages -->
                    <?php $this->include('components/flash-messages') ?>
                    
                    <!-- System Alerts -->
                    <?php if (isset($this->systemAlerts)): ?>
                        <?php foreach ($this->systemAlerts as $alert): ?>
                            <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> alert-dismissible fade show" role="alert">
                                <i class="bi bi-<?= htmlspecialchars($alert['icon'] ?? 'info-circle') ?> me-2"></i>
                                <strong><?= htmlspecialchars($alert['title'] ?? '') ?></strong>
                                <?= htmlspecialchars($alert['message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Main Content -->
                    <div class="admin-content">
                        <?= $this->content ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <footer class="admin-footer bg-light border-top py-3 mt-auto">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <div class="text-muted small">
                    &copy; <?= date('Y') ?> ABO-WBO Global Organization. Admin Panel v1.0
                </div>
                <div class="d-flex gap-3 mt-2 mt-sm-0">
                    <a href="<?= $this->url('/admin/help') ?>" class="text-muted text-decoration-none small">Help</a>
                    <a href="<?= $this->url('/admin/system-info') ?>" class="text-muted text-decoration-none small">System Info</a>
                    <a href="<?= $this->url('/admin/logs') ?>" class="text-muted text-decoration-none small">Logs</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Modals Container -->
    <div id="modals-container"></div>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (for DataTables and legacy components) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    
    <!-- Admin JavaScript -->
    <script src="<?= $this->asset('js/admin.js') ?>"></script>
    <script src="<?= $this->asset('js/admin-components.js') ?>"></script>
    <script src="<?= $this->asset('js/admin-notifications.js') ?>"></script>
    
    <!-- Additional JavaScript -->
    <?php if (isset($this->additionalJs)): ?>
        <?php foreach ($this->additionalJs as $js): ?>
            <script src="<?= $this->asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Scripts -->
    <?php if (isset($this->inlineScripts)): ?>
        <script><?= $this->inlineScripts ?></script>
    <?php endif; ?>
    
    <!-- Admin Application Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            
            // Initialize popovers
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
            
            // Initialize admin application
            if (window.AdminApp) {
                window.AdminApp.init({
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    baseUrl: '<?= $this->baseUrl ?? '' ?>',
                    currentUser: <?= json_encode($this->currentUser ?? null) ?>,
                    language: '<?= $this->lang ?? 'en' ?>'
                });
            }
            
            // Initialize DataTables
            $('.admin-datatable').each(function() {
                const table = $(this);
                const config = {
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                         '<"row"<"col-sm-12"tr>>' +
                         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    order: []
                };
                
                // Merge with custom config if provided
                const customConfig = table.data('config');
                if (customConfig) {
                    Object.assign(config, customConfig);
                }
                
                table.DataTable(config);
            });
            
            // Initialize confirmation dialogs
            $('.confirm-action').on('click', function(e) {
                const message = $(this).data('confirm') || 'Are you sure you want to perform this action?';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Auto-dismiss alerts
            $('.alert[data-auto-dismiss]').each(function() {
                const alert = $(this);
                const timeout = parseInt(alert.data('auto-dismiss')) || 5000;
                setTimeout(function() {
                    alert.fadeOut();
                }, timeout);
            });
            
            // Load notifications
            loadNotifications();
            
            // Refresh notifications every 5 minutes
            setInterval(loadNotifications, 300000);
        });
        
        // Load notifications function
        function loadNotifications() {
            fetch('<?= $this->url('/admin/api/notifications') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('notification-count').textContent = data.unread_count;
                        document.getElementById('notification-list').innerHTML = data.html;
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }
        
        // Global loading overlay
        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('d-none');
        }
        
        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('d-none');
        }
        
        // Global AJAX setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                showLoading();
            },
            complete: function() {
                hideLoading();
            }
        });
    </script>
    
    <!-- Performance Monitoring -->
    <?php if (($this->environment ?? 'production') === 'development'): ?>
        <script>
            // Development mode performance monitoring
            window.addEventListener('load', function() {
                const navigation = performance.getEntriesByType('navigation')[0];
                console.log('Page load time:', navigation.loadEventEnd - navigation.loadEventStart, 'ms');
                console.log('DOM ready time:', navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart, 'ms');
            });
        </script>
    <?php endif; ?>
</body>
</html>