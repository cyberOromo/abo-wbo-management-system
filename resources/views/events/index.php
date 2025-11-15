<?php
$title = $title ?? 'Community Events';
$events = $events ?? [];
$event_stats = $event_stats ?? [];
$user_scope = $user_scope ?? [];
$can_create = $can_create ?? false;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt text-success"></i>
                <?php echo htmlspecialchars($title); ?>
            </h1>
            <?php if (!empty($user_scope)): ?>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($user_scope['scope_name'] ?? 'Community Events'); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div>
            <?php if ($can_create): ?>
            <a href="/events/create" class="btn btn-success">
                <i class="fas fa-plus"></i> Create Event
            </a>
            <?php endif; ?>
            <a href="/events/calendar/view" class="btn btn-outline-info ml-2">
                <i class="fas fa-calendar"></i> Calendar View
            </a>
        </div>
    </div>

    <!-- Event Statistics -->
    <?php if (!empty($event_stats)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Events
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($event_stats['total'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Upcoming
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($event_stats['upcoming'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                My Registrations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($event_stats['my_registrations'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($event_stats['this_month'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Event Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-center">
                <div class="col-md-3">
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All Types</option>
                        <option value="cultural" <?php echo ($_GET['type'] ?? '') === 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                        <option value="educational" <?php echo ($_GET['type'] ?? '') === 'educational' ? 'selected' : ''; ?>>Educational</option>
                        <option value="fundraising" <?php echo ($_GET['type'] ?? '') === 'fundraising' ? 'selected' : ''; ?>>Fundraising</option>
                        <option value="community" <?php echo ($_GET['type'] ?? '') === 'community' ? 'selected' : ''; ?>>Community</option>
                        <option value="meeting" <?php echo ($_GET['type'] ?? '') === 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="upcoming" <?php echo ($_GET['status'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="ongoing" <?php echo ($_GET['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control form-control-sm" 
                           value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>" 
                           placeholder="Start Date">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="/events" class="btn btn-outline-secondary btn-sm ml-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Grid -->
    <?php if (!empty($events)): ?>
    <div class="row">
        <?php foreach ($events as $event): ?>
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <?php if (!empty($event['image_url'])): ?>
                <img class="card-img-top" src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                     alt="Event Image" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                     style="height: 200px;">
                    <i class="fas fa-calendar-alt fa-3x text-gray-400"></i>
                </div>
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        <?php
                        $eventType = $event['event_type'] ?? 'general';
                        $typeClass = [
                            'cultural' => 'warning',
                            'educational' => 'info',
                            'fundraising' => 'success',
                            'community' => 'primary',
                            'meeting' => 'secondary'
                        ][$eventType] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?php echo $typeClass; ?> mb-2">
                            <?php echo ucfirst($eventType); ?>
                        </span>
                    </div>
                    
                    <h5 class="card-title">
                        <?php echo htmlspecialchars($event['title'] ?? 'Untitled Event'); ?>
                    </h5>
                    
                    <p class="card-text text-muted">
                        <?php 
                        $description = $event['description'] ?? '';
                        echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description); 
                        ?>
                    </p>
                    
                    <div class="mb-3">
                        <?php if (!empty($event['start_date'])): ?>
                        <p class="mb-1">
                            <i class="fas fa-calendar text-primary"></i>
                            <strong>Date:</strong> <?php echo date('M j, Y', strtotime($event['start_date'])); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($event['start_time'])): ?>
                        <p class="mb-1">
                            <i class="fas fa-clock text-info"></i>
                            <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['start_time'])); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($event['location'])): ?>
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($event['max_participants'])): ?>
                        <p class="mb-1">
                            <i class="fas fa-users text-success"></i>
                            <strong>Capacity:</strong> 
                            <?php 
                            $registered = $event['registered_count'] ?? 0;
                            $max = $event['max_participants'];
                            echo "$registered / $max participants";
                            ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-auto">
                        <?php
                        $status = $event['status'] ?? 'upcoming';
                        $statusClass = [
                            'draft' => 'secondary',
                            'upcoming' => 'warning',
                            'ongoing' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ][$status] ?? 'secondary';
                        ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge badge-<?php echo $statusClass; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                            
                            <div>
                                <a href="/events/<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                <?php if ($status === 'upcoming' && !($event['is_registered'] ?? false)): ?>
                                <button type="button" class="btn btn-success btn-sm ml-1" 
                                        onclick="registerForEvent(<?php echo $event['id']; ?>)">
                                    <i class="fas fa-user-plus"></i> Register
                                </button>
                                <?php elseif ($event['is_registered'] ?? false): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm ml-1" 
                                        onclick="unregisterFromEvent(<?php echo $event['id']; ?>)">
                                    <i class="fas fa-user-times"></i> Unregister
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
    <?php else: ?>
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-alt fa-3x text-gray-300 mb-3"></i>
            <h5 class="text-gray-600">No events found</h5>
            <p class="text-muted">There are no community events available at this time.</p>
            <?php if ($can_create): ?>
            <a href="/events/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Your First Event
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function registerForEvent(eventId) {
    if (confirm('Are you sure you want to register for this event?')) {
        fetch('/events/' + eventId + '/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Registration failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error registering for event: ' + error.message);
        });
    }
}

function unregisterFromEvent(eventId) {
    if (confirm('Are you sure you want to unregister from this event?')) {
        fetch('/events/' + eventId + '/unregister', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Unregistration failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error unregistering from event: ' + error.message);
        });
    }
}
</script>