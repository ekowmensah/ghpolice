<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CaseService;
use App\Models\CaseModel;
use App\Models\CaseSuspect;
use App\Models\CaseWitness;
use App\Models\CaseAssignment;
use Tests\Factories\ModelFactory;

/**
 * CaseService Unit Tests
 */
class CaseServiceTest extends TestCase
{
    private CaseService $caseService;
    private ModelFactory $factory;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->caseService = new CaseService();
        $this->factory = new ModelFactory($this->db);
    }
    
    /**
     * Test getCaseFullDetails returns complete case data
     */
    public function testGetCaseFullDetailsReturnsCompleteData(): void
    {
        // Create test case
        $caseId = $this->factory->createCase();
        
        // Get full details
        $result = $this->caseService->getCaseFullDetails($caseId);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($caseId, $result['id']);
        $this->assertArrayHasKeys([
            'case_number',
            'description',
            'status',
            'suspects',
            'witnesses',
            'evidence',
            'assigned_officers'
        ], $result);
    }
    
    /**
     * Test getCaseFullDetails returns null for non-existent case
     */
    public function testGetCaseFullDetailsReturnsNullForNonExistentCase(): void
    {
        $result = $this->caseService->getCaseFullDetails(99999);
        
        $this->assertNull($result);
    }
    
    /**
     * Test addSuspectToCaseV2 adds suspect successfully
     */
    public function testAddSuspectToCaseV2AddsSuccessfully(): void
    {
        // Create test data
        $caseId = $this->factory->createCase();
        $personId = $this->factory->createPerson();
        $suspectId = $this->factory->createSuspect($personId);
        $userId = $this->factory->createUser();
        
        // Add suspect to case
        $result = $this->caseService->addSuspectToCaseV2($caseId, $suspectId, $userId);
        
        // Assert
        $this->assertTrue($result);
        
        // Verify suspect was added
        $caseSuspect = new CaseSuspect();
        $suspects = $caseSuspect->getByCaseId($caseId);
        $this->assertCount(1, $suspects);
        $this->assertEquals($suspectId, $suspects[0]['suspect_id']);
    }
    
    /**
     * Test addSuspectToCaseV2 prevents duplicate suspects
     */
    public function testAddSuspectToCaseV2PreventsDuplicates(): void
    {
        // Create test data
        $caseId = $this->factory->createCase();
        $personId = $this->factory->createPerson();
        $suspectId = $this->factory->createSuspect($personId);
        $userId = $this->factory->createUser();
        
        // Add suspect first time
        $result1 = $this->caseService->addSuspectToCaseV2($caseId, $suspectId, $userId);
        $this->assertTrue($result1);
        
        // Try to add same suspect again
        $result2 = $this->caseService->addSuspectToCaseV2($caseId, $suspectId, $userId);
        $this->assertFalse($result2);
        
        // Verify only one suspect exists
        $caseSuspect = new CaseSuspect();
        $suspects = $caseSuspect->getByCaseId($caseId);
        $this->assertCount(1, $suspects);
    }
    
    /**
     * Test assignOfficerToCase assigns successfully
     */
    public function testAssignOfficerToCaseAssignsSuccessfully(): void
    {
        // Create test data
        $caseId = $this->factory->createCase();
        $officerId = $this->factory->createOfficer();
        $supervisorId = $this->factory->createUser();
        
        // Assign officer
        $result = $this->caseService->assignOfficerToCase($caseId, $officerId, $supervisorId, 'Lead Investigator');
        
        // Assert
        $this->assertTrue($result);
        
        // Verify assignment
        $assignment = new CaseAssignment();
        $assignments = $assignment->getByCaseId($caseId);
        $this->assertCount(1, $assignments);
        $this->assertEquals($officerId, $assignments[0]['assigned_to']);
    }
    
    /**
     * Test getCaseTimeline returns combined timeline
     */
    public function testGetCaseTimelineReturnsCombinedTimeline(): void
    {
        // Create test case
        $caseId = $this->factory->createCase();
        
        // Get timeline
        $timeline = $this->caseService->getCaseTimeline($caseId);
        
        // Assert
        $this->assertIsArray($timeline);
        // Timeline might be empty for new case, but should be an array
    }
    
    /**
     * Test getOfficerWorkload returns count
     */
    public function testGetOfficerWorkloadReturnsCount(): void
    {
        // Create officer
        $officerId = $this->factory->createOfficer();
        
        // Get workload
        $workload = $this->caseService->getOfficerWorkload($officerId);
        
        // Assert
        $this->assertIsInt($workload);
        $this->assertGreaterThanOrEqual(0, $workload);
    }
    
    /**
     * Test closeCaseWorkflow validates requirements
     */
    public function testCloseCaseWorkflowValidatesRequirements(): void
    {
        // Create test case without suspects
        $caseId = $this->factory->createCase();
        $userId = $this->factory->createUser();
        
        // Try to close case without suspects
        $result = $this->caseService->closeCaseWorkflow($caseId, $userId, 'Convicted', 'Test closure');
        
        // Assert - should fail validation
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }
}
