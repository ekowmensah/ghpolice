<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;

/**
 * Base Test Case
 * 
 * All test classes should extend this class
 */
abstract class TestCase extends BaseTestCase
{
    protected PDO $db;
    protected bool $useTransactions = true;
    
    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get database connection
        $this->db = \App\Config\Database::getConnection();
        
        // Start transaction for test isolation
        if ($this->useTransactions) {
            $this->db->beginTransaction();
        }
    }
    
    /**
     * Tear down after each test
     */
    protected function tearDown(): void
    {
        // Rollback transaction to clean up test data
        if ($this->useTransactions && $this->db->inTransaction()) {
            $this->db->rollBack();
        }
        
        parent::tearDown();
    }
    
    /**
     * Create a mock for a class
     */
    protected function createMockWithMethods(string $className, array $methods = []): object
    {
        $mock = $this->createMock($className);
        
        foreach ($methods as $method => $returnValue) {
            $mock->method($method)->willReturn($returnValue);
        }
        
        return $mock;
    }
    
    /**
     * Assert array has keys
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message);
        }
    }
    
    /**
     * Assert response is successful
     */
    protected function assertSuccess($result): void
    {
        $this->assertTrue($result !== false && $result !== null, 'Expected successful result');
    }
}
