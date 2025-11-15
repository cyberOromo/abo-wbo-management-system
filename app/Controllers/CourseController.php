<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Services\NotificationService;
use Exception;

class CourseController extends Controller
{
    private $courseModel;
    private $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->courseModel = new Course();
        $this->notificationService = new NotificationService();
    }

    /**
     * Display course list
     */
    public function index()
    {
        try {
            $this->requireAuth();
            
            $scope = $_GET['scope'] ?? $this->user['level_scope'];
            $scopeId = $_GET['scope_id'] ?? $this->user['scope_id'];
            $status = $_GET['status'] ?? 'all';
            $type = $_GET['type'] ?? 'all';
            $search = $_GET['search'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            
            $courses = $this->courseModel->searchCourses($search, $scope, $scopeId, $status, $type, $page, $limit);
            $totalCourses = $this->courseModel->getCoursesCount($scope, $scopeId, $status, $type, $search);
            
            $data = [
                'courses' => $courses,
                'total' => $totalCourses,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($totalCourses / $limit),
                'filters' => [
                    'scope' => $scope,
                    'scope_id' => $scopeId,
                    'status' => $status,
                    'type' => $type,
                    'search' => $search
                ]
            ];
            
            $this->render('courses/index', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load courses: ' . $e->getMessage());
            $this->render('courses/index', ['courses' => [], 'total' => 0]);
        }
    }

    /**
     * Show create course form
     */
    public function create()
    {
        try {
            $this->requireAuth();
            
            // Check permissions
            if (!$this->canCreateCourse()) {
                $this->setError('You do not have permission to create courses');
                return $this->redirect('/courses');
            }
            
            $data = [
                'course' => null,
                'isEdit' => false
            ];
            
            $this->render('courses/create', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load create form: ' . $e->getMessage());
            $this->redirect('/courses');
        }
    }

    /**
     * Store new course
     */
    public function store()
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            // Check permissions
            if (!$this->canCreateCourse()) {
                throw new Exception('You do not have permission to create courses');
            }
            
            $data = $this->validateCourseData($_POST);
            $data['created_by'] = $this->user['id'];
            
            $courseId = $this->courseModel->createCourse($data);
            
            if ($courseId) {
                // Send notification if course is published
                if ($data['status'] === 'published') {
                    $this->notificationService->sendCourseAnnouncementNotification($courseId);
                }
                
                $this->setSuccess('Course created successfully');
                $this->redirect('/courses/' . $courseId);
            } else {
                throw new Exception('Failed to create course');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to create course: ' . $e->getMessage());
            $this->render('courses/create', ['course' => $_POST, 'isEdit' => false]);
        }
    }

    /**
     * Show specific course
     */
    public function show($id)
    {
        try {
            $this->requireAuth();
            
            $course = $this->courseModel->getCourseById($id);
            
            if (!$course) {
                $this->setError('Course not found');
                return $this->redirect('/courses');
            }
            
            // Check access permissions
            if (!$this->canViewCourse($course)) {
                $this->setError('You do not have permission to view this course');
                return $this->redirect('/courses');
            }
            
            $enrollments = $this->courseModel->getCourseEnrollments($id);
            $statistics = $this->courseModel->getCourseStatistics($id);
            $lessons = $this->courseModel->getCourseLessons($id);
            $userEnrollment = $this->courseModel->getUserEnrollment($id, $this->user['id']);
            $userProgress = null;
            
            if ($userEnrollment) {
                $userProgress = $this->courseModel->getStudentProgress($id, $this->user['id']);
            }
            
            $data = [
                'course' => $course,
                'enrollments' => $enrollments,
                'statistics' => $statistics,
                'lessons' => $lessons,
                'userEnrollment' => $userEnrollment,
                'userProgress' => $userProgress,
                'canEdit' => $this->canEditCourse($course),
                'canDelete' => $this->canDeleteCourse($course),
                'canEnroll' => $this->canEnrollInCourse($course, $userEnrollment),
                'canManageLessons' => $this->canManageCourseLessons($course)
            ];
            
            $this->render('courses/show', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load course: ' . $e->getMessage());
            $this->redirect('/courses');
        }
    }

    /**
     * Show edit course form
     */
    public function edit($id)
    {
        try {
            $this->requireAuth();
            
            $course = $this->courseModel->getCourseById($id);
            
            if (!$course) {
                $this->setError('Course not found');
                return $this->redirect('/courses');
            }
            
            if (!$this->canEditCourse($course)) {
                $this->setError('You do not have permission to edit this course');
                return $this->redirect('/courses/' . $id);
            }
            
            $data = [
                'course' => $course,
                'isEdit' => true
            ];
            
            $this->render('courses/edit', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load edit form: ' . $e->getMessage());
            $this->redirect('/courses');
        }
    }

    /**
     * Update course
     */
    public function update($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $course = $this->courseModel->getCourseById($id);
            
            if (!$course) {
                throw new Exception('Course not found');
            }
            
            if (!$this->canEditCourse($course)) {
                throw new Exception('You do not have permission to edit this course');
            }
            
            $data = $this->validateCourseData($_POST);
            
            $success = $this->courseModel->updateCourse($id, $data);
            
            if ($success) {
                // Log activity
                $this->courseModel->logCourseActivity($id, $this->user['id'], 'updated', 'Course details updated');
                
                $this->setSuccess('Course updated successfully');
                $this->redirect('/courses/' . $id);
            } else {
                throw new Exception('Failed to update course');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to update course: ' . $e->getMessage());
            $this->render('courses/edit', ['course' => array_merge($course, $_POST), 'isEdit' => true]);
        }
    }

    /**
     * Delete course
     */
    public function delete($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $course = $this->courseModel->getCourseById($id);
            
            if (!$course) {
                throw new Exception('Course not found');
            }
            
            if (!$this->canDeleteCourse($course)) {
                throw new Exception('You do not have permission to delete this course');
            }
            
            $success = $this->courseModel->deleteCourse($id);
            
            if ($success) {
                $this->setSuccess('Course deleted successfully');
                $this->redirect('/courses');
            } else {
                throw new Exception('Failed to delete course');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to delete course: ' . $e->getMessage());
            $this->redirect('/courses/' . $id);
        }
    }

    /**
     * Enroll in course
     */
    public function enroll($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $course = $this->courseModel->getCourseById($id);
            
            if (!$course) {
                throw new Exception('Course not found');
            }
            
            $existingEnrollment = $this->courseModel->getUserEnrollment($id, $this->user['id']);
            
            if ($existingEnrollment) {
                throw new Exception('You are already enrolled in this course');
            }
            
            if (!$this->canEnrollInCourse($course, null)) {
                throw new Exception('Enrollment is not available for this course');
            }
            
            $enrollmentData = $_POST['enrollment_data'] ?? [];
            
            $success = $this->courseModel->enrollUser($id, $this->user['id'], 'pending', $enrollmentData);
            
            if ($success) {
                // Log activity
                $this->courseModel->logCourseActivity($id, $this->user['id'], 'enrolled', 'User enrolled in course');
                
                // Send confirmation notification
                $this->notificationService->sendCourseEnrollmentConfirmationNotification($id, $this->user['id']);
                
                $this->setSuccess('Successfully enrolled in the course');
                $this->redirect('/courses/' . $id);
            } else {
                throw new Exception('Failed to enroll in course');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to enroll: ' . $e->getMessage());
            $this->redirect('/courses/' . $id);
        }
    }

    /**
     * Drop from course
     */
    public function drop($id)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $course = $this->courseModel->getCourseById($id);
            
            if (!$course) {
                throw new Exception('Course not found');
            }
            
            $enrollment = $this->courseModel->getUserEnrollment($id, $this->user['id']);
            
            if (!$enrollment) {
                throw new Exception('You are not enrolled in this course');
            }
            
            $success = $this->courseModel->updateEnrollmentStatus($id, $this->user['id'], 'dropped');
            
            if ($success) {
                // Log activity
                $this->courseModel->logCourseActivity($id, $this->user['id'], 'dropped', 'User dropped from course');
                
                $this->setSuccess('Successfully dropped from the course');
                $this->redirect('/courses/' . $id);
            } else {
                throw new Exception('Failed to drop from course');
            }
            
        } catch (Exception $e) {
            $this->setError('Failed to drop from course: ' . $e->getMessage());
            $this->redirect('/courses/' . $id);
        }
    }

    /**
     * Show course lessons
     */
    public function lessons($id)
    {
        try {
            $this->requireAuth();
            
            $course = $this->courseModel->getCourseById($id);
            
            if (!$course) {
                $this->setError('Course not found');
                return $this->redirect('/courses');
            }
            
            if (!$this->canViewCourse($course)) {
                $this->setError('You do not have permission to view this course');
                return $this->redirect('/courses');
            }
            
            $lessons = $this->courseModel->getCourseLessons($id);
            $userEnrollment = $this->courseModel->getUserEnrollment($id, $this->user['id']);
            $userProgress = null;
            
            if ($userEnrollment) {
                $userProgress = $this->courseModel->getStudentProgress($id, $this->user['id']);
            }
            
            $data = [
                'course' => $course,
                'lessons' => $lessons,
                'userEnrollment' => $userEnrollment,
                'userProgress' => $userProgress,
                'canManageLessons' => $this->canManageCourseLessons($course)
            ];
            
            $this->render('courses/lessons', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load lessons: ' . $e->getMessage());
            $this->redirect('/courses/' . $id);
        }
    }

    /**
     * Show single lesson
     */
    public function lesson($courseId, $lessonId)
    {
        try {
            $this->requireAuth();
            
            $course = $this->courseModel->getCourseById($courseId);
            $lesson = $this->courseModel->getLessonById($lessonId);
            
            if (!$course || !$lesson) {
                $this->setError('Course or lesson not found');
                return $this->redirect('/courses');
            }
            
            if (!$this->canViewCourse($course)) {
                $this->setError('You do not have permission to view this course');
                return $this->redirect('/courses');
            }
            
            $userEnrollment = $this->courseModel->getUserEnrollment($courseId, $this->user['id']);
            
            if (!$userEnrollment && !$this->canManageCourseLessons($course)) {
                $this->setError('You must be enrolled to view lessons');
                return $this->redirect('/courses/' . $courseId);
            }
            
            $lessonProgress = $this->courseModel->getLessonProgress($lessonId, $this->user['id']);
            
            $data = [
                'course' => $course,
                'lesson' => $lesson,
                'userEnrollment' => $userEnrollment,
                'lessonProgress' => $lessonProgress,
                'canManageLessons' => $this->canManageCourseLessons($course)
            ];
            
            $this->render('courses/lesson', $data);
            
        } catch (Exception $e) {
            $this->setError('Failed to load lesson: ' . $e->getMessage());
            $this->redirect('/courses/' . $courseId . '/lessons');
        }
    }

    /**
     * Update lesson progress
     */
    public function updateLessonProgress($courseId, $lessonId)
    {
        try {
            $this->requireAuth();
            $this->requirePost();
            $this->validateCsrfToken();
            
            $course = $this->courseModel->getCourseById($courseId);
            $lesson = $this->courseModel->getLessonById($lessonId);
            
            if (!$course || !$lesson) {
                throw new Exception('Course or lesson not found');
            }
            
            $userEnrollment = $this->courseModel->getUserEnrollment($courseId, $this->user['id']);
            
            if (!$userEnrollment) {
                throw new Exception('You must be enrolled to update lesson progress');
            }
            
            $status = $_POST['status'] ?? 'in_progress';
            $progressPercentage = (int)($_POST['progress_percentage'] ?? 0);
            $timeSpent = (int)($_POST['time_spent_minutes'] ?? 0);
            
            $success = $this->courseModel->updateLessonProgress($lessonId, $this->user['id'], $status, $progressPercentage, $timeSpent);
            
            if ($success) {
                // Log activity
                $this->courseModel->logCourseActivity($courseId, $this->user['id'], 'lesson_progress', "Updated progress for lesson: {$lesson['title']}");
                
                $this->setSuccess('Lesson progress updated successfully');
            } else {
                throw new Exception('Failed to update lesson progress');
            }
            
            $this->redirect('/courses/' . $courseId . '/lessons/' . $lessonId);
            
        } catch (Exception $e) {
            $this->setError('Failed to update lesson progress: ' . $e->getMessage());
            $this->redirect('/courses/' . $courseId . '/lessons/' . $lessonId);
        }
    }

    /**
     * API endpoints
     */
    public function api($action = 'list')
    {
        try {
            $this->requireAuth();
            $this->setJsonResponse();
            
            switch ($action) {
                case 'list':
                    return $this->apiList();
                case 'show':
                    return $this->apiShow();
                case 'enroll':
                    return $this->apiEnroll();
                case 'progress':
                    return $this->apiProgress();
                case 'statistics':
                    return $this->apiStatistics();
                default:
                    throw new Exception('Invalid API action');
            }
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    private function apiList()
    {
        $scope = $_GET['scope'] ?? $this->user['level_scope'];
        $scopeId = $_GET['scope_id'] ?? $this->user['scope_id'];
        $status = $_GET['status'] ?? 'published';
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        
        $courses = $this->courseModel->searchCourses($search, $scope, $scopeId, $status, 'all', $page, $limit);
        $total = $this->courseModel->getCoursesCount($scope, $scopeId, $status, 'all', $search);
        
        $this->jsonResponse([
            'courses' => $courses,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }

    private function apiShow()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Course ID is required');
        }
        
        $course = $this->courseModel->getCourseById($id);
        
        if (!$course) {
            throw new Exception('Course not found');
        }
        
        if (!$this->canViewCourse($course)) {
            throw new Exception('You do not have permission to view this course');
        }
        
        $statistics = $this->courseModel->getCourseStatistics($id);
        $userEnrollment = $this->courseModel->getUserEnrollment($id, $this->user['id']);
        $userProgress = null;
        
        if ($userEnrollment) {
            $userProgress = $this->courseModel->getStudentProgress($id, $this->user['id']);
        }
        
        $this->jsonResponse([
            'course' => $course,
            'statistics' => $statistics,
            'userEnrollment' => $userEnrollment,
            'userProgress' => $userProgress,
            'canEnroll' => $this->canEnrollInCourse($course, $userEnrollment)
        ]);
    }

    private function apiProgress()
    {
        $courseId = $_GET['course_id'] ?? null;
        $userId = $_GET['user_id'] ?? $this->user['id'];
        
        if (!$courseId) {
            throw new Exception('Course ID is required');
        }
        
        $course = $this->courseModel->getCourseById($courseId);
        
        if (!$course) {
            throw new Exception('Course not found');
        }
        
        // Check permissions
        if ($userId != $this->user['id'] && !$this->canManageCourseLessons($course)) {
            throw new Exception('You do not have permission to view this user\'s progress');
        }
        
        $progress = $this->courseModel->getStudentProgress($courseId, $userId);
        
        $this->jsonResponse(['progress' => $progress]);
    }

    /**
     * Validate course data
     */
    private function validateCourseData($data)
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['description'])) {
            $errors[] = 'Description is required';
        }
        
        if (!empty($data['duration_hours']) && (int)$data['duration_hours'] < 1) {
            $errors[] = 'Duration must be at least 1 hour';
        }
        
        if (!empty($data['max_students']) && (int)$data['max_students'] < 1) {
            $errors[] = 'Maximum students must be at least 1';
        }
        
        if (!empty($data['course_fee']) && (float)$data['course_fee'] < 0) {
            $errors[] = 'Course fee cannot be negative';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
        
        // Clean and structure data
        $cleanData = [
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'course_type' => $data['course_type'] ?? 'cultural',
            'level_scope' => $data['level_scope'] ?? $this->user['level_scope'],
            'scope_id' => $data['scope_id'] ?? $this->user['scope_id'],
            'difficulty_level' => $data['difficulty_level'] ?? 'beginner',
            'instructor_id' => !empty($data['instructor_id']) ? (int)$data['instructor_id'] : $this->user['id'],
            'duration_hours' => !empty($data['duration_hours']) ? (int)$data['duration_hours'] : null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'enrollment_type' => $data['enrollment_type'] ?? 'open',
            'enrollment_start' => $data['enrollment_start'] ?? null,
            'enrollment_end' => $data['enrollment_end'] ?? null,
            'max_students' => !empty($data['max_students']) ? (int)$data['max_students'] : null,
            'certification_available' => !empty($data['certification_available']) ? 1 : 0,
            'course_fee' => !empty($data['course_fee']) ? (float)$data['course_fee'] : 0.00,
            'currency' => $data['currency'] ?? 'USD',
            'language' => $data['language'] ?? 'en',
            'status' => $data['status'] ?? 'draft',
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'tags' => !empty($data['tags']) ? json_encode(explode(',', $data['tags'])) : null
        ];
        
        // Handle JSON fields
        if (!empty($data['prerequisites'])) {
            $cleanData['prerequisites'] = json_encode($data['prerequisites']);
        }
        
        if (!empty($data['learning_objectives'])) {
            $cleanData['learning_objectives'] = json_encode($data['learning_objectives']);
        }
        
        if (!empty($data['course_outline'])) {
            $cleanData['course_outline'] = json_encode($data['course_outline']);
        }
        
        if (!empty($data['materials_needed'])) {
            $cleanData['materials_needed'] = json_encode($data['materials_needed']);
        }
        
        if (!empty($data['assessment_criteria'])) {
            $cleanData['assessment_criteria'] = json_encode($data['assessment_criteria']);
        }
        
        if (!empty($data['co_instructors'])) {
            $cleanData['co_instructors'] = json_encode($data['co_instructors']);
        }
        
        return $cleanData;
    }

    /**
     * Permission checks
     */
    private function canCreateCourse()
    {
        return in_array($this->user['role'], ['admin', 'leader', 'instructor']);
    }
    
    private function canViewCourse($course)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($course['created_by'] == $this->user['id']) return true;
        if ($course['instructor_id'] == $this->user['id']) return true;
        if ($course['status'] === 'published') return true;
        
        // Check if user is in the same scope or parent scope
        return $this->isInScopeHierarchy($course['level_scope'], $course['scope_id']);
    }
    
    private function canEditCourse($course)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($course['created_by'] == $this->user['id']) return true;
        if ($course['instructor_id'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canDeleteCourse($course)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($course['created_by'] == $this->user['id']) return true;
        
        return false;
    }
    
    private function canEnrollInCourse($course, $existingEnrollment)
    {
        if ($existingEnrollment) return false;
        if ($course['status'] !== 'published') return false;
        if ($course['enrollment_type'] === 'closed') return false;
        
        // Check enrollment dates
        $now = time();
        if (!empty($course['enrollment_start']) && strtotime($course['enrollment_start']) > $now) {
            return false;
        }
        if (!empty($course['enrollment_end']) && strtotime($course['enrollment_end']) < $now) {
            return false;
        }
        
        // Check capacity
        if (!empty($course['max_students'])) {
            $statistics = $this->courseModel->getCourseStatistics($course['id']);
            if ($statistics['total_enrolled'] >= $course['max_students']) {
                return false;
            }
        }
        
        return true;
    }
    
    private function canManageCourseLessons($course)
    {
        if ($this->user['role'] === 'admin') return true;
        if ($course['created_by'] == $this->user['id']) return true;
        if ($course['instructor_id'] == $this->user['id']) return true;
        
        // Check if user is co-instructor
        $coInstructors = json_decode($course['co_instructors'] ?? '[]', true);
        return in_array($this->user['id'], $coInstructors);
    }
}