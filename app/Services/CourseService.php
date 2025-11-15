<?php

namespace App\Services;

use App\Models\Course;
use Exception;

class CourseService
{
    private $courseModel;
    private $notificationService;

    public function __construct()
    {
        $this->courseModel = new Course();
        $this->notificationService = new NotificationService();
    }

    /**
     * Create course with comprehensive setup
     */
    public function createCourseWithSetup(array $data, int $createdBy): ?int
    {
        try {
            // Validate course data
            $this->validateCourseData($data);
            
            // Set creator and instructor
            $data['created_by'] = $createdBy;
            if (empty($data['instructor_id'])) {
                $data['instructor_id'] = $createdBy;
            }
            
            // Generate UUID
            $data['uuid'] = $this->generateUuid('course_');
            
            // Create course
            $courseId = $this->courseModel->createCourse($data);
            
            if ($courseId) {
                // Create initial lessons if provided
                if (!empty($data['lessons'])) {
                    $this->createCourseLessons($courseId, $data['lessons'], $createdBy);
                }
                
                // Send announcement if course is published
                if ($data['status'] === 'published') {
                    $this->sendCourseAnnouncement($courseId);
                }
                
                // Log activity
                $this->logCourseActivity($courseId, $createdBy, 'created', 'Course created');
                
                return $courseId;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("CourseService::createCourseWithSetup failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enroll user in course with validation
     */
    public function enrollUserInCourse(int $courseId, int $userId, array $enrollmentData = []): bool
    {
        try {
            $course = $this->courseModel->getCourseById($courseId);
            
            if (!$course) {
                throw new Exception('Course not found');
            }
            
            // Check if course is published
            if ($course['status'] !== 'published') {
                throw new Exception('Course is not available for enrollment');
            }
            
            // Check if user is already enrolled
            $existingEnrollment = $this->courseModel->getUserEnrollment($courseId, $userId);
            if ($existingEnrollment) {
                throw new Exception('User is already enrolled in this course');
            }
            
            // Check capacity
            if (!empty($course['max_students'])) {
                $currentCount = $this->courseModel->getEnrollmentCount($courseId);
                if ($currentCount >= $course['max_students']) {
                    throw new Exception('Course has reached maximum capacity');
                }
            }
            
            // Check enrollment dates
            $now = time();
            if (!empty($course['enrollment_start']) && strtotime($course['enrollment_start']) > $now) {
                throw new Exception('Enrollment has not started yet');
            }
            if (!empty($course['enrollment_end']) && strtotime($course['enrollment_end']) < $now) {
                throw new Exception('Enrollment period has ended');
            }
            
            // Check prerequisites
            if (!empty($course['prerequisites'])) {
                $prerequisites = json_decode($course['prerequisites'], true);
                if (!$this->checkPrerequisites($userId, $prerequisites)) {
                    throw new Exception('Prerequisites not met for this course');
                }
            }
            
            // Process payment if required
            if ($course['course_fee'] > 0) {
                $paymentResult = $this->processCoursePayment($courseId, $userId, $course['course_fee']);
                if (!$paymentResult['success']) {
                    throw new Exception('Payment processing failed: ' . $paymentResult['error']);
                }
                $enrollmentData['payment_id'] = $paymentResult['payment_id'];
            }
            
            // Enroll user
            $status = $course['enrollment_type'] === 'approval_required' ? 'pending' : 'active';
            $success = $this->courseModel->enrollUser($courseId, $userId, $status, $enrollmentData);
            
            if ($success) {
                // Send confirmation notification
                $this->notificationService->sendCourseEnrollmentConfirmationNotification($courseId, $userId);
                
                // Initialize lesson progress
                $this->initializeLessonProgress($courseId, $userId);
                
                // Log activity
                $this->logCourseActivity($courseId, $userId, 'enrolled', 'User enrolled in course');
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("CourseService::enrollUserInCourse failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update lesson progress
     */
    public function updateLessonProgress(int $lessonId, int $userId, string $status, int $progressPercentage, int $timeSpent = 0): bool
    {
        try {
            $lesson = $this->courseModel->getLessonById($lessonId);
            
            if (!$lesson) {
                throw new Exception('Lesson not found');
            }
            
            // Check if user is enrolled in the course
            $enrollment = $this->courseModel->getUserEnrollment($lesson['course_id'], $userId);
            if (!$enrollment || $enrollment['status'] !== 'active') {
                throw new Exception('User is not enrolled in this course');
            }
            
            // Update lesson progress
            $success = $this->courseModel->updateLessonProgress($lessonId, $userId, $status, $progressPercentage, $timeSpent);
            
            if ($success) {
                // Check if course is completed
                $courseProgress = $this->calculateCourseProgress($lesson['course_id'], $userId);
                
                if ($courseProgress['completion_percentage'] >= 100) {
                    $this->completeCourse($lesson['course_id'], $userId);
                }
                
                // Log activity
                $this->logCourseActivity($lesson['course_id'], $userId, 'lesson_progress', 
                    "Updated progress for lesson: {$lesson['title']} ({$progressPercentage}%)");
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("CourseService::updateLessonProgress failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Complete course and issue certificate
     */
    public function completeCourse(int $courseId, int $userId): bool
    {
        try {
            // Update enrollment status to completed
            $success = $this->courseModel->updateEnrollmentStatus($courseId, $userId, 'completed');
            
            if ($success) {
                $course = $this->courseModel->getCourseById($courseId);
                
                // Issue certificate if available
                if ($course['certification_available']) {
                    $certificateId = $this->issueCertificate($courseId, $userId);
                    
                    // Send certificate notification
                    $this->notificationService->sendCourseCertificateNotification($courseId, $userId, $certificateId);
                }
                
                // Send completion notification
                $this->notificationService->sendCourseCompletionNotification($courseId, $userId);
                
                // Log activity
                $this->logCourseActivity($courseId, $userId, 'completed', 'Course completed successfully');
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("CourseService::completeCourse failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate course analytics
     */
    public function generateCourseAnalytics(int $courseId): array
    {
        try {
            $course = $this->courseModel->getCourseById($courseId);
            $statistics = $this->courseModel->getCourseStatistics($courseId);
            
            return [
                'course' => $course,
                'total_enrolled' => $statistics['total_enrolled'],
                'active_students' => $statistics['active_students'],
                'completed_students' => $statistics['completed_students'],
                'dropped_students' => $statistics['dropped_students'],
                'completion_rate' => $statistics['total_enrolled'] > 0 ? 
                    round(($statistics['completed_students'] / $statistics['total_enrolled']) * 100, 2) : 0,
                'dropout_rate' => $statistics['total_enrolled'] > 0 ? 
                    round(($statistics['dropped_students'] / $statistics['total_enrolled']) * 100, 2) : 0,
                'average_progress' => $this->getAverageCourseProgress($courseId),
                'average_completion_time' => $this->getAverageCompletionTime($courseId),
                'lesson_engagement' => $this->getLessonEngagementStats($courseId),
                'student_feedback' => $this->getStudentFeedbackSummary($courseId),
                'revenue' => $this->calculateCourseRevenue($courseId)
            ];
            
        } catch (Exception $e) {
            error_log("CourseService::generateCourseAnalytics failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create course lessons
     */
    private function createCourseLessons(int $courseId, array $lessons, int $createdBy): void
    {
        foreach ($lessons as $index => $lesson) {
            $lessonData = array_merge($lesson, [
                'course_id' => $courseId,
                'lesson_order' => $index + 1,
                'created_by' => $createdBy
            ]);
            
            $this->courseModel->createLesson($lessonData);
        }
    }

    /**
     * Initialize lesson progress for enrolled user
     */
    private function initializeLessonProgress(int $courseId, int $userId): void
    {
        $lessons = $this->courseModel->getCourseLessons($courseId);
        
        foreach ($lessons as $lesson) {
            if ($lesson['is_published']) {
                $this->courseModel->initializeLessonProgress($lesson['id'], $userId);
            }
        }
    }

    /**
     * Calculate course progress
     */
    private function calculateCourseProgress(int $courseId, int $userId): array
    {
        $lessons = $this->courseModel->getCourseLessons($courseId);
        $totalLessons = count($lessons);
        $completedLessons = 0;
        $totalProgress = 0;
        
        foreach ($lessons as $lesson) {
            $progress = $this->courseModel->getLessonProgress($lesson['id'], $userId);
            if ($progress) {
                $totalProgress += $progress['progress_percentage'];
                if ($progress['status'] === 'completed') {
                    $completedLessons++;
                }
            }
        }
        
        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'completion_percentage' => $totalLessons > 0 ? round($totalProgress / $totalLessons, 2) : 0,
            'average_progress' => $totalLessons > 0 ? round($totalProgress / $totalLessons, 2) : 0
        ];
    }

    /**
     * Issue certificate
     */
    private function issueCertificate(int $courseId, int $userId): string
    {
        $certificateData = [
            'course_id' => $courseId,
            'user_id' => $userId,
            'certificate_id' => 'cert_' . uniqid(),
            'issued_date' => date('Y-m-d H:i:s'),
            'status' => 'issued'
        ];
        
        // This would integrate with certificate generation system
        return $certificateData['certificate_id'];
    }

    /**
     * Check prerequisites
     */
    private function checkPrerequisites(int $userId, array $prerequisites): bool
    {
        foreach ($prerequisites as $prerequisite) {
            if ($prerequisite['type'] === 'course') {
                $enrollment = $this->courseModel->getUserEnrollment($prerequisite['course_id'], $userId);
                if (!$enrollment || $enrollment['status'] !== 'completed') {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Process course payment
     */
    private function processCoursePayment(int $courseId, int $userId, float $amount): array
    {
        // This would integrate with payment gateways
        return [
            'success' => true,
            'payment_id' => 'pay_' . uniqid(),
            'amount' => $amount,
            'currency' => 'USD'
        ];
    }

    /**
     * Send course announcement
     */
    private function sendCourseAnnouncement(int $courseId): void
    {
        $this->notificationService->sendCourseAnnouncementNotification($courseId);
    }

    /**
     * Get average course progress
     */
    private function getAverageCourseProgress(int $courseId): float
    {
        $enrollments = $this->courseModel->getCourseEnrollments($courseId);
        $totalProgress = 0;
        $count = 0;
        
        foreach ($enrollments as $enrollment) {
            if ($enrollment['status'] === 'active' || $enrollment['status'] === 'completed') {
                $progress = $this->calculateCourseProgress($courseId, $enrollment['user_id']);
                $totalProgress += $progress['completion_percentage'];
                $count++;
            }
        }
        
        return $count > 0 ? round($totalProgress / $count, 2) : 0;
    }

    /**
     * Get average completion time
     */
    private function getAverageCompletionTime(int $courseId): ?float
    {
        return $this->courseModel->getAverageCompletionTime($courseId);
    }

    /**
     * Get lesson engagement statistics
     */
    private function getLessonEngagementStats(int $courseId): array
    {
        return $this->courseModel->getLessonEngagementStats($courseId);
    }

    /**
     * Get student feedback summary
     */
    private function getStudentFeedbackSummary(int $courseId): array
    {
        return $this->courseModel->getStudentFeedbackSummary($courseId);
    }

    /**
     * Calculate course revenue
     */
    private function calculateCourseRevenue(int $courseId): float
    {
        $enrollments = $this->courseModel->getCourseEnrollments($courseId);
        $revenue = 0;
        
        foreach ($enrollments as $enrollment) {
            if (!empty($enrollment['payment_amount'])) {
                $revenue += $enrollment['payment_amount'];
            }
        }
        
        return $revenue;
    }

    /**
     * Validate course data
     */
    private function validateCourseData(array $data): void
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Course title is required';
        }
        
        if (empty($data['description'])) {
            $errors[] = 'Course description is required';
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
    }

    /**
     * Log course activity
     */
    private function logCourseActivity(int $courseId, int $userId, string $action, string $description): void
    {
        $this->courseModel->logCourseActivity($courseId, $userId, $action, $description);
    }

    /**
     * Generate UUID
     */
    private function generateUuid(string $prefix = ''): string
    {
        return $prefix . uniqid() . '_' . bin2hex(random_bytes(8));
    }
}