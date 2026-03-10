<?php

namespace App\Config;

class Auth
{
    /**
     * Session timeout in minutes
     */
    public const SESSION_LIFETIME = 120;
    
    /**
     * Password minimum length
     */
    public const PASSWORD_MIN_LENGTH = 8;
    
    /**
     * Password hashing algorithm
     */
    public const PASSWORD_ALGO = PASSWORD_BCRYPT;
    
    /**
     * Maximum login attempts before lockout
     */
    public const MAX_LOGIN_ATTEMPTS = 5;
    
    /**
     * Lockout duration in minutes
     */
    public const LOCKOUT_DURATION = 15;
    
    /**
     * Remember me token lifetime in days
     */
    public const REMEMBER_ME_LIFETIME = 30;
    
    /**
     * Two-factor authentication enabled
     */
    public const TWO_FACTOR_ENABLED = false;
    
    /**
     * Default user role
     */
    public const DEFAULT_ROLE = 'Officer';
    
    /**
     * Available roles with hierarchy
     */
    public const ROLES = [
        'Super Admin' => 100,
        'Administrator' => 90,
        'Commander' => 80,
        'Senior Officer' => 70,
        'Officer' => 60,
        'Clerk' => 50,
        'Guest' => 10
    ];
    
    /**
     * Data access levels
     */
    public const ACCESS_LEVELS = [
        'National' => 100,
        'Regional' => 80,
        'District' => 60,
        'Station' => 40,
        'Unit' => 20,
        'Own' => 10
    ];
    
    /**
     * Get role hierarchy level
     */
    public static function getRoleLevel(string $role): int
    {
        return self::ROLES[$role] ?? 0;
    }
    
    /**
     * Check if role has permission
     */
    public static function hasPermission(string $userRole, string $requiredRole): bool
    {
        return self::getRoleLevel($userRole) >= self::getRoleLevel($requiredRole);
    }
}
