<?php
/**
 * Events Index View Template
 * Comprehensive event management with RSVP functionality and participation tracking
 */

// Page metadata
$pageTitle = __('events.title');
$pageDescription = __('events.description');
$bodyClass = 'events-page';

// Event data
$events = $events ?? [];
$eventStats = $eventStats ?? [];
$upcomingEvents = $upcomingEvents ?? [];
$filters = $filters ?? [];

// User permissions
$canCreateEvents = $permissions['can_create_events'] ?? false;
$canManageEvents = $permissions['can_manage_events'] ?? false;
$canRSVPEvents = $permissions['can_rsvp_events'] ?? true;

// Event types
$eventTypes = [
    'meeting' => ['name' => __('events.meeting'), 'color' => 'primary', 'icon' => 'people'],
    'training' => ['name' => __('events.training'), 'color' => 'success', 'icon' => 'book'],
    'workshop' => ['name' => __('events.workshop'), 'color' => 'info', 'icon' => 'tools'],
    'conference' => ['name' => __('events.conference'), 'color' => 'warning', 'icon' => 'megaphone'],
    'social' => ['name' => __('events.social'), 'color' => 'danger', 'icon' => 'heart'],
    'fundraising' => ['name' => __('events.fundraising'), 'color' => 'secondary', 'icon' => 'currency-dollar'],
    'cultural' => ['name' => __('events.cultural'), 'color' => 'dark', 'icon' => 'music-note']
];

// Event statuses
$eventStatuses = [
    'draft' => ['name' => __('events.draft'), 'color' => 'secondary'],
    'published' => ['name' => __('events.published'), 'color' => 'primary'],
    'ongoing' => ['name' => __('events.ongoing'), 'color' => 'warning'],
    'completed' => ['name' => __('events.completed'), 'color' => 'success'],
    'cancelled' => ['name' => __('events.cancelled'), 'color' => 'danger']
];

