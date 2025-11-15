<?php

/**
 * English Financial System Translations
 * 
 * Donation management, financial tracking, receipt generation,
 * and payment processing translations.
 * 
 * @package Lang\EN
 * @version 1.0.0
 */

return [
    // Financial Management
    'title' => 'Financial Management',
    'donations' => 'Donations',
    'donation_management' => 'Donation Management',
    'financial_overview' => 'Financial Overview',
    'financial_reports' => 'Financial Reports',
    'transactions' => 'Transactions',
    'receipts' => 'Receipts',
    'contributions' => 'Contributions',
    'fundraising' => 'Fundraising',
    'budget_management' => 'Budget Management',
    'expense_tracking' => 'Expense Tracking',

    // Donation Operations
    'operations' => [
        'record_donation' => 'Record Donation',
        'new_donation' => 'New Donation',
        'edit_donation' => 'Edit Donation',
        'view_donation' => 'View Donation',
        'delete_donation' => 'Delete Donation',
        'approve_donation' => 'Approve Donation',
        'reject_donation' => 'Reject Donation',
        'process_donation' => 'Process Donation',
        'refund_donation' => 'Refund Donation',
        'generate_receipt' => 'Generate Receipt',
        'send_receipt' => 'Send Receipt',
        'download_receipt' => 'Download Receipt',
        'print_receipt' => 'Print Receipt',
        'bulk_import' => 'Bulk Import Donations',
        'export_donations' => 'Export Donations'
    ],

    // Donation Properties
    'properties' => [
        'donation_id' => 'Donation ID',
        'receipt_number' => 'Receipt Number',
        'donor_name' => 'Donor Name',
        'donor_email' => 'Donor Email',
        'donor_phone' => 'Donor Phone',
        'donor_address' => 'Donor Address',
        'donor_type' => 'Donor Type',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'donation_date' => 'Donation Date',
        'received_date' => 'Received Date',
        'processed_date' => 'Processed Date',
        'payment_method' => 'Payment Method',
        'payment_status' => 'Payment Status',
        'transaction_id' => 'Transaction ID',
        'reference_number' => 'Reference Number',
        'purpose' => 'Purpose',
        'category' => 'Category',
        'campaign' => 'Campaign',
        'project' => 'Project',
        'level_scope' => 'Scope Level',
        'scope_id' => 'Organization',
        'is_anonymous' => 'Anonymous Donation',
        'is_recurring' => 'Recurring Donation',
        'recurring_frequency' => 'Recurring Frequency',
        'tax_deductible' => 'Tax Deductible',
        'tax_receipt_sent' => 'Tax Receipt Sent',
        'notes' => 'Notes',
        'internal_notes' => 'Internal Notes',
        'receipt_sent' => 'Receipt Sent',
        'acknowledgment_sent' => 'Acknowledgment Sent',
        'created_by' => 'Recorded By',
        'approved_by' => 'Approved By',
        'processed_by' => 'Processed By',
        'created_at' => 'Created At',
        'updated_at' => 'Last Updated'
    ],

    // Donor Types
    'donor_types' => [
        'individual' => 'Individual',
        'family' => 'Family',
        'organization' => 'Organization',
        'business' => 'Business',
        'foundation' => 'Foundation',
        'government' => 'Government',
        'anonymous' => 'Anonymous',
        'corporate' => 'Corporate',
        'religious' => 'Religious Organization',
        'nonprofit' => 'Non-profit Organization',
        'educational' => 'Educational Institution',
        'healthcare' => 'Healthcare Organization'
    ],

    // Payment Methods
    'payment_methods' => [
        'cash' => 'Cash',
        'check' => 'Check',
        'credit_card' => 'Credit Card',
        'debit_card' => 'Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'wire_transfer' => 'Wire Transfer',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'mobile_payment' => 'Mobile Payment',
        'cryptocurrency' => 'Cryptocurrency',
        'money_order' => 'Money Order',
        'cashier_check' => 'Cashier\'s Check',
        'online_banking' => 'Online Banking',
        'direct_debit' => 'Direct Debit',
        'standing_order' => 'Standing Order',
        'venmo' => 'Venmo',
        'zelle' => 'Zelle',
        'apple_pay' => 'Apple Pay',
        'google_pay' => 'Google Pay',
        'other' => 'Other'
    ],

    // Payment Status
    'payment_status' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'partially_refunded' => 'Partially Refunded',
        'disputed' => 'Disputed',
        'chargeback' => 'Chargeback',
        'expired' => 'Expired',
        'on_hold' => 'On Hold',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'status_descriptions' => [
            'pending' => 'Payment is pending processing',
            'processing' => 'Payment is being processed',
            'completed' => 'Payment completed successfully',
            'failed' => 'Payment failed to process',
            'cancelled' => 'Payment was cancelled',
            'refunded' => 'Payment has been refunded',
            'partially_refunded' => 'Payment has been partially refunded',
            'disputed' => 'Payment is under dispute',
            'chargeback' => 'Payment has been charged back',
            'expired' => 'Payment authorization expired',
            'on_hold' => 'Payment is on hold for review',
            'under_review' => 'Payment is under manual review',
            'approved' => 'Payment has been approved',
            'rejected' => 'Payment has been rejected'
        ]
    ],

    // Donation Categories
    'categories' => [
        'general' => 'General Donation',
        'emergency' => 'Emergency Fund',
        'education' => 'Education',
        'healthcare' => 'Healthcare',
        'community_development' => 'Community Development',
        'youth_programs' => 'Youth Programs',
        'elderly_care' => 'Elderly Care',
        'women_empowerment' => 'Women Empowerment',
        'cultural_preservation' => 'Cultural Preservation',
        'religious_activities' => 'Religious Activities',
        'infrastructure' => 'Infrastructure',
        'technology' => 'Technology',
        'capacity_building' => 'Capacity Building',
        'advocacy' => 'Advocacy',
        'humanitarian_aid' => 'Humanitarian Aid',
        'disaster_relief' => 'Disaster Relief',
        'scholarship' => 'Scholarship Fund',
        'research' => 'Research',
        'sports_recreation' => 'Sports & Recreation',
        'arts_culture' => 'Arts & Culture',
        'environmental' => 'Environmental',
        'other' => 'Other'
    ],

    // Recurring Frequencies
    'recurring_frequencies' => [
        'weekly' => 'Weekly',
        'biweekly' => 'Bi-weekly',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'semi_annually' => 'Semi-annually',
        'annually' => 'Annually',
        'one_time' => 'One-time'
    ],

    // Scope Levels
    'scope_levels' => [
        'global' => 'Global Level',
        'godina' => 'Godina Level',
        'gamta' => 'Gamta Level',
        'gurmu' => 'Gurmu Level',
        'scope_descriptions' => [
            'global' => 'Donations for global organization activities',
            'godina' => 'Donations specific to a Godina region',
            'gamta' => 'Donations specific to a Gamta district',
            'gurmu' => 'Donations specific to a local Gurmu'
        ]
    ],

    // Currency
    'currency' => [
        'usd' => 'USD ($)',
        'eur' => 'EUR (€)',
        'gbp' => 'GBP (£)',
        'cad' => 'CAD ($)',
        'aud' => 'AUD ($)',
        'etb' => 'ETB (Br)',
        'kes' => 'KES (KSh)',
        'tzs' => 'TZS (TSh)',
        'ugx' => 'UGX (USh)',
        'default_currency' => 'USD',
        'currency_symbol' => '$',
        'format_amount' => ':symbol:amount'
    ],

    // Financial Reports
    'reports' => [
        'financial_summary' => 'Financial Summary',
        'donation_report' => 'Donation Report',
        'donor_report' => 'Donor Report',
        'campaign_report' => 'Campaign Report',
        'monthly_report' => 'Monthly Report',
        'quarterly_report' => 'Quarterly Report',
        'annual_report' => 'Annual Report',
        'tax_report' => 'Tax Report',
        'audit_report' => 'Audit Report',
        'trend_analysis' => 'Trend Analysis',
        'comparative_analysis' => 'Comparative Analysis',
        'budget_vs_actual' => 'Budget vs Actual',
        'cash_flow_report' => 'Cash Flow Report',
        'expense_report' => 'Expense Report',
        'revenue_report' => 'Revenue Report',
        'profit_loss' => 'Profit & Loss Statement',
        'balance_sheet' => 'Balance Sheet',
        'donor_retention' => 'Donor Retention Report',
        'fundraising_efficiency' => 'Fundraising Efficiency',
        'roi_analysis' => 'ROI Analysis'
    ],

    // Receipt Management
    'receipts' => [
        'receipt_management' => 'Receipt Management',
        'generate_receipt' => 'Generate Receipt',
        'receipt_template' => 'Receipt Template',
        'receipt_number' => 'Receipt Number',
        'receipt_date' => 'Receipt Date',
        'tax_receipt' => 'Tax Receipt',
        'donation_receipt' => 'Donation Receipt',
        'official_receipt' => 'Official Receipt',
        'receipt_header' => 'Receipt Header',
        'receipt_footer' => 'Receipt Footer',
        'receipt_logo' => 'Receipt Logo',
        'receipt_signature' => 'Authorized Signature',
        'receipt_settings' => 'Receipt Settings',
        'auto_generate' => 'Auto Generate Receipts',
        'email_receipt' => 'Email Receipt',
        'print_receipt' => 'Print Receipt',
        'receipt_sent' => 'Receipt Sent',
        'receipt_history' => 'Receipt History',
        'duplicate_receipt' => 'Duplicate Receipt',
        'void_receipt' => 'Void Receipt',
        'receipt_status' => 'Receipt Status'
    ],

    // Fundraising Campaigns
    'campaigns' => [
        'fundraising_campaigns' => 'Fundraising Campaigns',
        'create_campaign' => 'Create Campaign',
        'campaign_name' => 'Campaign Name',
        'campaign_description' => 'Campaign Description',
        'campaign_goal' => 'Campaign Goal',
        'campaign_target' => 'Target Amount',
        'amount_raised' => 'Amount Raised',
        'campaign_progress' => 'Campaign Progress',
        'campaign_status' => 'Campaign Status',
        'campaign_start_date' => 'Start Date',
        'campaign_end_date' => 'End Date',
        'campaign_duration' => 'Campaign Duration',
        'active_campaigns' => 'Active Campaigns',
        'completed_campaigns' => 'Completed Campaigns',
        'upcoming_campaigns' => 'Upcoming Campaigns',
        'campaign_analytics' => 'Campaign Analytics',
        'campaign_donors' => 'Campaign Donors',
        'campaign_donations' => 'Campaign Donations',
        'campaign_performance' => 'Campaign Performance',
        'goal_achieved' => 'Goal Achieved',
        'goal_percentage' => ':percent% of goal achieved'
    ],

    // Budget Management
    'budget' => [
        'budget_management' => 'Budget Management',
        'annual_budget' => 'Annual Budget',
        'budget_categories' => 'Budget Categories',
        'allocated_amount' => 'Allocated Amount',
        'spent_amount' => 'Spent Amount',
        'remaining_amount' => 'Remaining Amount',
        'budget_variance' => 'Budget Variance',
        'over_budget' => 'Over Budget',
        'under_budget' => 'Under Budget',
        'budget_approval' => 'Budget Approval',
        'budget_revision' => 'Budget Revision',
        'budget_monitoring' => 'Budget Monitoring',
        'budget_alerts' => 'Budget Alerts',
        'quarterly_budget' => 'Quarterly Budget',
        'monthly_budget' => 'Monthly Budget',
        'department_budget' => 'Department Budget',
        'project_budget' => 'Project Budget'
    ],

    // Expense Tracking
    'expenses' => [
        'expense_tracking' => 'Expense Tracking',
        'record_expense' => 'Record Expense',
        'expense_category' => 'Expense Category',
        'expense_amount' => 'Expense Amount',
        'expense_date' => 'Expense Date',
        'expense_description' => 'Expense Description',
        'receipt_required' => 'Receipt Required',
        'expense_approval' => 'Expense Approval',
        'reimbursement' => 'Reimbursement',
        'petty_cash' => 'Petty Cash',
        'operational_expenses' => 'Operational Expenses',
        'administrative_expenses' => 'Administrative Expenses',
        'program_expenses' => 'Program Expenses',
        'travel_expenses' => 'Travel Expenses',
        'equipment_expenses' => 'Equipment Expenses',
        'utilities' => 'Utilities',
        'rent_facilities' => 'Rent & Facilities',
        'professional_services' => 'Professional Services',
        'marketing_expenses' => 'Marketing Expenses',
        'training_expenses' => 'Training Expenses'
    ],

    // Payment Processing
    'payment_processing' => [
        'payment_processing' => 'Payment Processing',
        'process_payment' => 'Process Payment',
        'payment_gateway' => 'Payment Gateway',
        'secure_payment' => 'Secure Payment',
        'payment_confirmation' => 'Payment Confirmation',
        'payment_receipt' => 'Payment Receipt',
        'payment_history' => 'Payment History',
        'failed_payments' => 'Failed Payments',
        'retry_payment' => 'Retry Payment',
        'refund_payment' => 'Refund Payment',
        'payment_disputes' => 'Payment Disputes',
        'chargeback_management' => 'Chargeback Management',
        'transaction_fees' => 'Transaction Fees',
        'processing_fees' => 'Processing Fees',
        'gateway_fees' => 'Gateway Fees',
        'net_amount' => 'Net Amount',
        'gross_amount' => 'Gross Amount'
    ],

    // Tax Management
    'tax' => [
        'tax_management' => 'Tax Management',
        'tax_deductible' => 'Tax Deductible',
        'tax_receipt' => 'Tax Receipt',
        'tax_exemption' => 'Tax Exemption',
        'tax_id_number' => 'Tax ID Number',
        'ein_number' => 'EIN Number',
        'tax_year' => 'Tax Year',
        'tax_category' => 'Tax Category',
        'charitable_deduction' => 'Charitable Deduction',
        'tax_compliance' => 'Tax Compliance',
        'tax_reporting' => 'Tax Reporting',
        'annual_tax_summary' => 'Annual Tax Summary',
        'donor_tax_summary' => 'Donor Tax Summary',
        'tax_documentation' => 'Tax Documentation',
        'irs_requirements' => 'IRS Requirements',
        'tax_audit_trail' => 'Tax Audit Trail'
    ],

    // Donor Management
    'donor_management' => [
        'donor_management' => 'Donor Management',
        'donor_profile' => 'Donor Profile',
        'donor_history' => 'Donor History',
        'donor_communications' => 'Donor Communications',
        'donor_preferences' => 'Donor Preferences',
        'donor_segmentation' => 'Donor Segmentation',
        'major_donors' => 'Major Donors',
        'recurring_donors' => 'Recurring Donors',
        'first_time_donors' => 'First-time Donors',
        'lapsed_donors' => 'Lapsed Donors',
        'donor_retention' => 'Donor Retention',
        'donor_acquisition' => 'Donor Acquisition',
        'donor_engagement' => 'Donor Engagement',
        'donor_stewardship' => 'Donor Stewardship',
        'donor_appreciation' => 'Donor Appreciation',
        'donor_recognition' => 'Donor Recognition',
        'donor_privacy' => 'Donor Privacy',
        'donor_anonymity' => 'Donor Anonymity'
    ],

    // Financial Statistics
    'statistics' => [
        'total_donations' => 'Total Donations',
        'total_donors' => 'Total Donors',
        'average_donation' => 'Average Donation',
        'largest_donation' => 'Largest Donation',
        'smallest_donation' => 'Smallest Donation',
        'monthly_total' => 'Monthly Total',
        'yearly_total' => 'Yearly Total',
        'growth_rate' => 'Growth Rate',
        'retention_rate' => 'Retention Rate',
        'conversion_rate' => 'Conversion Rate',
        'fundraising_efficiency' => 'Fundraising Efficiency',
        'cost_per_dollar_raised' => 'Cost per Dollar Raised',
        'return_on_investment' => 'Return on Investment',
        'donor_lifetime_value' => 'Donor Lifetime Value',
        'acquisition_cost' => 'Donor Acquisition Cost'
    ],

    // Notifications
    'notifications' => [
        'donation_received' => 'New donation received',
        'donation_processed' => 'Donation processed successfully',
        'receipt_sent' => 'Receipt sent to donor',
        'payment_failed' => 'Payment processing failed',
        'refund_processed' => 'Refund processed',
        'recurring_donation_due' => 'Recurring donation due',
        'campaign_goal_reached' => 'Campaign goal reached',
        'budget_alert' => 'Budget alert',
        'expense_approval_required' => 'Expense approval required',
        'monthly_report_ready' => 'Monthly financial report ready',
        'tax_receipt_requested' => 'Tax receipt requested',
        'donor_anniversary' => 'Donor anniversary',
        'large_donation_alert' => 'Large donation alert',
        'payment_dispute' => 'Payment dispute notification',
        'chargeback_alert' => 'Chargeback alert'
    ],

    // Validation Messages
    'validation' => [
        'donor_name_required' => 'Donor name is required',
        'donor_email_valid' => 'Please enter a valid donor email',
        'amount_required' => 'Donation amount is required',
        'amount_positive' => 'Donation amount must be positive',
        'amount_max' => 'Donation amount cannot exceed :max',
        'payment_method_required' => 'Payment method is required',
        'donation_date_required' => 'Donation date is required',
        'donation_date_valid' => 'Please enter a valid donation date',
        'purpose_required' => 'Donation purpose is required',
        'category_required' => 'Donation category is required',
        'receipt_number_unique' => 'Receipt number must be unique',
        'transaction_id_unique' => 'Transaction ID must be unique',
        'currency_valid' => 'Please select a valid currency',
        'recurring_frequency_required' => 'Recurring frequency is required for recurring donations',
        'campaign_goal_positive' => 'Campaign goal must be positive',
        'budget_amount_positive' => 'Budget amount must be positive'
    ],

    // Success Messages
    'success' => [
        'donation_recorded' => 'Donation recorded successfully',
        'donation_updated' => 'Donation updated successfully',
        'donation_deleted' => 'Donation deleted successfully',
        'donation_approved' => 'Donation approved successfully',
        'payment_processed' => 'Payment processed successfully',
        'receipt_generated' => 'Receipt generated successfully',
        'receipt_sent' => 'Receipt sent successfully',
        'refund_processed' => 'Refund processed successfully',
        'campaign_created' => 'Campaign created successfully',
        'budget_saved' => 'Budget saved successfully',
        'expense_recorded' => 'Expense recorded successfully',
        'report_generated' => 'Report generated successfully',
        'settings_updated' => 'Financial settings updated successfully',
        'import_completed' => 'Data import completed successfully',
        'export_completed' => 'Data export completed successfully'
    ],

    // Error Messages
    'errors' => [
        'donation_not_found' => 'Donation not found',
        'access_denied' => 'Access denied to financial data',
        'payment_failed' => 'Payment processing failed',
        'insufficient_funds' => 'Insufficient funds',
        'invalid_card' => 'Invalid credit card',
        'card_declined' => 'Credit card declined',
        'gateway_error' => 'Payment gateway error',
        'refund_failed' => 'Refund processing failed',
        'receipt_generation_failed' => 'Receipt generation failed',
        'email_send_failed' => 'Failed to send email receipt',
        'campaign_not_found' => 'Campaign not found',
        'budget_exceeded' => 'Budget limit exceeded',
        'invalid_amount' => 'Invalid amount entered',
        'duplicate_transaction' => 'Duplicate transaction detected',
        'system_error' => 'System error, please try again',
        'data_import_failed' => 'Data import failed',
        'report_generation_failed' => 'Report generation failed'
    ],

    // Help and Tips
    'help' => [
        'donation_help' => 'Donation Management Help',
        'financial_help' => 'Financial Management Help',
        'receipt_help' => 'Receipt Generation Help',
        'campaign_help' => 'Campaign Management Help',
        'budget_help' => 'Budget Management Help',
        'best_practices' => 'Financial Best Practices',
        'security_tips' => 'Financial Security Tips',
        'compliance_guide' => 'Compliance Guidelines',
        'tax_guidelines' => 'Tax Guidelines',
        'tips' => [
            'Record donations promptly and accurately',
            'Generate receipts immediately for tax purposes',
            'Follow up with donors regularly',
            'Maintain detailed financial records',
            'Monitor budget regularly',
            'Set up automated recurring donations',
            'Use secure payment processing',
            'Provide multiple payment options',
            'Send thank you messages to donors',
            'Maintain donor privacy and security'
        ]
    ]
];