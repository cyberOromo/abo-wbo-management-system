<!-- Sidebar Navigation Component -->
<div class="sidebar-sticky">
    <!-- User Info Section -->
    <div class="sidebar-user-info p-3 border-bottom">
        <div class="d-flex align-items-center">
            <img src="<?= $this->currentUser['profile_image'] ?? $this->asset('images/default-avatar.png') ?>" 
                 alt="Profile" class="rounded-circle me-2" width="40" height="40">
            <div class="flex-grow-1 min-w-0">
                <div class="fw-medium text-truncate">
                    <?= htmlspecialchars(($this->currentUser['first_name'] ?? '') . ' ' . ($this->currentUser['last_name'] ?? '')) ?>
                </div>
                <small class="text-muted"><?= htmlspecialchars($this->currentUser['position'] ?? 'Member') ?></small>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/dashboard') ? 'active' : '' ?>" href="<?= $this->url('/dashboard') ?>">
                    <i class="bi bi-speedometer2 me-2"></i>
                    <?= $this->lang === 'om' ? 'Seensa' : 'Dashboard' ?>
                </a>
            </li>
            
            <!-- Task Management -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/tasks') ? 'active' : '' ?>" 
                   href="#tasksSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $this->isActiveRoute('/tasks') ? 'true' : 'false' ?>">
                    <i class="bi bi-list-task me-2"></i>
                    <?= $this->lang === 'om' ? 'Hojiiwwan' : 'Tasks' ?>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?= $this->isActiveRoute('/tasks') ? 'show' : '' ?>" id="tasksSubmenu">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/tasks', 'exact') ? 'active' : '' ?>" href="<?= $this->url('/tasks') ?>">
                                <i class="bi bi-list me-2"></i>
                                <?= $this->lang === 'om' ? 'Hunda' : 'All Tasks' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/tasks/my') ? 'active' : '' ?>" href="<?= $this->url('/tasks/my') ?>">
                                <i class="bi bi-person-check me-2"></i>
                                <?= $this->lang === 'om' ? 'Hojiiwwan koo' : 'My Tasks' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/tasks/create') ? 'active' : '' ?>" href="<?= $this->url('/tasks/create') ?>">
                                <i class="bi bi-plus-circle me-2"></i>
                                <?= $this->lang === 'om' ? 'Hojii haaraa' : 'New Task' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/tasks/calendar') ? 'active' : '' ?>" href="<?= $this->url('/tasks/calendar') ?>">
                                <i class="bi bi-calendar-week me-2"></i>
                                <?= $this->lang === 'om' ? 'Kaaleendarii' : 'Calendar' ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- Meeting System -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/meetings') ? 'active' : '' ?>" 
                   href="#meetingsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $this->isActiveRoute('/meetings') ? 'true' : 'false' ?>">
                    <i class="bi bi-camera-video me-2"></i>
                    <?= $this->lang === 'om' ? 'Walgahiilee' : 'Meetings' ?>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?= $this->isActiveRoute('/meetings') ? 'show' : '' ?>" id="meetingsSubmenu">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/meetings', 'exact') ? 'active' : '' ?>" href="<?= $this->url('/meetings') ?>">
                                <i class="bi bi-list me-2"></i>
                                <?= $this->lang === 'om' ? 'Hunda' : 'All Meetings' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/meetings/upcoming') ? 'active' : '' ?>" href="<?= $this->url('/meetings/upcoming') ?>">
                                <i class="bi bi-clock me-2"></i>
                                <?= $this->lang === 'om' ? 'Dhufu' : 'Upcoming' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/meetings/create') ? 'active' : '' ?>" href="<?= $this->url('/meetings/create') ?>">
                                <i class="bi bi-calendar-plus me-2"></i>
                                <?= $this->lang === 'om' ? 'Walgahii haaraa' : 'Schedule Meeting' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/meetings/recordings') ? 'active' : '' ?>" href="<?= $this->url('/meetings/recordings') ?>">
                                <i class="bi bi-play-circle me-2"></i>
                                <?= $this->lang === 'om' ? 'Waraabota' : 'Recordings' ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- Events -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/events') ? 'active' : '' ?>" 
                   href="#eventsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $this->isActiveRoute('/events') ? 'true' : 'false' ?>">
                    <i class="bi bi-calendar-event me-2"></i>
                    <?= $this->lang === 'om' ? 'Taateewwan' : 'Events' ?>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?= $this->isActiveRoute('/events') ? 'show' : '' ?>" id="eventsSubmenu">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/events', 'exact') ? 'active' : '' ?>" href="<?= $this->url('/events') ?>">
                                <i class="bi bi-list me-2"></i>
                                <?= $this->lang === 'om' ? 'Hunda' : 'All Events' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/events/upcoming') ? 'active' : '' ?>" href="<?= $this->url('/events/upcoming') ?>">
                                <i class="bi bi-calendar-check me-2"></i>
                                <?= $this->lang === 'om' ? 'Dhufu' : 'Upcoming' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/events/create') ? 'active' : '' ?>" href="<?= $this->url('/events/create') ?>">
                                <i class="bi bi-plus-circle me-2"></i>
                                <?= $this->lang === 'om' ? 'Taatee haaraa' : 'New Event' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/events/my-registrations') ? 'active' : '' ?>" href="<?= $this->url('/events/my-registrations') ?>">
                                <i class="bi bi-person-check me-2"></i>
                                <?= $this->lang === 'om' ? 'Galmeessaan koo' : 'My Registrations' ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- Education System -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/courses') ? 'active' : '' ?>" 
                   href="#coursesSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $this->isActiveRoute('/courses') ? 'true' : 'false' ?>">
                    <i class="bi bi-book me-2"></i>
                    <?= $this->lang === 'om' ? 'Barnootaa' : 'Education' ?>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?= $this->isActiveRoute('/courses') ? 'show' : '' ?>" id="coursesSubmenu">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/courses', 'exact') ? 'active' : '' ?>" href="<?= $this->url('/courses') ?>">
                                <i class="bi bi-list me-2"></i>
                                <?= $this->lang === 'om' ? 'Hunda' : 'All Courses' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/courses/my-courses') ? 'active' : '' ?>" href="<?= $this->url('/courses/my-courses') ?>">
                                <i class="bi bi-person-workspace me-2"></i>
                                <?= $this->lang === 'om' ? 'Barumsaan koo' : 'My Courses' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/courses/certificates') ? 'active' : '' ?>" href="<?= $this->url('/courses/certificates') ?>">
                                <i class="bi bi-award me-2"></i>
                                <?= $this->lang === 'om' ? 'Ragaa-dhagaa' : 'Certificates' ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- Financial Management -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/donations') ? 'active' : '' ?>" 
                   href="#donationsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $this->isActiveRoute('/donations') ? 'true' : 'false' ?>">
                    <i class="bi bi-currency-dollar me-2"></i>
                    <?= $this->lang === 'om' ? 'Maallaqaa' : 'Finance' ?>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?= $this->isActiveRoute('/donations') ? 'show' : '' ?>" id="donationsSubmenu">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/donations', 'exact') ? 'active' : '' ?>" href="<?= $this->url('/donations') ?>">
                                <i class="bi bi-list me-2"></i>
                                <?= $this->lang === 'om' ? 'Kennaawwan' : 'Donations' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/donations/create') ? 'active' : '' ?>" href="<?= $this->url('/donations/create') ?>">
                                <i class="bi bi-gift me-2"></i>
                                <?= $this->lang === 'om' ? 'Kennaa haaraa' : 'New Donation' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/donations/my-donations') ? 'active' : '' ?>" href="<?= $this->url('/donations/my-donations') ?>">
                                <i class="bi bi-heart me-2"></i>
                                <?= $this->lang === 'om' ? 'Kennaawwan koo' : 'My Donations' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/donations/receipts') ? 'active' : '' ?>" href="<?= $this->url('/donations/receipts') ?>">
                                <i class="bi bi-receipt me-2"></i>
                                <?= $this->lang === 'om' ? 'Ragailee' : 'Receipts' ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- User Management (Based on Role) -->
            <?php if (in_array($this->currentUser['role'] ?? '', ['admin', 'leader'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $this->isActiveRoute('/users') ? 'active' : '' ?>" 
                       href="#usersSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $this->isActiveRoute('/users') ? 'true' : 'false' ?>">
                        <i class="bi bi-people me-2"></i>
                        <?= $this->lang === 'om' ? 'Miseensota' : 'Members' ?>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse <?= $this->isActiveRoute('/users') ? 'show' : '' ?>" id="usersSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link <?= $this->isActiveRoute('/users', 'exact') ? 'active' : '' ?>" href="<?= $this->url('/users') ?>">
                                    <i class="bi bi-list me-2"></i>
                                    <?= $this->lang === 'om' ? 'Hunda' : 'All Members' ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $this->isActiveRoute('/users/pending') ? 'active' : '' ?>" href="<?= $this->url('/users/pending') ?>">
                                    <i class="bi bi-hourglass me-2"></i>
                                    <?= $this->lang === 'om' ? 'Eegamu' : 'Pending Approval' ?>
                                    <?php if (($this->pendingUsersCount ?? 0) > 0): ?>
                                        <span class="badge bg-warning rounded-pill ms-auto"><?= $this->pendingUsersCount ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $this->isActiveRoute('/users/create') ? 'active' : '' ?>" href="<?= $this->url('/users/create') ?>">
                                    <i class="bi bi-person-plus me-2"></i>
                                    <?= $this->lang === 'om' ? 'Miseensa haaraa' : 'Add Member' ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
            
            <!-- Reports & Analytics -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/reports') ? 'active' : '' ?>" 
                   href="#reportsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $this->isActiveRoute('/reports') ? 'true' : 'false' ?>">
                    <i class="bi bi-bar-chart me-2"></i>
                    <?= $this->lang === 'om' ? 'Gabaasaawwan' : 'Reports' ?>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?= $this->isActiveRoute('/reports') ? 'show' : '' ?>" id="reportsSubmenu">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/reports/dashboard') ? 'active' : '' ?>" href="<?= $this->url('/reports/dashboard') ?>">
                                <i class="bi bi-graph-up me-2"></i>
                                <?= $this->lang === 'om' ? 'Xiinxala' : 'Analytics' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/reports/activities') ? 'active' : '' ?>" href="<?= $this->url('/reports/activities') ?>">
                                <i class="bi bi-activity me-2"></i>
                                <?= $this->lang === 'om' ? 'Sochiiwwan' : 'Activities' ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->isActiveRoute('/reports/financial') ? 'active' : '' ?>" href="<?= $this->url('/reports/financial') ?>">
                                <i class="bi bi-wallet2 me-2"></i>
                                <?= $this->lang === 'om' ? 'Maallaqaa' : 'Financial' ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- Divider -->
            <li class="nav-divider my-3"></li>
            
            <!-- Settings -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/settings') ? 'active' : '' ?>" href="<?= $this->url('/settings') ?>">
                    <i class="bi bi-gear me-2"></i>
                    <?= $this->lang === 'om' ? 'Qindaa\'ina' : 'Settings' ?>
                </a>
            </li>
            
            <!-- Help & Support -->
            <li class="nav-item">
                <a class="nav-link <?= $this->isActiveRoute('/help') ? 'active' : '' ?>" href="<?= $this->url('/help') ?>">
                    <i class="bi bi-question-circle me-2"></i>
                    <?= $this->lang === 'om' ? 'Gargaarsa' : 'Help & Support' ?>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer p-3 border-top mt-auto">
        <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted">
                ABO-WBO v1.0
            </small>
            <div class="d-flex gap-1">
                <a href="<?= $this->url('/help') ?>" class="btn btn-sm btn-outline-secondary" title="Help">
                    <i class="bi bi-question-circle"></i>
                </a>
                <a href="<?= $this->url('/settings') ?>" class="btn btn-sm btn-outline-secondary" title="Settings">
                    <i class="bi bi-gear"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle sidebar collapsible menu items
    const collapseElements = document.querySelectorAll('.sidebar-nav [data-bs-toggle="collapse"]');
    
    collapseElements.forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            const chevron = this.querySelector('.bi-chevron-down');
            
            if (target) {
                const isExpanded = target.classList.contains('show');
                
                if (isExpanded) {
                    target.classList.remove('show');
                    this.setAttribute('aria-expanded', 'false');
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                } else {
                    // Close other open menus
                    document.querySelectorAll('.sidebar-nav .collapse.show').forEach(function(openCollapse) {
                        if (openCollapse !== target) {
                            openCollapse.classList.remove('show');
                            const parentLink = document.querySelector(`[href="#${openCollapse.id}"]`);
                            if (parentLink) {
                                parentLink.setAttribute('aria-expanded', 'false');
                                const parentChevron = parentLink.querySelector('.bi-chevron-down');
                                if (parentChevron) parentChevron.style.transform = 'rotate(0deg)';
                            }
                        }
                    });
                    
                    target.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            }
        });
        
        // Set initial chevron rotation for expanded items
        if (element.getAttribute('aria-expanded') === 'true') {
            const chevron = element.querySelector('.bi-chevron-down');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    });
    
    // Highlight active menu items
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    
    navLinks.forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && href !== '#' && currentPath.startsWith(href)) {
            link.classList.add('active');
            
            // If this is a submenu item, also expand its parent
            const submenu = link.closest('.submenu');
            if (submenu) {
                const parentCollapse = submenu.parentElement;
                const parentLink = document.querySelector(`[href="#${parentCollapse.id}"]`);
                
                if (parentCollapse && parentLink) {
                    parentCollapse.classList.add('show');
                    parentLink.setAttribute('aria-expanded', 'true');
                    parentLink.classList.add('active');
                    
                    const chevron = parentLink.querySelector('.bi-chevron-down');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            }
        }
    });
    
    // Add hover effects for better UX
    navLinks.forEach(function(link) {
        link.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.backgroundColor = 'rgba(var(--bs-primary-rgb), 0.1)';
            }
        });
        
        link.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.backgroundColor = '';
            }
        });
    });
});
</script>

