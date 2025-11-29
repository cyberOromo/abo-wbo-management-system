<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\DonationCampaign;
use App\Models\Budget;
use App\Models\Expense;

/**
 * Finance Controller
 * Handles all finance management operations including donations, budgets, and expenses
 * Part of the comprehensive Finance Management Module
 */
class FinanceController extends Controller
{
    protected $donation;
    protected $donor;
    protected $campaign;
    protected $budget;
    protected $expense;
    
    public function __construct()
    {
        parent::__construct();
        $this->donation = new Donation();
        $this->donor = new Donor();
        $this->campaign = new DonationCampaign();
        // $this->budget = new Budget(); // Will be created
        // $this->expense = new Expense(); // Will be created
        $this->requireAuth();
    }
    
    /**
     * Finance Dashboard - Overview
     */
    public function index()
    {
        $user = auth_user();
        $hierarchyScope = $this->getUserHierarchyScope($user);
        
        // Get financial statistics
        $donationStats = Donation::getDashboardStats($hierarchyScope['level'], $hierarchyScope['id']);
        $campaignStats = $this->getCampaignStats($hierarchyScope);
        $budgetStats = $this->getBudgetStats($hierarchyScope);
        $topDonors = Donor::getTopDonors(10, 'year');
        $recentDonations = $this->getRecentDonations($hierarchyScope, 10);
        $activeCampaigns = DonationCampaign::getActiveCampaigns($hierarchyScope['level'], $hierarchyScope['id']);
        
        return echo $this->render('finance.dashboard', [
            'title' => 'Finance Dashboard',
            'user' => $user,
            'donation_stats' => $donationStats,
            'campaign_stats' => $campaignStats,
            'budget_stats' => $budgetStats,
            'top_donors' => $topDonors,
            'recent_donations' => $recentDonations,
            'active_campaigns' => $activeCampaigns,
            'hierarchy_scope' => $hierarchyScope
        ]);
    }
    
    /**
     * DONATION MANAGEMENT
     */
    
    /**
     * List all donations
     */
    public function donations()
    {
        $user = auth_user();
        $hierarchyScope = $this->getUserHierarchyScope($user);
        
        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'campaign_id' => $_GET['campaign_id'] ?? '',
            'donor_type' => $_GET['donor_type'] ?? '',
            'min_amount' => $_GET['min_amount'] ?? '',
            'max_amount' => $_GET['max_amount'] ?? '',
            'limit' => $_GET['limit'] ?? 50
        ];
        
        $donations = Donation::getByHierarchyScope($hierarchyScope['level'], $hierarchyScope['id'], $filters);
        $campaigns = DonationCampaign::getByHierarchyScope($hierarchyScope['level'], $hierarchyScope['id']);
        $stats = Donation::getDashboardStats($hierarchyScope['level'], $hierarchyScope['id']);
        
