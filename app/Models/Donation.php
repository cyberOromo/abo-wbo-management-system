<?php
namespace App\Models;

use App\Core\Model;

/**
 * Donation Model
 * Handles donation tracking, receipt generation, and financial management
 * Part of the Finance Management Module
 */
class Donation extends Model
{
    protected $table = 'donations';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'donor_id', 'donor_type', 'is_anonymous', 'campaign_id', 'event_name', 'event_date',
        'amount', 'currency', 'exchange_rate', 'payment_method', 'payment_reference',
        'payment_date', 'payment_status', 'level_scope', 'global_id', 'godina_id',
        'gamta_id', 'gurmu_id', 'submitted_by', 'donation_purpose', 'dedication_type',
        'dedication_name', 'dedication_message', 'public_acknowledgment', 'is_recurring',
        'recurring_frequency', 'recurring_end_date', 'parent_donation_id', 'notes',
        'internal_notes', 'metadata'
    ];
    
    protected $dates = [
        'event_date', 'payment_date', 'submitted_at', 'approved_at',
        'receipt_generated_at', 'receipt_sent_at', 'recurring_end_date',
        'created_at', 'updated_at'
    ];
    
    protected $casts = [
        'is_anonymous' => 'boolean',
        'amount' => 'decimal',
        'exchange_rate' => 'decimal',
        'tax_deductible' => 'boolean',
        'public_acknowledgment' => 'boolean',
        'is_recurring' => 'boolean',
        'metadata' => 'json'
    ];
    
    /**
     * Get the donor for this donation
     */
    public function donor()
    {
        return $this->belongsTo('App\Models\Donor', 'donor_id');
    }
    
    /**
     * Get the campaign for this donation
     */
    public function campaign()
    {
        return $this->belongsTo('App\Models\DonationCampaign', 'campaign_id');
    }
    
    /**
     * Get the user who submitted this donation
     */
    public function submittedBy()
    {
        return $this->belongsTo('App\Models\User', 'submitted_by');
    }
    
    /**
     * Get the user who approved this donation
     */
    public function approvedBy()
    {
        return $this->belongsTo('App\Models\User', 'approved_by');
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
     * Get child donations (for recurring donations)
     */
    public function childDonations()
    {
        return $this->hasMany('App\Models\Donation', 'parent_donation_id');
    }
    
    /**
     * Get parent donation (for recurring donations)
     */
    public function parentDonation()
    {
        return $this->belongsTo('App\Models\Donation', 'parent_donation_id');
    }
    
    /**
     * Generate unique donation number
     */
    public static function generateDonationNumber()
    {
        $year = date('Y');
        $prefix = "DON-{$year}-";
        
        // Get the highest number for this year
        $sql = "SELECT donation_number FROM donations 
                WHERE donation_number LIKE ? 
                ORDER BY donation_number DESC 
                LIMIT 1";
        $result = static::query($sql, ["{$prefix}%"]);
        
        if (empty($result)) {
            $nextNumber = 1;
        } else {
            $lastNumber = str_replace($prefix, '', $result[0]['donation_number']);
            $nextNumber = intval($lastNumber) + 1;
        }
        
        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate unique receipt number
     */
    public function generateReceiptNumber()
    {
        if ($this->receipt_number) {
            return $this->receipt_number;
        }
        
        $year = date('Y');
        $prefix = "REC-{$year}-";
        
        // Get the highest receipt number for this year
        $sql = "SELECT receipt_number FROM donations 
                WHERE receipt_number LIKE ? 
                ORDER BY receipt_number DESC 
                LIMIT 1";
        $result = static::query($sql, ["{$prefix}%"]);
        
        if (empty($result)) {
            $nextNumber = 1;
        } else {
            $lastNumber = str_replace($prefix, '', $result[0]['receipt_number']);
            $nextNumber = intval($lastNumber) + 1;
        }
        
        $receiptNumber = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        
        // Update this donation with the receipt number
        $this->update(['receipt_number' => $receiptNumber]);
        
        return $receiptNumber;
    }
    
    /**
     * Submit donation for approval
     */
    public function submit()
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => date('Y-m-d H:i:s')
        ]);
        
        // Trigger notification to approvers
        $this->notifyApprovers();
        
        return true;
    }
    
    /**
     * Approve donation
     */
    public function approve($approverId, $autoGenerateReceipt = true)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($autoGenerateReceipt) {
            $this->generateReceipt();
        }
        
        // Update donor statistics (handled by trigger)
        // Update campaign statistics (handled by trigger)
        
        // Notify donor
        $this->notifyDonor('approved');
        
        return true;
    }
    
    /**
     * Reject donation
     */
    public function reject($approverId, $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ]);
        
        // Notify donor
        $this->notifyDonor('rejected');
        
        return true;
    }
    
    /**
     * Generate receipt PDF
     */
    public function generateReceipt($force = false)
    {
        if ($this->receipt_pdf_path && !$force) {
            return $this->receipt_pdf_path;
        }
        
        if ($this->status !== 'approved') {
            throw new \Exception('Cannot generate receipt for unapproved donation');
        }
        
        $receiptNumber = $this->generateReceiptNumber();
        
        // Load receipt template and generate PDF
        $receiptData = $this->getReceiptData();
        $pdfPath = $this->generateReceiptPDF($receiptData);
        
        $this->update([
            'receipt_pdf_path' => $pdfPath,
            'receipt_generated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $pdfPath;
    }
    
    /**
     * Get receipt data for PDF generation
     */
    private function getReceiptData()
    {
        $donor = $this->donor();
        $campaign = $this->campaign();
        $global = $this->global();
        
        return [
            'receipt_number' => $this->receipt_number,
            'donation_number' => $this->donation_number,
            'organization' => [
                'name' => $global['name'] ?? 'ABO-WBO Global Organization',
                'address' => $global['headquarters_address'] ?? '',
                'email' => $global['contact_email'] ?? 'info@abo-wbo.org',
                'phone' => $global['contact_phone'] ?? '',
                'website' => $global['website'] ?? 'https://abo-wbo.org'
            ],
            'donor' => [
                'name' => $this->getDonorDisplayName(),
                'email' => $donor['email'] ?? '',
                'address' => $this->formatDonorAddress($donor),
                'tax_id' => $donor['tax_id'] ?? ''
            ],
            'donation' => [
                'amount' => $this->amount,
                'currency' => $this->currency,
                'amount_usd' => $this->amount_usd,
                'payment_method' => $this->getPaymentMethodDisplay(),
                'payment_date' => $this->payment_date,
                'event_name' => $this->event_name,
                'event_date' => $this->event_date,
                'purpose' => $this->getDonationPurposeDisplay(),
                'campaign' => $campaign['name'] ?? null,
                'dedication' => $this->getDedicationText(),
                'tax_deductible' => $this->tax_deductible,
                'tax_year' => $this->tax_year
            ],
            'hierarchy' => $this->getHierarchyInfo(),
            'generated_date' => date('Y-m-d'),
            'generated_time' => date('H:i:s'),
            'is_anonymous' => $this->is_anonymous
        ];
    }
    
    /**
     * Generate receipt PDF file
     */
    private function generateReceiptPDF($data)
    {
        // This would integrate with a PDF library like TCPDF or DOMPDF
        // For now, return a placeholder path
        $filename = "receipt_{$this->receipt_number}.pdf";
        $directory = "storage/receipts/" . date('Y/m');
        $fullPath = $directory . "/" . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Generate PDF content (placeholder implementation)
        $pdfContent = $this->generateReceiptHTML($data);
        
        // Convert HTML to PDF (would use actual PDF library)
        // file_put_contents($fullPath, $pdfContent);
        
        return $fullPath;
    }
    
    /**
     * Generate receipt HTML template
     */
    private function generateReceiptHTML($data)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Donation Receipt - <?= htmlspecialchars($data['receipt_number']) ?></title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .logo { font-size: 24px; font-weight: bold; color: #2c5aa0; }
                .receipt-number { font-size: 18px; margin: 20px 0; }
                .donor-info, .donation-info { margin: 20px 0; }
                .amount { font-size: 20px; font-weight: bold; color: #28a745; }
                .footer { margin-top: 40px; font-size: 10px; color: #666; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 8px; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; width: 150px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo"><?= htmlspecialchars($data['organization']['name']) ?></div>
                <div><?= htmlspecialchars($data['organization']['address']) ?></div>
                <div><?= htmlspecialchars($data['organization']['email']) ?> | <?= htmlspecialchars($data['organization']['phone']) ?></div>
                <div class="receipt-number">DONATION RECEIPT #<?= htmlspecialchars($data['receipt_number']) ?></div>
            </div>
            
            <table>
                <tr>
                    <td class="label">Donor Name:</td>
                    <td><?= htmlspecialchars($data['donor']['name']) ?></td>
                </tr>
                <tr>
                    <td class="label">Donation Amount:</td>
                    <td class="amount"><?= htmlspecialchars($data['donation']['currency']) ?> <?= number_format($data['donation']['amount'], 2) ?></td>
                </tr>
                <tr>
                    <td class="label">Donation Date:</td>
                    <td><?= date('F j, Y', strtotime($data['donation']['payment_date'])) ?></td>
                </tr>
                <tr>
                    <td class="label">Event/Campaign:</td>
                    <td><?= htmlspecialchars($data['donation']['event_name']) ?></td>
                </tr>
                <tr>
                    <td class="label">Payment Method:</td>
                    <td><?= htmlspecialchars($data['donation']['payment_method']) ?></td>
                </tr>
                <tr>
                    <td class="label">Purpose:</td>
                    <td><?= htmlspecialchars($data['donation']['purpose']) ?></td>
                </tr>
                <?php if ($data['donation']['dedication']): ?>
                <tr>
                    <td class="label">Dedication:</td>
                    <td><?= htmlspecialchars($data['donation']['dedication']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">Tax Year:</td>
                    <td><?= htmlspecialchars($data['donation']['tax_year']) ?></td>
                </tr>
                <tr>
                    <td class="label">Tax Deductible:</td>
                    <td><?= $data['donation']['tax_deductible'] ? 'Yes' : 'No' ?></td>
                </tr>
            </table>
            
            <div class="footer">
                <p>This receipt was generated on <?= date('F j, Y') ?> at <?= date('g:i A') ?>.</p>
                <p>Thank you for your generous support of our mission.</p>
                <p>For questions about this donation, please contact us at <?= htmlspecialchars($data['organization']['email']) ?>.</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Send receipt to donor
     */
    public function sendReceipt()
    {
        if (!$this->receipt_pdf_path) {
            $this->generateReceipt();
        }
        
        $donor = $this->donor();
        if (!$donor['email']) {
            throw new \Exception('Donor email not available for receipt sending');
        }
        
        // Send email with receipt attachment
        $this->sendReceiptEmail($donor['email']);
        
        $this->update([
            'receipt_sent_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }
    
    /**
     * Get donations by hierarchy scope
     */
    public static function getByHierarchyScope($levelScope, $hierarchyId, $filters = [])
    {
        $sql = "SELECT d.*, 
                       dr.first_name, dr.last_name, dr.group_name, dr.organization_name,
                       dc.name as campaign_name,
                       u.first_name as submitted_by_first_name, u.last_name as submitted_by_last_name
                FROM donations d
                LEFT JOIN donors dr ON d.donor_id = dr.id
                LEFT JOIN donation_campaigns dc ON d.campaign_id = dc.id
                LEFT JOIN users u ON d.submitted_by = u.id
                WHERE d.level_scope = ? AND ";
        
        $params = [$levelScope];
        
        switch ($levelScope) {
            case 'global':
                $sql .= "d.global_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'godina':
                $sql .= "d.godina_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'gamta':
                $sql .= "d.gamta_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'gurmu':
                $sql .= "d.gurmu_id = ?";
                $params[] = $hierarchyId;
                break;
        }
        
        // Apply additional filters
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND d.payment_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND d.payment_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['campaign_id'])) {
            $sql .= " AND d.campaign_id = ?";
            $params[] = $filters['campaign_id'];
        }
        
        if (!empty($filters['donor_type'])) {
            $sql .= " AND d.donor_type = ?";
            $params[] = $filters['donor_type'];
        }
        
        if (!empty($filters['min_amount'])) {
            $sql .= " AND d.amount >= ?";
            $params[] = $filters['min_amount'];
        }
        
        if (!empty($filters['max_amount'])) {
            $sql .= " AND d.amount <= ?";
            $params[] = $filters['max_amount'];
        }
        
        $sql .= " ORDER BY d.payment_date DESC, d.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        return static::query($sql, $params);
    }
    
    /**
     * Get donation statistics for dashboard
     */
    public static function getDashboardStats($levelScope = 'global', $hierarchyId = 1)
    {
        $whereClause = "WHERE level_scope = ? AND ";
        $params = [$levelScope];
        
        switch ($levelScope) {
            case 'global':
                $whereClause .= "global_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'godina':
                $whereClause .= "godina_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'gamta':
                $whereClause .= "gamta_id = ?";
                $params[] = $hierarchyId;
                break;
            case 'gurmu':
                $whereClause .= "gurmu_id = ?";
                $params[] = $hierarchyId;
                break;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_donations,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_donations,
                    SUM(CASE WHEN status = 'pending_approval' THEN 1 ELSE 0 END) as pending_donations,
                    SUM(CASE WHEN status = 'approved' THEN amount_usd ELSE 0 END) as total_amount,
                    SUM(CASE WHEN status = 'approved' AND payment_date >= CURDATE() - INTERVAL 30 DAY THEN amount_usd ELSE 0 END) as monthly_amount,
                    COUNT(DISTINCT donor_id) as unique_donors
                FROM donations {$whereClause}";
        
        $result = static::query($sql, $params);
        return $result[0] ?? [];
    }
    
    /**
     * Helper methods for display
     */
    private function getDonorDisplayName()
    {
        if ($this->is_anonymous) {
            return 'Anonymous Donor';
        }
        
        $donor = $this->donor();
        if (!$donor) return 'Unknown Donor';
        
        switch ($this->donor_type) {
            case 'individual':
                return trim($donor['first_name'] . ' ' . $donor['last_name']);
            case 'group':
                return $donor['group_name'];
            case 'organization':
            case 'business':
                return $donor['organization_name'];
            default:
                return 'Unknown';
        }
    }
    
    private function formatDonorAddress($donor)
    {
        if (!$donor) return '';
        
        $parts = array_filter([
            $donor['address'],
            $donor['city'],
            $donor['state_province'],
            $donor['country'],
            $donor['postal_code']
        ]);
        
        return implode(', ', $parts);
    }
    
    private function getPaymentMethodDisplay()
    {
        $methods = [
            'cash' => 'Cash',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'bank_transfer' => 'Bank Transfer',
            'paypal' => 'PayPal',
            'stripe' => 'Credit Card (Stripe)',
            'mobile_money' => 'Mobile Money',
            'crypto' => 'Cryptocurrency',
            'other' => 'Other'
        ];
        
        return $methods[$this->payment_method] ?? $this->payment_method;
    }
    
    private function getDonationPurposeDisplay()
    {
        $purposes = [
            'general_support' => 'General Support',
            'olf_support' => 'Oromo Liberation Front Support',
            'education' => 'Education Programs',
            'healthcare' => 'Healthcare Initiatives',
            'community_development' => 'Community Development',
            'emergency_relief' => 'Emergency Relief',
            'cultural_programs' => 'Cultural Programs',
            'youth_programs' => 'Youth Programs',
            'women_programs' => 'Women Programs',
            'other' => 'Other'
        ];
        
        return $purposes[$this->donation_purpose] ?? $this->donation_purpose;
    }
    
    private function getDedicationText()
    {
        if ($this->dedication_type === 'none') {
            return null;
        }
        
        $prefix = $this->dedication_type === 'in_honor_of' ? 'In honor of' : 'In memory of';
        $text = $prefix . ' ' . $this->dedication_name;
        
        if ($this->dedication_message) {
            $text .= ': ' . $this->dedication_message;
        }
        
        return $text;
    }
    
    private function getHierarchyInfo()
    {
        $info = ['level' => $this->level_scope];
        
        switch ($this->level_scope) {
            case 'global':
                $global = $this->global();
                $info['organization'] = $global['name'] ?? 'Global';
                break;
            case 'godina':
                $godina = $this->godina();
                $info['organization'] = $godina['name'] ?? 'Unknown Godina';
                break;
            case 'gamta':
                $gamta = $this->gamta();
                $info['organization'] = $gamta['name'] ?? 'Unknown Gamta';
                break;
            case 'gurmu':
                $gurmu = $this->gurmu();
                $info['organization'] = $gurmu['name'] ?? 'Unknown Gurmu';
                break;
        }
        
        return $info;
    }
    
    /**
     * Notification methods (placeholder implementations)
     */
    private function notifyApprovers()
    {
        // Implementation would send notifications to appropriate approvers
        // based on hierarchy and approval workflow
    }
    
    private function notifyDonor($action)
    {
        // Implementation would send email/SMS to donor about donation status
    }
    
    private function sendReceiptEmail($email)
    {
        // Implementation would send email with PDF receipt attachment
    }
    
    /**
     * Validation rules
     */
    public static function validationRules()
    {
        return [
            'donor_id' => ['required', 'integer', 'exists:donors,id'],
            'donor_type' => ['required', 'in:individual,group,organization,business'],
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method' => ['required', 'in:cash,check,credit_card,bank_transfer,paypal,stripe,mobile_money,crypto,other'],
            'payment_date' => ['required', 'date'],
            'level_scope' => ['required', 'in:global,godina,gamta,gurmu'],
            'submitted_by' => ['required', 'integer', 'exists:users,id']
        ];
    }
}