<style>
/* Sidebar Styling */
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 1000;
    padding: 60px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, 0.1);
    overflow-x: hidden;
    overflow-y: auto;
}

.sidebar-sticky {
    height: calc(100vh - 60px);
    display: flex;
    flex-direction: column;
}

/* User Info Section */
.sidebar-user-info {
    flex-shrink: 0;
}

.sidebar-user-info img {
    border: 2px solid var(--bs-border-color);
}

/* Navigation */
.sidebar-nav {
    flex-grow: 1;
    padding: 1rem 0;
    overflow-y: auto;
}

.sidebar-nav .nav-link {
    color: var(--bs-body-color);
    padding: 0.75rem 1rem;
    margin: 0.125rem 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
    position: relative;
    border: none;
    background: none;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.sidebar-nav .nav-link:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    color: var(--bs-primary);
}

.sidebar-nav .nav-link.active {
    background-color: var(--bs-primary);
    color: white;
}

.sidebar-nav .nav-link i {
    width: 1.25rem;
    text-align: center;
}

/* Submenu Styling */
.submenu {
    padding-left: 0;
    margin: 0;
}

.submenu .nav-link {
    padding-left: 2.5rem;
    font-size: 0.9rem;
}

.submenu .nav-link::before {
    content: '';
    position: absolute;
    left: 1.75rem;
    top: 50%;
    width: 4px;
    height: 4px;
    background-color: var(--bs-secondary);
    border-radius: 50%;
    transform: translateY(-50%);
}

