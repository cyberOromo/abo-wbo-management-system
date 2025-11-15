<!-- Top Navigation Component -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center" href="<?= $this->url('/dashboard') ?>">
            <img src="<?= $this->asset('images/logo.png') ?>" alt="ABO-WBO Logo" height="32" class="me-2">
            <span class="fw-bold text-primary d-none d-md-inline">ABO-WBO</span>
        </a>
        
        <!-- Mobile Sidebar Toggle -->
        <button class="navbar-toggler border-0 me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-4"></i>
        </button>
        
        <!-- Search Form -->
        <form class="d-flex me-auto" style="max-width: 400px; width: 100%;" role="search">
            <div class="input-group">
                <input class="form-control border-end-0" type="search" placeholder="<?= $this->lang === 'om' ? 'Barbaacha...' : 'Search...' ?>" aria-label="Search" id="global-search">
                <button class="btn btn-outline-secondary border-start-0" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
        
        <!-- Right Navigation Items -->
        <div class="navbar-nav ms-auto d-flex flex-row">
            <!-- Hierarchy Indicator -->
            <?php if (isset($this->currentUser['hierarchy'])): ?>
                <div class="nav-item me-3 d-none d-lg-flex align-items-center">
                    <div class="hierarchy-indicator">
                        <span class="badge bg-primary rounded-pill small">
                            <?= htmlspecialchars($this->currentUser['hierarchy']['level'] ?? 'Member') ?>
                        </span>
                        <small class="text-muted ms-1">
                            <?= htmlspecialchars($this->currentUser['hierarchy']['name'] ?? '') ?>
                        </small>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Language Toggle -->
            <div class="nav-item dropdown me-2">
                <a class="nav-link dropdown-toggle p-2" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Change Language">
                    <i class="bi bi-globe fs-5"></i>
                    <span class="d-none d-xl-inline ms-1"><?= ($this->lang ?? 'en') === 'en' ? 'EN' : 'OM' ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                    <li>
                        <a class="dropdown-item <?= ($this->lang ?? 'en') === 'en' ? 'active' : '' ?>" 
                           href="<?= $this->url($this->currentUrl ?? '', ['lang' => 'en']) ?>">
                            <i class="bi bi-flag me-2"></i>English
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
            <div class="nav-item me-2">
                <button type="button" class="btn btn-link nav-link p-2" id="theme-toggle" title="Toggle theme">
                    <i class="bi bi-sun-fill theme-icon-light fs-5"></i>
                    <i class="bi bi-moon-fill theme-icon-dark fs-5"></i>
                </button>
            </div>
            
            <!-- Notifications -->
            <div class="nav-item dropdown me-2">
                <a class="nav-link position-relative p-2" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                    <i class="bi bi-bell fs-5"></i>
                    <?php if (($this->notificationCount ?? 0) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger small">
                            <?= $this->notificationCount > 99 ? '99+' : $this->notificationCount ?>
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg" aria-labelledby="notificationsDropdown" style="width: 380px;">
                    <!-- Notification Header -->
                    <div class="dropdown-header d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                        <h6 class="mb-0"><?= $this->lang === 'om' ? 'Beeksisa' : 'Notifications' ?></h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" id="mark-all-read" title="Mark all as read">
                                <i class="bi bi-check2-all"></i>
                            </button>
                            <a href="<?= $this->url('/notifications') ?>" class="btn btn-sm btn-outline-secondary" title="View all">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Notification List -->
                    <div class="notification-list" id="notification-list" style="max-height: 400px; overflow-y: auto;">
                        <!-- Notifications will be loaded here via AJAX -->
                        <div class="p-4 text-center text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Loading notifications...
                        </div>
                    </div>
                    
                    <!-- Notification Footer -->
                    <div class="dropdown-footer text-center py-3 border-top">
                        <a href="<?= $this->url('/notifications') ?>" class="text-decoration-none">
                            <?= $this->lang === 'om' ? 'Hunda ilaali' : 'View all notifications' ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="nav-item dropdown me-2">
                <a class="nav-link p-2" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Quick Actions">
                    <i class="bi bi-plus-lg fs-5"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionsDropdown">
                    <li><h6 class="dropdown-header"><?= $this->lang === 'om' ? 'Haaraa Uumi' : 'Create New' ?></h6></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/tasks/create') ?>"><i class="bi bi-plus-circle me-2"></i><?= $this->lang === 'om' ? 'Hojii' : 'Task' ?></a></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/meetings/create') ?>"><i class="bi bi-calendar-plus me-2"></i><?= $this->lang === 'om' ? 'Walgahii' : 'Meeting' ?></a></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/events/create') ?>"><i class="bi bi-calendar-event me-2"></i><?= $this->lang === 'om' ? 'Taateewwan' : 'Event' ?></a></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/donations/create') ?>"><i class="bi bi-gift me-2"></i><?= $this->lang === 'om' ? 'Kennaa' : 'Donation' ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/reports/create') ?>"><i class="bi bi-file-earmark-text me-2"></i><?= $this->lang === 'om' ? 'Gabaasa' : 'Report' ?></a></li>
                </ul>
            </div>
            
            <!-- User Profile Dropdown -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center p-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= $this->currentUser['profile_image'] ?? $this->asset('images/default-avatar.png') ?>" 
                         alt="Profile" class="rounded-circle me-2" width="32" height="32">
                    <span class="d-none d-lg-inline fw-medium">
                        <?= htmlspecialchars($this->currentUser['first_name'] ?? 'User') ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <div class="dropdown-header">
                            <div class="fw-bold"><?= htmlspecialchars(($this->currentUser['first_name'] ?? '') . ' ' . ($this->currentUser['last_name'] ?? '')) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($this->currentUser['email'] ?? '') ?></small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/profile') ?>"><i class="bi bi-person me-2"></i><?= $this->lang === 'om' ? 'Seenaa koo' : 'My Profile' ?></a></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/settings') ?>"><i class="bi bi-gear me-2"></i><?= $this->lang === 'om' ? 'Qindaa\'ina' : 'Settings' ?></a></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/my-tasks') ?>"><i class="bi bi-list-task me-2"></i><?= $this->lang === 'om' ? 'Hojiiwwan koo' : 'My Tasks' ?></a></li>
                    <li><a class="dropdown-item" href="<?= $this->url('/my-meetings') ?>"><i class="bi bi-calendar me-2"></i><?= $this->lang === 'om' ? 'Walgahiilee koo' : 'My Meetings' ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php if (($this->currentUser['role'] ?? '') === 'admin'): ?>
                        <li><a class="dropdown-item" href="<?= $this->url('/admin') ?>"><i class="bi bi-shield-check me-2"></i><?= $this->lang === 'om' ? 'Bulchiinsa' : 'Admin Panel' ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="<?= $this->url('/help') ?>"><i class="bi bi-question-circle me-2"></i><?= $this->lang === 'om' ? 'Gargaarsa' : 'Help & Support' ?></a></li>
                    <li>
                        <form method="POST" action="<?= $this->url('/auth/logout') ?>" class="d-inline">
                            <input type="hidden" name="_token" value="<?= $this->csrfToken ?>">
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i><?= $this->lang === 'om' ? 'Ba\'i' : 'Logout' ?>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Search Results Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel"><?= $this->lang === 'om' ? 'Bu\'uura Barbaacha' : 'Search Results' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="search-results">
                    <!-- Search results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update theme icons
            updateThemeIcons(newTheme);
        });
    }
    
    // Update theme icons based on current theme
    function updateThemeIcons(theme) {
        const lightIcon = document.querySelector('.theme-icon-light');
        const darkIcon = document.querySelector('.theme-icon-dark');
        
        if (theme === 'dark') {
            lightIcon.style.display = 'inline-block';
            darkIcon.style.display = 'none';
        } else {
            lightIcon.style.display = 'none';
            darkIcon.style.display = 'inline-block';
        }
    }
    
    // Initialize theme icons
    const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
    updateThemeIcons(currentTheme);
    
    // Global search functionality
    const searchForm = document.querySelector('form[role="search"]');
    const searchInput = document.getElementById('global-search');
    
    if (searchForm && searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => performSearch(query), 300);
            }
        });
        
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query.length >= 2) {
                performSearch(query);
            }
        });
    }
    
    // Search function
    function performSearch(query) {
        // Show search modal
        const searchModal = new bootstrap.Modal(document.getElementById('searchModal'));
        const searchResults = document.getElementById('search-results');
        
        searchResults.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Searching...</span>
                </div>
                <p class="mt-2">Searching for "${query}"...</p>
            </div>
        `;
        
        searchModal.show();
        
        // Perform AJAX search
        fetch(`<?= $this->url('/api/search') ?>?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.results, query);
                } else {
                    searchResults.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Search failed. Please try again.
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        An error occurred while searching.
                    </div>
                `;
            });
    }
    
    // Display search results
    function displaySearchResults(results, query) {
        const searchResults = document.getElementById('search-results');
        
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="text-center p-4">
                    <i class="bi bi-search fs-1 text-muted"></i>
                    <h5 class="mt-3">No results found</h5>
                    <p class="text-muted">No results found for "${query}"</p>
                </div>
            `;
            return;
        }
        
        let html = `<div class="search-results-count mb-3">
            <small class="text-muted">Found ${results.length} result(s) for "${query}"</small>
        </div>`;
        
        results.forEach(result => {
            html += `
                <div class="search-result-item border-bottom pb-3 mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <i class="bi bi-${result.icon || 'file-text'} fs-4 text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="${result.url}" class="text-decoration-none">${result.title}</a>
                            </h6>
                            <p class="mb-1 text-muted small">${result.description || ''}</p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-secondary small">${result.type}</span>
                                ${result.date ? `<small class="text-muted">${result.date}</small>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        searchResults.innerHTML = html;
    }
    
    // Load notifications
    loadNotifications();
    
    // Refresh notifications every 5 minutes
    setInterval(loadNotifications, 300000);
    
    // Mark all notifications as read
    document.getElementById('mark-all-read')?.addEventListener('click', function() {
        fetch('<?= $this->url('/api/notifications/mark-all-read') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
                showToast('All notifications marked as read', 'success');
            }
        })
        .catch(error => console.error('Error marking notifications as read:', error));
    });
});

