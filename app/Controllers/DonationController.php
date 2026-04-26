<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Utils\Database;
use App\Utils\Validator;

class DonationController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Display donations index with hierarchy-based filtering
     */
    public function index()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            // Get donations based on user's hierarchical scope
            $donations = $this->getDonationsForUserScope($userScope);
            $stats = $this->getDonationStatistics($userScope);
            
            return $this->render('donations/index_modern', [
                'donations' => $donations,
                'stats' => $stats,
                'user_scope' => $userScope,
                'can_create' => false,
                'can_manage' => $this->userCanManageDonations($user),
                'title' => 'My Donations'
            ]);
            
        } catch (\Exception $e) {
            error_log("DonationController::index error: " . $e->getMessage());
            return $this->errorResponse('Failed to load donations', 500);
        }
    }

    /**
     * Show create donation form
     */
    public function create()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            // Get donation types and categories
            $donationTypes = $this->getDonationTypes();
            $categories = $this->getDonationCategories();
            $organizationUnits = $this->getOrganizationUnitsForScope($userScope);
            
            return $this->render('donations/create', [
                'donation_types' => $donationTypes,
                'categories' => $categories,
                'organization_units' => $organizationUnits,
                'user_scope' => $userScope,
                'title' => 'Make Donation'
            ]);
            
        } catch (\Exception $e) {
            error_log("DonationController::create error: " . $e->getMessage());
            return $this->errorResponse('Failed to load donation form', 500);
        }
    }

    /**
     * Store new donation
     */
    public function store()
    {
        try {
            $user = $this->getAuthUser();
            $data = $_POST;
            
            // Validate donation data
            $validation = $this->validateDonationData($data);
            if (!$validation['valid']) {
                return $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            }

            // Prepare donation record
            $donationData = [
                'donor_id' => $user['id'],
                'amount' => floatval($data['amount']),
                'type' => $data['type'],
                'category' => $data['category'] ?? 'general',
                'description' => $data['description'] ?? '',
                'donation_date' => $data['donation_date'] ?? date('Y-m-d'),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $this->generateReferenceNumber(),
                'gurmu_id' => $data['gurmu_id'] ?? null,
                'gamta_id' => $data['gamta_id'] ?? null,
                'godina_id' => $data['godina_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $columns = ['donor_id', 'amount', 'type', 'category', 'description', 'donation_date', 'payment_method', 'reference_number'];
            $values = [
                $donationData['donor_id'],
                $donationData['amount'],
                $donationData['type'],
                $donationData['category'],
                $donationData['description'],
                $donationData['donation_date'],
                $donationData['payment_method'],
                $donationData['reference_number']
            ];

            if ($this->hasDonationStatusColumn()) {
                $columns[] = 'status';
                $values[] = 'pending';
            }

            $columns = array_merge($columns, ['gurmu_id', 'gamta_id', 'godina_id', 'created_at']);
            $values = array_merge($values, [
                $donationData['gurmu_id'],
                $donationData['gamta_id'],
                $donationData['godina_id'],
                $donationData['created_at']
            ]);

            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $sql = 'INSERT INTO donations (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
            
            $result = $this->db->query($sql, $values);
            
            if ($result) {
                $donationId = $this->db->getInsertId();
                
                // Log donation activity
                $this->logDonationActivity($donationId, 'created', $user['id']);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Donation recorded successfully',
                    'donation_id' => $donationId,
                    'reference' => $donationData['reference_number']
                ]);
            }
            
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to record donation'], 500);
            
        } catch (\Exception $e) {
            error_log("DonationController::store error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Server error occurred'], 500);
        }
    }

    /**
     * Show donation details
     */
    public function show($id)
    {
        try {
            $user = $this->getAuthUser();
            $donation = $this->getDonationWithAccess($id, $user);
            
            if (!$donation) {
                return $this->errorResponse('Donation not found or access denied', 404);
            }
            
            $activities = $this->getDonationActivities($id);
            
            return $this->render('donations/show', [
                'donation' => $donation,
                'activities' => $activities,
                'can_edit' => $this->canEditDonation($donation, $user),
                'can_delete' => $this->canDeleteDonation($donation, $user),
                'title' => 'Donation Details'
            ]);
            
        } catch (\Exception $e) {
            error_log("DonationController::show error: " . $e->getMessage());
            return $this->errorResponse('Failed to load donation details', 500);
        }
    }

    /**
     * Show edit donation form
     */
    public function edit($id)
    {
        try {
            $user = $this->getAuthUser();
            $donation = $this->getDonationWithAccess($id, $user);
            
            if (!$donation) {
                return $this->errorResponse('Donation not found or access denied', 404);
            }
            
            if (!$this->canEditDonation($donation, $user)) {
                return $this->errorResponse('Permission denied', 403);
            }
            
            $donationTypes = $this->getDonationTypes();
            $categories = $this->getDonationCategories();
            
            return $this->render('donations/edit', [
                'donation' => $donation,
                'donation_types' => $donationTypes,
                'categories' => $categories,
                'title' => 'Edit Donation'
            ]);
            
        } catch (\Exception $e) {
            error_log("DonationController::edit error: " . $e->getMessage());
            return $this->errorResponse('Failed to load donation edit form', 500);
        }
    }

    /**
     * Update donation
     */
    public function update($id)
    {
        try {
            $user = $this->getAuthUser();
            $donation = $this->getDonationWithAccess($id, $user);
            
            if (!$donation) {
                return $this->jsonResponse(['success' => false, 'message' => 'Donation not found'], 404);
            }
            
            if (!$this->canEditDonation($donation, $user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            $data = $_POST;
            
            // Validate update data
            $validation = $this->validateDonationData($data, $id);
            if (!$validation['valid']) {
                return $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            }
            
            // Update donation
            $sql = "UPDATE donations SET amount = ?, type = ?, category = ?, description = ?, 
                    donation_date = ?, payment_method = ?, updated_at = ? WHERE id = ?";
            
            $result = $this->db->query($sql, [
                floatval($data['amount']),
                $data['type'],
                $data['category'],
                $data['description'],
                $data['donation_date'],
                $data['payment_method'],
                date('Y-m-d H:i:s'),
                $id
            ]);
            
            if ($result) {
                // Log update activity
                $this->logDonationActivity($id, 'updated', $user['id']);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Donation updated successfully'
                ]);
            }
            
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update donation'], 500);
            
        } catch (\Exception $e) {
            error_log("DonationController::update error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Server error occurred'], 500);
        }
    }

    /**
     * Delete donation
     */
    public function destroy($id)
    {
        try {
            $user = $this->getAuthUser();
            $donation = $this->getDonationWithAccess($id, $user);
            
            if (!$donation) {
                return $this->jsonResponse(['success' => false, 'message' => 'Donation not found'], 404);
            }
            
            if (!$this->canDeleteDonation($donation, $user)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            
            if ($this->hasDonationStatusColumn()) {
                $sql = "UPDATE donations SET status = 'deleted', deleted_at = ? WHERE id = ?";
                $result = $this->db->query($sql, [date('Y-m-d H:i:s'), $id]);
            } else {
                $sql = "DELETE FROM donations WHERE id = ?";
                $result = $this->db->query($sql, [$id]);
            }
            
            if ($result) {
                // Log deletion activity
                $this->logDonationActivity($id, 'deleted', $user['id']);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Donation deleted successfully'
                ]);
            }
            
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to delete donation'], 500);
            
        } catch (\Exception $e) {
            error_log("DonationController::destroy error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Server error occurred'], 500);
        }
    }

    /**
     * Generate donation summary report
     */
    public function reportSummary()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $dateRange = $_GET['date_range'] ?? '30_days';
            $type = $_GET['type'] ?? 'all';
            
            $summaryData = $this->generateDonationSummary($userScope, $dateRange, $type);
            
            return $this->render('donations/reports/summary', [
                'summary' => $summaryData,
                'user_scope' => $userScope,
                'filters' => compact('dateRange', 'type'),
                'title' => 'Donation Summary Report'
            ]);
            
        } catch (\Exception $e) {
            error_log("DonationController::reportSummary error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate report', 500);
        }
    }

    /**
     * Generate detailed donation report
     */
    public function reportDetailed()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $filters = [
                'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
                'end_date' => $_GET['end_date'] ?? date('Y-m-t'),
                'type' => $_GET['type'] ?? 'all',
                'category' => $_GET['category'] ?? 'all',
                'status' => $_GET['status'] ?? 'all'
            ];
            
            $detailedData = $this->generateDetailedDonationReport($userScope, $filters);
            
            return $this->render('donations/reports/detailed', [
                'data' => $detailedData,
                'user_scope' => $userScope,
                'filters' => $filters,
                'title' => 'Detailed Donation Report'
            ]);
            
        } catch (\Exception $e) {
            error_log("DonationController::reportDetailed error: " . $e->getMessage());
            return $this->errorResponse('Failed to generate detailed report', 500);
        }
    }

    /**
     * Export donation report
     */
    public function exportReport()
    {
        try {
            $user = $this->getAuthUser();
            $userScope = $this->getUserHierarchicalScope($user['id']);
            
            $format = $_GET['format'] ?? 'csv';
            $type = $_GET['type'] ?? 'summary';
            
            $reportData = $this->generateExportData($userScope, $type);
            
            if ($format === 'csv') {
                return $this->exportAsCsv($reportData, "donations_report_" . date('Y-m-d'));
            } elseif ($format === 'pdf') {
                return $this->exportAsPdf($reportData, "donations_report_" . date('Y-m-d'));
            }
            
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid export format'], 400);
            
        } catch (\Exception $e) {
            error_log("DonationController::exportReport error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Export failed'], 500);
        }
    }

    // Helper methods

    protected function getUserHierarchicalScope($userId)
    {
        $db = Database::getInstance();
        
        $sql = "SELECT ua.*, p.name as position_name, p.hierarchy_type,
                       go.name as godina_name, ga.name as gamta_name, gu.name as gurmu_name
                FROM user_assignments ua
                LEFT JOIN positions p ON ua.position_id = p.id
                LEFT JOIN godinas go ON ua.godina_id = go.id
                LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
                LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
                WHERE ua.user_id = ? AND ua.status = 'active'
                LIMIT 1";
        
        return $db->fetch($sql, [$userId]) ?: [];
    }

    protected function getDonationsForUserScope($userScope)
    {
        $hasStatusColumn = $this->hasDonationStatusColumn();
        $sql = "SELECT d.*
            FROM donations d
                WHERE 1 = 1";

        if ($hasStatusColumn) {
            $sql .= " AND d.status != 'deleted'";
        }
        
        $params = [];
        
        $sql = $this->applyDonationScopeFilter($sql, $params, $userScope, 'd');
        
        $sql .= " ORDER BY d.created_at DESC LIMIT 100";
        
        return Database::getInstance()->fetchAll($sql, $params);
    }

    protected function getDonationStatistics($userScope)
    {
        $hasStatusColumn = $this->hasDonationStatusColumn();
        $sql = "SELECT 
                    COUNT(*) as total_donations,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_amount,
                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_count,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN amount ELSE 0 END) as today_amount,
                    COUNT(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 END) as month_count,
                    SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN amount ELSE 0 END) as month_amount
                FROM donations 
                WHERE 1 = 1";

        if ($hasStatusColumn) {
            $sql .= " AND status != 'deleted'";
        }
        
        $params = [];
        
        $sql = $this->applyDonationScopeFilter($sql, $params, $userScope);
        
        return Database::getInstance()->fetch($sql, $params) ?: [];
    }

    private function validateDonationData($data, $donationId = null)
    {
        $errors = [];
        
        if (empty($data['amount']) || !is_numeric($data['amount']) || floatval($data['amount']) <= 0) {
            $errors['amount'] = 'Valid donation amount is required';
        }
        
        if (empty($data['type'])) {
            $errors['type'] = 'Donation type is required';
        }
        
        if (!empty($data['donation_date']) && !strtotime($data['donation_date'])) {
            $errors['donation_date'] = 'Valid donation date is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function generateReferenceNumber()
    {
        return 'DN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function getDonationTypes()
    {
        return [
            'monthly' => 'Monthly Contribution',
            'annual' => 'Annual Membership',
            'special' => 'Special Occasion',
            'emergency' => 'Emergency Fund',
            'project' => 'Project Support',
            'other' => 'Other'
        ];
    }

    private function getDonationCategories()
    {
        return [
            'general' => 'General Fund',
            'education' => 'Education',
            'community' => 'Community Development',
            'cultural' => 'Cultural Activities',
            'emergency' => 'Emergency Relief',
            'infrastructure' => 'Infrastructure',
            'other' => 'Other'
        ];
    }

    private function userCanManageDonations($user)
    {
        return in_array($user['role'], ['admin', 'executive']);
    }

    private function canEditDonation($donation, $user)
    {
        if ($user['role'] === 'admin') {
            return true;
        }

        if (isset($donation['donor_id'])) {
            return (int) $donation['donor_id'] === (int) $user['id'];
        }

        if (isset($donation['member_id'])) {
            return (int) $donation['member_id'] === (int) $user['id'];
        }

        return false;
    }

    private function canDeleteDonation($donation, $user)
    {
        return $user['role'] === 'admin';
    }

    private function getDonationWithAccess($id, $user)
    {
        $hasStatusColumn = $this->hasDonationStatusColumn();
        $sql = "SELECT d.*
            FROM donations d
            WHERE d.id = ?";

        if ($hasStatusColumn) {
            $sql .= " AND d.status != 'deleted'";
        }
        
        $donation = Database::getInstance()->fetch($sql, [$id]);
        
        if (!$donation) {
            return null;
        }
        
        // Check access based on user role and hierarchy
        if ($user['role'] === 'admin') {
            return $donation;
        }
        
        if ((isset($donation['donor_id']) && (int) $donation['donor_id'] === (int) $user['id'])
            || (isset($donation['member_id']) && (int) $donation['member_id'] === (int) $user['id'])) {
            return $donation;
        }
        
        // Check hierarchy access for executives
        if ($user['role'] === 'executive') {
            $userScope = $this->getUserHierarchicalScope($user['id']);
            if ($this->donationInUserScope($donation, $userScope)) {
                return $donation;
            }
        }
        
        return null;
    }

    private function hasDonationStatusColumn(): bool
    {
        return Database::getInstance()->columnExists('donations', 'status');
    }

    private function donationInUserScope($donation, $userScope)
    {
        if (empty($userScope) || empty($userScope['level_scope'])) {
            return false;
        }

        if ($userScope['level_scope'] === 'global') {
            return true;
        }

        $unitId = $this->resolveScopeUnitId($userScope);
        $scopeKey = $userScope['level_scope'] . '_id';

        if ($unitId !== null && isset($donation[$scopeKey])) {
            return (int) $donation[$scopeKey] === $unitId;
        }

        if ($unitId !== null && isset($donation['level_scope'], $donation['global_id'])) {
            return $donation['level_scope'] === $userScope['level_scope']
                && (int) $donation['global_id'] === $unitId;
        }
        
        return false;
    }

    private function applyDonationScopeFilter(string $sql, array &$params, array $userScope = [], string $alias = ''): string
    {
        if (empty($userScope) || empty($userScope['level_scope']) || $userScope['level_scope'] === 'global') {
            return $sql;
        }

        $db = Database::getInstance();
        $levelScope = $userScope['level_scope'];
        $unitId = $this->resolveScopeUnitId($userScope);

        if ($unitId === null) {
            return $sql;
        }

        $prefix = $alias !== '' ? $alias . '.' : '';
        $directColumn = $levelScope . '_id';

        if ($db->columnExists('donations', 'gurmu_id')) {
            if ($levelScope === 'gurmu') {
                $sql .= " AND {$prefix}gurmu_id = ?";
                $params[] = $unitId;
                return $sql;
            }

            if ($levelScope === 'gamta') {
                $sql .= " AND EXISTS (SELECT 1 FROM gurmus scope_gurmu WHERE scope_gurmu.id = {$prefix}gurmu_id AND scope_gurmu.gamta_id = ?)";
                $params[] = $unitId;
                return $sql;
            }

            if ($levelScope === 'godina') {
                $sql .= " AND EXISTS (
                    SELECT 1
                    FROM gurmus scope_gurmu
                    INNER JOIN gamtas scope_gamta ON scope_gurmu.gamta_id = scope_gamta.id
                    WHERE scope_gurmu.id = {$prefix}gurmu_id
                      AND scope_gamta.godina_id = ?
                )";
                $params[] = $unitId;
                return $sql;
            }
        }

        if ($db->columnExists('donations', $directColumn)) {
            $sql .= " AND {$prefix}{$directColumn} = ?";
            $params[] = $unitId;
            return $sql;
        }

        if ($db->columnExists('donations', 'level_scope') && $db->columnExists('donations', 'global_id')) {
            $sql .= " AND {$prefix}level_scope = ? AND {$prefix}global_id = ?";
            $params[] = $levelScope;
            $params[] = $unitId;
        }

        return $sql;
    }

    private function resolveScopeUnitId(array $userScope): ?int
    {
        if (!empty($userScope['organizational_unit_id'])) {
            return (int) $userScope['organizational_unit_id'];
        }

        foreach (['gurmu_id', 'gamta_id', 'godina_id', 'global_id'] as $key) {
            if (!empty($userScope[$key])) {
                return (int) $userScope[$key];
            }
        }

        return null;
    }

    private function logDonationActivity($donationId, $action, $userId)
    {
        $sql = "INSERT INTO donation_activities (donation_id, user_id, action, created_at) 
                VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$donationId, $userId, $action, date('Y-m-d H:i:s')]);
    }

    private function getDonationActivities($donationId)
    {
        $sql = "SELECT da.*, u.first_name, u.last_name
                FROM donation_activities da
                JOIN users u ON da.user_id = u.id
                WHERE da.donation_id = ?
                ORDER BY da.created_at DESC";
        
        return $this->db->fetchAll($sql, [$donationId]);
    }

    private function getOrganizationUnitsForScope($userScope)
    {
        $units = [];
        
        if (!empty($userScope)) {
            if ($userScope['level_scope'] === 'gurmu') {
                $units['gurmu'] = ['id' => $userScope['gurmu_id'], 'name' => $userScope['gurmu_name']];
                $units['gamta'] = ['id' => $userScope['gamta_id'], 'name' => $userScope['gamta_name']];
                $units['godina'] = ['id' => $userScope['godina_id'], 'name' => $userScope['godina_name']];
            } elseif ($userScope['level_scope'] === 'gamta') {
                $units['gamta'] = ['id' => $userScope['gamta_id'], 'name' => $userScope['gamta_name']];
                $units['godina'] = ['id' => $userScope['godina_id'], 'name' => $userScope['godina_name']];
            } elseif ($userScope['level_scope'] === 'godina') {
                $units['godina'] = ['id' => $userScope['godina_id'], 'name' => $userScope['godina_name']];
            }
        }
        
        return $units;
    }

    private function generateDonationSummary($userScope, $dateRange, $type)
    {
        $filters = [
            'date_range' => $dateRange,
            'type' => $type,
        ];

        $donations = $this->getFilteredDonationRows($userScope, $filters);
        $normalized = $this->normalizeDonationRows($donations);

        return [
            'date_range' => $dateRange,
            'type' => $type,
            'total_amount' => array_sum(array_column($normalized, 'amount')),
            'total_donations' => count($normalized),
            'average_amount' => count($normalized) > 0 ? array_sum(array_column($normalized, 'amount')) / count($normalized) : 0,
            'by_type' => $this->summarizeDonationGroups($normalized, 'type'),
            'by_category' => $this->summarizeDonationGroups($normalized, 'category'),
            'recent_donations' => array_slice($normalized, 0, 10)
        ];
    }

    private function generateDetailedDonationReport($userScope, $filters)
    {
        $normalized = $this->normalizeDonationRows($this->getFilteredDonationRows($userScope, $filters));

        return [
            'donations' => $normalized,
            'summary' => [
                'total_amount' => array_sum(array_column($normalized, 'amount')),
                'total_donations' => count($normalized),
                'average_amount' => count($normalized) > 0 ? array_sum(array_column($normalized, 'amount')) / count($normalized) : 0,
            ],
            'breakdowns' => [
                'type' => $this->summarizeDonationGroups($normalized, 'type'),
                'category' => $this->summarizeDonationGroups($normalized, 'category'),
                'status' => $this->summarizeDonationGroups($normalized, 'status'),
            ],
        ];
    }

    private function generateExportData($userScope, $type)
    {
        $filters = [
            'date_range' => $_GET['date_range'] ?? '30_days',
            'type' => $_GET['type'] ?? 'all',
            'category' => $_GET['category'] ?? 'all',
            'status' => $_GET['status'] ?? 'all',
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
        ];

        $normalized = $this->normalizeDonationRows($this->getFilteredDonationRows($userScope, $filters));

        if ($type === 'summary') {
            return [
                [
                    'Scope' => $userScope['scope_name'] ?? 'Current scope',
                    'Level' => ucfirst($userScope['level_scope'] ?? 'all'),
                    'Total Donations' => count($normalized),
                    'Total Amount' => number_format(array_sum(array_column($normalized, 'amount')), 2, '.', ''),
                    'Average Amount' => number_format(count($normalized) > 0 ? array_sum(array_column($normalized, 'amount')) / count($normalized) : 0, 2, '.', ''),
                ],
            ];
        }

        return array_map(static function ($row) {
            return [
                'Reference' => $row['reference'],
                'Donor' => $row['donor'],
                'Amount' => number_format($row['amount'], 2, '.', ''),
                'Type' => $row['type'],
                'Category' => $row['category'],
                'Status' => $row['status'],
                'Date' => $row['date'],
                'Scope' => $row['scope'],
            ];
        }, $normalized);
    }

    private function exportAsCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    }

    private function exportAsPdf($data, $filename)
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.txt"');

        foreach ($data as $row) {
            echo implode(' | ', array_map('strval', $row)) . PHP_EOL;
        }
        exit;
    }

    private function getFilteredDonationRows(array $userScope, array $filters = []): array
    {
        $rows = $this->getDonationsForUserScope($userScope);

        return array_values(array_filter($rows, function (array $row) use ($filters) {
            $type = strtolower((string) ($this->resolveDonationField($row, ['type', 'donation_type', 'donor_type']) ?? ''));
            $category = strtolower((string) ($this->resolveDonationField($row, ['category', 'donation_purpose']) ?? ''));
            $status = strtolower((string) ($this->resolveDonationField($row, ['status', 'payment_status']) ?? ''));
            $date = (string) ($this->resolveDonationField($row, ['donation_date', 'payment_date', 'created_at']) ?? '');

            if (!empty($filters['type']) && $filters['type'] !== 'all' && $type !== strtolower((string) $filters['type'])) {
                return false;
            }

            if (!empty($filters['category']) && $filters['category'] !== 'all' && $category !== strtolower((string) $filters['category'])) {
                return false;
            }

            if (!empty($filters['status']) && $filters['status'] !== 'all' && $status !== strtolower((string) $filters['status'])) {
                return false;
            }

            if (!$this->matchesDonationDateFilter($date, $filters)) {
                return false;
            }

            return true;
        }));
    }

    private function normalizeDonationRows(array $rows): array
    {
        return array_map(function (array $row) {
            $donor = trim((string) (($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')));
            if ($donor === '') {
                $donor = (string) ($this->resolveDonationField($row, ['donor_name', 'group_name', 'organization_name', 'email']) ?? 'Unknown donor');
            }

            $reference = (string) ($this->resolveDonationField($row, ['reference_number', 'donation_number', 'receipt_number', 'uuid']) ?? 'No reference');
            $type = (string) ($this->resolveDonationField($row, ['type', 'donation_type', 'donor_type']) ?? 'General');
            $category = (string) ($this->resolveDonationField($row, ['category', 'donation_purpose']) ?? 'General');
            $status = (string) ($this->resolveDonationField($row, ['status', 'payment_status']) ?? 'n/a');
            $date = (string) ($this->resolveDonationField($row, ['donation_date', 'payment_date', 'created_at']) ?? date('Y-m-d'));
            $scope = (string) ($this->resolveDonationField($row, ['gurmu_name', 'gamta_name', 'godina_name', 'level_scope']) ?? 'Current scope');

            return [
                'reference' => $reference,
                'donor' => $donor,
                'amount' => (float) ($row['amount'] ?? 0),
                'type' => ucfirst($type),
                'category' => ucfirst(str_replace('_', ' ', $category)),
                'status' => ucfirst(str_replace('_', ' ', $status)),
                'date' => date('Y-m-d', strtotime($date)),
                'scope' => ucfirst((string) $scope),
            ];
        }, $rows);
    }

    private function summarizeDonationGroups(array $rows, string $field): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = (string) ($row[$field] ?? 'Unknown');
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' => $key,
                    'count' => 0,
                    'amount' => 0.0,
                ];
            }

            $groups[$key]['count']++;
            $groups[$key]['amount'] += (float) ($row['amount'] ?? 0);
        }

        return array_values($groups);
    }

    private function resolveDonationField(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    private function matchesDonationDateFilter(string $date, array $filters): bool
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return true;
        }

        if (!empty($filters['start_date']) && $timestamp < strtotime((string) $filters['start_date'] . ' 00:00:00')) {
            return false;
        }

        if (!empty($filters['end_date']) && $timestamp > strtotime((string) $filters['end_date'] . ' 23:59:59')) {
            return false;
        }

        $range = $filters['date_range'] ?? null;
        if (empty($range) || $range === 'all') {
            return true;
        }

        $start = null;
        switch ($range) {
            case '7_days':
                $start = strtotime('-7 days');
                break;
            case '30_days':
                $start = strtotime('-30 days');
                break;
            case '90_days':
                $start = strtotime('-90 days');
                break;
            case 'this_month':
                $start = strtotime(date('Y-m-01 00:00:00'));
                break;
            case 'this_year':
                $start = strtotime(date('Y-01-01 00:00:00'));
                break;
        }

        return $start === null || $timestamp >= $start;
    }
}