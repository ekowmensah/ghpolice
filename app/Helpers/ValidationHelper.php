<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Validate required field
     */
    public static function required($value): bool
    {
        return !empty($value) || $value === '0';
    }
    
    /**
     * Validate minimum length
     */
    public static function min($value, int $length): bool
    {
        return strlen($value) >= $length;
    }
    
    /**
     * Validate maximum length
     */
    public static function max($value, int $length): bool
    {
        return strlen($value) <= $length;
    }
    
    /**
     * Validate email
     */
    public static function email($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate numeric
     */
    public static function numeric($value): bool
    {
        return is_numeric($value);
    }
    
    /**
     * Validate integer
     */
    public static function integer($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate phone number (Ghana format)
     */
    public static function phone($value): bool
    {
        // Ghana phone format: 0XX XXX XXXX or +233 XX XXX XXXX
        $pattern = '/^(\+233|0)[2-5][0-9]{8}$/';
        return preg_match($pattern, str_replace(' ', '', $value)) === 1;
    }
    
    /**
     * Validate Ghana Card number
     */
    public static function ghanaCard($value): bool
    {
        // Ghana Card format: GHA-XXXXXXXXX-X
        $pattern = '/^GHA-[0-9]{9}-[0-9]$/';
        return preg_match($pattern, $value) === 1;
    }
    
    /**
     * Validate date
     */
    public static function date($value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }
    
    /**
     * Validate datetime
     */
    public static function datetime($value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        return $d && $d->format('Y-m-d H:i:s') === $value;
    }
    
    /**
     * Validate in array
     */
    public static function in($value, array $allowed): bool
    {
        return in_array($value, $allowed);
    }
    
    /**
     * Validate unique in database
     */
    public static function unique($value, string $table, string $column, ?int $exceptId = null): bool
    {
        $db = \App\Config\Database::getConnection();
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];
        
        if ($exceptId) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return (int)$result['count'] === 0;
    }
    
    /**
     * Validate file upload
     */
    public static function file($file, array $allowedTypes = [], int $maxSize = 0): array
    {
        $errors = [];
        
        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Invalid file upload';
            return $errors;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
            return $errors;
        }
        
        if ($maxSize > 0 && $file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed';
        }
        
        if (!empty($allowedTypes)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedTypes)) {
                $errors[] = 'File type not allowed';
            }
        }
        
        return $errors;
    }
}
