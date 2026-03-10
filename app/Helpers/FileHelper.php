<?php

namespace App\Helpers;

use App\Config\Constants;

class FileHelper
{
    /**
     * Upload file
     */
    public static function upload(array $file, string $directory = '', ?string $customName = null): array
    {
        $uploadPath = Constants::UPLOAD_PATH . $directory;
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Validate file
        $errors = \App\Helpers\ValidationHelper::file(
            $file,
            Constants::ALLOWED_FILE_TYPES,
            Constants::MAX_FILE_SIZE
        );
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Generate filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $customName ?? uniqid() . '_' . time();
        $filename .= '.' . $ext;
        
        $destination = $uploadPath . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $destination,
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }
        
        return ['success' => false, 'errors' => ['Failed to move uploaded file']];
    }
    
    /**
     * Delete file
     */
    public static function delete(string $path): bool
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Get file extension
     */
    public static function getExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Check if file type is allowed
     */
    public static function isAllowedType(string $filename): bool
    {
        $ext = self::getExtension($filename);
        return in_array($ext, Constants::ALLOWED_FILE_TYPES);
    }
    
    /**
     * Generate unique filename
     */
    public static function generateUniqueFilename(string $originalName): string
    {
        $ext = self::getExtension($originalName);
        return uniqid() . '_' . time() . '.' . $ext;
    }
}
