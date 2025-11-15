<?php
$pageTitle = $title ?? 'Events Management';
$layout = 'modern';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 gradient-text mb-1">
            <i class="bi bi-calendar-event me-2"></i>
            Events Management
        </h1>
        <p class="text-muted mb-0">Create, manage, and track community events and activities</p>
    </div>
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                <i class="bi bi-plus-circle me-1"></i>
                Create Event
            </button>
        </div>
        <div class="btn-group">
            <a href="/events/calendar" class="btn btn-outline-secondary">
                <i class="bi bi-calendar3 me-1"></i>
                Calendar View
            </a>
            <a href="/events/export" class="btn btn-outline-info">
                <i class="bi bi-download me-1"></i>
                Export Events
            </a>
        </div>
    </div>
</div>

<!-- Event Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_events'] ?? 32) ?></h3>
                    <p class="text-muted mb-0">Total Events</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        <?= $stats['this_month'] ?? 8 ?> This Month
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['upcoming'] ?? 12) ?></h3>
                    <p class="text-muted mb-0">Upcoming</p>
                    <small class="text-info">
                        <i class="bi bi-calendar"></i>
                        Next 30 Days
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['total_attendees'] ?? 1247) ?></h3>
                    <p class="text-muted mb-0">Total Attendees</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i>
                        +145 This Month
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-star"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0"><?= number_format($stats['avg_rating'] ?? 4.6, 1) ?></h3>
                    <p class="text-muted mb-0">Avg Rating</p>
                    <small class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        Based on <?= $stats['reviews'] ?? 156 ?> reviews
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Categories & Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Event Categories & Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <button class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-category="cultural">
                            <div class="action-icon bg-primary text-white mb-2">
                                <i class="bi bi-music-note"></i>
                            </div>
                            <span class="fw-bold">Cultural</span>
                            <small class="text-muted"><?= $stats['categories']['cultural'] ?? 8 ?> events</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-category="educational">
                            <div class="action-icon bg-success text-white mb-2">
                                <i class="bi bi-book"></i>
                            </div>
                            <span class="fw-bold">Educational</span>
                            <small class="text-muted"><?= $stats['categories']['educational'] ?? 6 ?> events</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-category="community">
                            <div class="action-icon bg-info text-white mb-2">
                                <i class="bi bi-people"></i>
                            </div>
                            <span class="fw-bold">Community</span>
                            <small class="text-muted"><?= $stats['categories']['community'] ?? 12 ?> events</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-category="fundraising">
                            <div class="action-icon bg-warning text-white mb-2">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <span class="fw-bold">Fundraising</span>
                            <small class="text-muted"><?= $stats['categories']['fundraising'] ?? 4 ?> events</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <button class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-category="sports">
                            <div class="action-icon bg-secondary text-white mb-2">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <span class="fw-bold">Sports</span>
                            <small class="text-muted"><?= $stats['categories']['sports'] ?? 3 ?> events</small>
                        </button>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <button class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" data-bs-toggle="modal" data-bs-target="#createEventModal">
                            <div class="action-icon bg-danger text-white mb-2">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <span class="fw-bold">Create New</span>
                            <small class="text-muted">Add event</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Events Grid & Filters -->