.submenu .nav-link.active::before {
    background-color: white;
}

/* Divider */
.nav-divider {
    height: 1px;
    background-color: var(--bs-border-color);
    border: none;
    margin: 0.5rem 1rem;
}

/* Sidebar Footer */
.sidebar-footer {
    flex-shrink: 0;
    margin-top: auto;
}

/* Chevron Animation */
.bi-chevron-down {
    transition: transform 0.2s ease;
}

/* Badge Styling */
.nav-link .badge {
    font-size: 0.65rem;
    padding: 0.25rem 0.4rem;
}

/* Collapse Animation */
.collapse {
    transition: height 0.2s ease;
}

/* Dark Mode Adjustments */
[data-bs-theme="dark"] .sidebar {
    background-color: var(--bs-dark);
    box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .sidebar-user-info {
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .sidebar-footer {
    border-top-color: rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .nav-divider {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .sidebar {
        position: relative;
        height: auto;
        padding: 0;
        box-shadow: none;
    }
    
    .sidebar-sticky {
        height: auto;
    }
    
    .sidebar-nav {
        max-height: none;
        overflow-y: visible;
    }
}

/* Scrollbar Styling */
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: var(--bs-border-color);
    border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: var(--bs-secondary);
}

/* Focus Styles for Accessibility */
.sidebar-nav .nav-link:focus {
    outline: 2px solid var(--bs-primary);
    outline-offset: 2px;
}

/* Loading State */
.sidebar-nav.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Print Styles */
@media print {
    .sidebar {
        display: none !important;
    }
}
</style>