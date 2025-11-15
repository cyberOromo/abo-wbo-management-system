<?php
$currentPage = 'events';
?>

<!-- Modern Events Management Interface -->
<style>
    .event-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -2px rgba(0, 0, 0, 0.15);
    }
    
    .event-featured {
        background: linear-gradient(135deg, rgba(139, 21, 56, 0.05) 0%, white 50%);
        border-left: 4px solid var(--primary-red);
    }
    
    .event-community {
        border-left: 4px solid var(--primary-green);
    }
    
    .event-educational {
        border-left: 4px solid #3b82f6;
    }
    
    .event-cultural {
        border-left: 4px solid #8b5cf6;
    }
    
    .event-social {
        border-left: 4px solid #f59e0b;
    }
    
    .event-header-ribbon {
        position: absolute;
        top: 15px;
        right: -35px;
        background: linear-gradient(45deg, var(--primary-red), #a21e3a);
        color: white;
        padding: 5px 45px;
        transform: rotate(45deg);
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .event-date-badge {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border-radius: 16px;
        padding: 0.75rem;
        text-align: center;
        box-shadow: 0 4px 8px rgba(45, 80, 22, 0.2);
        min-width: 80px;
    }
    
    .event-date-day {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .event-date-month {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
    }
    
    .event-category-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid transparent;
    }
    
    .category-community {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border-color: rgba(45, 80, 22, 0.2);
    }
    
    .category-educational {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .category-cultural {
        background: rgba(139, 92, 246, 0.1);
        color: #7c3aed;
        border-color: rgba(139, 92, 246, 0.2);
    }
    
    .category-social {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border-color: rgba(245, 158, 11, 0.2);
    }
    
    .category-festival {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border-color: rgba(139, 21, 56, 0.2);
    }
    
    .event-status-upcoming {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .event-status-ongoing {
        background: #fef3c7;
        color: #92400e;
    }
    
    .event-status-completed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .event-status-cancelled {
        background: #fecaca;
        color: #991b1b;
    }
    
    .attendee-counter {
        background: linear-gradient(135deg, var(--primary-red), #a21e3a);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 4px rgba(139, 21, 56, 0.2);
    }
    
    .event-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .event-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary-green), var(--primary-green-light));
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 1.5rem;
        width: 12px;
        height: 12px;
        background: var(--primary-green);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--primary-green);
    }
    
    .event-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .event-image {
        aspect-ratio: 16/10;
        background: #f3f4f6;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .event-image:hover {
        transform: scale(1.05);
    }
    
    .event-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .rsvp-button {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(45, 80, 22, 0.2);
    }
    
    .rsvp-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .rsvp-button.registered {
        background: linear-gradient(135deg, #10b981, #34d399);
    }
    
    .rsvp-button.full {
        background: linear-gradient(135deg, #6b7280, #9ca3af);
        cursor: not-allowed;
    }
    
    .event-details-modal .modal-content {
        border-radius: 20px;
        overflow: hidden;
    }
    
    .event-details-header {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .organizer-badge {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
</style>

<div class="page-header">
    <h1 class="page-title">Events Management</h1>
    <p class="page-description">Discover, organize, and manage community events with comprehensive tracking and engagement tools</p>
</div>

<!-- Enhanced Statistics Dashboard -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-green);"><?= $event_stats['total'] ?? 0 ?></div>
            <div class="text-muted fw-500">Total Events</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #fbbf24;"><?= $event_stats['upcoming'] ?? 0 ?></div>
            <div class="text-muted fw-500">Upcoming</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: var(--primary-red);"><?= $event_stats['featured'] ?? 0 ?></div>
            <div class="text-muted fw-500">Featured</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-number" style="color: #10b981;"><?= $event_stats['total_attendees'] ?? 0 ?></div>
            <div class="text-muted fw-500">Total Attendees</div>
        </div>
    </div>
</div>

<!-- Advanced Control Panel -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="view-toggle">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="cardView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary" for="cardView">
                            <i class="bi bi-grid-3x3-gap"></i> Cards
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="calendarView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="calendarView">
                            <i class="bi bi-calendar-month"></i> Calendar
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="timelineView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="timelineView">
                            <i class="bi bi-clock-history"></i> Timeline
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="d-flex gap-2">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="community">🏘️ Community</option>
                        <option value="educational">📚 Educational</option>
                        <option value="cultural">🎭 Cultural</option>
                        <option value="social">👥 Social</option>
                        <option value="festival">🎉 Festival</option>
                    </select>
                    
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="upcoming">📅 Upcoming</option>
                        <option value="ongoing">🚀 Ongoing</option>
                        <option value="completed">✅ Completed</option>
                        <option value="cancelled">❌ Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="d-flex gap-2 justify-content-end">
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showCreateEventModal()">
                            <i class="bi bi-plus-circle"></i> Create Event
                        </button>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-share"></i> Share
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/events/calendar-feed">📅 Calendar Feed</a></li>
                            <li><a class="dropdown-item" href="/events/export?format=ical">🗓️ iCal Export</a></li>
                            <li><a class="dropdown-item" href="/events/newsletter">📧 Newsletter</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card View -->
<div id="cardViewContainer">
    <div class="row g-4" id="eventsGrid">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 event-item" 
                     data-category="<?= $event['category'] ?? 'community' ?>" 
                     data-status="<?= $event['status'] ?? 'upcoming' ?>">
                    <div class="event-card event-<?= $event['category'] ?? 'community' ?> <?= ($event['is_featured'] ?? false) ? 'event-featured' : '' ?>">
                        
                        <!-- Featured Ribbon -->
                        <?php if ($event['is_featured'] ?? false): ?>
                            <div class="event-header-ribbon">Featured</div>
                        <?php endif; ?>
                        
                        <!-- Event Header with Image -->
                        <div style="height: 200px; background: linear-gradient(45deg, var(--primary-green), var(--primary-green-light)); position: relative; overflow: hidden;">
                            <?php if (isset($event['image_url'])): ?>
                                <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($event['title'] ?? '') ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, rgba(45,80,22,0.7), rgba(45,80,22,0.3));"></div>
                            <?php endif; ?>
                            
                            <!-- Date Badge -->
                            <div style="position: absolute; top: 1rem; left: 1rem;">
                                <div class="event-date-badge">
                                    <div class="event-date-day"><?= date('j', strtotime($event['event_date'] ?? '')) ?></div>
                                    <div class="event-date-month"><?= date('M', strtotime($event['event_date'] ?? '')) ?></div>
                                </div>
                            </div>
                            
                            <!-- RSVP Button -->
                            <div style="position: absolute; bottom: 1rem; right: 1rem;">
                                <button class="rsvp-button <?= ($event['user_registered'] ?? false) ? 'registered' : '' ?>" 
                                        onclick="toggleRSVP(<?= $event['id'] ?>)">
                                    <?= ($event['user_registered'] ?? false) ? '✓ Registered' : 'RSVP' ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Event Content -->
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="event-category-badge category-<?= $event['category'] ?? 'community' ?>">
                                    <?= getEventCategoryIcon($event['category'] ?? 'community') ?> 
                                    <?= ucfirst($event['category'] ?? 'community') ?>
                                </span>
                                
                                <span class="badge event-status-<?= $event['status'] ?? 'upcoming' ?>">
                                    <?= getEventStatusIcon($event['status'] ?? 'upcoming') ?> 
                                    <?= ucfirst($event['status'] ?? 'upcoming') ?>
                                </span>
                            </div>
                            
                            <h5 class="card-title mb-2 fw-600"><?= htmlspecialchars($event['title'] ?? 'Untitled Event') ?></h5>
                            <p class="card-text text-muted mb-3">
                                <?= htmlspecialchars(substr($event['description'] ?? 'No description provided', 0, 120)) ?>
                                <?= strlen($event['description'] ?? '') > 120 ? '...' : '' ?>
                            </p>
                            
                            <!-- Event Details -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                                    <i class="bi bi-clock text-primary"></i>
                                    <small>
                                        <?= date('g:i A', strtotime($event['start_time'] ?? '')) ?> 
                                        <?php if (isset($event['end_time'])): ?>
                                            - <?= date('g:i A', strtotime($event['end_time'])) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <?php if (isset($event['location'])): ?>
                                    <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                                        <i class="bi bi-geo-alt text-danger"></i>
                                        <small><?= htmlspecialchars($event['location']) ?></small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($event['organizer_name'])): ?>
                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="bi bi-person text-success"></i>
                                        <small>By <?= htmlspecialchars($event['organizer_name']) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Event Footer -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="attendee-counter">
                                    <i class="bi bi-people"></i>
                                    <span><?= $event['attendee_count'] ?? 0 ?></span>
                                    <?php if (isset($event['max_attendees']) && $event['max_attendees'] > 0): ?>
                                        <span>/ <?= $event['max_attendees'] ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewEventDetails(<?= $event['id'] ?>)">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="shareEvent(<?= $event['id'] ?>)">
                                        <i class="bi bi-share"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-calendar-x" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Events Found</h4>
                    <p class="text-muted mb-4">Create your first event to start building community engagement</p>
                    <?php if ($can_create ?? true): ?>
                        <button class="btn btn-primary" onclick="showCreateEventModal()">
                            <i class="bi bi-plus-circle"></i> Create Your First Event
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Timeline View (Hidden by default) -->
<div id="timelineViewContainer" style="display: none;">
    <div class="event-timeline">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <div class="timeline-item event-item" 
                     data-category="<?= $event['category'] ?? 'community' ?>" 
                     data-status="<?= $event['status'] ?? 'upcoming' ?>">
                    <div class="d-flex gap-3">
                        <div class="event-date-badge">
                            <div class="event-date-day"><?= date('j', strtotime($event['event_date'] ?? '')) ?></div>
                            <div class="event-date-month"><?= date('M', strtotime($event['event_date'] ?? '')) ?></div>
                        </div>
                        
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex gap-2">
                                    <span class="event-category-badge category-<?= $event['category'] ?? 'community' ?>">
                                        <?= getEventCategoryIcon($event['category'] ?? 'community') ?> 
                                        <?= ucfirst($event['category'] ?? 'community') ?>
                                    </span>
                                    
                                    <span class="badge event-status-<?= $event['status'] ?? 'upcoming' ?>">
                                        <?= getEventStatusIcon($event['status'] ?? 'upcoming') ?> 
                                        <?= ucfirst($event['status'] ?? 'upcoming') ?>
                                    </span>
                                </div>
                                
                                <div class="attendee-counter">
                                    <i class="bi bi-people"></i>
                                    <span><?= $event['attendee_count'] ?? 0 ?></span>
                                </div>
                            </div>
                            
                            <h5 class="fw-600 mb-2"><?= htmlspecialchars($event['title'] ?? 'Untitled Event') ?></h5>
                            <p class="text-muted mb-2"><?= htmlspecialchars(substr($event['description'] ?? '', 0, 200)) ?>...</p>
                            
                            <div class="d-flex gap-4 text-muted mb-3">
                                <div><i class="bi bi-clock"></i> <?= date('g:i A', strtotime($event['start_time'] ?? '')) ?></div>
                                <?php if (isset($event['location'])): ?>
                                    <div><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewEventDetails(<?= $event['id'] ?>)">
                                    View Details
                                </button>
                                <button class="btn btn-sm rsvp-button <?= ($event['user_registered'] ?? false) ? 'registered' : '' ?>" 
                                        onclick="toggleRSVP(<?= $event['id'] ?>)">
                                    <?= ($event['user_registered'] ?? false) ? '✓ Registered' : 'RSVP' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/events/create" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Event Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Enter event title">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Category</label>
                            <select name="category" class="form-select">
                                <option value="community">🏘️ Community</option>
                                <option value="educational">📚 Educational</option>
                                <option value="cultural">🎭 Cultural</option>
                                <option value="social">👥 Social</option>
                                <option value="festival">🎉 Festival</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Provide detailed event description, objectives, and what attendees can expect..."></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Event Date *</label>
                            <input type="date" name="event_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">End Time</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                        
                        <div class="col-md-8">
                            <label class="form-label fw-500">Location</label>
                            <input type="text" name="location" class="form-control" 
                                   placeholder="Venue address or online meeting link">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Max Attendees</label>
                            <input type="number" name="max_attendees" class="form-control" min="1" placeholder="Leave empty for unlimited">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Event Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Registration Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">ETB</span>
                                <input type="number" name="registration_fee" class="form-control" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured">
                                <label class="form-check-label fw-500" for="isFeatured">
                                    Mark as Featured Event
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Tags (Optional)</label>
                            <input type="text" name="tags" class="form-control" 
                                   placeholder="Enter comma-separated tags (e.g., networking, fundraising, youth)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switching functionality
    const cardView = document.getElementById('cardView');
    const timelineView = document.getElementById('timelineView');
    const cardContainer = document.getElementById('cardViewContainer');
    const timelineContainer = document.getElementById('timelineViewContainer');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'block';
            timelineContainer.style.display = 'none';
        }
    });
    
    timelineView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            timelineContainer.style.display = 'block';
        }
    });
    
    // Advanced filtering
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    function applyFilters() {
        const categoryValue = categoryFilter.value;
        const statusValue = statusFilter.value;
        
        document.querySelectorAll('.event-item').forEach(item => {
            const showCategory = !categoryValue || item.dataset.category === categoryValue;
            const showStatus = !statusValue || item.dataset.status === statusValue;
            item.style.display = showCategory && showStatus ? 'block' : 'none';
        });
    }
    
    categoryFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
});

function showCreateEventModal() {
    new bootstrap.Modal(document.getElementById('createEventModal')).show();
}

function toggleRSVP(eventId) {
    fetch(`/events/${eventId}/rsvp`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating RSVP');
        }
    })
    .catch(() => alert('Error updating RSVP'));
}

function viewEventDetails(eventId) {
    window.location.href = `/events/${eventId}`;
}

function shareEvent(eventId) {
    if (navigator.share) {
        navigator.share({
            title: 'Event Invitation',
            url: window.location.origin + `/events/${eventId}`
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.origin + `/events/${eventId}`)
            .then(() => alert('Event link copied to clipboard!'));
    }
}
</script>

<?php
// Helper functions for UI
function getEventCategoryIcon($category) {
    return [
        'community' => '🏘️',
        'educational' => '📚',
        'cultural' => '🎭',
        'social' => '👥',
        'festival' => '🎉'
    ][$category] ?? '🏘️';
}

function getEventStatusIcon($status) {
    return [
        'upcoming' => '📅',
        'ongoing' => '🚀',
        'completed' => '✅',
        'cancelled' => '❌'
    ][$status] ?? '📅';
}
?>