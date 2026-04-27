<?php

namespace App\Services;

use Exception;

class AttachmentUploadService
{
    private const MAX_FILE_SIZE_BYTES = 10485760;
    private const IMAGE_OPTIMIZE_THRESHOLD_BYTES = 1572864;
    private const MAX_IMAGE_DIMENSION = 2560;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'application/pdf' => ['pdf'],
        'text/plain' => ['txt'],
        'text/csv' => ['csv'],
        'application/json' => ['json'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'application/zip' => ['zip'],
        'audio/mpeg' => ['mp3'],
        'audio/wav' => ['wav'],
        'video/mp4' => ['mp4'],
        'video/webm' => ['webm'],
    ];

    public function uploadMany(array $files, string $bucket): array
    {
        if (empty($files) || empty($files['name'])) {
            return [];
        }

        $bucket = $this->normalizeBucket($bucket);
        $storagePrefix = 'uploads/' . $bucket . '/' . date('Y/m');
        $uploadDir = storage_path($storagePrefix);

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new Exception('Failed to prepare attachment storage.');
        }

        $attachments = [];
        $fileNames = is_array($files['name']) ? $files['name'] : [$files['name']];
        $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name'] ?? ''];
        $sizes = is_array($files['size']) ? $files['size'] : [$files['size'] ?? 0];
        $errors = is_array($files['error']) ? $files['error'] : [$files['error'] ?? UPLOAD_ERR_NO_FILE];
        $clientTypes = is_array($files['type']) ? $files['type'] : [$files['type'] ?? 'application/octet-stream'];

        foreach ($fileNames as $index => $originalName) {
            $errorCode = (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE);
            $tmpName = (string) ($tmpNames[$index] ?? '');

            if ($errorCode === UPLOAD_ERR_NO_FILE || $tmpName === '') {
                continue;
            }

            if ($errorCode !== UPLOAD_ERR_OK) {
                throw new Exception('One or more attachments failed to upload.');
            }

            $originalName = trim((string) $originalName);
            if ($originalName === '') {
                throw new Exception('Attachment name is missing.');
            }

            $size = (int) ($sizes[$index] ?? 0);
            if ($size <= 0) {
                throw new Exception('Attachment file is empty.');
            }

            if ($size > self::MAX_FILE_SIZE_BYTES) {
                throw new Exception('Attachments must be 10 MB or smaller per file.');
            }

            $mimeType = $this->detectMimeType($tmpName, (string) ($clientTypes[$index] ?? 'application/octet-stream'));
            if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
                throw new Exception('Unsupported attachment type: ' . $mimeType);
            }

            $safeBaseName = $this->sanitizeBaseName(pathinfo($originalName, PATHINFO_FILENAME));
            $extension = $this->resolveExtension($originalName, $mimeType);
            $storedName = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '-' . $safeBaseName . '.' . $extension;
            $destination = $uploadDir . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($tmpName, $destination)) {
                throw new Exception('Failed to persist an uploaded attachment.');
            }

            $optimized = false;
            if ($this->shouldOptimizeImage($mimeType, $destination)) {
                $optimized = $this->optimizeImage($destination, $mimeType);
            }

            $attachments[] = [
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'relative_path' => str_replace('\\', '/', $storagePrefix . '/' . $storedName),
                'mime_type' => $mimeType,
                'size' => (int) (filesize($destination) ?: $size),
                'sha256' => hash_file('sha256', $destination),
                'optimized' => $optimized,
                'uploaded_at' => date('Y-m-d H:i:s'),
            ];
        }

        return $attachments;
    }

    public static function getMaxFileSizeBytes(): int
    {
        return self::MAX_FILE_SIZE_BYTES;
    }

    public static function getMaxFileSizeLabel(): string
    {
        return '10 MB';
    }

    public static function getAllowedExtensionsLabel(): string
    {
        $extensions = [];

        foreach (self::ALLOWED_MIME_TYPES as $mimeExtensions) {
            $extensions = array_merge($extensions, $mimeExtensions);
        }

        $extensions = array_values(array_unique($extensions));
        sort($extensions);

        return implode(', ', $extensions);
    }

    private function normalizeBucket(string $bucket): string
    {
        $bucket = preg_replace('/[^A-Za-z0-9._-]/', '-', trim($bucket));

        return $bucket !== '' ? $bucket : 'attachments';
    }

    private function sanitizeBaseName(string $baseName): string
    {
        $baseName = preg_replace('/[^A-Za-z0-9._-]/', '-', $baseName);
        $baseName = trim((string) $baseName, '.-');

        return $baseName !== '' ? $baseName : 'attachment';
    }

    private function detectMimeType(string $tmpName, string $fallback): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detected = finfo_file($finfo, $tmpName) ?: null;
                finfo_close($finfo);

                if (is_string($detected) && $detected !== '') {
                    return $detected;
                }
            }
        }

        return $fallback !== '' ? $fallback : 'application/octet-stream';
    }

    private function resolveExtension(string $originalName, string $mimeType): string
    {
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = self::ALLOWED_MIME_TYPES[$mimeType] ?? [];

        if ($extension !== '' && in_array($extension, $allowedExtensions, true)) {
            return $extension;
        }

        return $allowedExtensions[0] ?? 'bin';
    }

    private function shouldOptimizeImage(string $mimeType, string $filePath): bool
    {
        return str_starts_with($mimeType, 'image/')
            && extension_loaded('gd')
            && is_file($filePath)
            && (int) (filesize($filePath) ?: 0) >= self::IMAGE_OPTIMIZE_THRESHOLD_BYTES;
    }

    private function optimizeImage(string $filePath, string $mimeType): bool
    {
        $imageInfo = @getimagesize($filePath);
        if (!is_array($imageInfo) || empty($imageInfo[0]) || empty($imageInfo[1])) {
            return false;
        }

        [$width, $height] = $imageInfo;
        $scale = min(
            1,
            self::MAX_IMAGE_DIMENSION / max(1, $width),
            self::MAX_IMAGE_DIMENSION / max(1, $height)
        );

        $targetWidth = max(1, (int) floor($width * $scale));
        $targetHeight = max(1, (int) floor($height * $scale));

        $source = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($filePath),
            'image/png' => @imagecreatefrompng($filePath),
            'image/gif' => @imagecreatefromgif($filePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : false,
            default => false,
        };

        if ($source === false) {
            return false;
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($canvas === false) {
            imagedestroy($source);
            return false;
        }

        if (in_array($mimeType, ['image/png', 'image/gif', 'image/webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $result = match ($mimeType) {
            'image/jpeg' => imagejpeg($canvas, $filePath, 82),
            'image/png' => imagepng($canvas, $filePath, 6),
            'image/gif' => imagegif($canvas, $filePath),
            'image/webp' => function_exists('imagewebp') ? imagewebp($canvas, $filePath, 82) : false,
            default => false,
        };

        imagedestroy($canvas);
        imagedestroy($source);

        return (bool) $result;
    }
}