<div class="row">
    <div class="col-lg-8">
        <!-- Event Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label for="eventSearch" class="form-label">Search Events</label>
                        <input type="text" class="form-control" id="eventSearch" placeholder="Search by name or description">
                    </div>
                    <div class="col-md-2">
                        <label for="eventCategory" class="form-label">Category</label>
                        <select class="form-select" id="eventCategory">
                            <option value="">All Categories</option>
                            <option value="cultural">Cultural</option>
                            <option value="educational">Educational</option>
                            <option value="community">Community</option>
                            <option value="fundraising">Fundraising</option>
                            <option value="sports">Sports</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="eventStatus" class="form-label">Status</label>
                        <select class="form-select" id="eventStatus">
                            <option value="">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="eventDateRange" class="form-label">Date Range</label>
                        <input type="date" class="form-control" id="eventDateRange">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Events Grid -->
        <div class="row" id="events-grid">
            <?php 
            $sampleEvents = [
                [
                    'id' => 1,
                    'title' => 'Oromo Cultural Night',
                    'category' => 'cultural',
                    'date' => '2025-12-15',
                    'time' => '18:00',
                    'location' => 'Community Center',
                    'attendees' => 120,
                    'max_attendees' => 150,
                    'status' => 'upcoming',
                    'featured' => true,
                    'price' => 25,
                    'image' => 'cultural-night.jpg'
                ],
                [
                    'id' => 2,
                    'title' => 'Youth Leadership Workshop',
                    'category' => 'educational',
                    'date' => '2025-11-28',
                    'time' => '14:00',
                    'location' => 'Virtual',
                    'attendees' => 45,
                    'max_attendees' => 50,
                    'status' => 'upcoming',
                    'featured' => false,
                    'price' => 0,
                    'image' => 'workshop.jpg'
                ],
                [
                    'id' => 3,
                    'title' => 'Community Service Day',
                    'category' => 'community',
                    'date' => '2025-12-08',
                    'time' => '09:00',
                    'location' => 'Local Park',
                    'attendees' => 65,
                    'max_attendees' => 100,
                    'status' => 'upcoming',
                    'featured' => true,
                    'price' => 0,
                    'image' => 'service-day.jpg'
                ],
                [
                    'id' => 4,
                    'title' => 'Fundraising Gala Dinner',
                    'category' => 'fundraising',
                    'date' => '2025-12-31',
                    'time' => '19:00',
                    'location' => 'Grand Hotel',
                    'attendees' => 85,
                    'max_attendees' => 200,
                    'status' => 'upcoming',
                    'featured' => true,
                    'price' => 75,
                    'image' => 'gala.jpg'
                ],
                [
                    'id' => 5,
                    'title' => 'Basketball Tournament',
                    'category' => 'sports',
                    'date' => '2025-11-25',
                    'time' => '10:00',
                    'location' => 'Sports Complex',
                    'attendees' => 32,
                    'max_attendees' => 64,
                    'status' => 'upcoming',
                    'featured' => false,
                    'price' => 10,
                    'image' => 'basketball.jpg'
                ],
                [
                    'id' => 6,
                    'title' => 'Language Learning Class',
                    'category' => 'educational',
                    'date' => '2025-12-01',
                    'time' => '15:30',
                    'location' => 'Library',
                    'attendees' => 28,
                    'max_attendees' => 30,
                    'status' => 'ongoing',
                    'featured' => false,
                    'price' => 15,
                    'image' => 'language.jpg'
                ]
            ];
            
            foreach ($sampleEvents as $event): 
                $categoryClass = [
                    'cultural' => 'primary',
                    'educational' => 'success',
                    'community' => 'info',
                    'fundraising' => 'warning',
                    'sports' => 'secondary'
                ][$event['category']] ?? 'secondary';
                
                $statusClass = [
                    'upcoming' => 'primary',
                    'ongoing' => 'success',
                    'completed' => 'secondary',
                    'cancelled' => 'danger'
                ][$event['status']] ?? 'secondary';
                
                $attendancePercentage = round(($event['attendees'] / $event['max_attendees']) * 100);
                $isNearFull = $attendancePercentage >= 90;
                $isFree = $event['price'] == 0;
            ?>
                <div class="col-lg-6 col-md-12 mb-4 event-card" data-category="<?= $event['category'] ?>" data-status="<?= $event['status'] ?>">
                    <div class="card event-item h-100 <?= $event['featured'] ? 'featured-event' : '' ?>">
                        <?php if ($event['featured']): ?>
                            <div class="featured-badge">
                                <i class="bi bi-star-fill"></i> Featured
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-image">
                            <div class="placeholder-image bg-<?= $categoryClass ?> bg-opacity-20 d-flex align-items-center justify-content-center">
                                <i class="bi bi-<?= [
                                    'cultural' => 'music-note',
                                    'educational' => 'book',
                                    'community' => 'people',
                                    'fundraising' => 'currency-dollar',
                                    'sports' => 'trophy'
                                ][$event['category']] ?? 'calendar-event' ?> text-<?= $categoryClass ?>" style="font-size: 3rem;"></i>
                            </div>
                            <div class="event-badges">
                                <span class="badge bg-<?= $categoryClass ?>"><?= ucfirst($event['category']) ?></span>
                                <?php if ($isFree): ?>
                                    <span class="badge bg-success">Free</span>
                                <?php endif; ?>
                                <?php if ($isNearFull): ?>
                                    <span class="badge bg-danger">Nearly Full</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                            
                            <div class="event-details mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar3 text-<?= $categoryClass ?> me-2"></i>
                                    <span><?= date('M j, Y', strtotime($event['date'])) ?> at <?= date('g:i A', strtotime($event['time'])) ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-geo-alt text-<?= $categoryClass ?> me-2"></i>
                                    <span><?= htmlspecialchars($event['location']) ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people text-<?= $categoryClass ?> me-2"></i>
                                    <span><?= $event['attendees'] ?>/<?= $event['max_attendees'] ?> attendees</span>
                                </div>
                            </div>
                            
                            <!-- Attendance Progress -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Registration Progress</small>
                                    <small class="fw-bold"><?= $attendancePercentage ?>%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $categoryClass ?>" style="width: <?= $attendancePercentage ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if (!$isFree): ?>
                                            <span class="fw-bold text-<?= $categoryClass ?>">$<?= number_format($event['price']) ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold text-success">Free Event</span>
                                        <?php endif; ?>
                                        <br>
                                        <span class="badge bg-<?= $statusClass ?> bg-opacity-20 text-<?= $statusClass ?>"><?= ucfirst($event['status']) ?></span>
                                    </div>
                                    <div class="btn-group">
                                        <a href="/events/<?= $event['id'] ?>" class="btn btn-sm btn-outline-<?= $categoryClass ?>">
                                            View Details
                                        </a>
                                        <?php if ($event['status'] === 'upcoming'): ?>
                                            <button class="btn btn-sm btn-<?= $categoryClass ?>" onclick="registerForEvent(<?= $event['id'] ?>)">
                                                Register
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Load More Button -->
        <div class="text-center mb-4">
            <button class="btn btn-outline-primary" onclick="loadMoreEvents()">
                <i class="bi bi-arrow-down-circle me-1"></i>
                Load More Events
            </button>
        </div>
    </div>
    
    <!-- Events Sidebar -->
    <div class="col-lg-4">
        <!-- Today's Events -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-calendar-day me-2"></i>
                    Today's Events
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center py-3">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 small">No events scheduled for today</p>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                        <i class="bi bi-plus-circle me-1"></i>
                        Create Event
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Popular Events -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-fire me-2"></i>
                    Popular Events
                </h6>
            </div>
            <div class="card-body">
                <div class="popular-event-item d-flex align-items-center mb-3">
                    <div class="event-thumbnail bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-music-note"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 small">Oromo Cultural Night</h6>
                        <small class="text-muted">120 registrations</small>
                    </div>
                    <span class="badge bg-primary">1</span>
                </div>
                
                <div class="popular-event-item d-flex align-items-center mb-3">
                    <div class="event-thumbnail bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 small">Fundraising Gala</h6>
                        <small class="text-muted">85 registrations</small>
                    </div>
                    <span class="badge bg-warning">2</span>
                </div>
                
                <div class="popular-event-item d-flex align-items-center">
                    <div class="event-thumbnail bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 small">Community Service</h6>
                        <small class="text-muted">65 registrations</small>
                    </div>
                    <span class="badge bg-info">3</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Quick Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">This Month</span>
                    <span class="fw-bold">8 events</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">Total Registrations</span>
                    <span class="fw-bold">375</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">Revenue Generated</span>
                    <span class="fw-bold">$3,250</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small">Average Rating</span>
                    <div>
                        <span class="fw-bold">4.6</span>
                        <div class="stars">
                            <i class="bi bi-star-fill text-warning small"></i>
                            <i class="bi bi-star-fill text-warning small"></i>
                            <i class="bi bi-star-fill text-warning small"></i>
                            <i class="bi bi-star-fill text-warning small"></i>
                            <i class="bi bi-star-half text-warning small"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Create New Event
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createEventForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="eventTitle" class="form-label">Event Title</label>
                                <input type="text" class="form-control" id="eventTitle" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="eventDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="eventDescription" name="description" rows="3" placeholder="Describe your event..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="eventCategory" class="form-label">Category</label>
                                <select class="form-select" id="eventCategoryModal" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="cultural">Cultural</option>
                                    <option value="educational">Educational</option>
                                    <option value="community">Community</option>
                                    <option value="fundraising">Fundraising</option>
                                    <option value="sports">Sports</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="featuredEvent" name="featured">
                                    <label class="form-check-label" for="featuredEvent">
                                        <i class="bi bi-star"></i> Featured Event
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="eventDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="eventDate" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="eventTime" class="form-label">Time</label>
                                <input type="time" class="form-control" id="eventTime" name="time" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="eventPrice" class="form-label">Price ($)</label>
                                <input type="number" class="form-control" id="eventPrice" name="price" min="0" step="0.01" placeholder="0.00">
                                <small class="form-text text-muted">Leave 0 for free events</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="maxAttendees" class="form-label">Max Attendees</label>
                                <input type="number" class="form-control" id="maxAttendees" name="max_attendees" min="1" placeholder="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="eventLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="eventLocation" name="location" placeholder="Event venue or 'Virtual'">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="eventOrganizer" class="form-label">Organizer</label>
                                <select class="form-select" id="eventOrganizer" name="organizer">
                                    <option value="">Select Organizer</option>
                                    <option value="abo">ABO Leadership</option>
                                    <option value="wbo">WBO Leadership</option>
                                    <option value="youth">Youth Committee</option>
                                    <option value="women">Women's Committee</option>
                                    <option value="cultural">Cultural Committee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventImage" class="form-label">Event Image</label>
                        <input type="file" class="form-control" id="eventImage" name="image" accept="image/*">
                        <small class="form-text text-muted">Upload an image to promote your event</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventTags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="eventTags" name="tags" placeholder="community, culture, education (comma-separated)">
                        <small class="form-text text-muted">Help people find your event with relevant tags</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createEvent()">
                    <i class="bi bi-calendar-check me-1"></i>
                    Create Event
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.event-item {
    transition: var(--abo-transition);
    position: relative;
    overflow: hidden;
}