// Load notifications function
function loadNotifications() {
    fetch('<?= $this->url('/api/notifications') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update notification count
                const countBadge = document.querySelector('#notificationsDropdown .badge');
                if (countBadge) {
                    if (data.unread_count > 0) {
                        countBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        countBadge.style.display = 'inline-block';
                    } else {
                        countBadge.style.display = 'none';
                    }
                }
                
                // Update notification list
                const notificationList = document.getElementById('notification-list');
                if (notificationList) {
                    if (data.notifications && data.notifications.length > 0) {
                        let html = '';
                        data.notifications.forEach(notification => {
                            html += `
                                <div class="notification-item p-3 border-bottom ${notification.read_at ? '' : 'bg-light'}" data-id="${notification.id}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="bi bi-${notification.icon || 'bell'} text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 small">${notification.title}</h6>
                                            <p class="mb-1 small text-muted">${notification.message}</p>
                                            <small class="text-muted">${notification.created_at}</small>
                                        </div>
                                        ${!notification.read_at ? '<div class="flex-shrink-0"><span class="badge bg-primary rounded-pill"></span></div>' : ''}
                                    </div>
                                </div>
                            `;
                        });
                        notificationList.innerHTML = html;
                    } else {
                        notificationList.innerHTML = `
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-bell-slash fs-1"></i>
                                <p class="mt-2 mb-0">No notifications</p>
                            </div>
                        `;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notification-list').innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <p class="mt-2 mb-0">Failed to load notifications</p>
                </div>
            `;
        });
}
</script>

<style>
/* Top Navigation Styling */
.navbar {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    z-index: 1030;
}

.navbar-brand img {
    transition: transform 0.2s;
}

.navbar-brand:hover img {
    transform: scale(1.05);
}

/* Search Form */
.input-group .form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: none;
}

.input-group .btn {
    border-color: var(--bs-border-color);
}

/* Hierarchy Indicator */
.hierarchy-indicator {
    padding: 0.25rem 0.5rem;
    background: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 0.375rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
}

/* Notification Dropdown */
.notification-dropdown {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.notification-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: var(--bs-light) !important;
}

.notification-item:last-child {
    border-bottom: none !important;
}

/* Search Results */
.search-result-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: var(--bs-light);
}

.search-result-item:last-child {
    border-bottom: none !important;
}

/* Theme Toggle */
#theme-toggle {
    border: none;
    background: none;
    color: var(--bs-nav-link-color);
}

#theme-toggle:hover {
    color: var(--bs-nav-link-hover-color);
}

.theme-icon-dark {
    display: none;
}

[data-bs-theme="dark"] .theme-icon-light {
    display: none;
}

[data-bs-theme="dark"] .theme-icon-dark {
    display: inline-block;
}

/* Profile Image */
.navbar .rounded-circle {
    border: 2px solid var(--bs-border-color);
    transition: border-color 0.2s;
}

.navbar .dropdown-toggle:hover .rounded-circle {
    border-color: var(--bs-primary);
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .navbar-nav {
        border-top: 1px solid var(--bs-border-color);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }
    
    .navbar-nav .nav-item {
        margin: 0.25rem 0;
    }
}

@media (max-width: 575.98px) {
    .notification-dropdown {
        width: calc(100vw - 2rem) !important;
        left: 1rem !important;
        right: 1rem !important;
        transform: none !important;
    }
}

/* Dark Mode Adjustments */
[data-bs-theme="dark"] .navbar {
    background-color: var(--bs-dark) !important;
    border-bottom-color: var(--bs-border-color);
}

[data-bs-theme="dark"] .notification-item.bg-light {
    background-color: rgba(255, 255, 255, 0.1) !important;
}

[data-bs-theme="dark"] .hierarchy-indicator {
    background: rgba(var(--bs-primary-rgb), 0.2);
    border-color: rgba(var(--bs-primary-rgb), 0.3);
}
</style>