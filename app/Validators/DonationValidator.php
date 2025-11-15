<?php

namespace App\Validators;

/**
 * DonationValidator - Validates donation-related input data
 * 
 * Handles validation for donation processing, campaign management,
 * receipt generation, and financial operations.
 * 
 * @package App\Validators
 * @version 1.0.0
 */
class DonationValidator extends BaseValidator
{
    /**
     * Define validation rules for donation operations
     */
    protected function defineRules(): void
    {
        // Default rules - can be overridden by specific validation methods
        $this->rules = [];
    }

    /**
     * Validate donation submission data
     */
    public function validateDonationSubmission(array $data): bool
    {
        $this->rules = [
            'donor_name' => 'required|min:2|max:100',
            'donor_email' => 'required|email|max:255',
            'donor_phone' => 'regex:/^[+]?[0-9]{10,15}$/',
            'amount' => 'required|numeric|min:1|max:1000000',
            'currency' => 'required|in:USD,EUR,ETB',
            'donation_type' => 'required|in:one_time,monthly,quarterly,annual',
            'category' => 'required|in:general,education,healthcare,infrastructure,emergency,cultural',
            'level_scope' => 'required|in:global,godina,gamta,gurmu',
            'scope_id' => 'required|integer|min:1',
            'payment_method' => 'required|in:credit_card,bank_transfer,mobile_money,cash,check',
            'is_anonymous' => 'boolean',
            'message' => 'max:1000',
            'dedication' => 'max:255',
            'receipt_preference' => 'required|in:email,postal,both,none',
            'terms_accepted' => 'required|boolean'
        ];

        // Additional validation for recurring donations
        if (isset($data['donation_type']) && $data['donation_type'] !== 'one_time') {
            $this->rules['start_date'] = 'required|date';
            $this->rules['end_date'] = 'date|after:start_date';
        }

        return $this->validate($data);
    }

    /**
     * Validate payment processing data
     */
    public function validatePaymentProcessing(array $data): bool
    {
        $this->rules = [
            'donation_id' => 'required|integer|min:1',
            'payment_method' => 'required|in:credit_card,bank_transfer,mobile_money,cash,check',
            'transaction_reference' => 'required|max:255',
            'gateway_response' => 'array',
            'fees_deducted' => 'numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'processed_at' => 'required|date'
        ];

        // Payment method specific validation
        switch ($data['payment_method'] ?? '') {
            case 'credit_card':
                $this->rules['card_last_four'] = 'required|numeric|min:1000|max:9999';
                $this->rules['card_type'] = 'required|in:visa,mastercard,amex,discover';
                break;
            case 'bank_transfer':
                $this->rules['bank_reference'] = 'required|max:100';
                break;
            case 'mobile_money':
                $this->rules['mobile_number'] = 'required|regex:/^[+]?[0-9]{10,15}$/';
                $this->rules['provider'] = 'required|in:mpesa,telebirr,ebirr';
                break;
            case 'check':
                $this->rules['check_number'] = 'required|max:50';
                $this->rules['bank_name'] = 'required|max:100';
                break;
        }

        return $this->validate($data);
    }

