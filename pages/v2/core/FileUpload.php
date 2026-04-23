<?php
/**
 * AeroCanada v2 — Secure File Upload Handler
 * Validates file type, size, and name. Prevents directory traversal.
 */

namespace AeroCanada\Core;

class FileUpload
{
    private array $config;

    public function __construct()
    {
        $cfg = require __DIR__ . '/../config.php';
        $this->config = $cfg['upload'];
    }

    /**
     * Upload a file securely.
     * Returns the final filename on success, null on failure.
     */
    public function upload(string $inputName, string $targetDir, array $options = []): ?string
    {
        if (empty($_FILES[$inputName]['name']) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file     = $_FILES[$inputName];
        $origName = basename($file['name']);
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $size     = $file['size'];

        // Block dangerous extensions
        if (in_array($ext, $this->config['blocked_ext'], true)) {
            throw new \RuntimeException("File type .$ext is not allowed.");
        }

        // Check size
        $maxSize = $options['max_size'] ?? $this->config['max_size'];
        if ($size > $maxSize) {
            throw new \RuntimeException('File exceeds maximum size of ' . round($maxSize / 1024 / 1024, 1) . ' MB.');
        }

        // Only allow specific extensions for images
        if (!empty($options['images_only'])) {
            if (!in_array($ext, $this->config['allowed_images'], true)) {
                throw new \RuntimeException("Only image files are allowed (jpg, png, gif, webp).");
            }
            // Verify it's actually an image
            $check = @getimagesize($file['tmp_name']);
            if ($check === false) {
                throw new \RuntimeException("File does not appear to be a valid image.");
            }
        }

        // Sanitize filename: replace spaces, remove special chars
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
        $safeName = preg_replace('/_+/', '_', $safeName);

        // Avoid collisions
        if (file_exists($targetDir . $safeName)) {
            $base     = pathinfo($safeName, PATHINFO_FILENAME);
            $safeName = $base . '_' . date('YmdHis') . '.' . $ext;
        }

        // Ensure target directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return $safeName;
    }

    /**
     * Delete a previously uploaded file.
     */
    public function delete(string $dir, string $filename): bool
    {
        $path = $dir . basename($filename); // prevent directory traversal
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}
