<?php
declare(strict_types=1);

namespace Core;

/**
 * Secure File Uploader
 * Validates MIME types, extensions, sizes, and image headers before moving files.
 */
class Uploader
{
    private const IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'image/svg+xml' => 'svg',
    ];

    private const DOC_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];

    public static function uploadImage(array $file, string $subFolder = 'media', bool $allowSvg = false): array
    {
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'error' => 'No file uploaded.'];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload failed with error code ' . (int) $file['error']];
        }

        $maxBytes = (int) app_config('max_upload_bytes', 5 * 1024 * 1024);
        if (($file['size'] ?? 0) > $maxBytes || ($file['size'] ?? 0) <= 0) {
            return ['ok' => false, 'error' => 'Image file size must be between 1 byte and 5 MB.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = self::IMAGE_MIMES;
        if (!$allowSvg) {
            unset($allowed['image/svg+xml']);
        }

        if (!isset($allowed[$mime])) {
            return ['ok' => false, 'error' => 'Unsupported image type. Allowed: JPG, PNG, WEBP, GIF, ICO' . ($allowSvg ? ', SVG' : '') . '.'];
        }

        // Verify raster image dimensions unless SVG/ICO
        if (!in_array($mime, ['image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'], true)) {
            if (@getimagesize($file['tmp_name']) === false) {
                return ['ok' => false, 'error' => 'Invalid or corrupted image file.'];
            }
        } elseif ($mime === 'image/svg+xml') {
            $svg = (string) file_get_contents($file['tmp_name']);
            if (preg_match('/<script|onload=|onerror=|javascript:/i', $svg)) {
                return ['ok' => false, 'error' => 'SVG contains disallowed scripting content.'];
            }
        }

        $ext = $allowed[$mime];
        $cleanFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $subFolder) ?: 'media';
        $targetDir = BASE_PATH . '/public/uploads/' . $cleanFolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'error' => 'Failed to save uploaded image.'];
        }

        return [
            'ok' => true,
            'path' => $cleanFolder . '/' . $filename,
            'mime' => $mime,
            'size' => (int) $file['size'],
            'original_name' => basename((string) ($file['name'] ?? $filename)),
        ];
    }

    public static function uploadPrivateDocument(array $file, string $subFolder = 'documents'): array
    {
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'error' => 'No document uploaded.'];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Document upload failed.'];
        }

        $maxBytes = 10 * 1024 * 1024; // 10 MB for docs
        if (($file['size'] ?? 0) > $maxBytes || ($file['size'] ?? 0) <= 0) {
            return ['ok' => false, 'error' => 'Document must be under 10 MB.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset(self::DOC_MIMES[$mime])) {
            return ['ok' => false, 'error' => 'Allowed document formats: PDF, JPG, PNG, DOC, DOCX.'];
        }

        $ext = self::DOC_MIMES[$mime];
        $cleanFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $subFolder) ?: 'documents';
        $targetDir = BASE_PATH . '/storage/' . $cleanFolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
        $destPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'error' => 'Could not store uploaded document.'];
        }

        return [
            'ok' => true,
            'path' => $cleanFolder . '/' . $filename,
            'mime' => $mime,
            'size' => (int) $file['size'],
            'original_name' => basename((string) ($file['name'] ?? $filename)),
        ];
    }
}
