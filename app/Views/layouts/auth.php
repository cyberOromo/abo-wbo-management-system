<!DOCTYPE html>
<html lang="<?= htmlspecialchars($this->lang ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($this->title ?? 'Authentication - ABO-WBO Management System') ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($this->description ?? 'Secure authentication for ABO-WBO Global Organization Management System') ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= htmlspecialchars($this->csrfToken ?? '') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $this->asset('images/favicon.ico') ?>">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Authentication CSS -->
    <link href="<?= $this->asset('css/auth.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('css/themes.css') ?>" rel="stylesheet">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="<?= $this->asset('fonts/inter.woff2') ?>" as="font" type="font/woff2" crossorigin>
    
    <!-- Theme Detection -->
    <script>
        const theme = localStorage.getItem('theme') || 
                     (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
    
    <!-- Additional CSS -->
    <?php if (isset($this->additionalCss)): ?>
        <?php foreach ($this->additionalCss as $css): ?>
            <link href="<?= $this->asset($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="auth-body <?= htmlspecialchars($this->bodyClass ?? '') ?>">
    <!-- Skip Navigation -->
    <a href="#main-content" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 m-2" style="z-index: 9999;">Skip to main content</a>
    
    <!-- Background Pattern -->
    <div class="auth-background">
        <div class="auth-pattern"></div>
    </div>
    
    <!-- Header -->
    <header class="auth-header">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center" href="<?= $this->url('/') ?>">
                    <img src="<?= $this->asset('images/logo.png') ?>" alt="ABO-WBO Logo" height="40" class="me-2">
                    <span class="fw-bold">ABO-WBO</span>
                </a>
                
                <!-- Language Toggle -->
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-globe me-1"></i>
                            <?= ($this->lang ?? 'en') === 'en' ? 'English' : 'Afaan Oromoo' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item <?= ($this->lang ?? 'en') === 'en' ? 'active' : '' ?>" 
                                   href="<?= $this->url($this->currentUrl ?? '', ['lang' => 'en']) ?>">
                                    <i class="bi bi-flag-fill me-2"></i>English
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($this->lang ?? 'en') === 'om' ? 'active' : '' ?>" 
                                   href="<?= $this->url($this->currentUrl ?? '', ['lang' => 'om']) ?>">
                                    <i class="bi bi-flag-fill me-2"></i>Afaan Oromoo
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <button type="button" class="btn btn-link nav-link" id="theme-toggle" title="Toggle theme">
                        <i class="bi bi-sun-fill theme-icon-light"></i>
                        <i class="bi bi-moon-fill theme-icon-dark"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main id="main-content" class="auth-main" role="main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <!-- Authentication Card -->
                    <div class="card auth-card shadow-lg">
                        <div class="card-body p-4 p-sm-5">
                            <!-- Organization Info -->
                            <div class="text-center mb-4">
                                <img src="<?= $this->asset('images/logo-large.png') ?>" alt="ABO-WBO Logo" class="auth-logo mb-3">
                                <h1 class="h4 fw-bold text-primary mb-1">ABO-WBO</h1>
                                <p class="text-muted small mb-0">Global Organization Management</p>
                            </div>
                            
                            <!-- Flash Messages -->
                            <?php $this->include('components/flash-messages') ?>
                            
                            <!-- Main Content -->
                            <div class="auth-content">
                                <?= $this->content ?>
                            </div>
                        </div>
                        
                        <!-- Card Footer -->
                        <?php if (isset($this->cardFooter)): ?>
                            <div class="card-footer bg-transparent border-0 text-center py-4">
                                <?= $this->cardFooter ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Additional Links -->
                    <div class="text-center mt-4">
                        <?php if (isset($this->additionalLinks)): ?>
                            <?= $this->additionalLinks ?>
                        <?php endif; ?>
                        
                        <!-- Help Link -->
                        <div class="mt-3">
                            <a href="<?= $this->url('/help/authentication') ?>" class="text-muted text-decoration-none small">
                                <i class="bi bi-question-circle me-1"></i>
                                <?= $this->lang === 'om' ? 'Gargaarsa barbaadda?' : 'Need help?' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="auth-footer mt-auto py-4">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center">
                        <!-- Copyright -->
                        <p class="text-muted small mb-2 mb-sm-0 me-sm-4">
                            &copy; <?= date('Y') ?> ABO-WBO Global Organization. All rights reserved.
                        </p>
                        
                        <!-- Links -->
                        <div class="d-flex gap-3">
                            <a href="<?= $this->url('/privacy') ?>" class="text-muted text-decoration-none small">
                                <?= $this->lang === 'om' ? 'Icciitii' : 'Privacy' ?>
                            </a>
                            <a href="<?= $this->url('/terms') ?>" class="text-muted text-decoration-none small">
                                <?= $this->lang === 'om' ? 'Haala Tajaajilaa' : 'Terms' ?>
                            </a>
                            <a href="<?= $this->url('/contact') ?>" class="text-muted text-decoration-none small">
                                <?= $this->lang === 'om' ? 'Nu Quunnamaa' : 'Contact' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Authentication JavaScript -->
    <script src="<?= $this->asset('js/auth.js') ?>"></script>
    
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
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            
            // Initialize authentication features
            if (window.Auth) {
                window.Auth.init({
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    baseUrl: '<?= $this->baseUrl ?? '' ?>',
                    language: '<?= $this->lang ?? 'en' ?>'
                });
            }
            
            // Theme toggle functionality
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    
                    document.documentElement.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                });
            }
            
            // Form validation enhancement
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
            
            // Password strength indicator
            const passwordInputs = document.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(function(input) {
                if (input.id.includes('password') && !input.id.includes('confirm')) {
                    const strengthIndicator = document.getElementById(input.id + '-strength');
                    if (strengthIndicator) {
                        input.addEventListener('input', function() {
                            const strength = calculatePasswordStrength(input.value);
                            updatePasswordStrength(strengthIndicator, strength);
                        });
                    }
                }
            });
        });
        
        // Password strength calculation
        function calculatePasswordStrength(password) {
            let score = 0;
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            return {
                score: score,
                percentage: (score / 5) * 100,
                label: ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][score] || 'Very Weak'
            };
        }
        
        // Update password strength indicator
        function updatePasswordStrength(indicator, strength) {
            const progressBar = indicator.querySelector('.progress-bar');
            const label = indicator.querySelector('.strength-label');
            
            if (progressBar) {
                progressBar.style.width = strength.percentage + '%';
                progressBar.className = 'progress-bar ' + 
                    ['bg-danger', 'bg-warning', 'bg-info', 'bg-success', 'bg-success'][strength.score];
            }
            
            if (label) {
                label.textContent = strength.label;
            }
        }
    </script>
    
    <!-- Security Headers -->
    <script>
        // Prevent clickjacking
        if (window.top !== window.self) {
            window.top.location = window.self.location;
        }
        
        // Disable right-click context menu on production
        <?php if (($this->environment ?? 'production') === 'production'): ?>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        <?php endif; ?>
    </script>
</body>
</html>