<?php
/**
 * Courses Index View Template
 * Comprehensive course management with LMS functionality and progress tracking
 */

// Page metadata
$pageTitle = __('courses.title');
$pageDescription = __('courses.description');
$bodyClass = 'courses-page';

// Course data
$courses = $courses ?? [];
$courseStats = $courseStats ?? [];
$enrolledCourses = $enrolledCourses ?? [];
$filters = $filters ?? [];

// User permissions
$canCreateCourses = $permissions['can_create_courses'] ?? false;
$canManageCourses = $permissions['can_manage_courses'] ?? false;
$canEnrollCourses = $permissions['can_enroll_courses'] ?? true;

// Course categories
$courseCategories = [
    'leadership' => ['name' => __('courses.leadership'), 'color' => 'primary', 'icon' => 'person-badge'],
    'technical' => ['name' => __('courses.technical'), 'color' => 'success', 'icon' => 'gear'],
    'language' => ['name' => __('courses.language'), 'color' => 'info', 'icon' => 'translate'],
    'business' => ['name' => __('courses.business'), 'color' => 'warning', 'icon' => 'briefcase'],
    'cultural' => ['name' => __('courses.cultural'), 'color' => 'danger', 'icon' => 'globe'],
    'health' => ['name' => __('courses.health'), 'color' => 'secondary', 'icon' => 'heart-pulse']
];

// Course difficulty levels
$difficultyLevels = [
    'beginner' => ['name' => __('courses.beginner'), 'color' => 'success'],
    'intermediate' => ['name' => __('courses.intermediate'), 'color' => 'warning'], 
    'advanced' => ['name' => __('courses.advanced'), 'color' => 'danger'],
    'expert' => ['name' => __('courses.expert'), 'color' => 'dark']
];

