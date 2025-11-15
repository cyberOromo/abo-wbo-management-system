<?php

namespace App\Repositories;

use PDO;

/**
 * DonationRepository - Donation data access layer
 * 
 * Handles all database operations related to donations including
 * payment processing, receipt generation, and financial reporting.
 * 
 * @package App\Repositories
 * @version 1.0.0
 */
class DonationRepository extends BaseRepository
{
    protected string $table = 'donations';
    protected array $fillable = [
        'uuid', 'donor_type', 'donor_name', 'donor_email', 'donor_phone', 'member_id',
        'amount', 'currency', 'donation_type', 'payment_method', 'payment_status',
        'payment_transaction_id', 'payment_gateway_response', 'receipt_number',
        'receipt_generated', 'event_id', 'project_id', 'gurmu_id', 'is_anonymous',
        'notes', 'processed_by', 'processed_at', 'refunded_at', 'refund_reason'
    ];
    protected array $casts = [
        'amount' => 'float',
        'is_anonymous' => 'boolean',
        'receipt_generated' => 'boolean',
        'payment_gateway_response' => 'json',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    /**
     * Get donations by hierarchy level
     */
    public function getByHierarchyLevel(string $level, int $scopeId, array $filters = []): array
    {
        $this->resetQuery();
        
        switch ($level) {
            case 'gurmu':
                $this->where('gurmu_id', $scopeId);
                break;
            case 'gamta':
                $this->join('gurmus gu', 'donations.gurmu_id', '=', 'gu.id')
                     ->where('gu.gamta_id', $scopeId);
                break;
            case 'godina':
                $this->join('gurmus gu', 'donations.gurmu_id', '=', 'gu.id')
                     ->join('gamtas ga', 'gu.gamta_id', '=', 'ga.id')
                     ->where('ga.godina_id', $scopeId);
                break;
        }
        
        return $this->applyFilters($filters)->get();
    }

    /**
     * Get donations by member
     */
    public function getByMember(int $memberId, array $filters = []): array
    {
        $this->resetQuery();
        $this->where('member_id', $memberId);
        
        return $this->applyFilters($filters)->get();
    }

    /**
     * Get pending donations needing approval
     */
    public function getPendingApprovals(string $level = 'global', ?int $scopeId = null): array
    {
        $this->resetQuery();
        $this->where('payment_status', 'pending');
        
        if ($level !== 'global' && $scopeId) {
            switch ($level) {
                case 'gurmu':
                    $this->where('gurmu_id', $scopeId);
                    break;
                case 'gamta':
                    $this->join('gurmus gu', 'donations.gurmu_id', '=', 'gu.id')
                         ->where('gu.gamta_id', $scopeId);
                    break;
                case 'godina':
                    $this->join('gurmus gu', 'donations.gurmu_id', '=', 'gu.id')
                         ->join('gamtas ga', 'gu.gamta_id', '=', 'ga.id')
                         ->where('ga.godina_id', $scopeId);
                    break;
            }
        }
        
        return $this->orderBy('created_at')->get();
    }

    /**
     * Get donation statistics
     */
    public function getStatistics(string $level = 'global', ?int $scopeId = null, array $dateRange = []): array
    {
        $baseWhere = "WHERE deleted_at IS NULL";
        $joinClause = "";
        
        // Apply hierarchy filtering
        if ($level !== 'global' && $scopeId) {
            switch ($level) {
                case 'gurmu':
                    $baseWhere .= " AND gurmu_id = {$scopeId}";
                    break;
                case 'gamta':
                    $joinClause = "JOIN gurmus gu ON donations.gurmu_id = gu.id";
                    $baseWhere .= " AND gu.gamta_id = {$scopeId}";
                    break;
                case 'godina':
                    $joinClause = "JOIN gurmus gu ON donations.gurmu_id = gu.id JOIN gamtas ga ON gu.gamta_id = ga.id";
                    $baseWhere .= " AND ga.godina_id = {$scopeId}";
                    break;
            }
        }
        
        // Apply date range
        if (!empty($dateRange['from'])) {
            $baseWhere .= " AND created_at >= '{$dateRange['from']}'";
        }
        if (!empty($dateRange['to'])) {
            $baseWhere .= " AND created_at <= '{$dateRange['to']} 23:59:59'";
        }
        
        // Total amount and count
        $totalSql = "SELECT SUM(amount) as total_amount, COUNT(*) as total_count FROM donations {$joinClause} {$baseWhere} AND payment_status = 'completed'";
        $stmt = $this->db->prepare($totalSql);
        $stmt->execute();
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // By status
        $statusSql = "SELECT payment_status, COUNT(*) as count, SUM(amount) as amount FROM donations {$joinClause} {$baseWhere} GROUP BY payment_status";
        $stmt = $this->db->prepare($statusSql);
        $stmt->execute();
        $statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // By type
        $typeSql = "SELECT donation_type, COUNT(*) as count, SUM(amount) as amount FROM donations {$joinClause} {$baseWhere} AND payment_status = 'completed' GROUP BY donation_type";
        $stmt = $this->db->prepare($typeSql);
        $stmt->execute();
        $typeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // By currency
        $currencySql = "SELECT currency, SUM(amount) as amount FROM donations {$joinClause} {$baseWhere} AND payment_status = 'completed' GROUP BY currency";
        $stmt = $this->db->prepare($currencySql);
        $stmt->execute();
        $currencyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Monthly trends (last 12 months)
        $trendSql = "
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                   SUM(amount) as amount, 
                   COUNT(*) as count
            FROM donations {$joinClause}
            {$baseWhere} AND payment_status = 'completed'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ";
        $stmt = $this->db->prepare($trendSql);
        $stmt->execute();
        $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'total_amount' => $totals['total_amount'] ?? 0,
            'total_count' => $totals['total_count'] ?? 0,
            'by_status' => $statusStats,
            'by_type' => $typeStats,
            'by_currency' => $currencyStats,
            'monthly_trends' => $trends
        ];
    }

