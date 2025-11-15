<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * FileService - Comprehensive file upload and management service
 * 
 * Handles file uploads, validation, storage, and management with security features
 * including virus scanning, file type validation, size limits, and audit logging.
 * 
 * @package App\Services
 * @version 1.0.0
 */
class FileService
{
    private const ALLOWED_TYPES = [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'max_size' => 5242880 // 5MB
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'application/rtf'
            ],
            'max_size' => 10485760 // 10MB
        ],
        'video' => [
            'extensions' => ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv'],
            'mime_types' => ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv'],
            'max_size' => 104857600 // 100MB
        ],
        'audio' => [
            'extensions' => ['mp3', 'wav', 'ogg', 'aac', 'flac'],
            'mime_types' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/aac', 'audio/flac'],
            'max_size' => 52428800 // 50MB
        ],
        'archive' => [
            'extensions' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'mime_types' => [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip'
            ],
            'max_size' => 52428800 // 50MB
        ]
    ];

    private const STORAGE_PATHS = [
        'image' => 'uploads/images/',
        'document' => 'uploads/documents/',
        'video' => 'uploads/videos/',
        'audio' => 'uploads/audio/',
        'archive' => 'uploads/archives/',
        'profile' => 'uploads/profiles/',
        'receipt' => 'uploads/receipts/',
        'temp' => 'uploads/temp/'
    ];

    private const THUMBNAIL_SIZES = [
        'small' => [150, 150],
        'medium' => [300, 300],
        'large' => [600, 600]
    ];

    private $basePath;
    private $baseUrl;
    private $database;
    private $logger;

    public function __construct()
    {
        $this->basePath = $_SERVER['DOCUMENT_ROOT'] . '/../storage/';
        $this->baseUrl = '/storage/';
        $this->database = \App\Utils\Database::getInstance();
        $this->initializeDirectories();
    }

    /**
     * Initialize storage directories if they don't exist
     */
    private function initializeDirectories(): void
    {
        foreach (self::STORAGE_PATHS as $path) {
            $fullPath = $this->basePath . $path;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
        }
    }

    /**
     * Upload single file with comprehensive validation and processing
     * 
     * @param array $file $_FILES array element
     * @param string $type File type category
     * @param array $options Additional options
     * @return array File information
     * @throws InvalidArgumentException|RuntimeException
     */
    public function uploadFile(array $file, string $type, array $options = []): array
    {
        // Validate file upload
        $this->validateFileUpload($file);
        
        // Validate file type and size
        $this->validateFileType($file, $type);
        
        // Generate unique filename
        $originalName = $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $uniqueId = $this->generateUniqueId();
        $filename = $uniqueId . '.' . $extension;
        
        // Determine storage path
        $storagePath = self::STORAGE_PATHS[$type] ?? self::STORAGE_PATHS['document'];
        $fullPath = $this->basePath . $storagePath . $filename;
        
        // Create directory if it doesn't exist
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new RuntimeException('Failed to move uploaded file');
        }
        
        // Set appropriate permissions
        chmod($fullPath, 0644);
        
        // Generate thumbnails for images
        $thumbnails = [];
        if ($type === 'image' && !in_array($extension, ['svg'])) {
            $thumbnails = $this->generateThumbnails($fullPath, $storagePath, $uniqueId, $extension);
        }
        
        // Scan for viruses if enabled
        $virusScanResult = $this->performVirusScan($fullPath);
        
        // Store file information in database
        $fileInfo = [
            'uuid' => $uniqueId,
            'original_name' => $originalName,
            'filename' => $filename,
            'file_path' => $storagePath . $filename,
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'file_type' => $type,
            'extension' => $extension,
            'thumbnails' => json_encode($thumbnails),
            'virus_scan_result' => $virusScanResult,
            'upload_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_by' => $options['user_id'] ?? null,
            'related_entity_type' => $options['entity_type'] ?? null,
            'related_entity_id' => $options['entity_id'] ?? null,
            'is_public' => $options['is_public'] ?? false,
            'expires_at' => $options['expires_at'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $fileId = $this->storeFileInfo($fileInfo);
        
        // Log file upload
        $this->logFileOperation('upload', $fileId, $fileInfo);
        
        return [
            'id' => $fileId,
            'uuid' => $uniqueId,
            'original_name' => $originalName,
            'filename' => $filename,
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'file_type' => $type,
            'url' => $this->baseUrl . $storagePath . $filename,
            'download_url' => "/api/files/{$uniqueId}/download",
            'thumbnails' => $thumbnails,
            'virus_scan_clean' => $virusScanResult === 'clean'
        ];
    }

    /**
     * Upload multiple files
     * 
     * @param array $files Array of file uploads
     * @param string $type File type category
     * @param array $options Additional options
     * @return array Array of uploaded file information
     */
    public function uploadMultipleFiles(array $files, string $type, array $options = []): array
    {
        $uploadedFiles = [];
        $errors = [];
        
        foreach ($files as $index => $file) {
            try {
                $uploadedFiles[] = $this->uploadFile($file, $type, $options);
            } catch (Exception $e) {
                $errors[$index] = $e->getMessage();
            }
        }
        
        return [
            'uploaded' => $uploadedFiles,
            'errors' => $errors,
            'total_uploaded' => count($uploadedFiles),
            'total_errors' => count($errors)
        ];
    }

    /**
     * Validate file upload
     * 
     * @param array $file File upload array
     * @throws InvalidArgumentException
     */
    private function validateFileUpload(array $file): void
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new InvalidArgumentException('Invalid file upload');
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new InvalidArgumentException('No file was uploaded');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new InvalidArgumentException('File size exceeds maximum allowed size');
            case UPLOAD_ERR_PARTIAL:
                throw new InvalidArgumentException('File was only partially uploaded');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new RuntimeException('Missing temporary upload directory');
            case UPLOAD_ERR_CANT_WRITE:
                throw new RuntimeException('Failed to write file to disk');
            case UPLOAD_ERR_EXTENSION:
                throw new RuntimeException('File upload stopped by extension');
            default:
                throw new RuntimeException('Unknown upload error');
        }
        
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('Invalid uploaded file');
        }
    }

    /**
     * Validate file type and size
     * 
     * @param array $file File upload array
     * @param string $type File type category
     * @throws InvalidArgumentException
     */
    private function validateFileType(array $file, string $type): void
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            throw new InvalidArgumentException('Invalid file type category');
        }
        
        $allowedType = self::ALLOWED_TYPES[$type];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = $file['type'];
        $fileSize = $file['size'];
        
        // Validate extension
        if (!in_array($extension, $allowedType['extensions'])) {
            throw new InvalidArgumentException("File extension '{$extension}' is not allowed for type '{$type}'");
        }
        
        // Validate MIME type
        if (!in_array($mimeType, $allowedType['mime_types'])) {
            throw new InvalidArgumentException("MIME type '{$mimeType}' is not allowed for type '{$type}'");
        }
        
        // Validate file size
        if ($fileSize > $allowedType['max_size']) {
            $maxSizeMB = round($allowedType['max_size'] / 1048576, 2);
            throw new InvalidArgumentException("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }
        
        // Additional security check: verify file content matches extension
        $this->verifyFileContent($file['tmp_name'], $extension, $mimeType);
    }

    /**
     * Verify file content matches declared type
     * 
     * @param string $filePath Temporary file path
     * @param string $extension File extension
     * @param string $mimeType MIME type
     * @throws InvalidArgumentException
     */
    private function verifyFileContent(string $filePath, string $extension, string $mimeType): void
    {
        // Use finfo to detect actual file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualMimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        // Check for common file type spoofing attempts
        $dangerousExtensions = ['php', 'js', 'html', 'htm', 'exe', 'bat', 'cmd', 'scr'];
        if (in_array($extension, $dangerousExtensions)) {
            throw new InvalidArgumentException('File type not allowed for security reasons');
        }
        
        // Verify MIME type matches (allow some flexibility for browsers)
        $mimeTypeMatches = [
            'image/jpeg' => ['image/jpeg', 'image/jpg'],
            'image/png' => ['image/png'],
            'image/gif' => ['image/gif'],
            'application/pdf' => ['application/pdf'],
            'text/plain' => ['text/plain', 'text/x-c++', 'text/x-c']
        ];
        
        if (isset($mimeTypeMatches[$mimeType])) {
            if (!in_array($actualMimeType, $mimeTypeMatches[$mimeType])) {
                throw new InvalidArgumentException('File content does not match declared type');
            }
        }
    }

    /**
     * Generate thumbnails for image files
     * 
     * @param string $imagePath Full path to image
     * @param string $storagePath Storage directory path
     * @param string $uniqueId Unique file ID
     * @param string $extension File extension
     * @return array Thumbnail information
     */
    private function generateThumbnails(string $imagePath, string $storagePath, string $uniqueId, string $extension): array
    {
        $thumbnails = [];
        
        foreach (self::THUMBNAIL_SIZES as $size => [$width, $height]) {
            try {
                $thumbnailFilename = "{$uniqueId}_{$size}.{$extension}";
                $thumbnailPath = $this->basePath . $storagePath . 'thumbs/' . $thumbnailFilename;
                
                // Create thumbs directory if it doesn't exist
                $thumbsDir = dirname($thumbnailPath);
                if (!is_dir($thumbsDir)) {
                    mkdir($thumbsDir, 0755, true);
                }
                
                if ($this->createThumbnail($imagePath, $thumbnailPath, $width, $height)) {
                    $thumbnails[$size] = [
                        'filename' => $thumbnailFilename,
                        'url' => $this->baseUrl . $storagePath . 'thumbs/' . $thumbnailFilename,
                        'width' => $width,
                        'height' => $height
                    ];
                }
            } catch (Exception $e) {
                // Log thumbnail generation error but don't fail the upload
                error_log("Thumbnail generation failed for {$uniqueId}: " . $e->getMessage());
            }
        }
        
        return $thumbnails;
    }

    /**
     * Create thumbnail image
     * 
     * @param string $sourcePath Source image path
     * @param string $destPath Destination thumbnail path
     * @param int $width Thumbnail width
     * @param int $height Thumbnail height
     * @return bool Success status
     */
    private function createThumbnail(string $sourcePath, string $destPath, int $width, int $height): bool
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        [$origWidth, $origHeight, $type] = $imageInfo;
        
        // Create source image resource
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Calculate dimensions maintaining aspect ratio
        $ratio = min($width / $origWidth, $height / $origHeight);
        $newWidth = round($origWidth * $ratio);
        $newHeight = round($origHeight * $ratio);
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Handle transparency for PNG and GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Save thumbnail
        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($thumbnail, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($thumbnail, $destPath, 6);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($thumbnail, $destPath);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($thumbnail, $destPath, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return $result;
    }

    /**
     * Perform virus scan on uploaded file
     * 
     * @param string $filePath Path to file
     * @return string Scan result ('clean', 'infected', 'error', 'skipped')
     */
    private function performVirusScan(string $filePath): string
    {
        // This is a placeholder for virus scanning integration
        // In production, integrate with ClamAV, VirusTotal API, or similar
        
        // Basic file size check (extremely large files might be suspicious)
        $fileSize = filesize($filePath);
        if ($fileSize > 500 * 1024 * 1024) { // 500MB
            return 'suspicious_size';
        }
        
        // Check for executable signatures in file headers
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            $header = fread($handle, 16);
            fclose($handle);
            
            // Check for common executable signatures
            $executableSignatures = [
                "\x4D\x5A", // PE/EXE
                "\x7F\x45\x4C\x46", // ELF
                "\xFE\xED\xFA\xCE", // Mach-O
                "\xFE\xED\xFA\xCF", // Mach-O
                "\xCE\xFA\xED\xFE", // Mach-O
                "\xCF\xFA\xED\xFE"  // Mach-O
            ];
            
            foreach ($executableSignatures as $signature) {
                if (strpos($header, $signature) === 0) {
                    return 'suspicious_executable';
                }
            }
        }
        
        return 'clean'; // Placeholder - implement actual virus scanning
    }

    /**
     * Store file information in database
     * 
     * @param array $fileInfo File information array
     * @return int File ID
     */
    private function storeFileInfo(array $fileInfo): int
    {
        $sql = "INSERT INTO files (
            uuid, original_name, filename, file_path, file_size, mime_type, 
            file_type, extension, thumbnails, virus_scan_result, upload_ip, 
            user_agent, created_by, related_entity_type, related_entity_id, 
            is_public, expires_at, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $fileInfo['uuid'],
            $fileInfo['original_name'],
            $fileInfo['filename'],
            $fileInfo['file_path'],
            $fileInfo['file_size'],
            $fileInfo['mime_type'],
            $fileInfo['file_type'],
            $fileInfo['extension'],
            $fileInfo['thumbnails'],
            $fileInfo['virus_scan_result'],
            $fileInfo['upload_ip'],
            $fileInfo['user_agent'],
            $fileInfo['created_by'],
            $fileInfo['related_entity_type'],
            $fileInfo['related_entity_id'],
            $fileInfo['is_public'] ? 1 : 0,
            $fileInfo['expires_at'],
            $fileInfo['created_at']
        ]);
        
        return $this->database->lastInsertId();
    }

    /**
     * Get file information by UUID
     * 
     * @param string $uuid File UUID
     * @return array|null File information
     */
    public function getFileByUuid(string $uuid): ?array
    {
        $sql = "SELECT * FROM files WHERE uuid = ? AND deleted_at IS NULL";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$uuid]);
        
        $file = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$file) {
            return null;
        }
        
        // Decode JSON fields
        $file['thumbnails'] = json_decode($file['thumbnails'], true) ?: [];
        
        // Add full URLs
        $file['url'] = $this->baseUrl . $file['file_path'];
        $file['download_url'] = "/api/files/{$uuid}/download";
        
        return $file;
    }

    /**
     * Download file by UUID
     * 
     * @param string $uuid File UUID
     * @param bool $forceDownload Force download instead of display
     * @return void
     */
    public function downloadFile(string $uuid, bool $forceDownload = false): void
    {
        $file = $this->getFileByUuid($uuid);
        if (!$file) {
            http_response_code(404);
            echo 'File not found';
            return;
        }
        
        $filePath = $this->basePath . $file['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'File not found on disk';
            return;
        }
        
        // Check permissions (implement based on your needs)
        if (!$this->canAccessFile($file)) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }
        
        // Log file download
        $this->logFileOperation('download', $file['id'], $file);
        
        // Set headers
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Length: ' . $file['file_size']);
        
        if ($forceDownload) {
            header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        } else {
            header('Content-Disposition: inline; filename="' . $file['original_name'] . '"');
        }
        
        // Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        // Stream file
        readfile($filePath);
    }

    /**
     * Delete file by UUID
     * 
     * @param string $uuid File UUID
     * @param int|null $userId User performing deletion
     * @return bool Success status
     */
    public function deleteFile(string $uuid, ?int $userId = null): bool
    {
        $file = $this->getFileByUuid($uuid);
        if (!$file) {
            return false;
        }
        
        // Soft delete in database
        $sql = "UPDATE files SET deleted_at = ?, deleted_by = ? WHERE uuid = ?";
        $stmt = $this->database->prepare($sql);
        $result = $stmt->execute([date('Y-m-d H:i:s'), $userId, $uuid]);
        
        if ($result) {
            // Log file deletion
            $this->logFileOperation('delete', $file['id'], $file);
            
            // Optionally move file to trash instead of immediate deletion
            $this->moveFileToTrash($file);
        }
        
        return $result;
    }

    /**
     * Clean up expired files
     * 
     * @return int Number of files cleaned up
     */
    public function cleanupExpiredFiles(): int
    {
        $sql = "SELECT * FROM files WHERE expires_at IS NOT NULL AND expires_at < NOW() AND deleted_at IS NULL";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        
        $expiredFiles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $cleanedCount = 0;
        
        foreach ($expiredFiles as $file) {
            if ($this->deleteFile($file['uuid'])) {
                $cleanedCount++;
            }
        }
        
        return $cleanedCount;
    }

    /**
     * Generate unique file ID
     * 
     * @return string Unique ID
     */
    private function generateUniqueId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Check if user can access file
     * 
     * @param array $file File information
     * @return bool Access permission
     */
    private function canAccessFile(array $file): bool
    {
        // Implement your access control logic here
        // This could check user permissions, file ownership, public status, etc.
        
        if ($file['is_public']) {
            return true;
        }
        
        // Check if user is authenticated and has access
        // This is a placeholder - implement actual authorization logic
        return isset($_SESSION['user_id']);
    }

    /**
     * Move file to trash directory
     * 
     * @param array $file File information
     * @return bool Success status
     */
    private function moveFileToTrash(array $file): bool
    {
        $currentPath = $this->basePath . $file['file_path'];
        $trashPath = $this->basePath . 'trash/' . $file['filename'];
        
        // Create trash directory if it doesn't exist
        $trashDir = dirname($trashPath);
        if (!is_dir($trashDir)) {
            mkdir($trashDir, 0755, true);
        }
        
        return rename($currentPath, $trashPath);
    }

    /**
     * Log file operations
     * 
     * @param string $operation Operation type
     * @param int $fileId File ID
     * @param array $fileInfo File information
     * @return void
     */
    private function logFileOperation(string $operation, int $fileId, array $fileInfo): void
    {
        $logData = [
            'operation' => $operation,
            'file_id' => $fileId,
            'file_uuid' => $fileInfo['uuid'],
            'file_name' => $fileInfo['original_name'],
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log to file or database
        error_log("File {$operation}: " . json_encode($logData));
    }

    /**
     * Get file statistics
     * 
     * @return array File statistics
     */
    public function getFileStatistics(): array
    {
        $stats = [];
        
        // Total files
        $sql = "SELECT COUNT(*) as total_files, SUM(file_size) as total_size FROM files WHERE deleted_at IS NULL";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $stats['total_files'] = $result['total_files'];
        $stats['total_size'] = $result['total_size'];
        $stats['total_size_mb'] = round($result['total_size'] / 1048576, 2);
        
        // Files by type
        $sql = "SELECT file_type, COUNT(*) as count, SUM(file_size) as size FROM files WHERE deleted_at IS NULL GROUP BY file_type";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Files uploaded today
        $sql = "SELECT COUNT(*) as count FROM files WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $stats['uploaded_today'] = $stmt->fetchColumn();
        
        return $stats;
    }

    /**
     * Get allowed file types configuration
     * 
     * @return array Allowed types configuration
     */
    public function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }

    /**
     * Get maximum upload size for a file type
     * 
     * @param string $type File type
     * @return int Maximum size in bytes
     */
    public function getMaxUploadSize(string $type): int
    {
        return self::ALLOWED_TYPES[$type]['max_size'] ?? 0;
    }
}