<!DOCTYPE html>
<html lang="<?= htmlspecialchars($this->lang ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($this->title ?? 'ABO-WBO Management System') ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($this->description ?? 'Comprehensive Global Oromo Organization Management System') ?>">
    <meta name="keywords" content="ABO, WBO, Oromo, Management, Organization, Global">
    <meta name="author" content="ABO-WBO Development Team">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= htmlspecialchars($this->csrfToken ?? '') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $this->asset('images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= $this->asset('images/apple-touch-icon.png') ?>">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= $this->asset('css/app.css') ?>" rel="stylesheet">
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
    
    <!-- Progressive Web App -->
    <link rel="manifest" href="<?= $this->asset('manifest.json') ?>">
    <meta name="theme-color" content="#0d6efd">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="<?= $this->asset('fonts/inter.woff2') ?>" as="font" type="font/woff2" crossorigin>
    
    <!-- Dark Mode Detection -->
    <script>
        // Detect user's theme preference
        const theme = localStorage.getItem('theme') || 
                     (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
</head>

<body class="<?= htmlspecialchars($this->bodyClass ?? '') ?>">
    <!-- Skip Navigation -->
    <a href="#main-content" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 m-2" style="z-index: 9999;">Skip to main content</a>
    
    <!-- Loading Spinner -->
    <div id="page-loader" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light" style="z-index: 9998; display: none !important;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Top Navigation -->
    <?php $this->include('components/top-navigation') ?>
    
    <!-- Main Container -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <?php if ($this->showSidebar ?? true): ?>
                <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-body-tertiary sidebar collapse">
                    <?php $this->include('components/sidebar-navigation') ?>
                </nav>
            <?php endif; ?>
            
            <!-- Main Content Area -->
            <main id="main-content" class="<?= ($this->showSidebar ?? true) ? 'col-md-9 ms-sm-auto col-lg-10' : 'col-12' ?> px-md-4" role="main">
                <!-- Breadcrumb Navigation -->
                <?php if (isset($this->breadcrumbs) && !empty($this->breadcrumbs)): ?>
                    <nav aria-label="breadcrumb" class="mt-3">
                        <ol class="breadcrumb">
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
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><?= htmlspecialchars($this->pageTitle ?? '') ?></h1>
                        <?php if (isset($this->pageActions)): ?>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <?= $this->pageActions ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Flash Messages -->
                <?php $this->include('components/flash-messages') ?>
                
                <!-- Main Content -->
                <div class="content-wrapper">
                    <?= $this->content ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Footer -->
    <?php if ($this->showFooter ?? true): ?>
        <?php $this->include('components/footer') ?>
    <?php endif; ?>
    
    <!-- Modals Container -->
    <div id="modals-container"></div>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>
    
    <!-- Notification Center -->
    <?php $this->include('components/notification-center') ?>
    
    <!-- Back to Top Button -->
    <button type="button" class="btn btn-primary btn-floating btn-lg" id="btn-back-to-top" title="Back to top" style="display: none;">
        <i class="bi bi-arrow-up"></i>
    </button>
    
    <!-- JavaScript Libraries -->
    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    
    <!-- Application JavaScript -->
    <script src="<?= $this->asset('js/app.js') ?>"></script>
    <script src="<?= $this->asset('js/components.js') ?>"></script>
    <script src="<?= $this->asset('js/notifications.js') ?>"></script>
    
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
    
    <!-- Application Initialization -->
    <script>
        // Initialize application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            
            // Initialize popovers
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
            
            // Initialize application components
            if (window.App) {
                window.App.init({
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    baseUrl: '<?= $this->baseUrl ?? '' ?>',
                    currentUser: <?= json_encode($this->currentUser ?? null) ?>,
                    language: '<?= $this->lang ?? 'en' ?>'
                });
            }
            
            // Hide page loader
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.display = 'none';
            }
        });
        
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= $this->asset('sw.js') ?>');
        }
        
        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTopButton = document.getElementById('btn-back-to-top');
            if (window.scrollY > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        document.getElementById('btn-back-to-top')?.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
    
    <!-- Performance Monitoring -->
    <?php if (($this->environment ?? 'production') === 'production'): ?>
        <script>
            // Performance monitoring
            window.addEventListener('load', function() {
                const navigation = performance.getEntriesByType('navigation')[0];
                if (navigation.loadEventEnd - navigation.loadEventStart > 3000) {
                    console.warn('Slow page load detected:', navigation.loadEventEnd - navigation.loadEventStart, 'ms');
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>