    /**
     * Process donation payment
     */
    public function processPayment(int $donationId, string $transactionId, array $gatewayResponse, int $processedBy): bool
    {
        $data = [
            'payment_status' => 'completed',
            'payment_transaction_id' => $transactionId,
            'payment_gateway_response' => json_encode($gatewayResponse),
            'processed_by' => $processedBy,
            'processed_at' => date('Y-m-d H:i:s')
        ];
        
        // Generate receipt number if not exists
        $donation = $this->find($donationId);
        if (!$donation['receipt_number']) {
            $data['receipt_number'] = $this->generateReceiptNumber();
        }
        
        return $this->update($donationId, $data);
    }

    /**
     * Mark donation as refunded
     */
    public function markAsRefunded(int $donationId, string $reason, int $refundedBy): bool
    {
        return $this->update($donationId, [
            'payment_status' => 'refunded',
            'refund_reason' => $reason,
            'refunded_by' => $refundedBy,
            'refunded_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get next sequence number for the month
        $sql = "SELECT COUNT(*) + 1 as next_num FROM donations WHERE receipt_number LIKE ? AND receipt_number IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["RCP-{$year}{$month}-%"]);
        $nextNum = $stmt->fetchColumn();
        
        return sprintf("RCP-%s%s-%04d", $year, $month, $nextNum);
    }

    /**
     * Mark receipt as generated
     */
    public function markReceiptGenerated(int $donationId): bool
    {
        return $this->update($donationId, ['receipt_generated' => true]);
    }

    /**
     * Get donations for receipt generation
     */
    public function getDonationsForReceiptGeneration(): array
    {
        return $this->where('payment_status', 'completed')
                    ->where('receipt_generated', false)
                    ->whereNotNull('receipt_number')
                    ->get();
    }

    /**
     * Get top donors
     */
    public function getTopDonors(int $limit = 10, string $level = 'global', ?int $scopeId = null): array
    {
        $joinClause = "";
        $whereClause = "WHERE d.payment_status = 'completed' AND d.deleted_at IS NULL";
        
        // Apply hierarchy filtering
        if ($level !== 'global' && $scopeId) {
            switch ($level) {
                case 'gurmu':
                    $whereClause .= " AND d.gurmu_id = {$scopeId}";
                    break;
                case 'gamta':
                    $joinClause .= " JOIN gurmus gu ON d.gurmu_id = gu.id";
                    $whereClause .= " AND gu.gamta_id = {$scopeId}";
                    break;
                case 'godina':
                    $joinClause .= " JOIN gurmus gu ON d.gurmu_id = gu.id JOIN gamtas ga ON gu.gamta_id = ga.id";
                    $whereClause .= " AND ga.godina_id = {$scopeId}";
                    break;
            }
        }
        
        $sql = "
            SELECT 
                CASE 
                    WHEN d.member_id IS NOT NULL THEN CONCAT(u.first_name, ' ', u.last_name)
                    WHEN d.is_anonymous = 1 THEN 'Anonymous Donor'
                    ELSE d.donor_name
                END as donor_name,
                d.member_id,
                SUM(d.amount) as total_amount,
                COUNT(*) as donation_count,
                MAX(d.created_at) as last_donation_date
            FROM donations d
            LEFT JOIN users u ON d.member_id = u.id
            {$joinClause}
            {$whereClause}
            GROUP BY d.member_id, d.donor_name, d.is_anonymous
            ORDER BY total_amount DESC
            LIMIT {$limit}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get donation report data
     */
    public function getReportData(array $filters): array
    {
        $this->resetQuery();
        
        // Apply filters
        if (!empty($filters['date_from'])) {
            $this->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $this->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
        
        if (!empty($filters['donation_type'])) {
            $this->where('donation_type', $filters['donation_type']);
        }
        
        if (!empty($filters['payment_status'])) {
            $this->where('payment_status', $filters['payment_status']);
        }
        
        if (!empty($filters['currency'])) {
            $this->where('currency', $filters['currency']);
        }
        
        if (!empty($filters['min_amount'])) {
            $this->where('amount', '>=', $filters['min_amount']);
        }
        
        if (!empty($filters['max_amount'])) {
            $this->where('amount', '<=', $filters['max_amount']);
        }
        
        // Apply hierarchy filter
        if (!empty($filters['gurmu_id'])) {
            $this->where('gurmu_id', $filters['gurmu_id']);
        } elseif (!empty($filters['gamta_id'])) {
            $this->join('gurmus gu', 'donations.gurmu_id', '=', 'gu.id')
                 ->where('gu.gamta_id', $filters['gamta_id']);
        } elseif (!empty($filters['godina_id'])) {
            $this->join('gurmus gu', 'donations.gurmu_id', '=', 'gu.id')
                 ->join('gamtas ga', 'gu.gamta_id', '=', 'ga.id')
                 ->where('ga.godina_id', $filters['godina_id']);
        }
        
        return $this->orderBy('created_at', 'desc')->get();
    }

    /**
     * Apply common filters
     */
    private function applyFilters(array $filters): self
    {
        if (!empty($filters['payment_status'])) {
            if (is_array($filters['payment_status'])) {
                $this->whereIn('payment_status', $filters['payment_status']);
            } else {
                $this->where('payment_status', $filters['payment_status']);
            }
        }
        
        if (!empty($filters['donation_type'])) {
            $this->where('donation_type', $filters['donation_type']);
        }
        
        if (!empty($filters['donor_type'])) {
            $this->where('donor_type', $filters['donor_type']);
        }
        
        if (!empty($filters['currency'])) {
            $this->where('currency', $filters['currency']);
        }
        
        if (!empty($filters['date_from'])) {
            $this->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $this->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
        
        if (!empty($filters['min_amount'])) {
            $this->where('amount', '>=', $filters['min_amount']);
        }
        
        if (!empty($filters['max_amount'])) {
            $this->where('amount', '<=', $filters['max_amount']);
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $this->whereLike('donor_name', $search)
                 ->orWhereLike('donor_email', $search)
                 ->orWhereLike('notes', $search);
        }
        
        return $this->orderBy('created_at', 'desc');
    }
}