.event-item:hover {
    transform: translateY(-4px);
    box-shadow: var(--abo-shadow-lg);
}

.featured-event {
    border: 2px solid var(--abo-primary);
}

.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--abo-primary);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: var(--abo-radius);
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.event-image {
    height: 180px;
    position: relative;
    overflow: hidden;
}

.placeholder-image {
    width: 100%;
    height: 100%;
}

.event-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
}

.event-badges .badge {
    margin-right: 0.25rem;
    margin-bottom: 0.25rem;
}

.event-details i {
    width: 16px;
}

.event-thumbnail {
    width: 40px;
    height: 40px;
    border-radius: var(--abo-radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.popular-event-item {
    transition: var(--abo-transition);
    padding: 0.25rem;
    border-radius: var(--abo-radius);
}

.popular-event-item:hover {
    background-color: var(--abo-gray-50);
}

.stars i {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .event-card {
        margin-bottom: 1rem !important;
    }
    
    .action-icon {
        width: 30px;
        height: 30px;
        font-size: 0.875rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    document.getElementById('eventDate').min = new Date().toISOString().split('T')[0];
    
    // Category filter functionality
    const categoryButtons = document.querySelectorAll('[data-category]');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            filterEventsByCategory(category);
        });
    });
    
    // Search and filter functionality
    const searchInput = document.getElementById('eventSearch');
    const categoryFilter = document.getElementById('eventCategory');
    const statusFilter = document.getElementById('eventStatus');
    
    [searchInput, categoryFilter, statusFilter].forEach(input => {
        input.addEventListener('change', applyFilters);
        if (input.type === 'text') {
            input.addEventListener('keyup', applyFilters);
        }
    });
});