// RSVP statuses
$rsvpStatuses = [
    'attending' => ['name' => __('events.attending'), 'color' => 'success'],
    'maybe' => ['name' => __('events.maybe'), 'color' => 'warning'],
    'not_attending' => ['name' => __('events.not_attending'), 'color' => 'danger']
];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('events.event_management') ?></h1>
        <p class="text-muted mb-0"><?= __('events.manage_organization_events') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canCreateEvents): ?>
            <a href="/events/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?= __('events.create_event') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> <?= __('events.export') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/events/export?format=ical">
                    <i class="bi bi-calendar-event me-2"></i><?= __('events.export_calendar') ?>
                </a></li>
                <li><a class="dropdown-item" href="/events/export?format=pdf">
                    <i class="bi bi-file-earmark-pdf me-2"></i><?= __('events.export_pdf') ?>
                </a></li>
                <li><a class="dropdown-item" href="/events/attendees-report">
                    <i class="bi bi-people me-2"></i><?= __('events.attendees_report') ?>
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Event Statistics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('events.total_events') ?></h5>
                        <h2 class="mb-0"><?= number_format($eventStats['total'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-calendar-event fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('events.upcoming') ?></h5>
                        <h2 class="mb-0"><?= number_format($eventStats['upcoming'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-clock fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('events.total_attendees') ?></h5>
                        <h2 class="mb-0"><?= number_format($eventStats['total_attendees'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-people fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('events.avg_attendance') ?></h5>
                        <h2 class="mb-0"><?= number_format($eventStats['avg_attendance'] ?? 0) ?>%</h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-graph-up fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and Upcoming Events -->
<div class="row g-3 mb-4">
    <!-- Upcoming Events -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><?= __('events.upcoming_events') ?></h6>
                <a href="/events?status=published" class="btn btn-sm btn-outline-primary"><?= __('events.view_all') ?></a>
            </div>
            <div class="card-body">
                <?php if (!empty($upcomingEvents)): ?>
                    <div class="upcoming-events-list">
                        <?php foreach (array_slice($upcomingEvents, 0, 5) as $event): ?>
                            <div class="event-item d-flex align-items-start mb-3">
                                <div class="event-date me-3 text-center">
                                    <div class="date-box bg-primary text-white rounded">
                                        <div class="date-day"><?= date('d', strtotime($event['start_date'])) ?></div>
                                        <div class="date-month"><?= date('M', strtotime($event['start_date'])) ?></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="/events/<?= $event['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($event['title']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= format_time($event['start_time']) ?>
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($event['location'] ?? __('events.online')) ?>
                                    </small>
                                    <div class="mt-1">
                                        <span class="badge bg-<?= $eventTypes[$event['type']]['color'] ?> badge-sm">
                                            <?= $eventTypes[$event['type']]['name'] ?>
                                        </span>
                                        <?php if ($event['rsvp_count'] > 0): ?>
                                            <small class="text-muted ms-2">
                                                <?= $event['rsvp_count'] ?> <?= __('events.attending') ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-calendar-event display-6"></i>
                        <p class="mt-2"><?= __('events.no_upcoming_events') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Event Chart -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0"><?= __('events.event_participation_trends') ?></h6>
            </div>
            <div class="card-body">
                <canvas id="eventChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Filters and View Toggle -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- View Toggle -->
            <div class="btn-group view-toggle" role="group">
                <input type="radio" class="btn-check" name="view-mode" id="grid-view" checked>
                <label class="btn btn-outline-primary" for="grid-view">
                    <i class="bi bi-grid"></i> <?= __('events.grid_view') ?>
                </label>
                <input type="radio" class="btn-check" name="view-mode" id="list-view">
                <label class="btn btn-outline-primary" for="list-view">
                    <i class="bi bi-list-ul"></i> <?= __('events.list_view') ?>
                </label>
                <input type="radio" class="btn-check" name="view-mode" id="calendar-view">
                <label class="btn btn-outline-primary" for="calendar-view">
                    <i class="bi bi-calendar3"></i> <?= __('events.calendar_view') ?>
                </label>
            </div>
            
            <!-- Quick Filters -->
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel"></i> <?= __('events.filters') ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                        <form class="filters-form">
                            <div class="mb-3">
                                <label class="form-label"><?= __('events.search') ?></label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                                       placeholder="<?= __('events.search_placeholder') ?>">
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('events.type') ?></label>
                                    <select class="form-select" name="type">
                                        <option value=""><?= __('events.all_types') ?></option>
                                        <?php foreach ($eventTypes as $type => $config): ?>
                                            <option value="<?= $type ?>" 
                                                    <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('events.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value=""><?= __('events.all_statuses') ?></option>
                                        <?php foreach ($eventStatuses as $status => $config): ?>
                                            <option value="<?= $status ?>" 
                                                    <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('events.date_from') ?></label>
                                    <input type="date" class="form-control" name="date_from" 
                                           value="<?= $filters['date_from'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('events.date_to') ?></label>
                                    <input type="date" class="form-control" name="date_to" 
                                           value="<?= $filters['date_to'] ?? '' ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><?= __('events.my_rsvp') ?></label>
                                <select class="form-select" name="my_rsvp">
                                    <option value=""><?= __('events.all_events') ?></option>
                                    <option value="attending" <?= ($filters['my_rsvp'] ?? '') === 'attending' ? 'selected' : '' ?>>
                                        <?= __('events.events_attending') ?>
                                    </option>
                                    <option value="maybe" <?= ($filters['my_rsvp'] ?? '') === 'maybe' ? 'selected' : '' ?>>
                                        <?= __('events.events_maybe') ?>
                                    </option>
                                    <option value="not_attending" <?= ($filters['my_rsvp'] ?? '') === 'not_attending' ? 'selected' : '' ?>>
                                        <?= __('events.events_not_attending') ?>
                                    </option>
                                </select>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <?= __('events.apply_filters') ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-filters">
                                    <?= __('events.clear') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid View -->
<div id="grid-view-content">
    <?php if (empty($events)): ?>
        <div class="card">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-calendar-event display-1 text-muted"></i>
                    <h4 class="text-muted mt-3"><?= __('events.no_events_found') ?></h4>
                    <p class="text-muted"><?= __('events.no_events_description') ?></p>
                    <?php if ($canCreateEvents): ?>
                        <a href="/events/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> <?= __('events.create_first_event') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($events as $event): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card event-card h-100">
                        <!-- Event Image -->
                        <div class="event-image-container position-relative">
                            <img src="<?= $event['featured_image'] ?? '/assets/images/default-event.jpg' ?>" 
                                 alt="<?= htmlspecialchars($event['title']) ?>"
                                 class="card-img-top event-image">
                            <div class="event-overlay">
                                <span class="badge bg-<?= $eventTypes[$event['type']]['color'] ?> event-type-badge">
                                    <i class="bi bi-<?= $eventTypes[$event['type']]['icon'] ?> me-1"></i>
                                    <?= $eventTypes[$event['type']]['name'] ?>
                                </span>
                                <span class="badge bg-<?= $eventStatuses[$event['status']]['color'] ?> event-status-badge">
                                    <?= $eventStatuses[$event['status']]['name'] ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="event-date">
                                    <small class="text-primary fw-bold">
                                        <?= format_date($event['start_date']) ?>
                                    </small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="/events/<?= $event['id'] ?>">
                                            <i class="bi bi-eye me-2"></i><?= __('events.view') ?>
                                        </a></li>
                                        
                                        <?php if ($canRSVPEvents && $event['status'] === 'published'): ?>
                                            <li><a class="dropdown-item rsvp-event" href="#" 
                                                   data-event-id="<?= $event['id'] ?>"
                                                   data-current-rsvp="<?= $event['user_rsvp'] ?? '' ?>">
                                                <i class="bi bi-calendar-check me-2"></i><?= __('events.rsvp') ?>
                                            </a></li>
                                        <?php endif; ?>
                                        
                                        <?php if ($canManageEvents): ?>
                                            <li><a class="dropdown-item" href="/events/<?= $event['id'] ?>/edit">
                                                <i class="bi bi-pencil me-2"></i><?= __('events.edit') ?>
                                            </a></li>
                                            <li><a class="dropdown-item" href="/events/<?= $event['id'] ?>/attendees">
                                                <i class="bi bi-people me-2"></i><?= __('events.manage_attendees') ?>
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger delete-event" href="#" 
                                                   data-event-id="<?= $event['id'] ?>">
                                                <i class="bi bi-trash me-2"></i><?= __('events.delete') ?>
                                            </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="/events/<?= $event['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h5>
                            
                            <?php if (!empty($event['description'])): ?>
                                <p class="card-text text-muted">
                                    <?= htmlspecialchars(substr($event['description'], 0, 120)) ?>
                                    <?= strlen($event['description']) > 120 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="event-details mb-3">
                                <div class="detail-item mb-2">
                                    <i class="bi bi-clock text-muted me-2"></i>
                                    <small class="text-muted">
                                        <?= format_time($event['start_time']) ?>
                                        <?php if (!empty($event['end_time'])): ?>
                                            - <?= format_time($event['end_time']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <?php if (!empty($event['location'])): ?>
                                    <div class="detail-item mb-2">
                                        <i class="bi bi-geo-alt text-muted me-2"></i>
                                        <small class="text-muted"><?= htmlspecialchars($event['location']) ?></small>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <i class="bi bi-people text-muted me-2"></i>
                                    <small class="text-muted">
                                        <?= $event['attendee_count'] ?> <?= __('events.attendees') ?>
                                        <?php if ($event['max_attendees']): ?>
                                            / <?= $event['max_attendees'] ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if ($event['user_rsvp']): ?>
                                    <span class="badge bg-<?= $rsvpStatuses[$event['user_rsvp']]['color'] ?>">
                                        <?= $rsvpStatuses[$event['user_rsvp']]['name'] ?>
                                    </span>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                                
                                <div class="d-flex gap-2">
                                    <?php if ($canRSVPEvents && $event['status'] === 'published'): ?>
                                        <button class="btn btn-sm btn-outline-primary rsvp-event" 
                                                data-event-id="<?= $event['id'] ?>"
                                                data-current-rsvp="<?= $event['user_rsvp'] ?? '' ?>">
                                            <i class="bi bi-calendar-check"></i> <?= __('events.rsvp') ?>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="/events/<?= $event['id'] ?>" class="btn btn-sm btn-primary">
                                        <?= __('events.view_details') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- List View -->
<div id="list-view-content" style="display: none;">
    <div class="card">
        <div class="card-body">
            <?php if (!empty($events)): ?>
                <div class="table-responsive">
                    <table class="table table-hover events-table">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('events.event') ?></th>
                                <th><?= __('events.type') ?></th>
                                <th><?= __('events.date_time') ?></th>
                                <th><?= __('events.location') ?></th>
                                <th><?= __('events.attendees') ?></th>
                                <th><?= __('events.status') ?></th>
                                <th><?= __('events.my_rsvp') ?></th>
                                <th style="width: 120px;"><?= __('events.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr class="event-row" data-event-id="<?= $event['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $event['featured_image'] ?? '/assets/images/default-event.jpg' ?>" 
                                                 alt="<?= htmlspecialchars($event['title']) ?>"
                                                 class="rounded me-3" width="48" height="48">
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="/events/<?= $event['id'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($event['title']) ?>
                                                    </a>
                                                </h6>
                                                <?php if (!empty($event['description'])): ?>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars(substr($event['description'], 0, 60)) ?>
                                                        <?= strlen($event['description']) > 60 ? '...' : '' ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $eventTypes[$event['type']]['color'] ?>">
                                            <i class="bi bi-<?= $eventTypes[$event['type']]['icon'] ?> me-1"></i>
                                            <?= $eventTypes[$event['type']]['name'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= format_date($event['start_date']) ?></strong><br>
                                            <small class="text-muted">
                                                <?= format_time($event['start_time']) ?>
                                                <?php if (!empty($event['end_time'])): ?>
                                                    - <?= format_time($event['end_time']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($event['location'])): ?>
                                            <small><?= htmlspecialchars($event['location']) ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-globe"></i> <?= __('events.online') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?= $event['attendee_count'] ?></span>
                                        <?php if ($event['max_attendees']): ?>
                                            / <?= $event['max_attendees'] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $eventStatuses[$event['status']]['color'] ?>">
                                            <?= $eventStatuses[$event['status']]['name'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($event['user_rsvp']): ?>
                                            <span class="badge bg-<?= $rsvpStatuses[$event['user_rsvp']]['color'] ?>">
                                                <?= $rsvpStatuses[$event['user_rsvp']]['name'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/events/<?= $event['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i><?= __('events.view') ?>
                                                </a></li>
                                                
                                                <?php if ($canRSVPEvents && $event['status'] === 'published'): ?>
                                                    <li><a class="dropdown-item rsvp-event" href="#" 
                                                           data-event-id="<?= $event['id'] ?>"
                                                           data-current-rsvp="<?= $event['user_rsvp'] ?? '' ?>">
                                                        <i class="bi bi-calendar-check me-2"></i><?= __('events.rsvp') ?>
                                                    </a></li>
                                                <?php endif; ?>
                                                
                                                <?php if ($canManageEvents): ?>
                                                    <li><a class="dropdown-item" href="/events/<?= $event['id'] ?>/edit">
                                                        <i class="bi bi-pencil me-2"></i><?= __('events.edit') ?>
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="/events/<?= $event['id'] ?>/attendees">
                                                        <i class="bi bi-people me-2"></i><?= __('events.manage_attendees') ?>
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger delete-event" href="#" 
                                                           data-event-id="<?= $event['id'] ?>">
                                                        <i class="bi bi-trash me-2"></i><?= __('events.delete') ?>
                                                    </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Calendar View -->
<div id="calendar-view-content" style="display: none;">
    <div class="card">
        <div class="card-body">
            <div id="events-calendar"></div>
        </div>
    </div>
</div>

<!-- RSVP Modal -->
<div class="modal fade" id="rsvpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('events.rsvp_to_event') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rsvpForm">
                    <input type="hidden" id="rsvp_event_id" name="event_id">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('events.your_response') ?></label>
                        <div class="rsvp-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rsvp_status" 
                                       value="attending" id="rsvp_attending">
                                <label class="form-check-label text-success" for="rsvp_attending">
                                    <i class="bi bi-check-circle me-2"></i><?= __('events.attending') ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rsvp_status" 
                                       value="maybe" id="rsvp_maybe">
                                <label class="form-check-label text-warning" for="rsvp_maybe">
                                    <i class="bi bi-question-circle me-2"></i><?= __('events.maybe') ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rsvp_status" 
                                       value="not_attending" id="rsvp_not_attending">
                                <label class="form-check-label text-danger" for="rsvp_not_attending">
                                    <i class="bi bi-x-circle me-2"></i><?= __('events.not_attending') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('events.additional_notes') ?></label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="<?= __('events.rsvp_notes_placeholder') ?>"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= __('common.cancel') ?>
                </button>
                <button type="button" class="btn btn-primary" id="submitRSVP">
                    <?= __('events.update_rsvp') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Event Styles -->
<style>
.stats-card {
    border: none;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.event-card {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
}

.event-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.event-image-container {
    height: 200px;
    overflow: hidden;
}

.event-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-card:hover .event-image {
    transform: scale(1.05);
}

.event-overlay {
    position: absolute;
    top: 1rem;
    left: 1rem;
    right: 1rem;
    display: flex;
    justify-content: space-between;
}

.event-type-badge, .event-status-badge {
    font-size: 0.75rem;
}

.date-box {
    padding: 0.5rem;
    min-width: 50px;
    text-align: center;
}

.date-day {
    font-size: 1.2rem;
    font-weight: bold;
    line-height: 1;
}

.date-month {
    font-size: 0.8rem;
    text-transform: uppercase;
}

.upcoming-events-list {
    max-height: 350px;
    overflow-y: auto;
}

.event-item:last-child {
    margin-bottom: 0 !important;
}

.detail-item {
    display: flex;
    align-items: center;
}

.view-toggle .btn {
    border-radius: 0;
}

.view-toggle .btn:first-child {
    border-radius: 0.375rem 0 0 0.375rem;
}

.view-toggle .btn:last-child {
    border-radius: 0 0.375rem 0.375rem 0;
}

.filters-form .form-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.rsvp-options .form-check {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    transition: all 0.2s ease;
}

.rsvp-options .form-check:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.rsvp-options .form-check-input:checked + .form-check-label {
    font-weight: 600;
}

#events-calendar {
    height: 600px;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .event-image-container {
        height: 150px;
    }
    
    .event-overlay {
        top: 0.5rem;
        left: 0.5rem;
        right: 0.5rem;
    }
    
    .date-box {
        min-width: 40px;
        padding: 0.25rem;
    }
    
    #events-calendar {
        height: 400px;
    }
}
</style>

<!-- Events JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event chart
    initializeEventChart();
    
    // Initialize calendar
    initializeEventsCalendar();
    
    // View switching
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const calendarView = document.getElementById('calendar-view');
    const gridContent = document.getElementById('grid-view-content');
    const listContent = document.getElementById('list-view-content');
    const calendarContent = document.getElementById('calendar-view-content');
    
    gridView.addEventListener('change', function() {
        if (this.checked) {
            gridContent.style.display = 'block';
            listContent.style.display = 'none';
            calendarContent.style.display = 'none';
        }
    });
    
    listView.addEventListener('change', function() {
        if (this.checked) {
            gridContent.style.display = 'none';
            listContent.style.display = 'block';
            calendarContent.style.display = 'none';
        }
    });
    
    calendarView.addEventListener('change', function() {
        if (this.checked) {
            gridContent.style.display = 'none';
            listContent.style.display = 'none';
            calendarContent.style.display = 'block';
        }
    });
    
    // Filter form handling
    document.querySelector('.filters-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/events?' + params.toString();
    });
    
    document.querySelector('.clear-filters').addEventListener('click', function() {
        window.location.href = '/events';
    });
    
    // Event actions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('rsvp-event') || e.target.closest('.rsvp-event')) {
            e.preventDefault();
            const eventId = e.target.dataset.eventId || e.target.closest('.rsvp-event').dataset.eventId;
            const currentRsvp = e.target.dataset.currentRsvp || e.target.closest('.rsvp-event').dataset.currentRsvp;
            openRSVPModal(eventId, currentRsvp);
        }
        
        if (e.target.classList.contains('delete-event') || e.target.closest('.delete-event')) {
            e.preventDefault();
            const eventId = e.target.dataset.eventId || e.target.closest('.delete-event').dataset.eventId;
            deleteEvent(eventId);
        }
    });
    
    // RSVP modal
    document.getElementById('submitRSVP').addEventListener('click', function() {
        const form = document.getElementById('rsvpForm');
        const formData = new FormData(form);
        
        fetch('/api/events/rsvp', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {  
                alert(data.message || '<?= __('events.rsvp_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('events.rsvp_error') ?>');
        });
    });
    
    // Functions
    function initializeEventChart() {
        const ctx = document.getElementById('eventChart');
        if (!ctx) return;
        
        const chartData = <?= json_encode($eventStats['chart_data'] ?? []) ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: '<?= __('events.events_count') ?>',
                    data: chartData.events || [],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: '<?= __('events.attendees_count') ?>',
                    data: chartData.attendees || [],
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '<?= __('events.events_count') ?>'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '<?= __('events.attendees_count') ?>'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }
    
    function initializeEventsCalendar() {
        const calendarEl = document.getElementById('events-calendar');
        if (!calendarEl) return;
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                fetch(`/api/events/calendar?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successCallback(data.events);
                        } else {
                            failureCallback(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Calendar error:', error);
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                window.location.href = `/events/${info.event.id}`;
            },
            eventClassNames: function(info) {
                return 'event-type-' + info.event.extendedProps.type;
            },
            height: 600,
            locale: '<?= $currentLanguage ?>',
            selectable: true,
            select: function(info) {
                <?php if ($canCreateEvents): ?>
                    const startDate = info.startStr;
                    window.location.href = `/events/create?date=${startDate}`;
                <?php endif; ?>
            }
        });
        
        calendar.render();
    }
    
    function openRSVPModal(eventId, currentRsvp) {
        document.getElementById('rsvp_event_id').value = eventId;
        
        // Set current RSVP status
        if (currentRsvp) {
            const radioButton = document.querySelector(`input[name="rsvp_status"][value="${currentRsvp}"]`);
            if (radioButton) {
                radioButton.checked = true;
            }
        }
        
        const modal = new bootstrap.Modal(document.getElementById('rsvpModal'));
        modal.show();
    }
    
    function deleteEvent(eventId) {
        if (confirm('<?= __('events.confirm_delete') ?>')) {
            fetch(`/api/events/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?= __('events.delete_failed') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('events.delete_error') ?>');
            });
        }
    }
});
</script>

<!-- Chart.js and FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>