<?php

namespace App\Utils;

require_once __DIR__ . '/../../vendor/autoload.php';

use TCPDF;

/**
 * PDFGenerator - PDF document generation utility
 * 
 * Provides PDF generation for receipts, reports, certificates,
 * and other documents with templating and styling support.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class PDFGenerator
{
    private TCPDF $pdf;
    private array $config = [
        'orientation' => 'P', // P=Portrait, L=Landscape
        'unit' => 'mm',
        'format' => 'A4',
        'unicode' => true,
        'encoding' => 'UTF-8',
        'diskcache' => false,
        'margins' => [
            'top' => 15,
            'right' => 15,
            'bottom' => 15,
            'left' => 15
        ],
        'header' => [
            'enabled' => true,
            'height' => 20,
            'logo' => null,
            'title' => 'ABO-WBO Management System',
            'string' => ''
        ],
        'footer' => [
            'enabled' => true,
            'height' => 15,
            'text' => 'Generated on {date} | Page {page} of {total}'
        ],
        'fonts' => [
            'default' => 'helvetica',
            'header' => 'helvetica',
            'body' => 'helvetica',
            'footer' => 'helvetica'
        ],
        'colors' => [
            'primary' => [0, 102, 204],
            'secondary' => [102, 102, 102],
            'success' => [40, 167, 69],
            'danger' => [220, 53, 69],
            'warning' => [255, 193, 7],
            'info' => [23, 162, 184],
            'light' => [248, 249, 250],
            'dark' => [52, 58, 64]
        ]
    ];

    private array $templateVars = [];
    private string $currentTemplate = '';

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->initializePDF();
    }

    /**
     * Initialize TCPDF instance
     */
    private function initializePDF(): void
    {
        $this->pdf = new TCPDF(
            $this->config['orientation'],
            $this->config['unit'],
            $this->config['format'],
            $this->config['unicode'],
            $this->config['encoding'],
            $this->config['diskcache']
        );

        // Set document information
        $this->pdf->SetCreator('ABO-WBO Management System');
        $this->pdf->SetAuthor('System Generated');
        $this->pdf->SetTitle('Document');
        $this->pdf->SetSubject('Generated Document');

        // Set margins
        $margins = $this->config['margins'];
        $this->pdf->SetMargins($margins['left'], $margins['top'], $margins['right']);
        $this->pdf->SetAutoPageBreak(true, $margins['bottom']);

        // Set header and footer
        if ($this->config['header']['enabled']) {
            $this->setupHeader();
        }
        
        if ($this->config['footer']['enabled']) {
            $this->setupFooter();
        }

        // Set default font
        $this->pdf->SetFont($this->config['fonts']['default'], '', 12);
    }

    /**
     * Setup PDF header
     */
    private function setupHeader(): void
    {
        $header = $this->config['header'];
        
        // Custom header class for TCPDF
        $headerClass = new class($this->pdf, $header) {
            private $pdf;
            private $config;

            public function __construct($pdf, $config)
            {
                $this->pdf = $pdf;
                $this->config = $config;
            }

            public function render()
            {
                // Header background
                $this->pdf->SetFillColor(240, 240, 240);
                $this->pdf->Rect(0, 0, $this->pdf->getPageWidth(), $this->config['height'], 'F');
                
                // Logo
                if ($this->config['logo'] && file_exists($this->config['logo'])) {
                    $this->pdf->Image($this->config['logo'], 15, 5, 20, 10);
                }
                
                // Title
                $this->pdf->SetFont('helvetica', 'B', 14);
                $this->pdf->SetTextColor(0, 102, 204);
                $this->pdf->SetXY(40, 8);
                $this->pdf->Cell(0, 5, $this->config['title'], 0, 1, 'L');
                
                // Subtitle/String
                if ($this->config['string']) {
                    $this->pdf->SetFont('helvetica', '', 10);
                    $this->pdf->SetTextColor(102, 102, 102);
                    $this->pdf->SetXY(40, 13);
                    $this->pdf->Cell(0, 3, $this->config['string'], 0, 1, 'L');
                }
            }
        };

        // Override TCPDF header method
        $this->pdf->setHeaderCallback([$headerClass, 'render']);
        $this->pdf->setHeaderMargin($this->config['header']['height']);
    }

    /**
     * Setup PDF footer
     */
    private function setupFooter(): void
    {
        $footer = $this->config['footer'];
        
        $footerClass = new class($this->pdf, $footer) {
            private $pdf;
            private $config;

            public function __construct($pdf, $config)
            {
                $this->pdf = $pdf;
                $this->config = $config;
            }

            public function render()
            {
                $this->pdf->SetY(-15);
                $this->pdf->SetFont('helvetica', '', 8);
                $this->pdf->SetTextColor(102, 102, 102);
                
                $text = str_replace(
                    ['{date}', '{page}', '{total}'],
                    [date('Y-m-d H:i:s'), $this->pdf->getAliasNumPage(), $this->pdf->getAliasNbPages()],
                    $this->config['text']
                );
                
                $this->pdf->Cell(0, 10, $text, 0, 0, 'C');
            }
        };

        $this->pdf->setFooterCallback([$footerClass, 'render']);
        $this->pdf->setFooterMargin($this->config['footer']['height']);
    }

    /**
     * Generate donation receipt
     */
    public function generateDonationReceipt(array $data): string
    {
        $this->pdf->AddPage();
        
        // Receipt header
        $this->addTitle('DONATION RECEIPT', 'center', 18, true);
        $this->addSpacing(10);
        
        // Receipt number and date
        $this->addText("Receipt No: {$data['receipt_number']}", 'right', 12, false);
        $this->addText("Date: {$data['date']}", 'right', 12, false);
        $this->addSpacing(10);
        
        // Donor information
        $this->addSectionHeader('DONOR INFORMATION');
        $this->addKeyValue('Name', $data['donor_name']);
        $this->addKeyValue('Address', $data['donor_address'] ?? 'N/A');
        $this->addKeyValue('Phone', $data['donor_phone'] ?? 'N/A');
        $this->addKeyValue('Email', $data['donor_email'] ?? 'N/A');
        $this->addSpacing(10);
        
        // Donation details
        $this->addSectionHeader('DONATION DETAILS');
        $this->addKeyValue('Amount', 'Rs. ' . number_format($data['amount'], 2));
        $this->addKeyValue('Purpose', $data['purpose'] ?? 'General Donation');
        $this->addKeyValue('Payment Method', $data['payment_method'] ?? 'Cash');
        if (!empty($data['transaction_id'])) {
            $this->addKeyValue('Transaction ID', $data['transaction_id']);
        }
        $this->addSpacing(15);
        
        // Thank you message
        $this->addText('Thank you for your generous donation!', 'center', 14, true);
        $this->addSpacing(20);
        
        // Signature section
        $this->addText('_________________________', 'right', 10, false);
        $this->addText('Authorized Signature', 'right', 10, false);
        
        return $this->output('donation_receipt_' . $data['receipt_number'] . '.pdf');
    }

    /**
     * Generate meeting minutes report
     */
    public function generateMeetingMinutes(array $data): string
    {
        $this->pdf->AddPage();
        
        // Title
        $this->addTitle('MEETING MINUTES', 'center', 18, true);
        $this->addSpacing(10);
        
        // Meeting info
        $this->addKeyValue('Meeting Title', $data['title']);
        $this->addKeyValue('Date & Time', $data['date_time']);
        $this->addKeyValue('Location', $data['location'] ?? 'N/A');
        $this->addKeyValue('Chair Person', $data['chair_person'] ?? 'N/A');
        $this->addSpacing(10);
        
        // Attendees
        if (!empty($data['attendees'])) {
            $this->addSectionHeader('ATTENDEES');
            foreach ($data['attendees'] as $attendee) {
                $this->addText('• ' . $attendee, 'left', 11, false);
            }
            $this->addSpacing(10);
        }
        
        // Agenda items
        if (!empty($data['agenda_items'])) {
            $this->addSectionHeader('AGENDA ITEMS');
            foreach ($data['agenda_items'] as $i => $item) {
                $this->addText(($i + 1) . '. ' . $item, 'left', 11, false);
            }
            $this->addSpacing(10);
        }
        
        // Discussion points
        if (!empty($data['discussion'])) {
            $this->addSectionHeader('DISCUSSION');
            $this->addText($data['discussion'], 'left', 11, false);
            $this->addSpacing(10);
        }
        
        // Decisions made
        if (!empty($data['decisions'])) {
            $this->addSectionHeader('DECISIONS MADE');
            foreach ($data['decisions'] as $i => $decision) {
                $this->addText(($i + 1) . '. ' . $decision, 'left', 11, false);
            }
            $this->addSpacing(10);
        }
        
        // Action items
        if (!empty($data['action_items'])) {
            $this->addSectionHeader('ACTION ITEMS');
            foreach ($data['action_items'] as $item) {
                $this->addText('• ' . $item['task'] . ' (Assigned to: ' . $item['assignee'] . ', Due: ' . $item['due_date'] . ')', 'left', 11, false);
            }
        }
        
        return $this->output('meeting_minutes_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Generate task report
     */
    public function generateTaskReport(array $data): string
    {
        $this->pdf->AddPage();
        
        // Title
        $this->addTitle('TASK REPORT', 'center', 18, true);
        $this->addSpacing(10);
        
        // Report period
        $this->addKeyValue('Report Period', $data['period'] ?? 'All Time');
        $this->addKeyValue('Generated On', date('Y-m-d H:i:s'));
        $this->addSpacing(10);
        
        // Summary statistics
        if (!empty($data['summary'])) {
            $this->addSectionHeader('SUMMARY');
            foreach ($data['summary'] as $key => $value) {
                $this->addKeyValue(ucwords(str_replace('_', ' ', $key)), $value);
            }
            $this->addSpacing(10);
        }
        
        // Tasks by status
        if (!empty($data['tasks_by_status'])) {
            $this->addSectionHeader('TASKS BY STATUS');
            foreach ($data['tasks_by_status'] as $status => $count) {
                $this->addKeyValue(ucfirst($status), $count);
            }
            $this->addSpacing(10);
        }
        
        // Recent tasks
        if (!empty($data['recent_tasks'])) {
            $this->addSectionHeader('RECENT TASKS');
            
            // Table header
            $this->pdf->SetFont($this->config['fonts']['body'], 'B', 10);
            $this->pdf->SetFillColor(240, 240, 240);
            $this->pdf->Cell(60, 8, 'Task Title', 1, 0, 'L', true);
            $this->pdf->Cell(30, 8, 'Assigned To', 1, 0, 'L', true);
            $this->pdf->Cell(20, 8, 'Status', 1, 0, 'L', true);
            $this->pdf->Cell(25, 8, 'Due Date', 1, 0, 'L', true);
            $this->pdf->Cell(20, 8, 'Priority', 1, 1, 'L', true);
            
            // Table rows
            $this->pdf->SetFont($this->config['fonts']['body'], '', 9);
            foreach ($data['recent_tasks'] as $task) {
                $this->pdf->Cell(60, 6, substr($task['title'], 0, 30), 1, 0, 'L');
                $this->pdf->Cell(30, 6, substr($task['assigned_to'], 0, 15), 1, 0, 'L');
                $this->pdf->Cell(20, 6, $task['status'], 1, 0, 'L');
                $this->pdf->Cell(25, 6, $task['due_date'], 1, 0, 'L');
                $this->pdf->Cell(20, 6, $task['priority'], 1, 1, 'L');
            }
        }
        
        return $this->output('task_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Generate membership certificate
     */
    public function generateMembershipCertificate(array $data): string
    {
        $this->pdf->AddPage();
        
        // Certificate border
        $this->pdf->SetLineWidth(2);
        $this->pdf->SetDrawColor(0, 102, 204);
        $this->pdf->Rect(10, 10, $this->pdf->getPageWidth() - 20, $this->pdf->getPageHeight() - 20);
        
        $this->pdf->SetLineWidth(1);
        $this->pdf->Rect(15, 15, $this->pdf->getPageWidth() - 30, $this->pdf->getPageHeight() - 30);
        
        $this->addSpacing(30);
        
        // Certificate title
        $this->addTitle('CERTIFICATE OF MEMBERSHIP', 'center', 24, true);
        $this->addSpacing(20);
        
        // This is to certify
        $this->addText('This is to certify that', 'center', 14, false);
        $this->addSpacing(10);
        
        // Member name
        $this->addTitle($data['member_name'], 'center', 20, true);
        $this->addSpacing(15);
        
        // Membership details
        $this->addText('has been granted membership in the', 'center', 14, false);
        $this->addText('ABO-WBO Management System', 'center', 16, true);
        $this->addSpacing(10);
        
        $this->addText('Member ID: ' . $data['member_id'], 'center', 12, false);
        $this->addText('Date of Membership: ' . $data['membership_date'], 'center', 12, false);
        $this->addText('Valid Until: ' . $data['valid_until'], 'center', 12, false);
        
        $this->addSpacing(30);
        
        // Signature section
        $this->pdf->SetY(-80);
        $this->pdf->SetX(40);
        $this->pdf->Cell(50, 5, '_________________________', 0, 0, 'C');
        $this->pdf->SetX(120);
        $this->pdf->Cell(50, 5, '_________________________', 0, 1, 'C');
        
        $this->pdf->SetX(40);
        $this->pdf->Cell(50, 10, 'President', 0, 0, 'C');
        $this->pdf->SetX(120);
        $this->pdf->Cell(50, 10, 'Secretary', 0, 1, 'C');
        
        return $this->output('membership_certificate_' . $data['member_id'] . '.pdf');
    }

    /**
     * Add title to PDF
     */
    private function addTitle(string $text, string $align = 'left', int $size = 16, bool $bold = true): void
    {
        $font = $bold ? 'B' : '';
        $this->pdf->SetFont($this->config['fonts']['header'], $font, $size);
        $this->pdf->SetTextColor(0, 102, 204);
        
        $alignMap = ['left' => 'L', 'center' => 'C', 'right' => 'R'];
        $this->pdf->Cell(0, 8, $text, 0, 1, $alignMap[$align] ?? 'L');
        
        // Reset to default
        $this->pdf->SetFont($this->config['fonts']['body'], '', 12);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Add section header
     */
    private function addSectionHeader(string $text): void
    {
        $this->pdf->SetFont($this->config['fonts']['body'], 'B', 14);
        $this->pdf->SetTextColor(0, 102, 204);
        $this->pdf->Cell(0, 8, $text, 0, 1, 'L');
        
        // Add underline
        $this->pdf->SetDrawColor(0, 102, 204);
        $this->pdf->Line($this->pdf->GetX(), $this->pdf->GetY() - 2, $this->pdf->GetX() + 100, $this->pdf->GetY() - 2);
        
        $this->addSpacing(5);
        
        // Reset to default
        $this->pdf->SetFont($this->config['fonts']['body'], '', 12);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Add text to PDF
     */
    private function addText(string $text, string $align = 'left', int $size = 12, bool $bold = false): void
    {
        $font = $bold ? 'B' : '';
        $this->pdf->SetFont($this->config['fonts']['body'], $font, $size);
        
        $alignMap = ['left' => 'L', 'center' => 'C', 'right' => 'R'];
        $this->pdf->Cell(0, 6, $text, 0, 1, $alignMap[$align] ?? 'L');
        
        // Reset to default
        $this->pdf->SetFont($this->config['fonts']['body'], '', 12);
    }

    /**
     * Add key-value pair
     */
    private function addKeyValue(string $key, string $value): void
    {
        $this->pdf->SetFont($this->config['fonts']['body'], 'B', 11);
        $this->pdf->Cell(50, 6, $key . ':', 0, 0, 'L');
        
        $this->pdf->SetFont($this->config['fonts']['body'], '', 11);
        $this->pdf->Cell(0, 6, $value, 0, 1, 'L');
    }

    /**
     * Add spacing
     */
    private function addSpacing(int $height): void
    {
        $this->pdf->Ln($height);
    }

    /**
     * Add table
     */
    public function addTable(array $headers, array $data, array $widths = []): void
    {
        $numCols = count($headers);
        $defaultWidth = (180 / $numCols); // Distribute width evenly if not specified
        
        if (empty($widths)) {
            $widths = array_fill(0, $numCols, $defaultWidth);
        }
        
        // Table header
        $this->pdf->SetFont($this->config['fonts']['body'], 'B', 10);
        $this->pdf->SetFillColor(240, 240, 240);
        
        foreach ($headers as $i => $header) {
            $this->pdf->Cell($widths[$i], 8, $header, 1, 0, 'L', true);
        }
        $this->pdf->Ln();
        
        // Table data
        $this->pdf->SetFont($this->config['fonts']['body'], '', 9);
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $this->pdf->Cell($widths[$i], 6, (string)$cell, 1, 0, 'L');
            }
            $this->pdf->Ln();
        }
    }

    /**
     * Add image
     */
    public function addImage(string $imagePath, int $x = null, int $y = null, int $width = 0, int $height = 0): void
    {
        if (!file_exists($imagePath)) {
            return;
        }
        
        if ($x === null) $x = $this->pdf->GetX();
        if ($y === null) $y = $this->pdf->GetY();
        
        $this->pdf->Image($imagePath, $x, $y, $width, $height);
    }

    /**
     * Add page break
     */
    public function addPageBreak(): void
    {
        $this->pdf->AddPage();
    }

    /**
     * Set template variables
     */
    public function setTemplateVars(array $vars): void
    {
        $this->templateVars = array_merge($this->templateVars, $vars);
    }

    /**
     * Output PDF
     */
    public function output(string $filename = 'document.pdf', string $destination = 'D'): string
    {
        // D = Download, F = File, I = Inline, S = String
        return $this->pdf->Output($filename, $destination);
    }

    /**
     * Save PDF to file
     */
    public function save(string $filepath): bool
    {
        try {
            $this->pdf->Output($filepath, 'F');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get PDF as string
     */
    public function getString(): string
    {
        return $this->pdf->Output('', 'S');
    }

    /**
     * Reset PDF for new document
     */
    public function reset(): void
    {
        $this->initializePDF();
        $this->templateVars = [];
    }

    /**
     * Static factory method
     */
    public static function create(array $config = []): self
    {
        return new self($config);
    }
}