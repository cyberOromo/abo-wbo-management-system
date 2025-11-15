<?php

namespace App\Scripts;

// Simple autoloader for our classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load config helper
require_once __DIR__ . '/../app/helpers.php';

use App\Utils\Database;
use App\Services\InternalEmailGenerator;

class UserEmailAnalyzer
{
    private $database;
    private $emailGenerator;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->emailGenerator = new InternalEmailGenerator();
    }
    
    /**
     * Analyze current users and their email status
     */
    public function analyzeUsers()
    {
        echo "=== ABO-WBO User Email Analysis ===\n\n";
        
        $users = $this->getUsersWithAssignments();
        
        $stats = [
            'total_users' => 0,
            'has_internal_email' => 0,
            'missing_internal_email' => 0,
            'has_assignments' => 0,
            'missing_assignments' => 0,
            'active_users' => 0,
            'pending_users' => 0
        ];
        
        $issues = [];
        
        foreach ($users as $user) {
            $stats['total_users']++;
            
            if ($user['user_status'] === 'active') {
                $stats['active_users']++;
            } elseif ($user['user_status'] === 'pending') {
                $stats['pending_users']++;
            }
            
            // Check internal email status
            if ($user['table_internal_email']) {
                $stats['has_internal_email']++;
            } else {
                $stats['missing_internal_email']++;
                
                if ($user['positions']) {
                    $issues[] = [
                        'type' => 'missing_internal_email',
                        'user_id' => $user['id'],
                        'name' => $user['first_name'] . ' ' . $user['last_name'],
                        'positions' => $user['positions'],
                        'level_scope' => $user['level_scope'],
                        'hierarchy_info' => $this->getHierarchyPath($user)
                    ];
                }
            }
            
            // Check assignments
            if ($user['positions']) {
                $stats['has_assignments']++;
            } else {
                $stats['missing_assignments']++;
                $issues[] = [
                    'type' => 'missing_assignments',
                    'user_id' => $user['id'],
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'email' => $user['personal_email']
                ];
            }
        }
        
        $this->displayAnalysisResults($stats, $issues);
        return ['stats' => $stats, 'issues' => $issues, 'users' => $users];
    }
    
    /**
     * Get users with their assignment information
     */
    private function getUsersWithAssignments()
    {
        $query = "
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email as personal_email,
                u.internal_email as user_internal_email,
                u.account_type,
                u.status as user_status,
                ua.level_scope,
                ua.global_id,
                ua.godina_id,
                ua.gamta_id,
                ua.gurmu_id,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions,
                ie.internal_email as table_internal_email,
                ie.status as internal_email_status,
                ie.cpanel_account_created
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            LEFT JOIN internal_emails ie ON u.id = ie.user_id
            WHERE u.status IN ('active', 'pending')
            GROUP BY u.id
            ORDER BY u.id
        ";
        
        return $this->database->fetchAll($query);
    }
    
    /**
     * Get hierarchy path for a user
     */
    private function getHierarchyPath($user)
    {
        $path = [];
        
        try {
            // Add hierarchy levels based on assignments
            if ($user['global_id']) {
                $global = $this->database->fetch("SELECT name FROM globals WHERE id = ?", [$user['global_id']]);
                if ($global) $path[] = "Global: " . $global['name'];
            }
            
            if ($user['godina_id']) {
                $godina = $this->database->fetch("SELECT name FROM godinas WHERE id = ?", [$user['godina_id']]);
                if ($godina) $path[] = "Godina: " . $godina['name'];
            }
            
            if ($user['gamta_id']) {
                $gamta = $this->database->fetch("SELECT name FROM gamtas WHERE id = ?", [$user['gamta_id']]);
                if ($gamta) $path[] = "Gamta: " . $gamta['name'];
            }
            
            if ($user['gurmu_id']) {
                $gurmu = $this->database->fetch("SELECT name FROM gurmus WHERE id = ?", [$user['gurmu_id']]);
                if ($gurmu) $path[] = "Gurmu: " . $gurmu['name'];
            }
            
        } catch (Exception $e) {
            $path[] = "Error retrieving hierarchy: " . $e->getMessage();
        }
        
        return implode(' → ', $path);
    }
    
    /**
     * Display analysis results
     */
    private function displayAnalysisResults($stats, $issues)
    {
        echo "USER STATISTICS:\n";
        echo "================\n";
        echo "Total Users: {$stats['total_users']}\n";
        echo "Active Users: {$stats['active_users']}\n";
        echo "Pending Users: {$stats['pending_users']}\n";
        echo "Users with Internal Email: {$stats['has_internal_email']}\n";
        echo "Users Missing Internal Email: {$stats['missing_internal_email']}\n";
        echo "Users with Position Assignments: {$stats['has_assignments']}\n";
        echo "Users Missing Assignments: {$stats['missing_assignments']}\n\n";
        
        if (!empty($issues)) {
            echo "IDENTIFIED ISSUES:\n";
            echo "==================\n";
            
            $missing_emails = array_filter($issues, function($issue) {
                return $issue['type'] === 'missing_internal_email';
            });
            
            $missing_assignments = array_filter($issues, function($issue) {
                return $issue['type'] === 'missing_assignments';
            });
            
            if (!empty($missing_emails)) {
                echo "\n1. USERS MISSING INTERNAL EMAILS (" . count($missing_emails) . "):\n";
                echo "-------------------------------------------\n";
                foreach ($missing_emails as $issue) {
                    echo "• User ID {$issue['user_id']}: {$issue['name']}\n";
                    echo "  Positions: {$issue['positions']}\n";
                    echo "  Level: {$issue['level_scope']}\n";
                    echo "  Hierarchy: {$issue['hierarchy_info']}\n";
                    echo "  Suggested Email: " . $this->generateSuggestedEmail($issue) . "\n\n";
                }
            }
            
            if (!empty($missing_assignments)) {
                echo "\n2. USERS MISSING POSITION ASSIGNMENTS (" . count($missing_assignments) . "):\n";
                echo "-----------------------------------------------\n";
                foreach ($missing_assignments as $issue) {
                    echo "• User ID {$issue['user_id']}: {$issue['name']} ({$issue['email']})\n";
                }
            }
        } else {
            echo "✅ No issues found! All users have proper email assignments.\n";
        }
    }
    
    /**
     * Generate suggested email for a user
     */
    private function generateSuggestedEmail($issue)
    {
        try {
            // Extract name parts
            $nameParts = explode(' ', $issue['name']);
            $firstName = strtolower($nameParts[0] ?? '');
            $lastName = strtolower($nameParts[1] ?? '');
            
            // Get position (use first position if multiple)
            $positions = explode(',', $issue['positions']);
            $position = strtolower(str_replace(' ', '', trim($positions[0])));
            
            // Extract hierarchy name from hierarchy_info
            $hierarchyParts = explode(' → ', $issue['hierarchy_info']);
            $lastHierarchy = end($hierarchyParts);
            
            if (strpos($lastHierarchy, ':') !== false) {
                $hierarchyName = trim(explode(':', $lastHierarchy)[1]);
                $hierarchyName = strtolower(str_replace([' ', '-', '_'], '', $hierarchyName));
                
                // Truncate long hierarchy names
                if (strlen($hierarchyName) > 15) {
                    $hierarchyName = substr($hierarchyName, 0, 15);
                }
            } else {
                $hierarchyName = 'abo-wbo';
            }
            
            return "{$position}.{$hierarchyName}.{$firstName}.{$lastName}@abo-wbo.org";
            
        } catch (Exception $e) {
            return "Error generating email: " . $e->getMessage();
        }
    }
    
    /**
     * Generate internal emails for users who don't have them
     */
    public function generateMissingEmails($dryRun = true)
    {
        echo "\n=== GENERATING MISSING INTERNAL EMAILS ===\n";
        echo "Dry Run Mode: " . ($dryRun ? "YES (no changes will be made)" : "NO (changes will be applied)") . "\n\n";
        
        $analysis = $this->analyzeUsers();
        $missing_emails = array_filter($analysis['issues'], function($issue) {
            return $issue['type'] === 'missing_internal_email';
        });
        
        if (empty($missing_emails)) {
            echo "✅ No users need internal email generation.\n";
            return;
        }
        
        $generated = 0;
        $errors = 0;
        
        foreach ($missing_emails as $issue) {
            echo "Processing User ID {$issue['user_id']}: {$issue['name']}\n";
            
            try {
                // Find the user's assignment details
                $user = $this->database->fetchAll(
                    "SELECT u.*, ua.* FROM users u 
                     LEFT JOIN user_assignments ua ON u.id = ua.user_id 
                     WHERE u.id = ? AND ua.status = 'active' 
                     LIMIT 1",
                    [$issue['user_id']]
                );
                
                if (empty($user)) {
                    echo "  ❌ Error: User not found or no active assignments\n";
                    $errors++;
                    continue;
                }
                
                $userData = $user[0];
                
                // Prepare data for email generation
                $emailData = [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'level_scope' => $userData['level_scope'],
                    'global_id' => $userData['global_id'],
                    'godina_id' => $userData['godina_id'],
                    'gamta_id' => $userData['gamta_id'],
                    'gurmu_id' => $userData['gurmu_id'],
                    'position_id' => $userData['position_id']
                ];
                
                // Generate the email using our service
                $result = $this->generateEmailForUser($emailData, $issue['user_id'], $dryRun);
                
                if ($result['success']) {
                    echo "  ✅ Generated: {$result['email']}\n";
                    $generated++;
                } else {
                    echo "  ❌ Error: {$result['message']}\n";
                    $errors++;
                }
                
            } catch (Exception $e) {
                echo "  ❌ Exception: " . $e->getMessage() . "\n";
                $errors++;
            }
            
            echo "\n";
        }
        
        echo "GENERATION SUMMARY:\n";
        echo "===================\n";
        echo "Successfully Generated: $generated\n";
        echo "Errors: $errors\n";
        echo "Total Processed: " . count($missing_emails) . "\n";
        
        if ($dryRun) {
            echo "\n⚠️  This was a DRY RUN - no changes were made to the database.\n";
            echo "To apply changes, run with dryRun = false\n";
        }
    }
    
    /**
     * Generate email for a specific user using the existing hierarchy structure
     */
    private function generateEmailForUser($userData, $userId, $dryRun)
    {
        try {
            // Get position name
            $position = $this->database->fetch("SELECT name FROM positions WHERE id = ?", [$userData['position_id']]);
            if (empty($position)) {
                return ['success' => false, 'message' => 'Position not found'];
            }
            $positionName = strtolower(str_replace(' ', '', $position['name']));
            
            // Build hierarchy path
            $hierarchyPath = [];
            
            // Start from the most specific level and work up
            if ($userData['gurmu_id']) {
                $gurmu = $this->database->fetch("SELECT name FROM gurmus WHERE id = ?", [$userData['gurmu_id']]);
                if ($gurmu) {
                    $hierarchyPath[] = $this->sanitizeName($gurmu['name']);
                }
            }
            
            if ($userData['gamta_id']) {
                $gamta = $this->database->fetch("SELECT name FROM gamtas WHERE id = ?", [$userData['gamta_id']]);
                if ($gamta) {
                    $hierarchyPath[] = $this->sanitizeName($gamta['name']);
                }
            }
            
            if ($userData['godina_id']) {
                $godina = $this->database->fetch("SELECT name FROM godinas WHERE id = ?", [$userData['godina_id']]);
                if ($godina) {
                    $hierarchyPath[] = $this->sanitizeName($godina['name']);
                }
            }
            
            // If no hierarchy found, use default
            if (empty($hierarchyPath)) {
                $hierarchyPath[] = 'abowbo';
            }
            
            // Reverse to get proper hierarchy order (Global -> Godina -> Gamta -> Gurmu)
            $hierarchyPath = array_reverse($hierarchyPath);
            
            // Build email
            $firstName = $this->sanitizeName($userData['first_name']);
            $lastName = $this->sanitizeName($userData['last_name']);
            
            $email = $positionName . '.' . implode('.', $hierarchyPath) . '.' . $firstName . '.' . $lastName . '@abo-wbo.org';
            
            // Check for collisions and resolve them
            $finalEmail = $this->resolveEmailCollision($email);
            
            if (!$dryRun) {
                // Insert into internal_emails table
                $this->database->insert('internal_emails', [
                    'user_id' => $userId,
                    'internal_email' => $finalEmail,
                    'email_type' => 'primary',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'activated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Update users table
                $this->database->update('users', [
                    'internal_email' => $finalEmail,
                    'account_type' => 'hybrid'
                ], ['id' => $userId]);
            }
            
            return ['success' => true, 'email' => $finalEmail];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Sanitize name for email use
     */
    private function sanitizeName($name)
    {
        // Convert to lowercase
        $name = strtolower($name);
        
        // Remove special characters and spaces
        $name = preg_replace('/[^a-z0-9]/', '', $name);
        
        // Truncate if too long
        if (strlen($name) > 15) {
            $name = substr($name, 0, 15);
        }
        
        return $name;
    }
    
    /**
     * Resolve email collisions by adding numbers
     */
    private function resolveEmailCollision($email)
    {
        $originalEmail = $email;
        $counter = 1;
        
        while ($this->emailExists($email)) {
            $counter++;
            $emailParts = explode('@', $originalEmail);
            $email = $emailParts[0] . $counter . '@' . $emailParts[1];
            
            if ($counter > 99) {
                throw new Exception("Unable to resolve email collision after 99 attempts");
            }
        }
        
        return $email;
    }
    
    /**
     * Check if email already exists
     */
    private function emailExists($email)
    {
        $count = $this->database->fetchColumn("SELECT COUNT(*) FROM internal_emails WHERE internal_email = ?", [$email]);
        return $count > 0;
    }
    
    /**
     * Generate email for a specific user (public method for API calls)
     */
    public function generateEmailForSpecificUser($userId)
    {
        try {
            // Find the user's assignment details
            $user = $this->database->fetchAll(
                "SELECT u.*, ua.* FROM users u 
                 LEFT JOIN user_assignments ua ON u.id = ua.user_id 
                 WHERE u.id = ? AND ua.status = 'active' 
                 LIMIT 1",
                [$userId]
            );
            
            if (empty($user)) {
                return ['success' => false, 'message' => 'User not found or no active assignments'];
            }
            
            $userData = $user[0];
            
            // Prepare data for email generation
            $emailData = [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'level_scope' => $userData['level_scope'],
                'global_id' => $userData['global_id'],
                'godina_id' => $userData['godina_id'],
                'gamta_id' => $userData['gamta_id'],
                'gurmu_id' => $userData['gurmu_id'],
                'position_id' => $userData['position_id']
            ];
            
            // Generate the email using our service
            $result = $this->generateEmailForUser($emailData, $userId, false);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Internal email generated successfully',
                    'email' => $result['email']
                ];
            } else {
                return $result;
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error generating email: ' . $e->getMessage()];
        }
    }
}

// Run the analyzer
try {
    $analyzer = new UserEmailAnalyzer();
    
    // Analyze current state
    $analyzer->analyzeUsers();
    
    // Generate missing emails automatically
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🔄 GENERATING MISSING INTERNAL EMAILS...\n\n";
    
    // First run a dry run to show what will be created
    echo "=== DRY RUN (Preview) ===\n";
    $analyzer->generateMissingEmails(true);
    
    echo "\n=== APPLYING CHANGES ===\n";
    $analyzer->generateMissingEmails(false);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}