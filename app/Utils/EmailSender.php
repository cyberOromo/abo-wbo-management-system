<?php

namespace App\Utils;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailSender - Email notification utility
 * 
 * Provides email sending capabilities with template support,
 * attachments, HTML/plain text content, and queue management.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class EmailSender
{
    private PHPMailer $mailer;
    private array $config = [
        'smtp' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'security' => 'tls', // tls, ssl, or false
            'auth' => true,
            'username' => '',
            'password' => ''
        ],
        'from' => [
            'email' => 'noreply@abo-wbo.org',
            'name' => 'ABO-WBO Management System'
        ],
        'reply_to' => [
            'email' => 'support@abo-wbo.org',
            'name' => 'Support Team'
        ],
        'charset' => 'UTF-8',
        'encoding' => '8bit',
        'word_wrap' => 70,
        'html' => true,
        'debug' => false,
        'queue' => [
            'enabled' => false,
            'batch_size' => 10,
            'delay' => 1 // seconds between batches
        ],
        'templates' => [
            'path' => '/resources/email-templates',
            'extension' => '.html'
        ]
    ];

    private array $emailQueue = [];
    private array $errors = [];
    private string $templatePath;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->templatePath = $_SERVER['DOCUMENT_ROOT'] . $this->config['templates']['path'];
        $this->initializeMailer();
    }

    /**
     * Initialize PHPMailer instance
     */
    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            if ($this->config['debug']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp']['host'];
            $this->mailer->SMTPAuth = $this->config['smtp']['auth'];
            $this->mailer->Username = $this->config['smtp']['username'];
            $this->mailer->Password = $this->config['smtp']['password'];
            
            // Set security
            if ($this->config['smtp']['security'] === 'tls') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($this->config['smtp']['security'] === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            
            $this->mailer->Port = $this->config['smtp']['port'];
            
            // Content settings
            $this->mailer->isHTML($this->config['html']);
            $this->mailer->CharSet = $this->config['charset'];
            $this->mailer->Encoding = $this->config['encoding'];
            $this->mailer->WordWrap = $this->config['word_wrap'];
            
            // Default sender
            $this->mailer->setFrom(
                $this->config['from']['email'],
                $this->config['from']['name']
            );
            
            // Default reply-to
            $this->mailer->addReplyTo(
                $this->config['reply_to']['email'],
                $this->config['reply_to']['name']
            );

        } catch (Exception $e) {
            $this->errors[] = 'Failed to initialize mailer: ' . $e->getMessage();
        }
    }

    /**
     * Send single email
     */
    public function sendEmail(
        string|array $to,
        string $subject,
        string $body,
        array $options = []
    ): bool {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearCustomHeaders();
            $this->mailer->clearReplyTos();

            // Set recipients
            if (is_string($to)) {
                $this->mailer->addAddress($to);
            } else {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            }

            // Set CC recipients
            if (!empty($options['cc'])) {
                foreach ((array)$options['cc'] as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addCC($name);
                    } else {
                        $this->mailer->addCC($email, $name);
                    }
                }
            }

            // Set BCC recipients
            if (!empty($options['bcc'])) {
                foreach ((array)$options['bcc'] as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addBCC($name);
                    } else {
                        $this->mailer->addBCC($email, $name);
                    }
                }
            }

            // Set custom from address
            if (!empty($options['from'])) {
                $this->mailer->setFrom(
                    $options['from']['email'] ?? $options['from'],
                    $options['from']['name'] ?? ''
                );
            }

            // Set custom reply-to
            if (!empty($options['reply_to'])) {
                $this->mailer->clearReplyTos();
                $this->mailer->addReplyTo(
                    $options['reply_to']['email'] ?? $options['reply_to'],
                    $options['reply_to']['name'] ?? ''
                );
            }

            // Set priority
            if (!empty($options['priority'])) {
                $priorityMap = [
                    'high' => 1,
                    'normal' => 3,
                    'low' => 5
                ];
                $this->mailer->Priority = $priorityMap[$options['priority']] ?? 3;
            }

            // Add attachments
            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_string($attachment)) {
                        $this->mailer->addAttachment($attachment);
                    } else {
                        $this->mailer->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? '',
                            $attachment['encoding'] ?? 'base64',
                            $attachment['type'] ?? ''
                        );
                    }
                }
            }

            // Add embedded images
            if (!empty($options['embedded'])) {
                foreach ($options['embedded'] as $cid => $imagePath) {
                    $this->mailer->addEmbeddedImage($imagePath, $cid);
                }
            }

            // Set custom headers
            if (!empty($options['headers'])) {
                foreach ($options['headers'] as $name => $value) {
                    $this->mailer->addCustomHeader($name, $value);
                }
            }

            // Set subject and body
            $this->mailer->Subject = $subject;
            
            if ($this->config['html']) {
                $this->mailer->Body = $body;
                // Add plain text version if provided
                if (!empty($options['alt_body'])) {
                    $this->mailer->AltBody = $options['alt_body'];
                }
            } else {
                $this->mailer->Body = strip_tags($body);
            }

            // Send email
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logEmail($to, $subject, 'sent');
            }
            
            return $result;

        } catch (Exception $e) {
            $this->errors[] = 'Failed to send email: ' . $e->getMessage();
            $this->logEmail($to, $subject, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using template
     */
    public function sendTemplate(
        string|array $to,
        string $template,
        array $variables = [],
        array $options = []
    ): bool {
        $templateContent = $this->loadTemplate($template, $variables);
        if (!$templateContent) {
            return false;
        }

        $subject = $templateContent['subject'] ?? 'Notification';
        $body = $templateContent['body'];

        return $this->sendEmail($to, $subject, $body, $options);
    }

    /**
     * Load email template
     */
    private function loadTemplate(string $template, array $variables = []): array|false
    {
        $templateFile = $this->templatePath . '/' . $template . $this->config['templates']['extension'];
        
        if (!file_exists($templateFile)) {
            $this->errors[] = "Template not found: {$template}";
            return false;
        }

        $content = file_get_contents($templateFile);
        
        // Replace variables
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        // Extract subject from template if present
        $subject = '';
        if (preg_match('/<title>(.*?)<\/title>/s', $content, $matches)) {
            $subject = trim($matches[1]);
            $content = preg_replace('/<title>.*?<\/title>/s', '', $content);
        }

        return [
            'subject' => $subject,
            'body' => $content
        ];
    }

    /**
     * Add email to queue
     */
    public function queueEmail(
        string|array $to,
        string $subject,
        string $body,
        array $options = []
    ): void {
        $this->emailQueue[] = [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'options' => $options,
            'queued_at' => time()
        ];
    }

    /**
     * Process email queue
     */
    public function processQueue(): array
    {
        if (!$this->config['queue']['enabled'] || empty($this->emailQueue)) {
            return ['processed' => 0, 'failed' => 0];
        }

        $processed = 0;
        $failed = 0;
        $batchSize = $this->config['queue']['batch_size'];
        $delay = $this->config['queue']['delay'];

        $batches = array_chunk($this->emailQueue, $batchSize);

        foreach ($batches as $batch) {
            foreach ($batch as $email) {
                if ($this->sendEmail(
                    $email['to'],
                    $email['subject'],
                    $email['body'],
                    $email['options']
                )) {
                    $processed++;
                } else {
                    $failed++;
                }
            }

            // Delay between batches to prevent overwhelming SMTP server
            if (count($batches) > 1 && $delay > 0) {
                sleep($delay);
            }
        }

        // Clear processed emails
        $this->emailQueue = [];

        return ['processed' => $processed, 'failed' => $failed];
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail(string $email, string $name, array $credentials = []): bool
    {
        $variables = [
            'name' => $name,
            'email' => $email,
            'login_url' => $this->getBaseUrl() . '/auth/login',
            'username' => $credentials['username'] ?? $email,
            'password' => $credentials['password'] ?? 'Please check your registration confirmation',
            'support_email' => $this->config['reply_to']['email']
        ];

        return $this->sendTemplate($email, 'welcome', $variables);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $email, string $name, string $resetToken): bool
    {
        $variables = [
            'name' => $name,
            'reset_url' => $this->getBaseUrl() . "/auth/reset-password?token={$resetToken}",
            'expiry_time' => '1 hour',
            'support_email' => $this->config['reply_to']['email']
        ];

        return $this->sendTemplate($email, 'password-reset', $variables);
    }

    /**
     * Send task assignment notification
     */
    public function sendTaskAssignmentEmail(
        string $email,
        string $assigneeName,
        array $taskData
    ): bool {
        $variables = [
            'assignee_name' => $assigneeName,
            'task_title' => $taskData['title'],
            'task_description' => $taskData['description'] ?? 'No description provided',
            'due_date' => $taskData['due_date'] ?? 'Not specified',
            'priority' => $taskData['priority'] ?? 'Normal',
            'assigned_by' => $taskData['assigned_by'] ?? 'System',
            'task_url' => $this->getBaseUrl() . "/tasks/{$taskData['id']}",
            'dashboard_url' => $this->getBaseUrl() . '/dashboard'
        ];

        return $this->sendTemplate($email, 'task-assignment', $variables);
    }

    /**
     * Send meeting invitation
     */
    public function sendMeetingInvitation(
        string|array $emails,
        array $meetingData
    ): bool {
        $variables = [
            'meeting_title' => $meetingData['title'],
            'meeting_date' => $meetingData['date'],
            'meeting_time' => $meetingData['time'],
            'meeting_location' => $meetingData['location'] ?? 'TBD',
            'meeting_agenda' => $meetingData['agenda'] ?? 'Will be shared separately',
            'organizer' => $meetingData['organizer'] ?? 'System',
            'meeting_url' => $this->getBaseUrl() . "/meetings/{$meetingData['id']}",
            'rsvp_url' => $this->getBaseUrl() . "/meetings/{$meetingData['id']}/rsvp"
        ];

        return $this->sendTemplate($emails, 'meeting-invitation', $variables);
    }

    /**
     * Send donation receipt email
     */
    public function sendDonationReceiptEmail(
        string $email,
        string $donorName,
        array $donationData
    ): bool {
        $variables = [
            'donor_name' => $donorName,
            'amount' => 'Rs. ' . number_format($donationData['amount'], 2),
            'receipt_number' => $donationData['receipt_number'],
            'donation_date' => $donationData['date'],
            'purpose' => $donationData['purpose'] ?? 'General Donation',
            'payment_method' => $donationData['payment_method'] ?? 'Cash',
            'receipt_url' => $this->getBaseUrl() . "/donations/receipt/{$donationData['receipt_number']}"
        ];

        $options = [];
        
        // Attach PDF receipt if available
        if (!empty($donationData['receipt_pdf'])) {
            $options['attachments'] = [
                [
                    'path' => $donationData['receipt_pdf'],
                    'name' => "Receipt_{$donationData['receipt_number']}.pdf"
                ]
            ];
        }

        return $this->sendTemplate($email, 'donation-receipt', $variables, $options);
    }

    /**
     * Send event reminder
     */
    public function sendEventReminder(
        string|array $emails,
        array $eventData,
        string $reminderType = 'upcoming'
    ): bool {
        $variables = [
            'event_title' => $eventData['title'],
            'event_date' => $eventData['date'],
            'event_time' => $eventData['time'] ?? 'All day',
            'event_location' => $eventData['location'] ?? 'TBD',
            'event_description' => $eventData['description'] ?? '',
            'event_url' => $this->getBaseUrl() . "/events/{$eventData['id']}",
            'reminder_type' => $reminderType
        ];

        $template = $reminderType === 'upcoming' ? 'event-reminder' : 'event-reminder-urgent';
        
        return $this->sendTemplate($emails, $template, $variables);
    }

    /**
     * Test email configuration
     */
    public function testConfiguration(): bool
    {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return true;
        } catch (Exception $e) {
            $this->errors[] = 'SMTP connection failed: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Log email activity
     */
    private function logEmail(
        string|array $to,
        string $subject,
        string $status,
        string $error = ''
    ): void {
        $recipients = is_array($to) ? implode(', ', array_keys($to)) : $to;
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $recipients,
            'subject' => $subject,
            'status' => $status,
            'error' => $error
        ];

        // Log to file or database
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/storage/logs/email.log';
        $logEntry = json_encode($logData) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get base URL
     */
    private function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Get email queue count
     */
    public function getQueueCount(): int
    {
        return count($this->emailQueue);
    }

    /**
     * Clear email queue
     */
    public function clearQueue(): void
    {
        $this->emailQueue = [];
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Clear errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Update configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->initializeMailer();
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        // Remove sensitive information
        $config = $this->config;
        unset($config['smtp']['password']);
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