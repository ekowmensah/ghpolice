<?php

namespace Tests\Helpers;

/**
 * Test Helper Functions
 */
class TestHelper
{
    /**
     * Generate random string
     */
    public static function randomString(int $length = 10): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }
    
    /**
     * Generate random email
     */
    public static function randomEmail(): string
    {
        return self::randomString(8) . '@test.com';
    }
    
    /**
     * Generate random phone number
     */
    public static function randomPhone(): string
    {
        return '0' . rand(200000000, 599999999);
    }
    
    /**
     * Generate random Ghana Card number
     */
    public static function randomGhanaCard(): string
    {
        return 'GHA-' . rand(100000000, 999999999) . '-' . rand(1, 9);
    }
    
    /**
     * Generate random case number
     */
    public static function randomCaseNumber(): string
    {
        return 'GH-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate random service number
     */
    public static function randomServiceNumber(): string
    {
        return 'GPS-' . rand(10000, 99999);
    }
    
    /**
     * Create test user data
     */
    public static function createUserData(array $overrides = []): array
    {
        return array_merge([
            'service_number' => self::randomServiceNumber(),
            'first_name' => 'Test',
            'middle_name' => 'User',
            'last_name' => 'Officer',
            'username' => self::randomString(8),
            'email' => self::randomEmail(),
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role_id' => 1,
            'station_id' => 1,
            'status' => 'Active'
        ], $overrides);
    }
    
    /**
     * Create test person data
     */
    public static function createPersonData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'middle_name' => 'Kwame',
            'last_name' => 'Mensah',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-15',
            'contact' => self::randomPhone(),
            'email' => self::randomEmail(),
            'address' => 'Test Address, Accra',
            'ghana_card_number' => self::randomGhanaCard()
        ], $overrides);
    }
    
    /**
     * Create test case data
     */
    public static function createCaseData(array $overrides = []): array
    {
        return array_merge([
            'case_number' => self::randomCaseNumber(),
            'case_type' => 'Complaint',
            'case_priority' => 'Medium',
            'description' => 'Test case description for testing purposes',
            'incident_location' => 'Test Location, Accra',
            'incident_date' => date('Y-m-d'),
            'station_id' => 1,
            'status' => 'Open',
            'created_by' => 1
        ], $overrides);
    }
}
