<?php

namespace App\Services;

use App\Models\Donation;
use Exception;

class DonationService
{
    private $donationModel;
    private $notificationService;

    public function __construct()
    {
        $this->donationModel = new Donation();
        $this->notificationService = new NotificationService();
    }

    /**
     * Process new donation with payment integration
     */
    public function processDonation(array $donationData): array
    {
        try {
            // Validate donation data
            $this->validateDonationData($donationData);
            
            // Generate donation reference
            $donationData['reference_number'] = $this->generateDonationReference();
            $donationData['uuid'] = $this->generateUuid('donation_');
            $donationData['status'] = 'pending';
            $donationData['created_at'] = date('Y-m-d H:i:s');
            
            // Process payment if online
            if ($donationData['payment_method'] !== 'cash') {
                $paymentResult = $this->processPayment($donationData);
                
                if (!$paymentResult['success']) {
                    throw new Exception('Payment processing failed: ' . $paymentResult['error']);
                }
                
                $donationData['payment_transaction_id'] = $paymentResult['transaction_id'];
                $donationData['payment_status'] = $paymentResult['status'];
            }
            
            // Create donation record
            $donationId = $this->donationModel->createDonation($donationData);
            
            if ($donationId) {
                // Send confirmation notifications
                $this->sendDonationConfirmation($donationId);
                
                // Update donor statistics
                $this->updateDonorStatistics($donationData['donor_id']);
                
                // Update hierarchy statistics
                $this->updateHierarchyStatistics($donationData['hierarchy_level'], $donationData['hierarchy_id']);
                
                return [
                    'success' => true,
                    'message' => 'Donation processed successfully',
                    'donation_id' => $donationId,
                    'reference_number' => $donationData['reference_number']
                ];
            }
            
            throw new Exception('Failed to create donation record');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update donation status
     */
    public function updateDonationStatus(int $donationId, string $status, ?int $updatedBy = null): array
    {
        try {
            $donation = $this->donationModel->findById($donationId);
            
            if (!$donation) {
                throw new Exception('Donation not found');
            }
            
            // Validate status
            $validStatuses = ['pending', 'confirmed', 'cancelled', 'refunded'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid status specified');
            }
            
            $updateData = [
                'status' => $status,
                'updated_by' => $updatedBy,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Add status-specific timestamps
            if ($status === 'confirmed') {
                $updateData['confirmed_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'cancelled') {
                $updateData['cancelled_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'refunded') {
                $updateData['refunded_at'] = date('Y-m-d H:i:s');
            }
            
            $success = $this->donationModel->updateDonation($donationId, $updateData);
            
            if ($success) {
                // Send status update notification
                $this->sendStatusUpdateNotification($donationId, $status);
                
                // Update statistics if confirmed or cancelled
                if (in_array($status, ['confirmed', 'cancelled'])) {
                    $this->recalculateStatistics($donation);
                }
                
                return [
                    'success' => true,
                    'message' => 'Donation status updated successfully'
                ];
            }
            
            throw new Exception('Failed to update donation status');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get donation details with related information
     */
    public function getDonationDetails(int $donationId): ?array
    {
        try {
            $donation = $this->donationModel->getDonationWithDetails($donationId);
            
            if (!$donation) {
                return null;
            }
            
            // Add calculated fields
            $donation['tax_deductible_amount'] = $this->calculateTaxDeductibleAmount($donation);
            $donation['processing_fee'] = $this->calculateProcessingFee($donation);
            $donation['net_amount'] = $donation['amount'] - $donation['processing_fee'];
            
            return $donation;
            
        } catch (Exception $e) {
            error_log("DonationService::getDonationDetails failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search donations with advanced filters
     */
    public function searchDonations(array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $donations = $this->donationModel->searchDonations($filters, $limit, $offset);
            $totalCount = $this->donationModel->countDonations($filters);
            
            // Add calculated fields to each donation
            foreach ($donations as &$donation) {
                $donation['tax_deductible_amount'] = $this->calculateTaxDeductibleAmount($donation);
                $donation['processing_fee'] = $this->calculateProcessingFee($donation);
                $donation['net_amount'] = $donation['amount'] - $donation['processing_fee'];
            }
            
            return [
                'success' => true,
                'data' => [
                    'donations' => $donations,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $totalCount,
                        'total_pages' => ceil($totalCount / $limit)
                    ],
                    'summary' => $this->getDonationsSummary($filters)
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
     * Generate donation analytics report
     */
    public function generateDonationAnalytics(array $filters = []): array
    {
        try {
            $analytics = [
                'overview' => $this->getDonationOverview($filters),
                'trends' => $this->getDonationTrends($filters),
                'by_hierarchy' => $this->getDonationsByHierarchy($filters),
                'by_category' => $this->getDonationsByCategory($filters),
                'by_payment_method' => $this->getDonationsByPaymentMethod($filters),
                'top_donors' => $this->getTopDonors($filters),
                'recurring_donations' => $this->getRecurringDonationsStats($filters)
            ];
            
            return [
                'success' => true,
                'analytics' => $analytics,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create recurring donation schedule
     */
    public function createRecurringDonation(array $donationData): array
    {
        try {
            // Validate recurring donation data
            $this->validateRecurringDonationData($donationData);
            
            // Create initial donation
            $initialDonation = $this->processDonation($donationData);
            
            if (!$initialDonation['success']) {
                throw new Exception('Failed to process initial donation: ' . $initialDonation['error']);
            }
            
            // Create recurring schedule
            $scheduleData = [
                'donation_id' => $initialDonation['donation_id'],
                'donor_id' => $donationData['donor_id'],
                'frequency' => $donationData['recurring_frequency'],
                'amount' => $donationData['amount'],
                'payment_method' => $donationData['payment_method'],
                'status' => 'active',
                'next_donation_date' => $this->calculateNextDonationDate($donationData['recurring_frequency']),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $scheduleId = $this->donationModel->createRecurringSchedule($scheduleData);
            
            if ($scheduleId) {
                return [
                    'success' => true,
                    'message' => 'Recurring donation created successfully',
                    'initial_donation_id' => $initialDonation['donation_id'],
                    'schedule_id' => $scheduleId
                ];
            }
            
            throw new Exception('Failed to create recurring donation schedule');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process recurring donations that are due
     */
    public function processDueRecurringDonations(): array
    {
        try {
            $dueSchedules = $this->donationModel->getDueRecurringDonations();
            $processed = 0;
            $failed = 0;
            $errors = [];
            
            foreach ($dueSchedules as $schedule) {
                try {
                    // Prepare donation data
                    $donationData = [
                        'donor_id' => $schedule['donor_id'],
                        'amount' => $schedule['amount'],
                        'payment_method' => $schedule['payment_method'],
                        'donation_type' => 'recurring',
                        'parent_schedule_id' => $schedule['id']
                    ];
                    
                    // Process the donation
                    $result = $this->processDonation($donationData);
                    
                    if ($result['success']) {
                        // Update next donation date
                        $nextDate = $this->calculateNextDonationDate($schedule['frequency']);
                        $this->donationModel->updateRecurringSchedule($schedule['id'], [
                            'last_donation_date' => date('Y-m-d H:i:s'),
                            'next_donation_date' => $nextDate,
                            'total_donations' => $schedule['total_donations'] + 1
                        ]);
                        
                        $processed++;
                    } else {
                        $failed++;
                        $errors[] = "Schedule {$schedule['id']}: " . $result['error'];
                    }
                    
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = "Schedule {$schedule['id']}: " . $e->getMessage();
                }
            }
            
            return [
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate tax receipt for donation
     */
    public function generateTaxReceipt(int $donationId): array
    {
        try {
            $donation = $this->getDonationDetails($donationId);
            
            if (!$donation || $donation['status'] !== 'confirmed') {
                throw new Exception('Donation not found or not confirmed');
            }
            
            // Generate receipt PDF
            $receiptData = [
                'donation' => $donation,
                'receipt_number' => $this->generateReceiptNumber($donationId),
                'tax_year' => date('Y', strtotime($donation['created_at'])),
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $pdfPath = $this->generateReceiptPDF($receiptData);
            
            if ($pdfPath) {
                // Update donation with receipt info
                $this->donationModel->updateDonation($donationId, [
                    'tax_receipt_generated' => true,
                    'tax_receipt_path' => $pdfPath,
                    'tax_receipt_number' => $receiptData['receipt_number']
                ]);
                
                return [
                    'success' => true,
                    'receipt_path' => $pdfPath,
                    'receipt_number' => $receiptData['receipt_number']
                ];
            }
            
            throw new Exception('Failed to generate tax receipt PDF');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process payment through payment gateway
     */
    private function processPayment(array $donationData): array
    {
        try {
            // This would integrate with actual payment gateway
            // For now, simulate payment processing
            
            $paymentMethods = ['credit_card', 'bank_transfer', 'mobile_money', 'paypal'];
            
            if (!in_array($donationData['payment_method'], $paymentMethods)) {
                throw new Exception('Unsupported payment method');
            }
            
            // Simulate payment processing
            $transactionId = 'txn_' . uniqid() . '_' . bin2hex(random_bytes(4));
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => 'completed'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate tax deductible amount
     */
    private function calculateTaxDeductibleAmount(array $donation): float
    {
        // Tax deductible percentage might vary by donation type
        $deductiblePercentage = 1.0; // 100% deductible
        
        if ($donation['donation_type'] === 'political') {
            $deductiblePercentage = 0.75; // 75% deductible
        }
        
        return $donation['amount'] * $deductiblePercentage;
    }

    /**
     * Calculate processing fee
     */
    private function calculateProcessingFee(array $donation): float
    {
        if ($donation['payment_method'] === 'cash') {
            return 0.0;
        }
        
        // Different fees for different payment methods
        $feePercentages = [
            'credit_card' => 0.029, // 2.9%
            'bank_transfer' => 0.01, // 1%
            'mobile_money' => 0.015, // 1.5%
            'paypal' => 0.034 // 3.4%
        ];
        
        $feePercentage = $feePercentages[$donation['payment_method']] ?? 0.025;
        
        return $donation['amount'] * $feePercentage;
    }

    /**
     * Get donations summary
     */
    private function getDonationsSummary(array $filters): array
    {
        return $this->donationModel->getDonationsSummary($filters);
    }

    /**
     * Get donation overview statistics
     */
    private function getDonationOverview(array $filters): array
    {
        return $this->donationModel->getDonationOverview($filters);
    }

    /**
     * Get donation trends
     */
    private function getDonationTrends(array $filters): array
    {
        return $this->donationModel->getDonationTrends($filters);
    }

    /**
     * Get donations by hierarchy
     */
    private function getDonationsByHierarchy(array $filters): array
    {
        return $this->donationModel->getDonationsByHierarchy($filters);
    }

    /**
     * Get donations by category
     */
    private function getDonationsByCategory(array $filters): array
    {
        return $this->donationModel->getDonationsByCategory($filters);
    }

    /**
     * Get donations by payment method
     */
    private function getDonationsByPaymentMethod(array $filters): array
    {
        return $this->donationModel->getDonationsByPaymentMethod($filters);
    }

    /**
     * Get top donors
     */
    private function getTopDonors(array $filters): array
    {
        return $this->donationModel->getTopDonors($filters);
    }

    /**
     * Get recurring donations statistics
     */
    private function getRecurringDonationsStats(array $filters): array
    {
        return $this->donationModel->getRecurringDonationsStats($filters);
    }

    /**
     * Send donation confirmation
     */
    private function sendDonationConfirmation(int $donationId): void
    {
        $this->notificationService->sendDonationConfirmationNotification($donationId);
    }

    /**
     * Send status update notification
     */
    private function sendStatusUpdateNotification(int $donationId, string $status): void
    {
        $this->notificationService->sendDonationStatusUpdateNotification($donationId, $status);
    }

    /**
     * Update donor statistics
     */
    private function updateDonorStatistics(int $donorId): void
    {
        // This would update donor statistics in database
        // Simplified for now
    }

    /**
     * Update hierarchy statistics
     */
    private function updateHierarchyStatistics(string $hierarchyLevel, int $hierarchyId): void
    {
        // This would update hierarchy donation statistics
        // Simplified for now
    }

    /**
     * Recalculate statistics after status change
     */
    private function recalculateStatistics(array $donation): void
    {
        $this->updateDonorStatistics($donation['donor_id']);
        $this->updateHierarchyStatistics($donation['hierarchy_level'], $donation['hierarchy_id']);
    }

    /**
     * Calculate next donation date for recurring donations
     */
    private function calculateNextDonationDate(string $frequency): string
    {
        $intervals = [
            'weekly' => '+1 week',
            'monthly' => '+1 month',
            'quarterly' => '+3 months',
            'yearly' => '+1 year'
        ];
        
        return date('Y-m-d H:i:s', strtotime($intervals[$frequency] ?? '+1 month'));
    }

    /**
     * Generate receipt PDF
     */
    private function generateReceiptPDF(array $receiptData): string
    {
        // This would generate actual PDF using a library like TCPDF or FPDF
        // For now, return a placeholder path
        $fileName = 'receipt_' . $receiptData['receipt_number'] . '.pdf';
        return 'storage/receipts/' . $fileName;
    }

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber(int $donationId): string
    {
        return 'RCP-' . date('Y') . '-' . str_pad($donationId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate donation reference
     */
    private function generateDonationReference(): string
    {
        return 'DON-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Validate donation data
     */
    private function validateDonationData(array $data): void
    {
        $errors = [];
        
        if (empty($data['donor_id'])) {
            $errors[] = 'Donor ID is required';
        }
        
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Valid donation amount is required';
        }
        
        if (empty($data['payment_method'])) {
            $errors[] = 'Payment method is required';
        }
        
        if (empty($data['donation_type'])) {
            $errors[] = 'Donation type is required';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
    }

    /**
     * Validate recurring donation data
     */
    private function validateRecurringDonationData(array $data): void
    {
        $this->validateDonationData($data);
        
        $errors = [];
        
        if (empty($data['recurring_frequency'])) {
            $errors[] = 'Recurring frequency is required';
        }
        
        $validFrequencies = ['weekly', 'monthly', 'quarterly', 'yearly'];
        if (!in_array($data['recurring_frequency'], $validFrequencies)) {
            $errors[] = 'Invalid recurring frequency';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
    }

    /**
     * Generate UUID
     */
    private function generateUuid(string $prefix = ''): string
    {
        return $prefix . uniqid() . '_' . bin2hex(random_bytes(8));
    }
}