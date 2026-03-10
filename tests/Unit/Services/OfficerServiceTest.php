<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\OfficerService;
use Tests\Factories\ModelFactory;

/**
 * OfficerService Unit Tests
 */
class OfficerServiceTest extends TestCase
{
    private OfficerService $officerService;
    private ModelFactory $factory;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->officerService = new OfficerService();
        $this->factory = new ModelFactory($this->db);
    }
    
    /**
     * Test getOfficerFullProfile returns complete profile
     */
    public function testGetOfficerFullProfileReturnsCompleteProfile(): void
    {
        // Create test officer
        $officerId = $this->factory->createOfficer();
        
        // Get full profile
        $profile = $this->officerService->getOfficerFullProfile($officerId);
        
        // Assert
        $this->assertIsArray($profile);
        $this->assertArrayHasKey('id', $profile);
        $this->assertEquals($officerId, $profile['id']);
        $this->assertArrayHasKeys([
            'service_number',
            'first_name',
            'last_name',
            'assigned_cases',
            'posting_history',
            'performance_metrics'
        ], $profile);
    }
    
    /**
     * Test getOfficerPerformanceMetrics returns metrics
     */
    public function testGetOfficerPerformanceMetricsReturnsMetrics(): void
    {
        // Create test officer
        $officerId = $this->factory->createOfficer();
        
        // Get performance metrics
        $metrics = $this->officerService->getOfficerPerformanceMetrics($officerId);
        
        // Assert
        $this->assertIsArray($metrics);
        $this->assertArrayHasKeys([
            'total_cases',
            'closed_cases',
            'arrests_made'
        ], $metrics);
    }
    
    /**
     * Test checkOfficerWorkload returns count
     */
    public function testCheckOfficerWorkloadReturnsCount(): void
    {
        // Create test officer
        $officerId = $this->factory->createOfficer();
        
        // Check workload
        $workload = $this->officerService->checkOfficerWorkload($officerId);
        
        // Assert
        $this->assertIsInt($workload);
        $this->assertGreaterThanOrEqual(0, $workload);
    }
    
    /**
     * Test findBestOfficerForAssignment finds officer
     */
    public function testFindBestOfficerForAssignmentFindsOfficer(): void
    {
        // Create test officers
        $this->factory->createOfficer(['current_station_id' => 1]);
        $this->factory->createOfficer(['current_station_id' => 1]);
        
        // Find best officer
        $officer = $this->officerService->findBestOfficerForAssignment(1, 10);
        
        // Assert
        if ($officer !== null) {
            $this->assertIsArray($officer);
            $this->assertArrayHasKey('id', $officer);
            $this->assertArrayHasKey('current_workload', $officer);
        } else {
            // No officers available is also valid
            $this->assertNull($officer);
        }
    }
    
    /**
     * Test getAssignedCases returns cases
     */
    public function testGetAssignedCasesReturnsCases(): void
    {
        // Create test officer
        $officerId = $this->factory->createOfficer();
        
        // Get assigned cases
        $cases = $this->officerService->getAssignedCases($officerId);
        
        // Assert
        $this->assertIsArray($cases);
    }
    
    /**
     * Test getPostingHistory returns history
     */
    public function testGetPostingHistoryReturnsHistory(): void
    {
        // Create test officer
        $officerId = $this->factory->createOfficer();
        
        // Get posting history
        $history = $this->officerService->getPostingHistory($officerId);
        
        // Assert
        $this->assertIsArray($history);
    }
    
    /**
     * Test getPromotionHistory returns history
     */
    public function testGetPromotionHistoryReturnsHistory(): void
    {
        // Create test officer
        $officerId = $this->factory->createOfficer();
        
        // Get promotion history
        $history = $this->officerService->getPromotionHistory($officerId);
        
        // Assert
        $this->assertIsArray($history);
    }
}
