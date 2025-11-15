<?php
namespace App\Models;

use App\Core\Model;

/**
 * Donation Campaign Model
 * Manages donation campaigns and events
 * Part of the Finance Management Module
 */
class DonationCampaign extends Model
{
    protected $table = 'donation_campaigns';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name', 'code', 'description', 'campaign_type', 'target_amount', 'currency',
        'start_date', 'end_date', 'status', 'visibility', 'level_scope',
        'global_id', 'godina_id', 'gamta_id', 'gurmu_id', 'allow_anonymous',
        'min_donation_amount', 'max_donation_amount', 'enable_recurring',
        'auto_receipt', 'public_donor_list', 'banner_image', 'featured_image',
        'progress_image', 'metadata'
    ];
    
    protected $dates = [
        'start_date', 'end_date', 'created_at', 'updated_at'
    ];
    
    protected $casts = [
        'target_amount' => 'decimal',
        'raised_amount' => 'decimal',
        'min_donation_amount' => 'decimal',
        'max_donation_amount' => 'decimal',
        'allow_anonymous' => 'boolean',
        'enable_recurring' => 'boolean',
        'auto_receipt' => 'boolean',
        'public_donor_list' => 'boolean',
        'metadata' => 'json'
    ];
    
    /**
     * Get all donations for this campaign
     */
    public function donations()
    {
        return $this->hasMany('App\Models\Donation', 'campaign_id');
    }
    
    /**
     * Get the user who created this campaign
     */
    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
    
    /**
     * Get the global organization
     */
    public function global()
    {
        return $this->belongsTo('App\Models\Global', 'global_id');
    }
    
    /**
     * Get the godina
     */
    public function godina()
    {
        return $this->belongsTo('App\Models\Godina', 'godina_id');
    }
    
    /**
     * Get the gamta
     */
    public function gamta()
    {
        return $this->belongsTo('App\Models\Gamta', 'gamta_id');
    }
    
    /**
     * Get the gurmu
     */
    public function gurmu()
    {
        return $this->belongsTo('App\Models\Gurmu', 'gurmu_id');
    }
    
    /**
     * Calculate campaign progress percentage
     */
    public function getProgressPercentage()
    {
        if (!$this->target_amount || $this->target_amount <= 0) {
            return 0;
        }
        
        return min(100, round(($this->raised_amount / $this->target_amount) * 100, 2));
    }
    
    /**
     * Get remaining amount to reach target
     */
    public function getRemainingAmount()
    {
        if (!$this->target_amount) {
            return null;
        }
        
        return max(0, $this->target_amount - $this->raised_amount);
    }
    
    /**
     * Check if campaign is active
     */
    public function isActive()
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        $now = date('Y-m-d');
        
        if ($this->start_date && $now < $this->start_date) {
            return false;
        }
        
        if ($this->end_date && $now > $this->end_date) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if campaign has ended
     */
    public function hasEnded()
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return true;
        }
        
        if ($this->end_date && date('Y-m-d') > $this->end_date) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get campaign statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_donations,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_donations,
                    SUM(CASE WHEN status = 'pending_approval' THEN 1 ELSE 0 END) as pending_donations,
                    SUM(CASE WHEN status = 'approved' THEN amount_usd ELSE 0 END) as total_raised,
                    AVG(CASE WHEN status = 'approved' THEN amount_usd ELSE NULL END) as average_donation,
                    COUNT(DISTINCT donor_id) as unique_donors,
                    MIN(payment_date) as first_donation_date,
                    MAX(payment_date) as last_donation_date
                FROM donations 
                WHERE campaign_id = ?";
        
        $result = $this->query($sql, [$this->id]);
        $stats = $result[0] ?? [];
        
        // Add calculated fields
        $stats['progress_percentage'] = $this->getProgressPercentage();
        $stats['remaining_amount'] = $this->getRemainingAmount();
        $stats['is_active'] = $this->isActive();
        $stats['has_ended'] = $this->hasEnded();
        $stats['days_remaining'] = $this->getDaysRemaining();
        
        return $stats;
    }
    
    /**
     * Get days remaining in campaign
     */
    public function getDaysRemaining()
    {
        if (!$this->end_date) {
            return null;
        }
        
        $now = new \DateTime();
        $endDate = new \DateTime($this->end_date);
        
        if ($endDate < $now) {
            return 0;
        }
        
        return $now->diff($endDate)->days;
    }
    
    /**
     * Get top donors for this campaign
     */
    public function getTopDonors($limit = 10)
    {
        $sql = "SELECT 
                    d.donor_id,
                    dr.first_name, dr.last_name, dr.group_name, dr.organization_name,
                    dr.donor_type, dr.is_anonymous,
                    SUM(d.amount_usd) as total_donated,
                    COUNT(d.id) as donation_count,
                    MAX(d.payment_date) as last_donation_date
                FROM donations d
                INNER JOIN donors dr ON d.donor_id = dr.id
                WHERE d.campaign_id = ? AND d.status = 'approved'
                GROUP BY d.donor_id
                ORDER BY total_donated DESC
                LIMIT ?";
        
        return $this->query($sql, [$this->id, $limit]);
    }
    
    /**
     * Get public donor list (if enabled)
     */
    public function getPublicDonorList()
    {
        if (!$this->public_donor_list) {
            return [];
        }
        
        $sql = "SELECT 
                    dr.first_name, dr.last_name, dr.group_name, dr.organization_name,
                    dr.donor_type, dr.is_anonymous,
                    d.amount, d.currency, d.payment_date, d.dedication_name, d.dedication_message
                FROM donations d
                INNER JOIN donors dr ON d.donor_id = dr.id
                WHERE d.campaign_id = ? AND d.status = 'approved' 
                AND (dr.is_anonymous = 0 OR d.public_acknowledgment = 1)
                ORDER BY d.payment_date DESC";
        
        return $this->query($sql, [$this->id]);
    }
    
    /**
     * Get donation timeline for this campaign
     */
    public function getDonationTimeline()
    {
        $sql = "SELECT 
                    DATE(payment_date) as date,
                    COUNT(*) as donation_count,
                    SUM(amount_usd) as daily_total
                FROM donations 
                WHERE campaign_id = ? AND status = 'approved'
                GROUP BY DATE(payment_date)
                ORDER BY date";
        
        return $this->query($sql, [$this->id]);
    }
    
    /**
     * Update raised amount (usually called by trigger, but can be manually updated)
     */
    public function updateRaisedAmount()
    {
        $sql = "SELECT SUM(amount_usd) as total 
                FROM donations 
                WHERE campaign_id = ? AND status = 'approved'";
        
        $result = $this->query($sql, [$this->id]);
        $total = $result[0]['total'] ?? 0;
        
        $this->update(['raised_amount' => $total]);
        
        return $total;
    }
    
    /**
     * Generate campaign report
     */
    public function generateReport($format = 'array')
    {
        $stats = $this->getStatistics();
        $topDonors = $this->getTopDonors(20);
        $timeline = $this->getDonationTimeline();
        
        $report = [
            'campaign' => [
                'id' => $this->id,
                'name' => $this->name,
                'code' => $this->code,
                'type' => $this->campaign_type,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'target_amount' => $this->target_amount,
                'currency' => $this->currency,
                'status' => $this->status
            ],
            'statistics' => $stats,
            'top_donors' => $topDonors,
            'timeline' => $timeline,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($format === 'json') {
            return json_encode($report, JSON_PRETTY_PRINT);
        }
        
        return $report;
    }
    
    /**
     * Get campaigns by hierarchy scope
     */
    public static function getByHierarchyScope($levelScope, $hierarchyId, $filters = [])
    {
        $sql = "SELECT dc.*, 
                       u.first_name as created_by_first_name, u.last_name as created_by_last_name
                FROM donation_campaigns dc
                LEFT JOIN users u ON dc.created_by = u.id
                WHERE dc.level_scope = ? AND ";
        
        $params = [$levelScope];
        
        switch ($levelScope) {
            case 'global':
                $sql .= "dc.global_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'godina':
                $sql .= "dc.godina_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'gamta':
                $sql .= "dc.gamta_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'gurmu':
                $sql .= "dc.gurmu_id = ?";
                $params[] = $hierarchyId;
                break;
        }
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND dc.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['campaign_type'])) {
            $sql .= " AND dc.campaign_type = ?";
            $params[] = $filters['campaign_type'];
        }
        
        if (!empty($filters['visibility'])) {
            $sql .= " AND dc.visibility = ?";
            $params[] = $filters['visibility'];
        }
        
        if (!empty($filters['active_only'])) {
            $now = date('Y-m-d');
            $sql .= " AND dc.status = 'active' AND (dc.start_date IS NULL OR dc.start_date <= ?) AND (dc.end_date IS NULL OR dc.end_date >= ?)";
            $params[] = $now;
            $params[] = $now;
        }
        
        $sql .= " ORDER BY dc.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        return static::query($sql, $params);
    }
    
    /**
     * Get active campaigns
     */
    public static function getActiveCampaigns($hierarchyScope = null, $hierarchyId = null)
    {
        $sql = "SELECT * FROM donation_campaigns 
                WHERE status = 'active' 
                AND (start_date IS NULL OR start_date <= CURDATE())
                AND (end_date IS NULL OR end_date >= CURDATE())";
        
        $params = [];
        
        if ($hierarchyScope && $hierarchyId) {
            $sql .= " AND level_scope = ? AND {$hierarchyScope}_id = ?";
            $params[] = $hierarchyScope;
            $params[] = $hierarchyId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return static::query($sql, $params);
    }
    
    /**
     * Get featured campaigns
     */
    public static function getFeaturedCampaigns($limit = 5)
    {
        $sql = "SELECT dc.*, 
                       (dc.raised_amount / NULLIF(dc.target_amount, 0) * 100) as progress_percentage
                FROM donation_campaigns dc
                WHERE dc.status = 'active' 
                AND dc.visibility = 'public'
                AND (dc.start_date IS NULL OR dc.start_date <= CURDATE())
                AND (dc.end_date IS NULL OR dc.end_date >= CURDATE())
                AND JSON_EXTRACT(dc.metadata, '$.featured') = true
                ORDER BY dc.created_at DESC
                LIMIT ?";
        
        return static::query($sql, [$limit]);
    }
    
    /**
     * Search campaigns
     */
    public static function search($criteria = [])
    {
        $sql = "SELECT dc.*, 
                       (dc.raised_amount / NULLIF(dc.target_amount, 0) * 100) as progress_percentage,
                       u.first_name as created_by_first_name, u.last_name as created_by_last_name
                FROM donation_campaigns dc
                LEFT JOIN users u ON dc.created_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($criteria['name'])) {
            $sql .= " AND (dc.name LIKE ? OR dc.description LIKE ?)";
            $searchTerm = '%' . $criteria['name'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($criteria['code'])) {
            $sql .= " AND dc.code LIKE ?";
            $params[] = '%' . $criteria['code'] . '%';
        }
        
        if (!empty($criteria['campaign_type'])) {
            $sql .= " AND dc.campaign_type = ?";
            $params[] = $criteria['campaign_type'];
        }
        
        if (!empty($criteria['status'])) {
            $sql .= " AND dc.status = ?";
            $params[] = $criteria['status'];
        }
        
        if (!empty($criteria['level_scope'])) {
            $sql .= " AND dc.level_scope = ?";
            $params[] = $criteria['level_scope'];
        }
        
        if (!empty($criteria['date_from'])) {
            $sql .= " AND dc.start_date >= ?";
            $params[] = $criteria['date_from'];
        }
        
        if (!empty($criteria['date_to'])) {
            $sql .= " AND dc.end_date <= ?";
            $params[] = $criteria['date_to'];
        }
        
        $sql .= " ORDER BY dc.created_at DESC";
        
        if (!empty($criteria['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$criteria['limit'];
        }
        
        return static::query($sql, $params);
    }
    
    /**
     * Generate unique campaign code
     */
    public static function generateCampaignCode($name)
    {
        // Create base code from name
        $baseCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $baseCode = substr($baseCode, 0, 8);
        
        if (strlen($baseCode) < 3) {
            $baseCode = 'CAMPAIGN';
        }
        
        $year = date('Y');
        $code = "{$baseCode}-{$year}";
        
        // Check for uniqueness and add number if needed
        $counter = 1;
        $originalCode = $code;
        
        while (static::where('code', $code)->first()) {
            $code = $originalCode . "-{$counter}";
            $counter++;
        }
        
        return $code;
    }
    
    /**
     * Clone campaign for new period
     */
    public function cloneForNewPeriod($newStartDate, $newEndDate, $newName = null)
    {
        $newCampaignData = $this->toArray();
        
        // Remove fields that shouldn't be copied
        unset($newCampaignData['id']);
        unset($newCampaignData['raised_amount']);
        unset($newCampaignData['created_at']);
        unset($newCampaignData['updated_at']);
        
        // Set new values
        $newCampaignData['name'] = $newName ?: $this->name . ' (Copy)';
        $newCampaignData['code'] = static::generateCampaignCode($newCampaignData['name']);
        $newCampaignData['start_date'] = $newStartDate;
        $newCampaignData['end_date'] = $newEndDate;
        $newCampaignData['status'] = 'draft';
        $newCampaignData['raised_amount'] = 0.00;
        
        return static::create($newCampaignData);
    }
    
    /**
     * Get campaign performance compared to similar campaigns
     */
    public function getPerformanceComparison()
    {
        $sql = "SELECT 
                    AVG(raised_amount) as avg_raised,
                    AVG(raised_amount / NULLIF(target_amount, 0) * 100) as avg_progress,
                    COUNT(*) as total_campaigns
                FROM donation_campaigns 
                WHERE campaign_type = ? AND status IN ('completed', 'active')
                AND id != ?";
        
        $result = $this->query($sql, [$this->campaign_type, $this->id]);
        $comparison = $result[0] ?? [];
        
        if ($comparison['total_campaigns'] > 0) {
            $comparison['performance_vs_avg'] = $this->raised_amount - $comparison['avg_raised'];
            $comparison['progress_vs_avg'] = $this->getProgressPercentage() - $comparison['avg_progress'];
        }
        
        return $comparison;
    }
    
    /**
     * Validation rules
     */
    public static function validationRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:donation_campaigns,code'],
            'campaign_type' => ['required', 'in:general,emergency,project,memorial,annual,special'],
            'target_amount' => ['numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'start_date' => ['date'],
            'end_date' => ['date', 'after:start_date'],
            'status' => ['in:draft,active,paused,completed,cancelled'],
            'visibility' => ['in:public,members_only,private'],
            'level_scope' => ['required', 'in:global,godina,gamta,gurmu,cross_level'],
            'min_donation_amount' => ['numeric', 'min:0'],
            'max_donation_amount' => ['numeric', 'gte:min_donation_amount']
        ];
    }
}