function filterEventsByCategory(category) {
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        if (category && card.getAttribute('data-category') !== category) {
            card.style.display = 'none';
        } else {
            card.style.display = 'block';
        }
    });
    
    // Update category filter dropdown
    document.getElementById('eventCategory').value = category || '';
}

function applyFilters() {
    const searchTerm = document.getElementById('eventSearch').value.toLowerCase();
    const categoryFilter = document.getElementById('eventCategory').value;
    const statusFilter = document.getElementById('eventStatus').value;
    
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        const title = card.querySelector('.card-title').textContent.toLowerCase();
        const category = card.getAttribute('data-category');
        const status = card.getAttribute('data-status');
        
        let show = true;
        
        if (searchTerm && !title.includes(searchTerm)) {
            show = false;
        }
        
        if (categoryFilter && category !== categoryFilter) {
            show = false;
        }
        
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}

function clearFilters() {
    document.getElementById('eventSearch').value = '';
    document.getElementById('eventCategory').value = '';
    document.getElementById('eventStatus').value = '';
    document.getElementById('eventDateRange').value = '';
    
    // Show all events
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.style.display = 'block';
    });
}

function createEvent() {
    const form = document.getElementById('createEventForm');
    const formData = new FormData(form);
    
    // Here you would normally send the data to the server
    console.log('Creating event with data:', Object.fromEntries(formData));
    
    // Simulate success
    alert('Event created successfully!');
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('createEventModal'));
    modal.hide();
    form.reset();
    
    // Refresh page (in real app, you'd add the event to the DOM)
    window.location.reload();
}

function registerForEvent(eventId) {
    // Here you would handle event registration
    console.log('Registering for event:', eventId);
    
    if (confirm('Would you like to register for this event?')) {
        alert('Registration successful! You will receive a confirmation email shortly.');
    }
}

function loadMoreEvents() {
    // Here you would load more events from the server
    console.log('Loading more events...');
    alert('Loading more events... (This would normally fetch from server)');
}
</script>
