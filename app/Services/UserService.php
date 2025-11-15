<?php

namespace App\Services;

use App\Models\User;
use Exception;

class UserService
{
    private $userModel;
    private $notificationService;
    private $fileService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->notificationService = new NotificationService();
        $this->fileService = new FileService();
    }

    /**
     * Create new user with comprehensive profile setup
     */
    public function createUser(array $userData, ?int $createdBy = null): array
    {
        try {
            // Validate user data
            $this->validateUserData($userData);
            
            // Check for duplicates
            if ($this->userModel->findByEmail($userData['email'])) {
                throw new Exception('Email address already exists');
            }
            
            if (!empty($userData['phone']) && $this->userModel->findByPhone($userData['phone'])) {
                throw new Exception('Phone number already exists');
            }
            
            // Process profile picture if provided
            if (!empty($userData['profile_picture_file'])) {
                $uploadResult = $this->fileService->uploadProfilePicture($userData['profile_picture_file']);
                if ($uploadResult['success']) {
                    $userData['profile_picture'] = $uploadResult['file_path'];
                }
                unset($userData['profile_picture_file']);
            }
            
            // Hash password if provided
            if (!empty($userData['password'])) {
                $userData['password_hash'] = password_hash($userData['password'], PASSWORD_ARGON2ID);
                unset($userData['password']);
            }
            
            // Generate UUID
            $userData['uuid'] = $this->generateUuid('user_');
            $userData['created_by'] = $createdBy;
            $userData['created_at'] = date('Y-m-d H:i:s');
            
            // Create user
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                // Send welcome notification
                $this->notificationService->sendWelcomeNotification($userId);
                
                return [
                    'success' => true,
                    'message' => 'User created successfully',
                    'user_id' => $userId
                ];
            }
            
            throw new Exception('Failed to create user');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user profile
     */
    public function updateUser(int $userId, array $updateData, ?int $updatedBy = null): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Validate update data
            $this->validateUpdateData($updateData, $userId);
            
            // Process profile picture if provided
            if (!empty($updateData['profile_picture_file'])) {
                $uploadResult = $this->fileService->uploadProfilePicture($updateData['profile_picture_file']);
                if ($uploadResult['success']) {
                    // Delete old profile picture
                    if (!empty($user['profile_picture'])) {
                        $this->fileService->deleteFile($user['profile_picture']);
                    }
                    $updateData['profile_picture'] = $uploadResult['file_path'];
                }
                unset($updateData['profile_picture_file']);
            }
            
            // Handle password change
            if (!empty($updateData['password'])) {
                $updateData['password_hash'] = password_hash($updateData['password'], PASSWORD_ARGON2ID);
                unset($updateData['password']);
            }
            
            $updateData['updated_by'] = $updatedBy;
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            // Update user
            $success = $this->userModel->updateUser($userId, $updateData);
            
            if ($success) {
                // Send profile update notification
                $this->notificationService->sendProfileUpdateNotification($userId);
                
                return [
                    'success' => true,
                    'message' => 'User profile updated successfully'
                ];
            }
            
            throw new Exception('Failed to update user profile');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user profile with hierarchy information
     */
    public function getUserProfile(int $userId, bool $includeHierarchy = true): ?array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                return null;
            }
            
            // Remove sensitive data
            unset($user['password_hash'], $user['email_verification_token']);
            
            if ($includeHierarchy) {
                $user['hierarchy'] = $this->getUserHierarchy($userId);
                $user['roles'] = $this->getUserRoles($userId);
                $user['statistics'] = $this->getUserStatistics($userId);
            }
            
            return $user;
            
        } catch (Exception $e) {
            error_log("UserService::getUserProfile failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search users with filters
     */
    public function searchUsers(array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $users = $this->userModel->searchUsers($filters, $limit, $offset);
            $totalCount = $this->userModel->countUsers($filters);
            
            // Remove sensitive data from all users
            foreach ($users as &$user) {
                unset($user['password_hash'], $user['email_verification_token']);
            }
            
            return [
                'success' => true,
                'data' => [
                    'users' => $users,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $totalCount,
                        'total_pages' => ceil($totalCount / $limit)
                    ]
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get users by hierarchy level
     */
    public function getUsersByHierarchy(string $hierarchyType, int $hierarchyId): array
    {
        try {
            $users = $this->userModel->getUsersByHierarchy($hierarchyType, $hierarchyId);
            
            // Remove sensitive data
            foreach ($users as &$user) {
                unset($user['password_hash'], $user['email_verification_token']);
            }
            
            return [
                'success' => true,
                'users' => $users
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, string $role, ?int $assignedBy = null): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Validate role
            $validRoles = ['admin', 'leader', 'member'];
            if (!in_array($role, $validRoles)) {
                throw new Exception('Invalid role specified');
            }
            
            // Update user role
            $success = $this->userModel->updateUser($userId, [
                'role' => $role,
                'updated_by' => $assignedBy,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                // Send role assignment notification
                $this->notificationService->sendRoleAssignmentNotification($userId, $role);
                
                return [
                    'success' => true,
                    'message' => 'Role assigned successfully'
                ];
            }
            
            throw new Exception('Failed to assign role');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user status
     */
    public function updateUserStatus(int $userId, string $status, ?int $updatedBy = null): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Validate status
            $validStatuses = ['active', 'inactive', 'suspended', 'pending'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid status specified');
            }
            
            // Update status
            $success = $this->userModel->updateUser($userId, [
                'status' => $status,
                'updated_by' => $updatedBy,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                // Send status change notification
                $this->notificationService->sendStatusChangeNotification($userId, $status);
                
                return [
                    'success' => true,
                    'message' => 'User status updated successfully'
                ];
            }
            
            throw new Exception('Failed to update user status');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transfer user to different hierarchy
     */
    public function transferUser(int $userId, int $newGurmuId, ?int $transferredBy = null): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Validate new gurmu exists
            if (!$this->validateGurmuExists($newGurmuId)) {
                throw new Exception('Invalid Gurmu specified');
            }
            
            // Store old hierarchy for notification
            $oldGurmuId = $user['gurmu_id'];
            
            // Update user hierarchy
            $success = $this->userModel->updateUser($userId, [
                'gurmu_id' => $newGurmuId,
                'updated_by' => $transferredBy,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                // Log transfer
                $this->logUserTransfer($userId, $oldGurmuId, $newGurmuId, $transferredBy);
                
                // Send transfer notification
                $this->notificationService->sendUserTransferNotification($userId, $oldGurmuId, $newGurmuId);
                
                return [
                    'success' => true,
                    'message' => 'User transferred successfully'
                ];
            }
            
            throw new Exception('Failed to transfer user');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(int $userId, string $reason, ?int $deactivatedBy = null): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            if ($user['status'] === 'inactive') {
                throw new Exception('User is already inactive');
            }
            
            // Update status to inactive
            $success = $this->userModel->updateUser($userId, [
                'status' => 'inactive',
                'deactivation_reason' => $reason,
                'deactivated_by' => $deactivatedBy,
                'deactivated_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                // Send deactivation notification
                $this->notificationService->sendAccountDeactivationNotification($userId, $reason);
                
                return [
                    'success' => true,
                    'message' => 'User account deactivated successfully'
                ];
            }
            
            throw new Exception('Failed to deactivate user account');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Reactivate user account
     */
    public function reactivateUser(int $userId, ?int $reactivatedBy = null): array
    {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            if ($user['status'] === 'active') {
                throw new Exception('User is already active');
            }
            
            // Update status to active
            $success = $this->userModel->updateUser($userId, [
                'status' => 'active',
                'reactivated_by' => $reactivatedBy,
                'reactivated_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                // Send reactivation notification
                $this->notificationService->sendAccountReactivationNotification($userId);
                
                return [
                    'success' => true,
                    'message' => 'User account reactivated successfully'
                ];
            }
            
            throw new Exception('Failed to reactivate user account');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user activity statistics
     */
    public function getUserStatistics(int $userId): array
    {
        try {
            $stats = [
                'tasks' => $this->userModel->getUserTaskStats($userId),
                'meetings' => $this->userModel->getUserMeetingStats($userId),
                'events' => $this->userModel->getUserEventStats($userId),
                'courses' => $this->userModel->getUserCourseStats($userId),
                'donations' => $this->userModel->getUserDonationStats($userId),
                'login_activity' => $this->userModel->getUserLoginStats($userId)
            ];
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("UserService::getUserStatistics failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user hierarchy information
     */
    private function getUserHierarchy(int $userId): array
    {
        return $this->userModel->getUserHierarchy($userId);
    }

    /**
     * Get user roles
     */
    private function getUserRoles(int $userId): array
    {
        return $this->userModel->getUserRoles($userId);
    }

    /**
     * Validate user data
     */
    private function validateUserData(array $data): void
    {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }
        
        if (!empty($data['phone']) && !$this->isValidPhone($data['phone'])) {
            $errors[] = 'Valid phone number is required';
        }
        
        if (empty($data['gurmu_id'])) {
            $errors[] = 'Gurmu selection is required';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
    }

    /**
     * Validate update data
     */
    private function validateUpdateData(array $data, int $userId): void
    {
        $errors = [];
        
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email address is required';
            }
            
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors[] = 'Email address already exists';
            }
        }
        
        if (!empty($data['phone'])) {
            if (!$this->isValidPhone($data['phone'])) {
                $errors[] = 'Valid phone number is required';
            }
            
            $existingUser = $this->userModel->findByPhone($data['phone']);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors[] = 'Phone number already exists';
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
    }

    /**
     * Validate phone number
     */
    private function isValidPhone(string $phone): bool
    {
        return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone);
    }

    /**
     * Validate gurmu exists
     */
    private function validateGurmuExists(int $gurmuId): bool
    {
        // This would query the hierarchy table
        return true; // Simplified for now
    }

    /**
     * Log user transfer
     */
    private function logUserTransfer(int $userId, int $oldGurmuId, int $newGurmuId, ?int $transferredBy): void
    {
        // This would log to user_transfer_log table
        error_log("User {$userId} transferred from Gurmu {$oldGurmuId} to {$newGurmuId} by user {$transferredBy}");
    }

    /**
     * Generate UUID
     */
    private function generateUuid(string $prefix = ''): string
    {
        return $prefix . uniqid() . '_' . bin2hex(random_bytes(8));
    }
}