        return echo $this->render('finance.donations.index', [
            'title' => 'Donation Management',
            'donations' => $donations,
            'campaigns' => $campaigns,
            'filters' => $filters,
            'stats' => $stats,
            'hierarchy_scope' => $hierarchyScope
        ]);
    }
    
    /**
     * Show donation creation form
     */
    public function createDonation()
    {
        $user = auth_user();
        $hierarchyScope = $this->getUserHierarchyScope($user);
        
        $campaigns = DonationCampaign::getActiveCampaigns($hierarchyScope['level'], $hierarchyScope['id']);
        $donors = $this->getRecentDonors(20);
        
        return echo $this->render('finance.donations.create', [
            'title' => 'Create Donation Record',
            'campaigns' => $campaigns,
            'donors' => $donors,
            'hierarchy_scope' => $hierarchyScope
        ]);
    }
    
    /**
     * Store new donation
     */
    public function storeDonation()
    {
        try {
            $this->validateCSRF();
            
            $data = $_POST;
            $user = auth_user();
            
            // Validate donation data
            $errors = $this->validateDonationData($data);
            if (!empty($errors)) {
                return $this->redirectWithErrors('/finance/donations/create', $errors);
            }
            
            // Handle donor creation/selection
            $donorId = $this->handleDonorData($data);
            
            // Generate donation number
            $donationNumber = Donation::generateDonationNumber();
            
            // Prepare donation data
            $donationData = [
                'donation_number' => $donationNumber,
                'donor_id' => $donorId,
                'donor_type' => $data['donor_type'],
                'is_anonymous' => isset($data['is_anonymous']) ? 1 : 0,
                'campaign_id' => $data['campaign_id'] ?: null,
                'event_name' => $data['event_name'],
                'event_date' => $data['event_date'],
                'amount' => floatval($data['amount']),
                'currency' => $data['currency'] ?? 'USD',
                'exchange_rate' => floatval($data['exchange_rate'] ?? 1.0),
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_date' => $data['payment_date'],
                'payment_status' => $data['payment_status'] ?? 'completed',
                'level_scope' => $data['level_scope'],
                'global_id' => $data['global_id'] ?? 1,
                'godina_id' => $data['godina_id'] ?? null,
                'gamta_id' => $data['gamta_id'] ?? null,
                'gurmu_id' => $data['gurmu_id'] ?? null,
                'submitted_by' => $user['id'],
                'donation_purpose' => $data['donation_purpose'] ?? 'general_support',
                'dedication_type' => $data['dedication_type'] ?? 'none',
                'dedication_name' => $data['dedication_name'] ?? null,
                'dedication_message' => $data['dedication_message'] ?? null,
                'public_acknowledgment' => isset($data['public_acknowledgment']) ? 1 : 0,
                'is_recurring' => isset($data['is_recurring']) ? 1 : 0,
                'recurring_frequency' => $data['recurring_frequency'] ?? null,
                'recurring_end_date' => $data['recurring_end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'tax_year' => date('Y', strtotime($data['payment_date'])),
                'metadata' => json_encode([
                    'created_via' => 'admin_panel',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ])
            ];
            
            // Create donation
            $donation = $this->donation->create($donationData);
            
            // Auto-submit for approval if configured
            if (isset($data['auto_submit']) && $data['auto_submit']) {
                $donationObj = new Donation();
                $donationObj->fill($donation);
                $donationObj->submit();
            }
            
            return $this->redirectWithMessage('/finance/donations', 'Donation record created successfully', 'success');
            
        } catch (\Exception $e) {
            error_log("Error creating donation: " . $e->getMessage());
            return $this->redirectWithMessage('/finance/donations/create', 'Error creating donation: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Show donation details
     */
    public function showDonation($id)
    {
        $donation = $this->donation->find($id);
        if (!$donation) {
            return $this->notFound('Donation not found');
        }
        
        // Check access permissions
        if (!$this->canAccessDonation($donation)) {
            return $this->forbidden('Access denied');
        }
        
        $donationObj = new Donation();
        $donationObj->fill($donation);
        
        $donor = $donationObj->donor();
        $campaign = $donationObj->campaign();
        $submittedBy = $donationObj->submittedBy();
        $approvedBy = $donationObj->approvedBy();
        
        return echo $this->render('finance.donations.show', [
            'title' => 'Donation Details',
            'donation' => $donation,
            'donor' => $donor,
            'campaign' => $campaign,
            'submitted_by' => $submittedBy,
            'approved_by' => $approvedBy
        ]);
    }
    
    /**
     * Approve donation
     */
    public function approveDonation($id)
    {
        try {
            $this->validateCSRF();
            
            $donation = $this->donation->find($id);
            if (!$donation) {
                return $this->jsonResponse(['error' => 'Donation not found'], 404);
            }
            
            if (!$this->canApproveDonation($donation)) {
                return $this->jsonResponse(['error' => 'Access denied'], 403);
            }
            
            $donationObj = new Donation();
            $donationObj->fill($donation);
            
            $user = auth_user();
            $donationObj->approve($user['id']);
            
            return $this->jsonResponse(['message' => 'Donation approved successfully']);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Reject donation
     */
    public function rejectDonation($id)
    {
        try {
            $this->validateCSRF();
            
            $donation = $this->donation->find($id);
            if (!$donation) {
                return $this->jsonResponse(['error' => 'Donation not found'], 404);
            }
            
            if (!$this->canApproveDonation($donation)) {
                return $this->jsonResponse(['error' => 'Access denied'], 403);
            }
            
            $reason = $_POST['rejection_reason'] ?? 'No reason provided';
            
            $donationObj = new Donation();
            $donationObj->fill($donation);
            
            $user = auth_user();
            $donationObj->reject($user['id'], $reason);
            
            return $this->jsonResponse(['message' => 'Donation rejected']);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Generate and download receipt
     */
    public function downloadReceipt($id)
    {
        $donation = $this->donation->find($id);
        if (!$donation) {
            return $this->notFound('Donation not found');
        }
        
        if ($donation['status'] !== 'approved') {
            return $this->forbidden('Receipt can only be generated for approved donations');
        }
        
        if (!$this->canAccessDonation($donation)) {
            return $this->forbidden('Access denied');
        }
        
        try {
            $donationObj = new Donation();
            $donationObj->fill($donation);
            
            $receiptPath = $donationObj->generateReceipt();
            
            // In a real implementation, this would serve the PDF file
            // For now, return a success message
            return $this->jsonResponse([
                'message' => 'Receipt generated successfully',
                'receipt_path' => $receiptPath,
                'receipt_number' => $donation['receipt_number']
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * DONOR MANAGEMENT
     */
    
    /**
     * List all donors
     */
    public function donors()
    {
        $filters = [
            'name' => $_GET['name'] ?? '',
            'email' => $_GET['email'] ?? '',
            'phone' => $_GET['phone'] ?? '',
            'donor_type' => $_GET['donor_type'] ?? '',
            'country' => $_GET['country'] ?? '',
            'min_donated' => $_GET['min_donated'] ?? '',
            'max_donated' => $_GET['max_donated'] ?? '',
            'min_donations' => $_GET['min_donations'] ?? '',
            'limit' => $_GET['limit'] ?? 50
        ];
        
        $donors = Donor::search($filters);
        $topDonors = Donor::getTopDonors(10);
        $geoDistribution = Donor::getGeographicDistribution();
        
        return echo $this->render('finance.donors.index', [
            'title' => 'Donor Management',
            'donors' => $donors,
            'top_donors' => $topDonors,
            'geo_distribution' => $geoDistribution,
            'filters' => $filters
        ]);
    }
    
    /**
     * Show donor details
     */
    public function showDonor($id)
    {
        $donor = $this->donor->find($id);
        if (!$donor) {
            return $this->notFound('Donor not found');
        }
        
        $donorObj = new Donor();
        $donorObj->fill($donor);
        
        $statistics = $donorObj->getStatistics();
        $recentDonations = $donorObj->getRecentDonations(20);
        $donationsByYear = $donorObj->getDonationsByYear();
        $donationsByCampaign = $donorObj->getDonationsByCampaign();
        
        return echo $this->render('finance.donors.show', [
            'title' => 'Donor Profile',
            'donor' => $donor,
            'statistics' => $statistics,
            'recent_donations' => $recentDonations,
            'donations_by_year' => $donationsByYear,
            'donations_by_campaign' => $donationsByCampaign
        ]);
    }
    
    /**
     * CAMPAIGN MANAGEMENT
     */
    
    /**
     * List all donation campaigns
     */
    public function campaigns()
    {
        $user = auth_user();
        $hierarchyScope = $this->getUserHierarchyScope($user);
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'campaign_type' => $_GET['campaign_type'] ?? '',
            'visibility' => $_GET['visibility'] ?? '',
            'active_only' => isset($_GET['active_only']),
            'limit' => $_GET['limit'] ?? 25
        ];
        
        $campaigns = DonationCampaign::getByHierarchyScope($hierarchyScope['level'], $hierarchyScope['id'], $filters);
        
        return echo $this->render('finance.campaigns.index', [
            'title' => 'Campaign Management',
            'campaigns' => $campaigns,
            'filters' => $filters,
            'hierarchy_scope' => $hierarchyScope
        ]);
    }
    
    /**
     * Show campaign details
     */
    public function showCampaign($id)
    {
        $campaign = $this->campaign->find($id);
        if (!$campaign) {
            return $this->notFound('Campaign not found');
        }
        
        $campaignObj = new DonationCampaign();
        $campaignObj->fill($campaign);
        
        $statistics = $campaignObj->getStatistics();
        $topDonors = $campaignObj->getTopDonors(10);
        $timeline = $campaignObj->getDonationTimeline();
        $performance = $campaignObj->getPerformanceComparison();
        
        return echo $this->render('finance.campaigns.show', [
            'title' => 'Campaign Details',
            'campaign' => $campaign,
            'statistics' => $statistics,
            'top_donors' => $topDonors,
            'timeline' => $timeline,
            'performance' => $performance
        ]);
    }
    
    /**
     * REPORTING
     */
    
    /**
     * Financial reports dashboard
     */
    public function reports()
    {
        $user = auth_user();
        $hierarchyScope = $this->getUserHierarchyScope($user);
        
        return echo $this->render('finance.reports.index', [
            'title' => 'Financial Reports',
            'hierarchy_scope' => $hierarchyScope
        ]);
    }
    
    /**
     * Generate donation report
     */
    public function generateDonationReport()
    {
        try {
            $filters = [
                'date_from' => $_GET['date_from'] ?? date('Y-01-01'),
                'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
                'status' => $_GET['status'] ?? 'approved',
                'campaign_id' => $_GET['campaign_id'] ?? '',
                'donor_type' => $_GET['donor_type'] ?? '',
                'level_scope' => $_GET['level_scope'] ?? 'global',
                'hierarchy_id' => $_GET['hierarchy_id'] ?? 1,
                'format' => $_GET['format'] ?? 'html'
            ];
            
            $donations = Donation::getByHierarchyScope($filters['level_scope'], $filters['hierarchy_id'], $filters);
            $stats = Donation::getDashboardStats($filters['level_scope'], $filters['hierarchy_id']);
            
            $reportData = [
                'title' => 'Donation Report',
                'period' => $filters['date_from'] . ' to ' . $filters['date_to'],
                'filters' => $filters,
                'donations' => $donations,
                'statistics' => $stats,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => auth_user()['first_name'] . ' ' . auth_user()['last_name']
            ];
            
            if ($filters['format'] === 'json') {
                return $this->jsonResponse($reportData);
            }
            
            return echo $this->render('finance.reports.donation_report', $reportData);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * HELPER METHODS
     */
    
    /**
     * Get user's hierarchy scope for finance access
     */
    private function getUserHierarchyScope($user)
    {
        // This would determine the user's hierarchy scope based on their position
        // For now, return global scope
        return [
            'level' => 'global',
            'id' => 1,
            'name' => 'Global Organization'
        ];
    }
    
    /**
     * Get campaign statistics for dashboard
     */
    private function getCampaignStats($hierarchyScope)
    {
        $sql = "SELECT 
                    COUNT(*) as total_campaigns,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_campaigns,
                    SUM(target_amount) as total_targets,
                    SUM(raised_amount) as total_raised
                FROM donation_campaigns 
                WHERE level_scope = ? AND {$hierarchyScope['level']}_id = ?";
        
        $result = $this->campaign->query($sql, [$hierarchyScope['level'], $hierarchyScope['id']]);
        return $result[0] ?? [];
    }
    
    /**
     * Get budget statistics for dashboard
     */
    private function getBudgetStats($hierarchyScope)
    {
        // Placeholder - would be implemented when Budget model is created
        return [
            'total_budget' => 0,
            'allocated_budget' => 0,
            'spent_budget' => 0,
            'remaining_budget' => 0
        ];
    }
    
    /**
     * Get recent donations
     */
    private function getRecentDonations($hierarchyScope, $limit = 10)
    {
        return Donation::getByHierarchyScope($hierarchyScope['level'], $hierarchyScope['id'], ['limit' => $limit]);
    }
    
    /**
     * Get recent donors for dropdown
     */
    private function getRecentDonors($limit = 20)
    {
        $sql = "SELECT * FROM donors 
                WHERE status = 'active' 
                ORDER BY last_donation_date DESC, created_at DESC 
                LIMIT ?";
        
        return $this->donor->query($sql, [$limit]);
    }
    
    /**
     * Validate donation data
     */
    private function validateDonationData($data)
    {
        $errors = [];
        
        if (empty($data['donor_type'])) {
            $errors['donor_type'] = 'Donor type is required';
        }
        
        if (empty($data['event_name'])) {
            $errors['event_name'] = 'Event name is required';
        }
        
        if (empty($data['event_date'])) {
            $errors['event_date'] = 'Event date is required';
        }
        
        if (empty($data['amount']) || floatval($data['amount']) <= 0) {
            $errors['amount'] = 'Valid amount is required';
        }
        
        if (empty($data['payment_method'])) {
            $errors['payment_method'] = 'Payment method is required';
        }
        
        if (empty($data['payment_date'])) {
            $errors['payment_date'] = 'Payment date is required';
        }
        
        // Validate donor information based on type
        switch ($data['donor_type']) {
            case 'individual':
                if (empty($data['donor_first_name'])) {
                    $errors['donor_first_name'] = 'First name is required for individual donors';
                }
                if (empty($data['donor_last_name'])) {
                    $errors['donor_last_name'] = 'Last name is required for individual donors';
                }
                break;
            case 'group':
                if (empty($data['donor_group_name'])) {
                    $errors['donor_group_name'] = 'Group name is required for group donors';
                }
                break;
            case 'organization':
            case 'business':
                if (empty($data['donor_organization_name'])) {
                    $errors['donor_organization_name'] = 'Organization name is required';
                }
                break;
        }
        
        return $errors;
    }
    
    /**
     * Handle donor data - create new or find existing
     */
    private function handleDonorData($data)
    {
        // Check if donor_id is provided (existing donor)
        if (!empty($data['donor_id'])) {
            return $data['donor_id'];
        }
        
        // Create new donor
        $donorData = [
            'donor_type' => $data['donor_type'],
            'first_name' => $data['donor_first_name'] ?? null,
            'last_name' => $data['donor_last_name'] ?? null,
            'group_name' => $data['donor_group_name'] ?? null,
            'organization_name' => $data['donor_organization_name'] ?? null,
            'email' => $data['donor_email'] ?? null,
            'phone' => $data['donor_phone'] ?? null,
            'address' => $data['donor_address'] ?? null,
            'city' => $data['donor_city'] ?? null,
            'state_province' => $data['donor_state'] ?? null,
            'country' => $data['donor_country'] ?? null,
            'postal_code' => $data['donor_postal_code'] ?? null,
            'is_anonymous' => isset($data['is_anonymous']) ? 1 : 0,
            'created_by' => auth_user()['id']
        ];
        
        $donor = $this->donor->create($donorData);
        return $donor['id'];
    }
    
    /**
     * Check if user can access donation
     */
    private function canAccessDonation($donation)
    {
        $user = auth_user();
        $hierarchyScope = $this->getUserHierarchyScope($user);
        
        // Check if donation belongs to user's hierarchy scope
        switch ($hierarchyScope['level']) {
            case 'global':
                return true; // Global users can access all donations
            case 'godina':
                return $donation['godina_id'] == $hierarchyScope['id'];
            case 'gamta':
                return $donation['gamta_id'] == $hierarchyScope['id'];
            case 'gurmu':
                return $donation['gurmu_id'] == $hierarchyScope['id'];
        }
        
        return false;
    }
    
    /**
     * Check if user can approve donation
     */
    private function canApproveDonation($donation)
    {
        $user = auth_user();
        
        // User cannot approve their own submissions
        if ($donation['submitted_by'] == $user['id']) {
            return false;
        }
        
        // Check if user has approval permissions in hierarchy
        return $this->canAccessDonation($donation);
    }
}