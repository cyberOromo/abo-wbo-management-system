    /**
     * INTERNAL EMAIL MANAGEMENT
     */
    
    /**
     * User Email Management Dashboard
     */
    public function userEmailManagement()
    {
        $db = Database::getInstance();
        
        // Get users with their email status
        $users = $db->fetchAll("
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email as personal_email,
                u.internal_email,
                u.status,
                u.account_type,
                ua.level_scope,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions,
                ie.internal_email as table_internal_email,
                ie.status as internal_email_status,
                ie.cpanel_account_created,
                ie.created_at as email_created_at
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            LEFT JOIN internal_emails ie ON u.id = ie.user_id
            WHERE u.status IN ('active', 'pending')
            GROUP BY u.id
            ORDER BY u.id
        ");
        
        // Generate statistics
        $stats = [
            'total_users' => count($users),
            'has_internal_email' => count(array_filter($users, fn($u) => !empty($u['internal_email']))),
            'missing_internal_email' => count(array_filter($users, fn($u) => empty($u['internal_email']))),
            'has_assignments' => count(array_filter($users, fn($u) => !empty($u['positions']))),
            'cpanel_accounts' => count(array_filter($users, fn($u) => $u['cpanel_account_created'] == 1))
        ];
        
        return $this->render('admin.user_email_management', [
            'title' => 'User Email Management',
            'users' => $users,
            'stats' => $stats
        ]);
    }
    
    /**
     * Generate missing internal emails for users
     */
    public function generateInternalEmails()
    {
        try {
            require_once __DIR__ . '/../../scripts/analyze-user-emails.php';
            
            $analyzer = new \App\Scripts\UserEmailAnalyzer();
            
            // Get users missing emails
            $missingEmails = $this->getUsersMissingEmails();
            
            if (empty($missingEmails)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'All users already have internal emails',
                    'generated' => 0
                ]);
            }
            
            $generated = 0;
            $errors = [];
            
            foreach ($missingEmails as $issue) {
                $result = $analyzer->generateEmailForSpecificUser($issue['user_id']);
                
                if ($result['success']) {
                    $generated++;
                } else {
                    $errors[] = "User {$issue['user_id']}: {$result['message']}";
                }
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => "Successfully generated {$generated} internal emails",
                'generated' => $generated,
                'errors' => $errors
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error generating emails: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Regenerate internal email for specific user
     */
    public function regenerateUserEmail($userId)
    {
        try {
            $db = Database::getInstance();
            
            // Verify user exists
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }
            
            require_once __DIR__ . '/../../scripts/analyze-user-emails.php';
            $analyzer = new \App\Scripts\UserEmailAnalyzer();
            
            // Delete existing internal email
            $db->query("DELETE FROM internal_emails WHERE user_id = ?", [$userId]);
            $db->query("UPDATE users SET internal_email = NULL WHERE id = ?", [$userId]);
            
            // Generate new email
            $result = $analyzer->generateEmailForSpecificUser($userId);
            
            return $this->jsonResponse($result);
            
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error regenerating email: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Hybrid Registration Management Dashboard
     */
    public function hybridRegistrationManagement()
    {
        $db = Database::getInstance();
        
        // Get hybrid registration statistics
        $stats = [
            'total_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations"),
            'pending_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations WHERE status = 'pending'"),
            'approved_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations WHERE status = 'approved'"),
            'rejected_registrations' => $db->fetchColumn("SELECT COUNT(*) FROM hybrid_registrations WHERE status = 'rejected'")
        ];
        
        // Get recent registrations
        $recentRegistrations = $db->fetchAll("
            SELECT hr.*, u.first_name, u.last_name, u.email
            FROM hybrid_registrations hr
            LEFT JOIN users u ON hr.user_id = u.id
            ORDER BY hr.created_at DESC
            LIMIT 20
        ");
        
        return $this->render('admin.hybrid_registration_management', [
            'title' => 'Hybrid Registration Management',
            'stats' => $stats,
            'recent_registrations' => $recentRegistrations
        ]);
    }
    
    /**
     * Get users missing internal emails
     */
    private function getUsersMissingEmails()
    {
        $db = Database::getInstance();
        
        return $db->fetchAll("
            SELECT 
                u.id as user_id,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                u.email,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name) as positions,
                ua.level_scope
            FROM users u
            LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
            LEFT JOIN positions p ON ua.position_id = p.id
            WHERE u.status IN ('active', 'pending')
            AND (u.internal_email IS NULL OR u.internal_email = '')
            AND ua.user_id IS NOT NULL
            GROUP BY u.id
            ORDER BY u.id
        ");
    }