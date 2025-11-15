<?php

namespace App\Utils;

/**
 * FileUpload - Secure file upload handling
 * 
 * Provides secure file upload with validation, sanitization,
 * virus scanning, and multiple storage backend support.
 * 
 * @package App\Utils
 * @version 1.0.0
 */
class FileUpload
{
    private array $config = [
        'upload_path' => '/uploads',
        'max_file_size' => 10485760, // 10MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'allowed_mime_types' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'virus_scan' => false,
        'generate_thumbnails' => true,
        'thumbnail_sizes' => [150, 300, 600],
        'storage_backend' => 'local', // local, s3, ftp
        'secure_filename' => true,
        'preserve_original_name' => false
    ];

    private array $errors = [];
    private string $uploadPath;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->uploadPath = rtrim($_SERVER['DOCUMENT_ROOT'] . $this->config['upload_path'], '/');
        
        // Ensure upload directory exists
        $this->ensureUploadDirectory();
    }

    /**
     * Upload single file
     */
    public function uploadFile(array $file, ?string $category = null): array|false
    {
        $this->errors = [];

        // Validate file
        if (!$this->validateFile($file)) {
            return false;
        }

        // Determine upload path
        $categoryPath = $category ? '/' . trim($category, '/') : '';
        $fullUploadPath = $this->uploadPath . $categoryPath;
        
        if (!is_dir($fullUploadPath)) {
            mkdir($fullUploadPath, 0755, true);
        }

        // Generate secure filename
        $filename = $this->generateSecureFilename($file['name']);
        $filePath = $fullUploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->errors[] = 'Failed to move uploaded file';
            return false;
        }

        // Set proper permissions
        chmod($filePath, 0644);

        // Perform virus scan if enabled
        if ($this->config['virus_scan'] && !$this->scanForVirus($filePath)) {
            unlink($filePath);
            return false;
        }

        // Generate thumbnails for images
        $thumbnails = [];
        if ($this->config['generate_thumbnails'] && $this->isImage($file['type'])) {
            $thumbnails = $this->generateThumbnails($filePath, $filename);
        }

        // Get file metadata
        $metadata = $this->getFileMetadata($filePath, $file);

        return [
            'success' => true,
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath),
            'full_path' => $filePath,
            'size' => $file['size'],
            'type' => $file['type'],
            'category' => $category,
            'thumbnails' => $thumbnails,
            'metadata' => $metadata,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, ?string $category = null): array
    {
        $results = [];
        
        // Handle different file input formats
        if (isset($files['name']) && is_array($files['name'])) {
            // Multiple files in single input
            for ($i = 0; $i < count($files['name']); $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $result = $this->uploadFile($file, $category);
                $results[] = $result ?: ['success' => false, 'errors' => $this->errors];
            }
        } else {
            // Multiple separate file inputs
            foreach ($files as $file) {
                $result = $this->uploadFile($file, $category);
                $results[] = $result ?: ['success' => false, 'errors' => $this->errors];
            }
        }

        return $results;
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(array $file): bool
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Check file size
        if ($file['size'] > $this->config['max_file_size']) {
            $this->errors[] = 'File size exceeds maximum allowed size of ' . 
                            $this->formatBytes($this->config['max_file_size']);
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->config['allowed_extensions'])) {
            $this->errors[] = 'File extension not allowed. Allowed: ' . 
                            implode(', ', $this->config['allowed_extensions']);
            return false;
        }

        // Check MIME type
        $mimeType = $this->getMimeType($file['tmp_name']);
        if (!in_array($mimeType, $this->config['allowed_mime_types'])) {
            $this->errors[] = 'File type not allowed';
            return false;
        }

        // Additional security checks
        if (!$this->isSecureFile($file)) {
            return false;
        }

        return true;
    }

    /**
     * Security checks for uploaded file
     */
    private function isSecureFile(array $file): bool
    {
        // Check if file is actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'File was not uploaded through HTTP POST';
            return false;
        }

        // Check for malicious content in filename
        if (preg_match('/[<>:"\/\\|?*]/', $file['name'])) {
            $this->errors[] = 'Filename contains invalid characters';
            return false;
        }

        // Check for executable file extensions
        $dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'sh', 'cmd'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $dangerousExtensions)) {
            $this->errors[] = 'Executable files are not allowed';
            return false;
        }

        // Check file content for PHP tags (for non-PHP files)
        if (!in_array($extension, ['php', 'phtml'])) {
            $fileContent = file_get_contents($file['tmp_name'], false, null, 0, 1024);
            if (strpos($fileContent, '<?php') !== false || strpos($fileContent, '<?=') !== false) {
                $this->errors[] = 'File contains potentially malicious content';
                return false;
            }
        }

        return true;
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        if ($this->config['secure_filename']) {
            // Generate random filename
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        } else {
            // Sanitize original filename
            $basename = pathinfo($originalName, PATHINFO_FILENAME);
            $basename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $basename);
            $basename = trim($basename, '._-');
            
            if (empty($basename)) {
                $basename = 'file_' . time();
            }
            
            $filename = $basename . '.' . $extension;
        }

        // Ensure filename is unique
        $counter = 1;
        $originalFilename = $filename;
        while (file_exists($this->uploadPath . '/' . $filename)) {
            $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
            $filename = $basename . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    /**
     * Get MIME type of file
     */
    private function getMimeType(string $filePath): string
    {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType ?: 'application/octet-stream';
        }

        return mime_content_type($filePath) ?: 'application/octet-stream';
    }

    /**
     * Check if file is an image
     */
    private function isImage(string $mimeType): bool
    {
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * Generate thumbnails for images
     */
    private function generateThumbnails(string $filePath, string $filename): array
    {
        if (!extension_loaded('gd')) {
            return [];
        }

        $thumbnails = [];
        $imageInfo = getimagesize($filePath);
        
        if (!$imageInfo) {
            return [];
        }

        [$originalWidth, $originalHeight, $imageType] = $imageInfo;

        // Create source image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($filePath);
                break;
            default:
                return [];
        }

        if (!$sourceImage) {
            return [];
        }

        // Create thumbnails directory
        $thumbDir = dirname($filePath) . '/thumbnails';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        foreach ($this->config['thumbnail_sizes'] as $size) {
            // Calculate dimensions maintaining aspect ratio
            if ($originalWidth > $originalHeight) {
                $thumbWidth = $size;
                $thumbHeight = ($originalHeight * $size) / $originalWidth;
            } else {
                $thumbHeight = $size;
                $thumbWidth = ($originalWidth * $size) / $originalHeight;
            }

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            
            // Preserve transparency for PNG and GIF
            if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
                imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }

            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, 
                             $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);

            // Save thumbnail
            $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '_' . $size . '.' . 
                           pathinfo($filename, PATHINFO_EXTENSION);
            $thumbPath = $thumbDir . '/' . $thumbFilename;

            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    imagejpeg($thumbnail, $thumbPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($thumbnail, $thumbPath, 9);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($thumbnail, $thumbPath);
                    break;
            }

            imagedestroy($thumbnail);

            $thumbnails[$size] = [
                'path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $thumbPath),
                'width' => $thumbWidth,
                'height' => $thumbHeight
            ];
        }

        imagedestroy($sourceImage);
        return $thumbnails;
    }

    /**
     * Virus scan (placeholder - would integrate with ClamAV or similar)
     */
    private function scanForVirus(string $filePath): bool
    {
        if (!$this->config['virus_scan']) {
            return true;
        }

        // Placeholder for virus scanning
        // In production, integrate with ClamAV, Windows Defender, or cloud scanning service
        
        // Basic check for known malicious patterns
        $fileContent = file_get_contents($filePath, false, null, 0, 4096);
        $maliciousPatterns = [
            'eval(',
            'base64_decode(',
            'shell_exec(',
            'system(',
            'exec(',
            'passthru(',
            'file_get_contents(',
            'file_put_contents('
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (strpos($fileContent, $pattern) !== false) {
                $this->errors[] = 'File contains potentially malicious content';
                return false;
            }
        }

        return true;
    }

    /**
     * Get file metadata
     */
    private function getFileMetadata(string $filePath, array $file): array
    {
        $metadata = [
            'size' => filesize($filePath),
            'modified' => filemtime($filePath),
            'permissions' => substr(sprintf('%o', fileperms($filePath)), -4),
            'hash' => [
                'md5' => md5_file($filePath),
                'sha1' => sha1_file($filePath)
            ]
        ];

        // Add image-specific metadata
        if ($this->isImage($file['type'])) {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $metadata['image'] = [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'type' => $imageInfo[2],
                    'bits' => $imageInfo['bits'] ?? null,
                    'channels' => $imageInfo['channels'] ?? null
                ];

                // Get EXIF data for JPEG images
                if ($imageInfo[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                    $exif = @exif_read_data($filePath);
                    if ($exif) {
                        $metadata['exif'] = [
                            'camera' => $exif['Model'] ?? null,
                            'date_taken' => $exif['DateTime'] ?? null,
                            'gps' => isset($exif['GPSLatitude']) ? 'Present' : 'None'
                        ];
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * Delete uploaded file and its thumbnails
     */
    public function deleteFile(string $filename, ?string $category = null): bool
    {
        $categoryPath = $category ? '/' . trim($category, '/') : '';
        $filePath = $this->uploadPath . $categoryPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return false;
        }

        // Delete main file
        $deleted = unlink($filePath);

        // Delete thumbnails
        $thumbDir = dirname($filePath) . '/thumbnails';
        if (is_dir($thumbDir)) {
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            foreach ($this->config['thumbnail_sizes'] as $size) {
                $thumbFile = $thumbDir . '/' . $basename . '_' . $size . '.' . $extension;
                if (file_exists($thumbFile)) {
                    unlink($thumbFile);
                }
            }
        }

        return $deleted;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Format bytes to human readable size
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        // Create .htaccess for security
        $htaccessPath = $this->uploadPath . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Disable PHP execution\n";
            $htaccessContent .= "php_flag engine off\n";
            $htaccessContent .= "AddType text/plain .php .php3 .phtml .pht\n";
            $htaccessContent .= "# Prevent directory browsing\n";
            $htaccessContent .= "Options -Indexes\n";
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }

    /**
     * Get upload errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Update configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get allowed extensions
     */
    public function getAllowedExtensions(): array
    {
        return $this->config['allowed_extensions'];
    }

    /**
     * Get maximum file size
     */
    public function getMaxFileSize(): int
    {
        return $this->config['max_file_size'];
    }

    /**
     * Static factory method
     */
    public static function create(array $config = []): self
    {
        return new self($config);
    }
}