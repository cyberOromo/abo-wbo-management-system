<?php
namespace App\Models;

use App\Core\Model;

/**
 * Donor Model
 * Manages donor information and relationships
 * Part of the Finance Management Module
 */
class Donor extends Model
{
    protected $table = 'donors';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'donor_type', 'first_name', 'last_name', 'group_name', 'organization_name',
        'email', 'phone', 'address', 'city', 'state_province', 'country', 'postal_code',
        'date_of_birth', 'gender', 'occupation', 'employer', 'preferred_contact',
        'communication_preferences', 'tax_id', 'is_anonymous', 'notes', 'metadata'
    ];
    
    protected $dates = [
        'date_of_birth', 'first_donation_date', 'last_donation_date', 'created_at', 'updated_at'
    ];
    
    protected $casts = [
        'is_anonymous' => 'boolean',
        'total_donated' => 'decimal',
        'donation_count' => 'integer',
        'communication_preferences' => 'json',
        'metadata' => 'json'
    ];
    
    /**
     * Get all donations for this donor
     */
    public function donations()
    {
        return $this->hasMany('App\Models\Donation', 'donor_id');
    }
    
    /**
     * Get the user who created this donor record
     */
    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
    
    /**
     * Get donor's full display name
     */
    public function getDisplayName()
    {
        if ($this->is_anonymous) {
            return 'Anonymous Donor';
        }
        
        switch ($this->donor_type) {
            case 'individual':
                return trim($this->first_name . ' ' . $this->last_name);
            case 'group':
                return $this->group_name;
            case 'organization':
            case 'business':
                return $this->organization_name;
            default:
                return 'Unknown Donor';
        }
    }
    
    /**
     * Get formatted address
     */
    public function getFormattedAddress()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state_province,
            $this->country,
            $this->postal_code
        ]);
        
        return implode(', ', $parts);
    }
    
    /**
     * Get donor statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_donations,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_donations,
                    SUM(CASE WHEN status = 'approved' THEN amount_usd ELSE 0 END) as total_amount,
                    MIN(payment_date) as first_donation_date,
                    MAX(payment_date) as last_donation_date,
                    AVG(CASE WHEN status = 'approved' THEN amount_usd ELSE NULL END) as average_donation,
                    COUNT(DISTINCT YEAR(payment_date)) as active_years
                FROM donations 
                WHERE donor_id = ? AND status = 'approved'";
        
        $result = $this->query($sql, [$this->id]);
        return $result[0] ?? [];
    }
    
    /**
     * Get recent donations
     */
    public function getRecentDonations($limit = 10)
    {
        $sql = "SELECT d.*, dc.name as campaign_name
                FROM donations d
                LEFT JOIN donation_campaigns dc ON d.campaign_id = dc.id
                WHERE d.donor_id = ?
                ORDER BY d.payment_date DESC, d.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$this->id, $limit]);
    }
    
    /**
     * Get donation history by year
     */
    public function getDonationsByYear()
    {
        $sql = "SELECT 
                    YEAR(payment_date) as year,
                    COUNT(*) as donation_count,
                    SUM(amount_usd) as total_amount
                FROM donations 
                WHERE donor_id = ? AND status = 'approved'
                GROUP BY YEAR(payment_date)
                ORDER BY year DESC";
        
        return $this->query($sql, [$this->id]);
    }
    
    /**
     * Get donations by campaign
     */
    public function getDonationsByCampaign()
    {
        $sql = "SELECT 
                    dc.name as campaign_name,
                    COUNT(d.id) as donation_count,
                    SUM(d.amount_usd) as total_amount
                FROM donations d
                LEFT JOIN donation_campaigns dc ON d.campaign_id = dc.id
                WHERE d.donor_id = ? AND d.status = 'approved'
                GROUP BY d.campaign_id, dc.name
                ORDER BY total_amount DESC";
        
        return $this->query($sql, [$this->id]);
    }
    
    /**
     * Check if donor is a major donor
     */
    public function isMajorDonor($threshold = 1000)
    {
        return $this->total_donated >= $threshold;
    }
    
    /**
     * Check if donor is a recurring donor
     */
    public function isRecurringDonor()
    {
        return $this->donation_count >= 2;
    }
    
    /**
     * Get donor's tax receipts for a year
     */
    public function getTaxReceipts($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        
        $sql = "SELECT * FROM donations 
                WHERE donor_id = ? AND status = 'approved' 
                AND tax_deductible = 1 AND tax_year = ?
                ORDER BY payment_date";
        
        return $this->query($sql, [$this->id, $year]);
    }
    
    /**
     * Generate consolidated tax receipt for the year
     */
    public function generateConsolidatedTaxReceipt($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        
        $donations = $this->getTaxReceipts($year);
        
        if (empty($donations)) {
            throw new \Exception("No tax-deductible donations found for {$year}");
        }
        
        $totalAmount = array_sum(array_column($donations, 'amount_usd'));
        
        // Generate consolidated receipt data
        $receiptData = [
            'donor' => [
                'name' => $this->getDisplayName(),
                'email' => $this->email,
                'address' => $this->getFormattedAddress(),
                'tax_id' => $this->tax_id
            ],
            'year' => $year,
            'total_amount' => $totalAmount,
            'total_donations' => count($donations),
            'donations' => $donations,
            'receipt_number' => "CONSOL-{$year}-" . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'generated_date' => date('Y-m-d')
        ];
        
        return $receiptData;
    }
    
    /**
     * Update communication preferences
     */
    public function updateCommunicationPreferences($preferences)
    {
        $currentPrefs = $this->communication_preferences ?? [];
        $newPrefs = array_merge($currentPrefs, $preferences);
        
        $this->update(['communication_preferences' => $newPrefs]);
        
        return $newPrefs;
    }
    
    /**
     * Check if donor can receive communications of a type
     */
    public function canReceiveCommunication($type)
    {
        $prefs = $this->communication_preferences ?? [];
        
        // Default to true if no preferences set
        if (!isset($prefs[$type])) {
            return true;
        }
        
        return $prefs[$type];
    }
    
    /**
     * Get preferred contact method
     */
    public function getPreferredContact()
    {
        switch ($this->preferred_contact) {
            case 'email':
                return $this->email;
            case 'phone':
                return $this->phone;
            case 'mail':
                return $this->getFormattedAddress();
            default:
                return $this->email ?: $this->phone;
        }
    }
    
    /**
     * Search donors by various criteria
     */
    public static function search($criteria = [])
    {
        $sql = "SELECT * FROM donors WHERE status = 'active'";
        $params = [];
        
        if (!empty($criteria['name'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR group_name LIKE ? OR organization_name LIKE ?)";
            $searchTerm = '%' . $criteria['name'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($criteria['email'])) {
            $sql .= " AND email LIKE ?";
            $params[] = '%' . $criteria['email'] . '%';
        }
        
        if (!empty($criteria['phone'])) {
            $sql .= " AND phone LIKE ?";
            $params[] = '%' . $criteria['phone'] . '%';
        }
        
        if (!empty($criteria['donor_type'])) {
            $sql .= " AND donor_type = ?";
            $params[] = $criteria['donor_type'];
        }
        
        if (!empty($criteria['country'])) {
            $sql .= " AND country = ?";
            $params[] = $criteria['country'];
        }
        
        if (!empty($criteria['min_donated'])) {
            $sql .= " AND total_donated >= ?";
            $params[] = $criteria['min_donated'];
        }
        
        if (!empty($criteria['max_donated'])) {
            $sql .= " AND total_donated <= ?";
            $params[] = $criteria['max_donated'];
        }
        
        if (!empty($criteria['min_donations'])) {
            $sql .= " AND donation_count >= ?";
            $params[] = $criteria['min_donations'];
        }
        
        $sql .= " ORDER BY total_donated DESC, last_donation_date DESC";
        
        if (!empty($criteria['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$criteria['limit'];
        }
        
        return static::query($sql, $params);
    }
    
    /**
     * Get top donors
     */
    public static function getTopDonors($limit = 20, $timeframe = null)
    {
        $sql = "SELECT d.*, 
                    COUNT(dn.id) as donation_count,
                    SUM(dn.amount_usd) as total_donated
                FROM donors d
                INNER JOIN donations dn ON d.id = dn.donor_id
                WHERE dn.status = 'approved' AND d.status = 'active'";
        
        $params = [];
        
        if ($timeframe) {
            switch ($timeframe) {
                case 'year':
                    $sql .= " AND dn.payment_date >= CURDATE() - INTERVAL 1 YEAR";
                    break;
                case 'month':
                    $sql .= " AND dn.payment_date >= CURDATE() - INTERVAL 1 MONTH";
                    break;
                case 'quarter':
                    $sql .= " AND dn.payment_date >= CURDATE() - INTERVAL 3 MONTH";
                    break;
            }
        }
        
        $sql .= " GROUP BY d.id
                 ORDER BY total_donated DESC
                 LIMIT ?";
        
        $params[] = $limit;
        
        return static::query($sql, $params);
    }
    
    /**
     * Get donors by geographic distribution
     */
    public static function getGeographicDistribution()
    {
        $sql = "SELECT 
                    country,
                    COUNT(*) as donor_count,
                    SUM(total_donated) as total_donations
                FROM donors 
                WHERE status = 'active' AND country IS NOT NULL
                GROUP BY country
                ORDER BY total_donations DESC";
        
        return static::query($sql);
    }
    
    /**
     * Merge duplicate donors
     */
    public function mergeDuplicates($duplicateIds)
    {
        if (empty($duplicateIds)) {
            return false;
        }
        
        // Start transaction
        $this->beginTransaction();
        
        try {
            // Update donations to point to this donor
            $placeholders = str_repeat('?,', count($duplicateIds) - 1) . '?';
            $sql = "UPDATE donations SET donor_id = ? WHERE donor_id IN ({$placeholders})";
            $params = array_merge([$this->id], $duplicateIds);
            $this->query($sql, $params);
            
            // Aggregate data from duplicate donors
            $sql = "SELECT 
                        SUM(total_donated) as total_donated,
                        SUM(donation_count) as donation_count,
                        MIN(first_donation_date) as first_donation_date,
                        MAX(last_donation_date) as last_donation_date
                    FROM donors 
                    WHERE id IN ({$placeholders})";
            
            $aggregateData = $this->query($sql, $duplicateIds)[0];
            
            // Update this donor with aggregated data
            $this->update([
                'total_donated' => $this->total_donated + $aggregateData['total_donated'],
                'donation_count' => $this->donation_count + $aggregateData['donation_count'],
                'first_donation_date' => min($this->first_donation_date, $aggregateData['first_donation_date']),
                'last_donation_date' => max($this->last_donation_date, $aggregateData['last_donation_date'])
            ]);
            
            // Delete duplicate donors
            $sql = "DELETE FROM donors WHERE id IN ({$placeholders})";
            $this->query($sql, $duplicateIds);
            
            $this->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Find potential duplicate donors
     */
    public static function findPotentialDuplicates()
    {
        $sql = "SELECT 
                    GROUP_CONCAT(id) as donor_ids,
                    email,
                    COUNT(*) as duplicate_count
                FROM donors 
                WHERE status = 'active' AND email IS NOT NULL AND email != ''
                GROUP BY email
                HAVING COUNT(*) > 1
                UNION
                SELECT 
                    GROUP_CONCAT(id) as donor_ids,
                    CONCAT(first_name, ' ', last_name) as name,
                    COUNT(*) as duplicate_count
                FROM donors 
                WHERE status = 'active' AND first_name IS NOT NULL AND last_name IS NOT NULL
                GROUP BY first_name, last_name
                HAVING COUNT(*) > 1";
        
        return static::query($sql);
    }
    
    /**
     * Validation rules
     */
    public static function validationRules()
    {
        return [
            'donor_type' => ['required', 'in:individual,group,organization,business'],
            'first_name' => ['required_if:donor_type,individual', 'string', 'max:255'],
            'last_name' => ['required_if:donor_type,individual', 'string', 'max:255'],
            'group_name' => ['required_if:donor_type,group', 'string', 'max:255'],
            'organization_name' => ['required_if:donor_type,organization,business', 'string', 'max:255'],
            'email' => ['email', 'max:255'],
            'phone' => ['string', 'max:50'],
            'date_of_birth' => ['date', 'before:today'],
            'gender' => ['in:male,female,other'],
            'preferred_contact' => ['in:email,phone,mail'],
            'country' => ['string', 'max:255'],
            'postal_code' => ['string', 'max:20']
        ];
    }
    
    /**
     * Get validation rules for specific donor type
     */
    public static function validationRulesForType($donorType)
    {
        $baseRules = static::validationRules();
        
        switch ($donorType) {
            case 'individual':
                $baseRules['first_name'][0] = 'required';
                $baseRules['last_name'][0] = 'required';
                break;
            case 'group':
                $baseRules['group_name'][0] = 'required';
                break;
            case 'organization':
            case 'business':
                $baseRules['organization_name'][0] = 'required';
                break;
        }
        
        return $baseRules;
    }
}