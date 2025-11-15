<?php
namespace App\Services;

use App\Utils\Database;
use Exception;

/**
 * Internal Email Generator Service
 * 
 * Generates and manages internal organizational email addresses
 * Format: {position}.{hierarchy}.{firstname}.{lastname}@abo-wbo.org
 * 
 * Features:
 * - Hierarchical email generation
 * - Collision detection and resolution
 * - cPanel email account creation (future)
 * - Email forwarding setup
 * - Quota management
 */
class InternalEmailGenerator
{
    protected $db;
    protected $domain;
    protected $maxEmailLength = 64; // Standard email local part limit
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->domain = $this->getConfigValue('internal_email_domain', 'abo-wbo.org');
    }
    
    /**
     * Generate internal email address for user
     */
    public function generateInternalEmail(array $userData, array $positionData = null, array $hierarchyData = null): string
    {
        $attempts = 0;
        $maxAttempts = 10;
        
        while ($attempts < $maxAttempts) {
            $email = $this->buildEmailAddress($userData, $positionData, $hierarchyData, $attempts);
            
            if ($this->isEmailUnique($email)) {
                return $email;
            }
            
            $attempts++;
        }
        
        throw new Exception("Unable to generate unique internal email after {$maxAttempts} attempts");
    }
    
    /**
     * Build email address based on hierarchy and position
     */
    protected function buildEmailAddress(array $userData, array $positionData = null, array $hierarchyData = null, int $attempt = 0): string
    {
        $parts = [];
        
        // Position prefix (if available)
        if ($positionData && !empty($positionData['key_name'])) {
            $parts[] = $this->sanitizeEmailPart($positionData['key_name']);
        } else {
            $parts[] = 'member'; // Default for members without specific positions
        }
        
        // Hierarchy identifier
        if ($hierarchyData) {
            $hierarchyPart = $this->getHierarchyIdentifier($hierarchyData);
            if ($hierarchyPart) {
                $parts[] = $hierarchyPart;
            }
        } else {
            $parts[] = 'general'; // Default hierarchy
        }
        
        // Name parts
        $firstName = $this->sanitizeEmailPart($userData['first_name']);
        $lastName = $this->sanitizeEmailPart($userData['last_name']);
        
        // Handle collision attempts
        if ($attempt > 0) {
            $lastName .= $attempt;
        }
        
        $parts[] = $firstName;
        $parts[] = $lastName;
        
        // Join parts and create email
        $localPart = implode('.', $parts);
        
        // Ensure length compliance
        if (strlen($localPart) > $this->maxEmailLength) {
            $localPart = $this->truncateEmailPart($localPart, $this->maxEmailLength);
        }
        
        return strtolower($localPart) . '@' . $this->domain;
    }
    
    /**
     * Get hierarchy identifier for email
     */
    protected function getHierarchyIdentifier(array $hierarchyData): string
    {
        $level = $hierarchyData['level'] ?? 'global';
        
        switch ($level) {
            case 'global':
                return 'global';
                
            case 'godina':
                return $this->sanitizeEmailPart($hierarchyData['godina_code'] ?? 'godina');
                
            case 'gamta':
                return $this->sanitizeEmailPart($hierarchyData['gamta_code'] ?? 'gamta');
                
            case 'gurmu':
                return $this->sanitizeEmailPart($hierarchyData['gurmu_code'] ?? 'gurmu');
                
            default:
                return 'general';
        }
    }
    
    /**
     * Sanitize email part for RFC compliance
     */
    protected function sanitizeEmailPart(string $part): string
    {
        // Convert to lowercase
        $part = strtolower($part);
        
        // Remove or replace invalid characters
        $part = preg_replace('/[^a-z0-9._-]/', '', $part);
        
        // Remove multiple consecutive dots/dashes
        $part = preg_replace('/[._-]{2,}/', '.', $part);
        
        // Remove leading/trailing dots/dashes
        $part = trim($part, '._-');
        
        // Ensure not empty
        if (empty($part)) {
            $part = 'user';
        }
        
        return $part;
    }
    
    /**
     * Truncate email part while preserving structure
     */
    protected function truncateEmailPart(string $localPart, int $maxLength): string
    {
        if (strlen($localPart) <= $maxLength) {
            return $localPart;
        }
        
        $parts = explode('.', $localPart);
        $truncated = [];
        $currentLength = 0;
        
        // Calculate dots needed
        $dotsNeeded = count($parts) - 1;
        $availableLength = $maxLength - $dotsNeeded;
        
        // Distribute length among parts
        $lengthPerPart = floor($availableLength / count($parts));
        $remainder = $availableLength % count($parts);
        
        foreach ($parts as $i => $part) {
            $partLength = $lengthPerPart;
            if ($i < $remainder) {
                $partLength++;
            }
            
            if (strlen($part) > $partLength) {
                $part = substr($part, 0, $partLength);
            }
            
            $truncated[] = $part;
        }
        
        return implode('.', $truncated);
    }
    
    /**
     * Check if email address is unique
     */
    public function isEmailUnique(string $email): bool
    {
        // Check in internal_emails table
        $exists = $this->db->fetch(
            "SELECT id FROM internal_emails WHERE internal_email = ?",
            [$email]
        );
        
        if ($exists) {
            return false;
        }
        
        // Check in users table
        $userExists = $this->db->fetch(
            "SELECT id FROM users WHERE internal_email = ?",
            [$email]
        );
        
        return !$userExists;
    }
    
    /**
     * Create internal email record
     */
    public function createInternalEmailRecord(int $userId, string $internalEmail, array $options = []): int
    {
        $defaultQuota = $this->getConfigValue('default_email_quota_mb', 1024);
        
        $data = [
            'user_id' => $userId,
            'internal_email' => $internalEmail,
            'email_type' => $options['email_type'] ?? 'primary',
            'email_quota_mb' => $options['quota_mb'] ?? $defaultQuota,
            'auto_forward_to' => $options['forward_to'] ?? null,
            'status' => 'pending_creation',
            'creation_metadata' => json_encode([
                'created_by' => $options['created_by'] ?? null,
                'creation_method' => $options['creation_method'] ?? 'hybrid_registration',
                'hierarchy_data' => $options['hierarchy_data'] ?? null,
                'position_data' => $options['position_data'] ?? null
            ]),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('internal_emails', $data);
    }
    
    /**
     * Generate temporary email password
     */
    public function generateEmailPassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*';
        
        $password = '';
        
        // Ensure at least one character from each category
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Fill remaining length
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }
    
    /**
     * Create cPanel email account (placeholder for future implementation)
     */
    public function createCPanelEmailAccount(string $email, string $password, int $quotaMB = 1024): array
    {
        // This would integrate with cPanel API in production
        // For now, we'll simulate the process
        
        try {
            // Extract local part and domain
            list($localPart, $domain) = explode('@', $email, 2);
            
            // Simulate cPanel API call
            $response = [
                'success' => true,
                'email' => $email,
                'quota_mb' => $quotaMB,
                'created_at' => date('Y-m-d H:i:s'),
                'cpanel_response' => [
                    'status' => 'success',
                    'message' => 'Email account created successfully'
                ]
            ];
            
            // Update internal_emails table
            $this->db->update('internal_emails', [
                'cpanel_account_created' => true,
                'email_password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'status' => 'active',
                'activated_at' => date('Y-m-d H:i:s')
            ], ['internal_email' => $email]);
            
            return $response;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'email' => $email
            ];
        }
    }
    
    /**
     * Setup email forwarding
     */
    public function setupEmailForwarding(string $internalEmail, string $forwardToEmail): bool
    {
        try {
            // Update database record
            $updated = $this->db->update('internal_emails', [
                'auto_forward_to' => $forwardToEmail,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['internal_email' => $internalEmail]);
            
            // In production, this would configure cPanel forwarding
            // For now, we'll just log the configuration
            error_log("Email forwarding configured: {$internalEmail} -> {$forwardToEmail}");
            
            return $updated > 0;
            
        } catch (Exception $e) {
            error_log("Failed to setup email forwarding: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get configuration value
     */
    protected function getConfigValue(string $key, $default = null)
    {
        $config = $this->db->fetch(
            "SELECT config_value, config_type FROM hybrid_system_config WHERE config_key = ?",
            [$key]
        );
        
        if (!$config) {
            return $default;
        }
        
        $value = $config['config_value'];
        
        // Convert based on type
        switch ($config['config_type']) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Update email statistics
     */
    public function updateEmailStats(string $internalEmail, array $stats): bool
    {
        $updateData = [];
        
        if (isset($stats['sent_count'])) {
            $updateData['total_sent'] = $stats['sent_count'];
        }
        
        if (isset($stats['received_count'])) {
            $updateData['total_received'] = $stats['received_count'];
        }
        
        if (isset($stats['last_login'])) {
            $updateData['last_login_at'] = $stats['last_login'];
        }
        
        if (empty($updateData)) {
            return true;
        }
        
        return $this->db->update('internal_emails', $updateData, [
            'internal_email' => $internalEmail
        ]) > 0;
    }
    
    /**
     * Get internal email by user ID
     */
    public function getInternalEmailByUserId(int $userId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM internal_emails WHERE user_id = ? AND email_type = 'primary' AND status = 'active'",
            [$userId]
        );
    }
    
    /**
     * Get all internal emails for a user
     */
    public function getUserInternalEmails(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM internal_emails WHERE user_id = ? ORDER BY email_type, created_at",
            [$userId]
        );
    }
    
    /**
     * Validate email format
     */
    public function validateEmailFormat(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Check domain
        list($localPart, $domain) = explode('@', $email, 2);
        
        if ($domain !== $this->domain) {
            return false;
        }
        
        // Check local part length
        if (strlen($localPart) > $this->maxEmailLength) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get email generation preview
     */
    public function previewEmailGeneration(array $userData, array $positionData = null, array $hierarchyData = null): array
    {
        try {
            $email = $this->buildEmailAddress($userData, $positionData, $hierarchyData, 0);
            
            return [
                'success' => true,
                'email' => $email,
                'is_unique' => $this->isEmailUnique($email),
                'breakdown' => [
                    'position' => $positionData['key_name'] ?? 'member',
                    'hierarchy' => $this->getHierarchyIdentifier($hierarchyData ?? []),
                    'first_name' => $this->sanitizeEmailPart($userData['first_name']),
                    'last_name' => $this->sanitizeEmailPart($userData['last_name']),
                    'domain' => $this->domain
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}