    /**
     * Validate donation campaign data
     */
    public function validateCampaignCreation(array $data): bool
    {
        $this->rules = [
            'title' => 'required|min:5|max:255',
            'description' => 'required|min:50|max:5000',
            'category' => 'required|in:general,education,healthcare,infrastructure,emergency,cultural',
            'goal_amount' => 'required|numeric|min:100|max:10000000',
            'currency' => 'required|in:USD,EUR,ETB',
            'level_scope' => 'required|in:global,godina,gamta,gurmu',
            'scope_id' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'featured_image' => 'file|image|max:5120', // 5MB max
            'video_url' => 'url|max:500',
            'beneficiary_details' => 'max:2000',
            'min_donation_amount' => 'numeric|min:1',
            'max_donation_amount' => 'numeric|min:1',
            'allow_anonymous' => 'boolean',
            'show_donor_list' => 'boolean',
            'send_updates' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate donation search/filter data
     */
    public function validateDonationSearch(array $data): bool
    {
        $this->rules = [
            'search' => 'max:255',
            'status' => 'in:pending,completed,failed,refunded,cancelled',
            'payment_method' => 'in:credit_card,bank_transfer,mobile_money,cash,check',
            'donation_type' => 'in:one_time,monthly,quarterly,annual',
            'category' => 'in:general,education,healthcare,infrastructure,emergency,cultural',
            'level_scope' => 'in:global,godina,gamta,gurmu',
            'scope_id' => 'integer|min:1',
            'amount_from' => 'numeric|min:0',
            'amount_to' => 'numeric|min:0',
            'date_from' => 'date',
            'date_to' => 'date',
            'donor_email' => 'email',
            'is_anonymous' => 'boolean',
            'campaign_id' => 'integer|min:1',
            'sort_by' => 'in:amount,date,donor_name,status',
            'sort_order' => 'in:asc,desc',
            'per_page' => 'integer|min:10|max:100'
        ];

        return $this->validate($data);
    }

    /**
     * Validate refund processing data
     */
    public function validateRefundProcessing(array $data): bool
    {
        $this->rules = [
            'donation_id' => 'required|integer|min:1',
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_reason' => 'required|in:duplicate,error,cancellation,fraud,dispute',
            'reason_details' => 'required|min:10|max:1000',
            'refund_method' => 'required|in:original_payment,bank_transfer,check',
            'notify_donor' => 'boolean',
            'processed_by' => 'required|integer|min:1'
        ];

        return $this->validate($data);
    }

    /**
     * Validate receipt generation data
     */
    public function validateReceiptGeneration(array $data): bool
    {
        $this->rules = [
            'donation_ids' => 'required|array|min:1',
            'donation_ids.*' => 'integer|min:1',
            'receipt_type' => 'required|in:individual,consolidated,tax_deductible',
            'year' => 'required|integer|min:2020|max:2099',
            'include_details' => 'boolean',
            'format' => 'required|in:pdf,email'
        ];

        return $this->validate($data);
    }

    /**
     * Validate donor profile update data
     */
    public function validateDonorProfileUpdate(array $data): bool
    {
        $this->rules = [
            'donor_name' => 'required|min:2|max:100',
            'donor_email' => 'required|email|max:255',
            'donor_phone' => 'regex:/^[+]?[0-9]{10,15}$/',
            'address' => 'max:500',
            'city' => 'max:100',
            'state' => 'max:100',
            'country' => 'required|max:100',
            'postal_code' => 'max:20',
            'preferred_contact' => 'required|in:email,phone,postal,none',
            'communication_preferences' => 'array',
            'tax_id' => 'max:50',
            'company_name' => 'max:255',
            'is_organization' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate donation report generation
     */
    public function validateDonationReport(array $data): bool
    {
        $this->rules = [
            'report_type' => 'required|in:summary,detailed,tax_report,campaign_report,donor_report',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'level_scope' => 'in:global,godina,gamta,gurmu',
            'scope_id' => 'integer|min:1',
            'category' => 'array',
            'payment_method' => 'array',
            'donation_type' => 'array',
            'status' => 'array',
            'include_anonymous' => 'boolean',
            'include_refunded' => 'boolean',
            'group_by' => 'in:date,category,payment_method,campaign,level',
            'format' => 'required|in:pdf,excel,csv'
        ];

        return $this->validate($data);
    }

    /**
     * Validate bulk donation import
     */
    public function validateBulkDonationImport(array $data): bool
    {
        $this->rules = [
            'import_file' => 'required|file|mimes:text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'level_scope' => 'required|in:global,godina,gamta,gurmu',
            'scope_id' => 'required|integer|min:1',
            'default_category' => 'required|in:general,education,healthcare,infrastructure,emergency,cultural',
            'default_currency' => 'required|in:USD,EUR,ETB',
            'auto_generate_receipts' => 'boolean',
            'send_thank_you_emails' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate donation matching/pledge data
     */
    public function validateDonationMatching(array $data): bool
    {
        $this->rules = [
            'campaign_id' => 'integer|min:1',
            'matching_amount' => 'required|numeric|min:1',
            'match_ratio' => 'required|numeric|min:0.1|max:10', // 1:10 ratio max
            'max_match_per_donation' => 'numeric|min:1',
            'total_match_limit' => 'numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'matcher_name' => 'required|min:2|max:255',
            'matcher_type' => 'required|in:individual,organization,foundation,government',
            'conditions' => 'max:1000',
            'is_anonymous' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate recurring donation management
     */
    public function validateRecurringDonationUpdate(array $data): bool
    {
        $this->rules = [
            'donation_id' => 'required|integer|min:1',
            'action' => 'required|in:pause,resume,cancel,update_amount,update_frequency',
            'new_amount' => 'required_if:action,update_amount|numeric|min:1',
            'new_frequency' => 'required_if:action,update_frequency|in:monthly,quarterly,annual',
            'effective_date' => 'date',
            'reason' => 'max:500'
        ];

        return $this->validate($data);
    }

    /**
     * Define custom error messages for donation validation
     */
    protected function defineMessages(): void
    {
        parent::defineMessages();
        
        $this->messages = array_merge($this->messages, [
            'donor_name.required' => 'Donor name is required.',
            'donor_name.min' => 'Donor name must be at least 2 characters.',
            'donor_name.max' => 'Donor name may not exceed 100 characters.',
            
            'donor_email.required' => 'Donor email is required.',
            'donor_email.email' => 'Please provide a valid email address.',
            
            'donor_phone.regex' => 'Please provide a valid phone number.',
            
            'amount.required' => 'Donation amount is required.',
            'amount.numeric' => 'Donation amount must be a number.',
            'amount.min' => 'Minimum donation amount is $1.',
            'amount.max' => 'Maximum donation amount is $1,000,000.',
            
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Please select a valid currency.',
            
            'donation_type.required' => 'Donation type is required.',
            'donation_type.in' => 'Please select a valid donation type.',
            
            'category.required' => 'Donation category is required.',
            'category.in' => 'Please select a valid donation category.',
            
            'level_scope.required' => 'Organization level is required.',
            'level_scope.in' => 'Please select a valid organization level.',
            
            'scope_id.required' => 'Organization unit is required.',
            'scope_id.integer' => 'Please select a valid organization unit.',
            
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Please select a valid payment method.',
            
            'receipt_preference.required' => 'Receipt preference is required.',
            'receipt_preference.in' => 'Please select a valid receipt preference.',
            
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.boolean' => 'Please accept or decline the terms and conditions.',
            
            'start_date.required' => 'Start date is required for recurring donations.',
            'end_date.after' => 'End date must be after start date.',
            
            'card_last_four.required' => 'Card last four digits are required.',
            'card_last_four.numeric' => 'Card last four digits must be numeric.',
            'card_last_four.min' => 'Card last four digits must be 4 digits.',
            'card_last_four.max' => 'Card last four digits must be 4 digits.',
            
            'card_type.required' => 'Card type is required.',
            'card_type.in' => 'Please select a valid card type.',
            
            'bank_reference.required' => 'Bank reference is required for bank transfers.',
            
            'mobile_number.required' => 'Mobile number is required for mobile money payments.',
            'mobile_number.regex' => 'Please provide a valid mobile number.',
            
            'provider.required' => 'Mobile money provider is required.',
            'provider.in' => 'Please select a valid mobile money provider.',
            
            'check_number.required' => 'Check number is required for check payments.',
            'bank_name.required' => 'Bank name is required for check payments.',
            
            'title.required' => 'Campaign title is required.',
            'title.min' => 'Campaign title must be at least 5 characters.',
            'title.max' => 'Campaign title may not exceed 255 characters.',
            
            'description.required' => 'Campaign description is required.',
            'description.min' => 'Campaign description must be at least 50 characters.',
            'description.max' => 'Campaign description may not exceed 5000 characters.',
            
            'goal_amount.required' => 'Campaign goal amount is required.',
            'goal_amount.numeric' => 'Goal amount must be a number.',
            'goal_amount.min' => 'Minimum goal amount is $100.',
            'goal_amount.max' => 'Maximum goal amount is $10,000,000.',
            
            'start_date.required' => 'Campaign start date is required.',
            'end_date.required' => 'Campaign end date is required.',
            'end_date.after' => 'End date must be after start date.',
            
            'featured_image.image' => 'Featured image must be an image file.',
            'featured_image.max' => 'Featured image size may not exceed 5MB.',
            
            'video_url.url' => 'Please provide a valid video URL.',
            
            'refund_amount.required' => 'Refund amount is required.',
            'refund_amount.numeric' => 'Refund amount must be a number.',
            'refund_amount.min' => 'Minimum refund amount is $0.01.',
            
            'refund_reason.required' => 'Refund reason is required.',
            'refund_reason.in' => 'Please select a valid refund reason.',
            
            'reason_details.required' => 'Please provide details for the refund reason.',
            'reason_details.min' => 'Reason details must be at least 10 characters.',
            
            'refund_method.required' => 'Refund method is required.',
            'refund_method.in' => 'Please select a valid refund method.',
            
            'processed_by.required' => 'Processor ID is required.',
            
            'donation_ids.required' => 'Please select at least one donation.',
            'donation_ids.array' => 'Donation selection is invalid.',
            'donation_ids.min' => 'Please select at least one donation.',
            
            'receipt_type.required' => 'Receipt type is required.',
            'receipt_type.in' => 'Please select a valid receipt type.',
            
            'year.required' => 'Year is required for receipt generation.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be 2020 or later.',
            'year.max' => 'Year cannot exceed 2099.',
            
            'format.required' => 'Format is required.',
            'format.in' => 'Please select a valid format.',
            
            'country.required' => 'Country is required.',
            
            'preferred_contact.required' => 'Preferred contact method is required.',
            'preferred_contact.in' => 'Please select a valid contact method.',
            
            'report_type.required' => 'Report type is required.',
            'report_type.in' => 'Please select a valid report type.',
            
            'date_from.required' => 'Start date is required.',
            'date_to.required' => 'End date is required.',
            'date_to.after' => 'End date must be after start date.',
            
            'import_file.required' => 'Please select a file to import.',
            'import_file.mimes' => 'Import file must be CSV or Excel format.',
            
            'default_category.required' => 'Default category is required for import.',
            'default_currency.required' => 'Default currency is required for import.',
            
            'matching_amount.required' => 'Matching amount is required.',
            'matching_amount.numeric' => 'Matching amount must be a number.',
            'matching_amount.min' => 'Minimum matching amount is $1.',
            
            'match_ratio.required' => 'Match ratio is required.',
            'match_ratio.numeric' => 'Match ratio must be a number.',
            'match_ratio.min' => 'Minimum match ratio is 0.1:1.',
            'match_ratio.max' => 'Maximum match ratio is 10:1.',
            
            'matcher_name.required' => 'Matcher name is required.',
            'matcher_name.min' => 'Matcher name must be at least 2 characters.',
            
            'matcher_type.required' => 'Matcher type is required.',
            'matcher_type.in' => 'Please select a valid matcher type.',
            
            'action.required' => 'Action is required.',
            'action.in' => 'Please select a valid action.',
            
            'new_amount.required_if' => 'New amount is required for amount updates.',
            'new_frequency.required_if' => 'New frequency is required for frequency updates.'
        ]);
    }

    /**
     * Validate donation amount against campaign limits
     */
    protected function validateCampaignLimits(string $field, $value, array $campaignData): bool
    {
        if (isset($campaignData['min_donation_amount']) && $value < $campaignData['min_donation_amount']) {
            $this->addError($field, "Minimum donation amount for this campaign is {$campaignData['currency']} {$campaignData['min_donation_amount']}.");
            return false;
        }
        
        if (isset($campaignData['max_donation_amount']) && $value > $campaignData['max_donation_amount']) {
            $this->addError($field, "Maximum donation amount for this campaign is {$campaignData['currency']} {$campaignData['max_donation_amount']}.");
            return false;
        }
        
        return true;
    }

    /**
     * Validate individual donation data from bulk import
     */
    public function validateImportRow(array $rowData, int $rowNumber = 0): bool
    {
        $this->rules = [
            'donor_name' => 'required|min:2|max:100',
            'donor_email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:1',
            'donation_date' => 'required|date',
            'payment_method' => 'required|in:credit_card,bank_transfer,mobile_money,cash,check',
            'category' => 'in:general,education,healthcare,infrastructure,emergency,cultural'
        ];

        $result = $this->validate($rowData);
        
        // Add row number to error messages for bulk import
        if (!$result && $rowNumber > 0) {
            $modifiedErrors = [];
            foreach ($this->errors as $field => $messages) {
                $modifiedErrors[$field] = array_map(function($message) use ($rowNumber) {
                    return "Row {$rowNumber}: {$message}";
                }, $messages);
            }
            $this->errors = $modifiedErrors;
        }
        
        return $result;
    }
}