<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Course Model
 * Handles education/training platform with lesson tracking
 */
class Course extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    
    // Course types
    const TYPE_LANGUAGE = 'language';
    const TYPE_LEADERSHIP = 'leadership';
    const TYPE_TECHNICAL = 'technical';
    const TYPE_CULTURAL = 'cultural';
    const TYPE_HISTORY = 'history';
    const TYPE_POLITICAL = 'political';
    const TYPE_BUSINESS = 'business';
    const TYPE_LIFE_SKILLS = 'life_skills';
    
    // Course statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';
    
    // Course levels
    const LEVEL_BEGINNER = 'beginner';
    const LEVEL_INTERMEDIATE = 'intermediate';
    const LEVEL_ADVANCED = 'advanced';
    const LEVEL_EXPERT = 'expert';
    
    // Enrollment types
    const ENROLLMENT_OPEN = 'open';
    const ENROLLMENT_APPROVAL_REQUIRED = 'approval_required';
    const ENROLLMENT_INVITATION_ONLY = 'invitation_only';
    const ENROLLMENT_CLOSED = 'closed';

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'course_type',
        'level_scope',
        'scope_id',
        'difficulty_level',
        'instructor_id',
        'co_instructors',
        'duration_hours',
        'start_date',
        'end_date',
        'enrollment_type',
        'enrollment_start',
        'enrollment_end',
        'max_students',
        'prerequisites',
        'learning_objectives',
        'course_outline',
        'materials_needed',
        'assessment_criteria',
        'certification_available',
        'certificate_template',
        'course_fee',
        'currency',
        'thumbnail_image',
        'banner_image',
        'video_trailer',
        'language',
        'tags',
        'status',
        'is_featured',
        'created_by'
    ];

    protected $casts = [
        'co_instructors' => 'json',
        'prerequisites' => 'json',
        'learning_objectives' => 'json',
        'course_outline' => 'json',
        'materials_needed' => 'json',
        'assessment_criteria' => 'json',
        'tags' => 'json'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new course
     */
    public function createCourse(array $data): array
    {
        try {
            // Generate UUID
            $data['uuid'] = $this->generateUUID();
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Ensure JSON fields are properly encoded
            $jsonFields = ['co_instructors', 'prerequisites', 'learning_objectives', 
                          'course_outline', 'materials_needed', 'assessment_criteria', 'tags'];
            
            foreach ($jsonFields as $field) {
                if (isset($data[$field]) && is_array($data[$field])) {
                    $data[$field] = json_encode($data[$field]);
                }
            }

            $courseId = $this->create($data);
            
            if ($courseId) {
                // Log course creation
                $this->logCourseActivity($courseId, 'created', 'Course created', $data['created_by']);
                
                return [
                    'success' => true,
                    'course_id' => $courseId,
                    'message' => 'Course created successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create course'];
            
        } catch (\Exception $e) {
            error_log("Course creation error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Course creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get courses by hierarchical scope
     */
    public function getCoursesByScope(string $scope, int $scopeId = null, array $filters = []): array
    {
        try {
            $query = "SELECT c.*, 
                             u.first_name as instructor_first_name, 
                             u.last_name as instructor_last_name,
                             instructor.first_name as instructor_name,
                             COUNT(DISTINCT ce.id) as enrolled_students,
                             COUNT(DISTINCT cl.id) as total_lessons,
                             AVG(cr.rating) as average_rating
                      FROM {$this->table} c
                      LEFT JOIN users u ON c.created_by = u.id
                      LEFT JOIN users instructor ON c.instructor_id = instructor.id
                      LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status = 'active'
                      LEFT JOIN course_lessons cl ON c.id = cl.course_id
                      LEFT JOIN course_reviews cr ON c.id = cr.course_id
                      WHERE c.level_scope = :scope";
            
            $params = ['scope' => $scope];
            
            if ($scopeId) {
                $query .= " AND c.scope_id = :scope_id";
                $params['scope_id'] = $scopeId;
            }
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND c.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['course_type'])) {
                $query .= " AND c.course_type = :course_type";
                $params['course_type'] = $filters['course_type'];
            }
            
            if (!empty($filters['difficulty_level'])) {
                $query .= " AND c.difficulty_level = :difficulty_level";
                $params['difficulty_level'] = $filters['difficulty_level'];
            }
            
            if (!empty($filters['instructor_id'])) {
                $query .= " AND c.instructor_id = :instructor_id";
                $params['instructor_id'] = $filters['instructor_id'];
            }
            
            if (!empty($filters['is_featured'])) {
                $query .= " AND c.is_featured = :is_featured";
                $params['is_featured'] = $filters['is_featured'];
            }

            $query .= " GROUP BY c.id ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($courses as &$course) {
                $this->decodeCourseJsonFields($course);
            }
            
            return $courses;
            
        } catch (\Exception $e) {
            error_log("Get courses by scope error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Enroll user in course
     */
    public function enrollUser(int $courseId, int $userId, array $enrollmentData = []): array
    {
        try {
            // Check if course exists and enrollment is open
            $course = $this->find($courseId);
            if (!$course) {
                return ['success' => false, 'message' => 'Course not found'];
            }
            
            if (!$this->canEnrollInCourse($course)) {
                return ['success' => false, 'message' => 'Enrollment is not available for this course'];
            }
            
            // Check if user is already enrolled
            $existingQuery = "SELECT id FROM course_enrollments 
                             WHERE course_id = :course_id AND user_id = :user_id";
            $stmt = $this->db->prepare($existingQuery);
            $stmt->execute(['course_id' => $courseId, 'user_id' => $userId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'User is already enrolled in this course'];
            }
            
            // Check capacity
            if ($course['max_students'] > 0) {
                $currentCount = $this->getActiveEnrollmentsCount($courseId);
                if ($currentCount >= $course['max_students']) {
                    return ['success' => false, 'message' => 'Course is full'];
                }
            }
            
            // Determine initial status based on enrollment type
            $status = $this->getInitialEnrollmentStatus($course);
            
            // Enroll user
            $insertQuery = "INSERT INTO course_enrollments 
                           (course_id, user_id, status, enrollment_data, enrolled_at) 
                           VALUES (:course_id, :user_id, :status, :enrollment_data, :enrolled_at)";
            
            $stmt = $this->db->prepare($insertQuery);
            $result = $stmt->execute([
                'course_id' => $courseId,
                'user_id' => $userId,
                'status' => $status,
                'enrollment_data' => json_encode($enrollmentData),
                'enrolled_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                // Log enrollment
                $this->logCourseActivity($courseId, 'user_enrolled', 'User enrolled in course', $userId);
                
                return [
                    'success' => true,
                    'status' => $status,
                    'message' => 'Enrollment successful'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to enroll in course'];
            
        } catch (\Exception $e) {
            error_log("Enroll user in course error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Enrollment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get course lessons
     */
    public function getCourseLessons(int $courseId, bool $includeContent = false): array
    {
        try {
            $fields = "cl.*, u.first_name as created_by_name, u.last_name as created_by_surname";
            if (!$includeContent) {
                $fields = str_replace('cl.*', 'cl.id, cl.course_id, cl.title, cl.description, cl.lesson_order, cl.duration_minutes, cl.is_published, cl.created_at', $fields);
            }
            
            $query = "SELECT {$fields}
                      FROM course_lessons cl
                      LEFT JOIN users u ON cl.created_by = u.id
                      WHERE cl.course_id = :course_id
                      ORDER BY cl.lesson_order ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['course_id' => $courseId]);
            
            $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON content if included
            if ($includeContent) {
                foreach ($lessons as &$lesson) {
                    $lesson['content'] = json_decode($lesson['content'] ?? '{}', true);
                    $lesson['resources'] = json_decode($lesson['resources'] ?? '[]', true);
                    $lesson['quiz_questions'] = json_decode($lesson['quiz_questions'] ?? '[]', true);
                }
            }
            
            return $lessons;
            
        } catch (\Exception $e) {
            error_log("Get course lessons error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get student progress in course
     */
    public function getStudentProgress(int $courseId, int $userId): array
    {
        try {
            // Get enrollment info
            $enrollmentQuery = "SELECT * FROM course_enrollments 
                               WHERE course_id = :course_id AND user_id = :user_id";
            $stmt = $this->db->prepare($enrollmentQuery);
            $stmt->execute(['course_id' => $courseId, 'user_id' => $userId]);
            $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$enrollment) {
                return ['enrolled' => false];
            }
            
            // Get lesson progress
            $progressQuery = "SELECT 
                                cl.id as lesson_id,
                                cl.title as lesson_title,
                                cl.lesson_order,
                                lp.status,
                                lp.progress_percentage,
                                lp.completed_at,
                                lp.time_spent_minutes
                              FROM course_lessons cl
                              LEFT JOIN lesson_progress lp ON cl.id = lp.lesson_id AND lp.user_id = :user_id
                              WHERE cl.course_id = :course_id
                              ORDER BY cl.lesson_order ASC";
            
            $stmt = $this->db->prepare($progressQuery);
            $stmt->execute(['course_id' => $courseId, 'user_id' => $userId]);
            $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate overall progress
            $totalLessons = count($lessons);
            $completedLessons = 0;
            $totalTimeSpent = 0;
            
            foreach ($lessons as $lesson) {
                if ($lesson['status'] === 'completed') {
                    $completedLessons++;
                }
                $totalTimeSpent += (int) ($lesson['time_spent_minutes'] ?? 0);
            }
            
            $overallProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;
            
            return [
                'enrolled' => true,
                'enrollment' => $enrollment,
                'lessons' => $lessons,
                'statistics' => [
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'overall_progress' => $overallProgress,
                    'total_time_spent' => $totalTimeSpent
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Get student progress error: " . $e->getMessage());
            return ['enrolled' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update lesson progress
     */
    public function updateLessonProgress(int $lessonId, int $userId, array $progressData): array
    {
        try {
            // Check if progress record exists
            $existingQuery = "SELECT id FROM lesson_progress 
                             WHERE lesson_id = :lesson_id AND user_id = :user_id";
            $stmt = $this->db->prepare($existingQuery);
            $stmt->execute(['lesson_id' => $lessonId, 'user_id' => $userId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $data = [
                'status' => $progressData['status'] ?? 'in_progress',
                'progress_percentage' => max(0, min(100, (int) ($progressData['progress_percentage'] ?? 0))),
                'time_spent_minutes' => (int) ($progressData['time_spent_minutes'] ?? 0),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($data['status'] === 'completed') {
                $data['completed_at'] = date('Y-m-d H:i:s');
                $data['progress_percentage'] = 100;
            }
            
            if ($existing) {
                // Update existing record
                $query = "UPDATE lesson_progress SET 
                         status = :status, 
                         progress_percentage = :progress_percentage,
                         time_spent_minutes = time_spent_minutes + :time_spent_minutes,
                         updated_at = :updated_at";
                
                if ($data['status'] === 'completed') {
                    $query .= ", completed_at = :completed_at";
                }
                
                $query .= " WHERE lesson_id = :lesson_id AND user_id = :user_id";
                
                $data['lesson_id'] = $lessonId;
                $data['user_id'] = $userId;
            } else {
                // Create new record
                $query = "INSERT INTO lesson_progress 
                         (lesson_id, user_id, status, progress_percentage, time_spent_minutes, updated_at" .
                         ($data['status'] === 'completed' ? ', completed_at' : '') . ") 
                         VALUES (:lesson_id, :user_id, :status, :progress_percentage, :time_spent_minutes, :updated_at" .
                         ($data['status'] === 'completed' ? ', :completed_at' : '') . ")";
                
                $data['lesson_id'] = $lessonId;
                $data['user_id'] = $userId;
            }
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($data);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Progress updated successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to update progress'];
            
        } catch (\Exception $e) {
            error_log("Update lesson progress error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Progress update failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get course statistics by scope
     */
    public function getCourseStatistics(string $scope, int $scopeId = null): array
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total_courses,
                        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_courses,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active_courses,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_courses,
                        SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_courses,
                        AVG(duration_hours) as avg_duration
                      FROM {$this->table}
                      WHERE level_scope = :scope";
            
            $params = ['scope' => $scope];
            
            if ($scopeId) {
                $query .= " AND scope_id = :scope_id";
                $params['scope_id'] = $scopeId;
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get enrollment statistics
            $enrollmentQuery = "SELECT 
                                  COUNT(DISTINCT ce.user_id) as unique_students,
                                  COUNT(ce.id) as total_enrollments,
                                  SUM(CASE WHEN ce.status = 'active' THEN 1 ELSE 0 END) as active_enrollments,
                                  SUM(CASE WHEN ce.status = 'completed' THEN 1 ELSE 0 END) as completed_enrollments
                                FROM course_enrollments ce
                                JOIN courses c ON ce.course_id = c.id
                                WHERE c.level_scope = :scope";
            
            if ($scopeId) {
                $enrollmentQuery .= " AND c.scope_id = :scope_id";
            }
            
            $stmt = $this->db->prepare($enrollmentQuery);
            $stmt->execute($params);
            
            $enrollmentStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Merge statistics
            $stats = array_merge($stats, $enrollmentStats);
            
            // Calculate completion rate
            $stats['completion_rate'] = $stats['total_enrollments'] > 0 
                ? round(($stats['completed_enrollments'] / $stats['total_enrollments']) * 100, 2) 
                : 0;
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Get course statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search courses
     */
    public function searchCourses(array $criteria): array
    {
        try {
            $query = "SELECT c.*, 
                             instructor.first_name as instructor_first_name,
                             instructor.last_name as instructor_last_name,
                             COUNT(DISTINCT ce.id) as enrolled_students,
                             AVG(cr.rating) as average_rating
                      FROM {$this->table} c
                      LEFT JOIN users instructor ON c.instructor_id = instructor.id
                      LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status = 'active'
                      LEFT JOIN course_reviews cr ON c.id = cr.course_id
                      WHERE c.status = 'published'";
            
            $params = [];
            
            if (!empty($criteria['title'])) {
                $query .= " AND c.title LIKE :title";
                $params['title'] = '%' . $criteria['title'] . '%';
            }
            
            if (!empty($criteria['course_type'])) {
                $query .= " AND c.course_type = :course_type";
                $params['course_type'] = $criteria['course_type'];
            }
            
            if (!empty($criteria['difficulty_level'])) {
                $query .= " AND c.difficulty_level = :difficulty_level";
                $params['difficulty_level'] = $criteria['difficulty_level'];
            }
            
            if (!empty($criteria['language'])) {
                $query .= " AND c.language = :language";
                $params['language'] = $criteria['language'];
            }
            
            if (!empty($criteria['instructor_id'])) {
                $query .= " AND c.instructor_id = :instructor_id";
                $params['instructor_id'] = $criteria['instructor_id'];
            }
            
            if (!empty($criteria['tags'])) {
                $query .= " AND JSON_CONTAINS(c.tags, :tags)";
                $params['tags'] = json_encode($criteria['tags']);
            }
            
            if (!empty($criteria['is_free'])) {
                $query .= " AND (c.course_fee = 0 OR c.course_fee IS NULL)";
            }
            
            $query .= " GROUP BY c.id ORDER BY average_rating DESC, enrolled_students DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON fields
            foreach ($courses as &$course) {
                $this->decodeCourseJsonFields($course);
            }
            
            return $courses;
            
        } catch (\Exception $e) {
            error_log("Search courses error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user can enroll in course
     */
    private function canEnrollInCourse(array $course): bool
    {
        $now = date('Y-m-d H:i:s');
        
        // Check if enrollment is open
        if ($course['enrollment_type'] === self::ENROLLMENT_CLOSED) {
            return false;
        }
        
        // Check enrollment period
        if ($course['enrollment_start'] && $now < $course['enrollment_start']) {
            return false;
        }
        
        if ($course['enrollment_end'] && $now > $course['enrollment_end']) {
            return false;
        }
        
        // Check course status
        if ($course['status'] !== self::STATUS_PUBLISHED) {
            return false;
        }
        
        return true;
    }

    /**
     * Get initial enrollment status based on course settings
     */
    private function getInitialEnrollmentStatus(array $course): string
    {
        switch ($course['enrollment_type']) {
            case self::ENROLLMENT_APPROVAL_REQUIRED:
                return 'pending'; // Requires approval
                
            case self::ENROLLMENT_INVITATION_ONLY:
                return 'pending'; // Requires invitation
                
            case self::ENROLLMENT_OPEN:
            default:
                return 'active'; // Auto-active
        }
    }

    /**
     * Get active enrollments count
     */
    private function getActiveEnrollmentsCount(int $courseId): int
    {
        try {
            $query = "SELECT COUNT(*) FROM course_enrollments 
                     WHERE course_id = :course_id AND status = 'active'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['course_id' => $courseId]);
            
            return (int) $stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Get active enrollments count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Decode JSON fields in course array
     */
    private function decodeCourseJsonFields(array &$course): void
    {
        $jsonFields = ['co_instructors', 'prerequisites', 'learning_objectives', 
                      'course_outline', 'materials_needed', 'assessment_criteria', 'tags'];
        
        foreach ($jsonFields as $field) {
            $course[$field] = json_decode($course[$field] ?? '[]', true);
        }
    }

    /**
     * Log course activity
     */
    private function logCourseActivity(int $courseId, string $action, string $description, int $userId): void
    {
        try {
            $query = "INSERT INTO course_activities (course_id, user_id, action, description, created_at) 
                     VALUES (:course_id, :user_id, :action, :description, :created_at)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'course_id' => $courseId,
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            error_log("Log course activity error: " . $e->getMessage());
        }
    }

    /**
     * Generate UUID for course
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}