// Course statuses
$courseStatuses = [
    'draft' => ['name' => __('courses.draft'), 'color' => 'secondary'],
    'published' => ['name' => __('courses.published'), 'color' => 'primary'],
    'ongoing' => ['name' => __('courses.ongoing'), 'color' => 'warning'],
    'completed' => ['name' => __('courses.completed'), 'color' => 'success'],
    'archived' => ['name' => __('courses.archived'), 'color' => 'muted']
];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= __('courses.course_management') ?></h1>
        <p class="text-muted mb-0"><?= __('courses.manage_learning_programs') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canCreateCourses): ?>
            <a href="/courses/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> <?= __('courses.create_course') ?>
            </a>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> <?= __('courses.export') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/courses/export?format=excel">
                    <i class="bi bi-file-earmark-excel me-2"></i><?= __('courses.export_excel') ?>
                </a></li>
                <li><a class="dropdown-item" href="/courses/export?format=pdf">
                    <i class="bi bi-file-earmark-pdf me-2"></i><?= __('courses.export_pdf') ?>
                </a></li>
                <li><a class="dropdown-item" href="/courses/certificates">
                    <i class="bi bi-award me-2"></i><?= __('courses.certificates') ?>
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Course Statistics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= __('courses.total_courses') ?></h5>
                        <h2 class="mb-0"><?= number_format($courseStats['total'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-book fs-1 opacity-25"></i>
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
                        <h5 class="card-title mb-1"><?= __('courses.active_enrollments') ?></h5>
                        <h2 class="mb-0"><?= number_format($courseStats['active_enrollments'] ?? 0) ?></h2>
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
                        <h5 class="card-title mb-1"><?= __('courses.completion_rate') ?></h5>
                        <h2 class="mb-0"><?= number_format($courseStats['completion_rate'] ?? 0) ?>%</h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-trophy fs-1 opacity-25"></i>
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
                        <h5 class="card-title mb-1"><?= __('courses.certificates_issued') ?></h5>
                        <h2 class="mb-0"><?= number_format($courseStats['certificates_issued'] ?? 0) ?></h2>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-award fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Learning Dashboard -->
<?php if (!empty($enrolledCourses)): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><?= __('courses.my_learning') ?></h6>
                <a href="/courses/my-courses" class="btn btn-sm btn-outline-primary"><?= __('courses.view_all_enrolled') ?></a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach (array_slice($enrolledCourses, 0, 4) as $course): ?>
                        <div class="col-xl-3 col-md-6">
                            <div class="enrolled-course-card">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="<?= $course['thumbnail'] ?? '/assets/images/default-course.jpg' ?>" 
                                         alt="<?= htmlspecialchars($course['title']) ?>"
                                         class="course-thumbnail me-3">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="/courses/<?= $course['id'] ?>/learn" class="text-decoration-none">
                                                <?= htmlspecialchars($course['title']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted"><?= $course['instructor_name'] ?></small>
                                    </div>
                                </div>
                                
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar" style="width: <?= $course['progress_percentage'] ?>%"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?= $course['progress_percentage'] ?>% <?= __('courses.complete') ?>
                                    </small>
                                    <div class="d-flex gap-1">
                                        <a href="/courses/<?= $course['id'] ?>/learn" class="btn btn-sm btn-primary">
                                            <?= __('courses.continue') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Course Categories Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Category Filters -->
            <div class="category-filters d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-secondary btn-sm category-filter active" data-category="">
                    <?= __('courses.all_categories') ?>
                </button>
                <?php foreach ($courseCategories as $category => $config): ?>
                    <button class="btn btn-outline-<?= $config['color'] ?> btn-sm category-filter" 
                            data-category="<?= $category ?>">
                        <i class="bi bi-<?= $config['icon'] ?> me-1"></i>
                        <?= $config['name'] ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- View Toggle and Filters -->
            <div class="d-flex gap-2">
                <div class="btn-group view-toggle" role="group">
                    <input type="radio" class="btn-check" name="view-mode" id="grid-view" checked>
                    <label class="btn btn-outline-primary btn-sm" for="grid-view">
                        <i class="bi bi-grid"></i>
                    </label>
                    <input type="radio" class="btn-check" name="view-mode" id="list-view">
                    <label class="btn btn-outline-primary btn-sm" for="list-view">
                        <i class="bi bi-list-ul"></i>
                    </label>
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel"></i> <?= __('courses.filters') ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                        <form class="filters-form">
                            <div class="mb-3">
                                <label class="form-label"><?= __('courses.search') ?></label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                                       placeholder="<?= __('courses.search_placeholder') ?>">
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label"><?= __('courses.difficulty') ?></label>
                                    <select class="form-select" name="difficulty">
                                        <option value=""><?= __('courses.all_levels') ?></option>
                                        <?php foreach ($difficultyLevels as $level => $config): ?>
                                            <option value="<?= $level ?>" 
                                                    <?= ($filters['difficulty'] ?? '') === $level ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label"><?= __('courses.status') ?></label>
                                    <select class="form-select" name="status">
                                        <option value=""><?= __('courses.all_statuses') ?></option>
                                        <?php foreach ($courseStatuses as $status => $config): ?>
                                            <option value="<?= $status ?>" 
                                                    <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>>
                                                <?= $config['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><?= __('courses.duration_hours') ?></label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="duration_min" 
                                               value="<?= $filters['duration_min'] ?? '' ?>" 
                                               placeholder="<?= __('courses.min') ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="duration_max" 
                                               value="<?= $filters['duration_max'] ?? '' ?>" 
                                               placeholder="<?= __('courses.max') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="free_only" 
                                           value="1" <?= ($filters['free_only'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= __('courses.free_courses_only') ?>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="certificate_available" 
                                           value="1" <?= ($filters['certificate_available'] ?? '') ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= __('courses.certificate_available') ?>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <?= __('courses.apply_filters') ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-filters">
                                    <?= __('courses.clear') ?>
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
    <?php if (empty($courses)): ?>
        <div class="card">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-book display-1 text-muted"></i>
                    <h4 class="text-muted mt-3"><?= __('courses.no_courses_found') ?></h4>
                    <p class="text-muted"><?= __('courses.no_courses_description') ?></p>
                    <?php if ($canCreateCourses): ?>
                        <a href="/courses/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> <?= __('courses.create_first_course') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3 courses-grid">
            <?php foreach ($courses as $course): ?>
                <div class="col-xl-4 col-md-6 course-item" data-category="<?= $course['category'] ?>">
                    <div class="card course-card h-100">
                        <!-- Course Thumbnail -->
                        <div class="course-image-container position-relative">
                            <img src="<?= $course['thumbnail'] ?? '/assets/images/default-course.jpg' ?>" 
                                 alt="<?= htmlspecialchars($course['title']) ?>"
                                 class="card-img-top course-image">
                            <div class="course-overlay">
                                <span class="badge bg-<?= $courseCategories[$course['category']]['color'] ?> course-category-badge">
                                    <i class="bi bi-<?= $courseCategories[$course['category']]['icon'] ?> me-1"></i>
                                    <?= $courseCategories[$course['category']]['name'] ?>
                                </span>
                                <span class="badge bg-<?= $difficultyLevels[$course['difficulty']]['color'] ?> course-difficulty-badge">
                                    <?= $difficultyLevels[$course['difficulty']]['name'] ?>
                                </span>
                            </div>
                            <?php if ($course['is_free']): ?>
                                <div class="free-badge">
                                    <span class="badge bg-success"><?= __('courses.free') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="course-meta">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= $course['duration_hours'] ?> <?= __('courses.hours') ?>
                                    </small>
                                </div>
                                <div class="course-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= $course['rating'] ? '-fill text-warning' : ' text-muted' ?>"></i>
                                    <?php endfor; ?>
                                    <small class="text-muted ms-1">(<?= $course['reviews_count'] ?>)</small>
                                </div>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="/courses/<?= $course['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($course['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted">
                                <?= htmlspecialchars(substr($course['description'], 0, 120)) ?>
                                <?= strlen($course['description']) > 120 ? '...' : '' ?>
                            </p>
                            
                            <div class="course-instructor d-flex align-items-center mb-3">
                                <img src="<?= $course['instructor_avatar'] ?? '/assets/images/default-avatar.svg' ?>" 
                                     alt="<?= htmlspecialchars($course['instructor_name']) ?>"
                                     class="rounded-circle me-2" width="32" height="32">
                                <div>
                                    <small class="fw-medium"><?= htmlspecialchars($course['instructor_name']) ?></small>
                                    <br><small class="text-muted"><?= htmlspecialchars($course['instructor_title'] ?? '') ?></small>
                                </div>
                            </div>
                            
                            <div class="course-stats mb-3">
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value"><?= $course['lessons_count'] ?></div>
                                            <small class="text-muted"><?= __('courses.lessons') ?></small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value"><?= $course['enrolled_count'] ?></div>
                                            <small class="text-muted"><?= __('courses.students') ?></small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value"><?= $course['completion_rate'] ?>%</div>
                                            <small class="text-muted"><?= __('courses.completion') ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="course-price">
                                    <?php if ($course['is_free']): ?>
                                        <span class="h6 text-success mb-0"><?= __('courses.free') ?></span>
                                    <?php else: ?>
                                        <span class="h6 text-primary mb-0"><?= format_currency($course['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="course-actions d-flex gap-2">
                                    <?php if ($course['is_enrolled']): ?>
                                        <a href="/courses/<?= $course['id'] ?>/learn" class="btn btn-sm btn-success">
                                            <i class="bi bi-play-circle"></i> <?= __('courses.continue') ?>
                                        </a>
                                    <?php elseif ($canEnrollCourses): ?>
                                        <button class="btn btn-sm btn-primary enroll-course" 
                                                data-course-id="<?= $course['id'] ?>">
                                            <i class="bi bi-person-plus"></i> <?= __('courses.enroll') ?>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="/courses/<?= $course['id'] ?>">
                                                <i class="bi bi-eye me-2"></i><?= __('courses.view_details') ?>
                                            </a></li>
                                            <li><a class="dropdown-item add-to-wishlist" href="#" 
                                                   data-course-id="<?= $course['id'] ?>">
                                                <i class="bi bi-heart me-2"></i><?= __('courses.add_to_wishlist') ?>
                                            </a></li>
                                            <?php if ($canManageCourses): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="/courses/<?= $course['id'] ?>/edit">
                                                    <i class="bi bi-pencil me-2"></i><?= __('courses.edit') ?>
                                                </a></li>
                                                <li><a class="dropdown-item" href="/courses/<?= $course['id'] ?>/analytics">
                                                    <i class="bi bi-graph-up me-2"></i><?= __('courses.analytics') ?>
                                                </a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
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
            <?php if (!empty($courses)): ?>
                <div class="courses-list">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-list-item border-bottom pb-3 mb-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <img src="<?= $course['thumbnail'] ?? '/assets/images/default-course.jpg' ?>" 
                                         alt="<?= htmlspecialchars($course['title']) ?>"
                                         class="img-fluid rounded course-list-image">
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="badge bg-<?= $courseCategories[$course['category']]['color'] ?>">
                                            <i class="bi bi-<?= $courseCategories[$course['category']]['icon'] ?> me-1"></i>
                                            <?= $courseCategories[$course['category']]['name'] ?>
                                        </span>
                                        <span class="badge bg-<?= $difficultyLevels[$course['difficulty']]['color'] ?>">
                                            <?= $difficultyLevels[$course['difficulty']]['name'] ?>
                                        </span>
                                        <?php if ($course['is_free']): ?>
                                            <span class="badge bg-success"><?= __('courses.free') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h5>
                                        <a href="/courses/<?= $course['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($course['title']) ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="text-muted mb-2">
                                        <?= htmlspecialchars(substr($course['description'], 0, 150)) ?>
                                        <?= strlen($course['description']) > 150 ? '...' : '' ?>
                                    </p>
                                    
                                    <div class="course-meta d-flex gap-3 small text-muted">
                                        <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($course['instructor_name']) ?></span>
                                        <span><i class="bi bi-clock me-1"></i><?= $course['duration_hours'] ?> <?= __('courses.hours') ?></span>
                                        <span><i class="bi bi-book me-1"></i><?= $course['lessons_count'] ?> <?= __('courses.lessons') ?></span>
                                        <span><i class="bi bi-people me-1"></i><?= $course['enrolled_count'] ?> <?= __('courses.students') ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="course-rating mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $course['rating'] ? '-fill text-warning' : ' text-muted' ?>"></i>
                                        <?php endfor; ?>
                                        <small class="text-muted ms-1">(<?= $course['reviews_count'] ?>)</small>
                                    </div>
                                    
                                    <div class="course-price mb-2">
                                        <?php if ($course['is_free']): ?>
                                            <span class="h5 text-success"><?= __('courses.free') ?></span>
                                        <?php else: ?>
                                            <span class="h5 text-primary"><?= format_currency($course['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="course-actions d-flex flex-column gap-2">
                                        <?php if ($course['is_enrolled']): ?>
                                            <a href="/courses/<?= $course['id'] ?>/learn" class="btn btn-success btn-sm">
                                                <i class="bi bi-play-circle"></i> <?= __('courses.continue') ?>
                                            </a>
                                        <?php elseif ($canEnrollCourses): ?>
                                            <button class="btn btn-primary btn-sm enroll-course" 
                                                    data-course-id="<?= $course['id'] ?>">
                                                <i class="bi bi-person-plus"></i> <?= __('courses.enroll') ?>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="/courses/<?= $course['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <?= __('courses.view_details') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Enrollment Modal -->
<div class="modal fade" id="enrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('courses.enroll_in_course') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="enrollment-course-info">
                    <!-- Course info will be loaded via JavaScript -->
                </div>
                
                <form id="enrollmentForm">
                    <input type="hidden" id="enrollment_course_id" name="course_id">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('courses.enrollment_type') ?></label>
                        <div class="enrollment-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="enrollment_type" 
                                       value="self_paced" id="self_paced" checked>
                                <label class="form-check-label" for="self_paced">
                                    <strong><?= __('courses.self_paced') ?></strong><br>
                                    <small class="text-muted"><?= __('courses.self_paced_description') ?></small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="enrollment_type" 
                                       value="instructor_led" id="instructor_led">
                                <label class="form-check-label" for="instructor_led">
                                    <strong><?= __('courses.instructor_led') ?></strong><br>
                                    <small class="text-muted"><?= __('courses.instructor_led_description') ?></small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('courses.learning_goals') ?></label>
                        <textarea class="form-control" name="learning_goals" rows="3" 
                                  placeholder="<?= __('courses.learning_goals_placeholder') ?>"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="email_notifications" 
                                   value="1" id="email_notifications" checked>
                            <label class="form-check-label" for="email_notifications">
                                <?= __('courses.receive_email_notifications') ?>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= __('common.cancel') ?>
                </button>
                <button type="button" class="btn btn-primary" id="confirmEnrollment">
                    <?= __('courses.enroll_now') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Course Styles -->
<style>
.stats-card {
    border: none;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.course-card {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
}

.course-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.course-image-container {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.course-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.course-card:hover .course-image {
    transform: scale(1.05);
}

.course-overlay {
    position: absolute;
    top: 1rem;
    left: 1rem;
    right: 1rem;
    display: flex;
    justify-content: space-between;
}

.free-badge {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
}

.course-thumbnail {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 4px;
}

.enrolled-course-card {
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #f8f9fa;
}

.category-filters .category-filter {
    transition: all 0.2s ease;
}

.category-filters .category-filter.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

.course-stats .stat-item {
    padding: 0.5rem;
    border-radius: 4px;
    background: #f8f9fa;
}

.stat-value {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--bs-primary);
}

.course-list-image {
    height: 100px;
    width: 100%;
    object-fit: cover;
}

.course-list-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
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

.enrollment-options .form-check {
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}

.enrollment-options .form-check:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.enrollment-options .form-check-input:checked + .form-check-label {
    color: var(--bs-primary);
}

@media (max-width: 768px) {
    .course-image-container {
        height: 150px;
    }
    
    .course-overlay {
        top: 0.5rem;
        left: 0.5rem;
        right: 0.5rem;
    }
    
    .free-badge {
        bottom: 0.5rem;
        right: 0.5rem;
    }
    
    .category-filters {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .course-actions {
        flex-direction: column;
    }
}
</style>

<!-- Courses JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switching
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const gridContent = document.getElementById('grid-view-content');
    const listContent = document.getElementById('list-view-content');
    
    gridView.addEventListener('change', function() {
        if (this.checked) {
            gridContent.style.display = 'block';
            listContent.style.display = 'none';
        }
    });
    
    listView.addEventListener('change', function() {
        if (this.checked) {
            gridContent.style.display = 'none';
            listContent.style.display = 'block';
        }
    });
    
    // Category filtering
    document.querySelectorAll('.category-filter').forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.category-filter').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.dataset.category;
            filterCoursesByCategory(category);
        });
    });
    
    // Filter form handling
    document.querySelector('.filters-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/courses?' + params.toString();
    });
    
    document.querySelector('.clear-filters').addEventListener('click', function() {
        window.location.href = '/courses';
    });
    
    // Course actions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('enroll-course') || e.target.closest('.enroll-course')) {
            e.preventDefault();
            const courseId = e.target.dataset.courseId || e.target.closest('.enroll-course').dataset.courseId;
            openEnrollmentModal(courseId);
        }
        
        if (e.target.classList.contains('add-to-wishlist') || e.target.closest('.add-to-wishlist')) {
            e.preventDefault();
            const courseId = e.target.dataset.courseId || e.target.closest('.add-to-wishlist').dataset.courseId;
            addToWishlist(courseId);
        }
    });
    
    // Enrollment modal
    document.getElementById('confirmEnrollment').addEventListener('click', function() {
        const form = document.getElementById('enrollmentForm');
        const formData = new FormData(form);
        
        fetch('/api/courses/enroll', {
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
                alert(data.message || '<?= __('courses.enrollment_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('courses.enrollment_error') ?>');
        });
    });
    
    // Functions
    function filterCoursesByCategory(category) {
        const courseItems = document.querySelectorAll('.course-item');
        
        courseItems.forEach(item => {
            if (!category || item.dataset.category === category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    function openEnrollmentModal(courseId) {
        document.getElementById('enrollment_course_id').value = courseId;
        
        // Load course information
        fetch(`/api/courses/${courseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const course = data.course;
                    document.getElementById('enrollment-course-info').innerHTML = `
                        <div class="d-flex align-items-center mb-3">
                            <img src="${course.thumbnail || '/assets/images/default-course.jpg'}" 
                                 alt="${course.title}" class="rounded me-3" width="64" height="64">
                            <div>
                                <h6 class="mb-1">${course.title}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>${course.instructor_name} • 
                                    <i class="bi bi-clock me-1"></i>${course.duration_hours} <?= __('courses.hours') ?> • 
                                    <i class="bi bi-book me-1"></i>${course.lessons_count} <?= __('courses.lessons') ?>
                                </small>
                                <br>
                                <span class="h6 text-primary">
                                    ${course.is_free ? '<?= __('courses.free') ?>' : course.price_formatted}
                                </span>
                            </div>
                        </div>
                    `;
                }
            });
        
        const modal = new bootstrap.Modal(document.getElementById('enrollmentModal'));
        modal.show();
    }
    
    function addToWishlist(courseId) {
        fetch(`/api/courses/${courseId}/wishlist`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI to show course is in wishlist
                const button = document.querySelector(`[data-course-id="${courseId}"].add-to-wishlist`);
                if (button) {
                    button.innerHTML = '<i class="bi bi-heart-fill me-2"></i><?= __('courses.in_wishlist') ?>';
                    button.classList.remove('add-to-wishlist');
                    button.classList.add('remove-from-wishlist');
                }
            } else {
                alert(data.message || '<?= __('courses.wishlist_add_failed') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('courses.wishlist_add_error') ?>');
        });
    }
});
</script>