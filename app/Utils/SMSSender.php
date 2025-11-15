<?php

namespace App\Utils;

/**
 * SMSSender - SMS notification utility
 * 
 * Provides SMS sending capabilities with multiple provider support,
 * template management, and delivery tracking.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class SMSSender
{
    private array $config = [
        'provider' => 'twilio', // twilio, nexmo, sparrow, custom
        'providers' => [
            'twilio' => [
                'account_sid' => '',
                'auth_token' => '',
                'from_number' => '',
                'api_url' => 'https://api.twilio.com/2010-04-01/Accounts/{account_sid}/Messages.json'
            ],
            'nexmo' => [
                'api_key' => '',
                'api_secret' => '',
                'from' => '',
                'api_url' => 'https://rest.nexmo.com/sms/json'
            ],
            'sparrow' => [
                'token' => '',
                'from' => '',
                'api_url' => 'http://api.sparrowsms.com/v2/sms/'
            ],
            'custom' => [
                'api_url' => '',
                'api_key' => '',
                'from' => '',
                'headers' => []
            ]
        ],
        'default_country_code' => '+977', // Nepal
        'max_length' => 160,
        'unicode_support' => true,
        'queue' => [
            'enabled' => false,
            'batch_size' => 50,
            'delay' => 1
        ],
        'templates' => [
            'welcome' => 'Welcome to ABO-WBO Management System! Your account has been created successfully.',
            'password_reset' => 'Your password reset code is: {code}. Valid for 10 minutes.',
            'task_assignment' => 'New task assigned: {task_title}. Due: {due_date}',
            'meeting_reminder' => 'Meeting reminder: {meeting_title} at {time} on {date}',
            'donation_receipt' => 'Thank you for your donation of Rs. {amount}. Receipt: {receipt_number}',
            'event_reminder' => 'Event reminder: {event_title} on {date} at {location}',
            'otp' => 'Your OTP code is: {otp}. Valid for {expiry} minutes.'
        ]
    ];

    private array $smsQueue = [];
    private array $errors = [];
    private array $deliveryReports = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    /**
     * Send single SMS
     */
    public function sendSMS(
        string $to,
        string $message,
        array $options = []
    ): array {
        $this->errors = [];

        // Validate and format phone number
        $to = $this->formatPhoneNumber($to);
        if (!$this->isValidPhoneNumber($to)) {
            $this->errors[] = 'Invalid phone number format';
            return ['success' => false, 'errors' => $this->errors];
        }

        // Validate message length
        if (!$this->validateMessageLength($message)) {
            return ['success' => false, 'errors' => $this->errors];
        }

        // Get provider configuration
        $provider = $options['provider'] ?? $this->config['provider'];
        $providerConfig = $this->config['providers'][$provider] ?? null;

        if (!$providerConfig) {
            $this->errors[] = "Provider '{$provider}' not configured";
            return ['success' => false, 'errors' => $this->errors];
        }

        // Send SMS based on provider
        $result = match ($provider) {
            'twilio' => $this->sendViaTwilio($to, $message, $providerConfig),
            'nexmo' => $this->sendViaNexmo($to, $message, $providerConfig),
            'sparrow' => $this->sendViaSparrow($to, $message, $providerConfig),
            'custom' => $this->sendViaCustom($to, $message, $providerConfig),
            default => ['success' => false, 'error' => 'Unknown provider']
        };

        // Log SMS activity
        $this->logSMS($to, $message, $result['success'] ? 'sent' : 'failed', $result['error'] ?? '');

        return $result;
    }

    /**
     * Send SMS using Twilio
     */
    private function sendViaTwilio(string $to, string $message, array $config): array
    {
        $url = str_replace('{account_sid}', $config['account_sid'], $config['api_url']);
        
        $data = [
            'From' => $config['from_number'],
            'To' => $to,
            'Body' => $message
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_USERPWD => $config['account_sid'] . ':' . $config['auth_token'],
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        $responseData = json_decode($response, true);

        if ($httpCode === 201 && isset($responseData['sid'])) {
            return [
                'success' => true,
                'message_id' => $responseData['sid'],
                'status' => $responseData['status'] ?? 'sent',
                'provider' => 'twilio'
            ];
        }

        $errorMessage = $responseData['message'] ?? 'Unknown error';
        return ['success' => false, 'error' => $errorMessage];
    }

    /**
     * Send SMS using Nexmo (Vonage)
     */
    private function sendViaNexmo(string $to, string $message, array $config): array
    {
        $data = [
            'api_key' => $config['api_key'],
            'api_secret' => $config['api_secret'],
            'from' => $config['from'],
            'to' => ltrim($to, '+'),
            'text' => $message,
            'type' => $this->containsUnicode($message) ? 'unicode' : 'text'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $config['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        $responseData = json_decode($response, true);

        if ($httpCode === 200 && isset($responseData['messages'][0])) {
            $messageData = $responseData['messages'][0];
            
            if ($messageData['status'] === '0') {
                return [
                    'success' => true,
                    'message_id' => $messageData['message-id'],
                    'status' => 'sent',
                    'provider' => 'nexmo'
                ];
            }

            return ['success' => false, 'error' => $messageData['error-text'] ?? 'Unknown error'];
        }

        return ['success' => false, 'error' => 'Invalid response format'];
    }

    /**
     * Send SMS using Sparrow SMS (Nepal)
     */
    private function sendViaSparrow(string $to, string $message, array $config): array
    {
        $data = [
            'token' => $config['token'],
            'from' => $config['from'],
            'to' => ltrim($to, '+'),
            'text' => $message
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $config['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        $responseData = json_decode($response, true);

        if ($httpCode === 200 && isset($responseData['response_code'])) {
            if ($responseData['response_code'] === 'OK') {
                return [
                    'success' => true,
                    'message_id' => $responseData['id'] ?? uniqid(),
                    'status' => 'sent',
                    'provider' => 'sparrow'
                ];
            }

            return ['success' => false, 'error' => $responseData['message'] ?? 'Unknown error'];
        }

        return ['success' => false, 'error' => 'Invalid response format'];
    }

    /**
     * Send SMS using custom provider
     */
    private function sendViaCustom(string $to, string $message, array $config): array
    {
        $data = [
            'to' => $to,
            'message' => $message,
            'from' => $config['from']
        ];

        $headers = array_merge([
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config['api_key']
        ], $config['headers'] ?? []);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $config['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        $responseData = json_decode($response, true);

        // Assume success if HTTP 200/201
        if (in_array($httpCode, [200, 201])) {
            return [
                'success' => true,
                'message_id' => $responseData['id'] ?? uniqid(),
                'status' => 'sent',
                'provider' => 'custom'
            ];
        }

        return ['success' => false, 'error' => $responseData['error'] ?? 'HTTP ' . $httpCode];
    }

    /**
     * Send SMS using template
     */
    public function sendTemplate(
        string $to,
        string $template,
        array $variables = [],
        array $options = []
    ): array {
        if (!isset($this->config['templates'][$template])) {
            return ['success' => false, 'error' => "Template '{$template}' not found"];
        }

        $message = $this->config['templates'][$template];
        
        // Replace variables
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $this->sendSMS($to, $message, $options);
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'results' => []
        ];

        foreach ($recipients as $recipient) {
            $result = $this->sendSMS($recipient, $message, $options);
            $results['results'][$recipient] = $result;
            
            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }

            // Small delay to prevent rate limiting
            if (isset($options['delay'])) {
                usleep($options['delay'] * 1000); // Convert to microseconds
            }
        }

        return $results;
    }

    /**
     * Add SMS to queue
     */
    public function queueSMS(
        string $to,
        string $message,
        array $options = []
    ): void {
        $this->smsQueue[] = [
            'to' => $to,
            'message' => $message,
            'options' => $options,
            'queued_at' => time()
        ];
    }

    /**
     * Process SMS queue
     */
    public function processQueue(): array
    {
        if (!$this->config['queue']['enabled'] || empty($this->smsQueue)) {
            return ['processed' => 0, 'failed' => 0];
        }

        $processed = 0;
        $failed = 0;
        $batchSize = $this->config['queue']['batch_size'];
        $delay = $this->config['queue']['delay'];

        $batches = array_chunk($this->smsQueue, $batchSize);

        foreach ($batches as $batch) {
            foreach ($batch as $sms) {
                $result = $this->sendSMS($sms['to'], $sms['message'], $sms['options']);
                
                if ($result['success']) {
                    $processed++;
                } else {
                    $failed++;
                }
            }

            // Delay between batches
            if (count($batches) > 1 && $delay > 0) {
                sleep($delay);
            }
        }

        // Clear processed SMS
        $this->smsQueue = [];

        return ['processed' => $processed, 'failed' => $failed];
    }

    /**
     * Send OTP SMS
     */
    public function sendOTP(string $to, string $otp, int $expiryMinutes = 10): array
    {
        return $this->sendTemplate($to, 'otp', [
            'otp' => $otp,
            'expiry' => $expiryMinutes
        ]);
    }

    /**
     * Send welcome SMS
     */
    public function sendWelcomeSMS(string $to, string $name = ''): array
    {
        $message = $this->config['templates']['welcome'];
        if ($name) {
            $message = "Hi {$name}! " . $message;
        }
        
        return $this->sendSMS($to, $message);
    }

    /**
     * Send task assignment SMS
     */
    public function sendTaskAssignmentSMS(
        string $to,
        string $taskTitle,
        string $dueDate = ''
    ): array {
        return $this->sendTemplate($to, 'task_assignment', [
            'task_title' => $taskTitle,
            'due_date' => $dueDate ?: 'Not specified'
        ]);
    }

    /**
     * Send meeting reminder SMS
     */
    public function sendMeetingReminderSMS(
        string $to,
        string $meetingTitle,
        string $date,
        string $time
    ): array {
        return $this->sendTemplate($to, 'meeting_reminder', [
            'meeting_title' => $meetingTitle,
            'date' => $date,
            'time' => $time
        ]);
    }

    /**
     * Send donation receipt SMS
     */
    public function sendDonationReceiptSMS(
        string $to,
        float $amount,
        string $receiptNumber
    ): array {
        return $this->sendTemplate($to, 'donation_receipt', [
            'amount' => number_format($amount, 2),
            'receipt_number' => $receiptNumber
        ]);
    }

    /**
     * Send event reminder SMS
     */
    public function sendEventReminderSMS(
        string $to,
        string $eventTitle,
        string $date,
        string $location = ''
    ): array {
        return $this->sendTemplate($to, 'event_reminder', [
            'event_title' => $eventTitle,
            'date' => $date,
            'location' => $location ?: 'TBD'
        ]);
    }

    /**
     * Format phone number
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Add default country code if not present
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '0')) {
                // Remove leading zero and add country code
                $phone = $this->config['default_country_code'] . substr($phone, 1);
            } else {
                $phone = $this->config['default_country_code'] . $phone;
            }
        }

        return $phone;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        return preg_match('/^\+[1-9]\d{6,14}$/', $phone);
    }

    /**
     * Validate message length
     */
    private function validateMessageLength(string $message): bool
    {
        $length = $this->containsUnicode($message) ? 
                 mb_strlen($message, 'UTF-8') : 
                 strlen($message);

        $maxLength = $this->containsUnicode($message) ? 70 : $this->config['max_length'];

        if ($length > $maxLength) {
            $this->errors[] = "Message too long. Maximum {$maxLength} characters allowed.";
            return false;
        }

        return true;
    }

    /**
     * Check if message contains Unicode characters
     */
    private function containsUnicode(string $text): bool
    {
        return mb_strlen($text, 'UTF-8') !== strlen($text);
    }

    /**
     * Log SMS activity
     */
    private function logSMS(string $to, string $message, string $status, string $error = ''): void
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'message' => substr($message, 0, 50) . (strlen($message) > 50 ? '...' : ''),
            'status' => $status,
            'error' => $error
        ];

        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/storage/logs/sms.log';
        $logEntry = json_encode($logData) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get SMS queue count
     */
    public function getQueueCount(): int
    {
        return count($this->smsQueue);
    }

    /**
     * Clear SMS queue
     */
    public function clearQueue(): void
    {
        $this->smsQueue = [];
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Add custom template
     */
    public function addTemplate(string $name, string $template): void
    {
        $this->config['templates'][$name] = $template;
    }

    /**
     * Get templates
     */
    public function getTemplates(): array
    {
        return $this->config['templates'];
    }

    /**
     * Update configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    /**
     * Get configuration (without sensitive data)
     */
    public function getConfig(): array
    {
        $config = $this->config;
        
        // Remove sensitive information
        foreach ($config['providers'] as &$provider) {
            unset($provider['auth_token'], $provider['api_secret'], $provider['token'], $provider['api_key']);
        }
        
        return $config;
    }

    /**
     * Static factory method
     */
    public static function create(array $config = []): self
    {
        return new self($config);
    }
}