<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PersonService;
use App\Models\Person;
use Tests\Factories\ModelFactory;

/**
 * PersonService Unit Tests
 */
class PersonServiceTest extends TestCase
{
    private PersonService $personService;
    private ModelFactory $factory;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->personService = new PersonService();
        $this->factory = new ModelFactory($this->db);
    }
    
    /**
     * Test getPersonFullProfile returns complete profile
     */
    public function testGetPersonFullProfileReturnsCompleteProfile(): void
    {
        // Create test person
        $personId = $this->factory->createPerson();
        
        // Get full profile
        $profile = $this->personService->getPersonFullProfile($personId);
        
        // Assert
        $this->assertIsArray($profile);
        $this->assertArrayHasKey('id', $profile);
        $this->assertEquals($personId, $profile['id']);
        $this->assertArrayHasKeys([
            'first_name',
            'last_name',
            'ghana_card_number',
            'criminal_history',
            'alerts',
            'aliases'
        ], $profile);
    }
    
    /**
     * Test getPersonFullProfile returns null for non-existent person
     */
    public function testGetPersonFullProfileReturnsNullForNonExistentPerson(): void
    {
        $profile = $this->personService->getPersonFullProfile(99999);
        
        $this->assertNull($profile);
    }
    
    /**
     * Test findSimilarPersons finds duplicates
     */
    public function testFindSimilarPersonsFindsMatches(): void
    {
        // Create test person
        $personData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-15',
            'contact' => '0244123456'
        ];
        $this->factory->createPerson($personData);
        
        // Search for similar persons
        $similar = $this->personService->findSimilarPersons(
            $personData['first_name'],
            $personData['last_name'],
            $personData['date_of_birth'],
            $personData['contact']
        );
        
        // Assert
        $this->assertIsArray($similar);
        $this->assertGreaterThan(0, count($similar));
    }
    
    /**
     * Test getCriminalHistory returns history
     */
    public function testGetCriminalHistoryReturnsHistory(): void
    {
        // Create test person
        $personId = $this->factory->createPerson();
        
        // Get criminal history
        $history = $this->personService->getCriminalHistory($personId);
        
        // Assert
        $this->assertIsArray($history);
        // History might be empty for new person
    }
    
    /**
     * Test getActiveAlerts returns alerts
     */
    public function testGetActiveAlertsReturnsAlerts(): void
    {
        // Create test person
        $personId = $this->factory->createPerson();
        
        // Get active alerts
        $alerts = $this->personService->getActiveAlerts($personId);
        
        // Assert
        $this->assertIsArray($alerts);
        // Alerts might be empty for new person
    }
    
    /**
     * Test getPersonAliases returns aliases
     */
    public function testGetPersonAliasesReturnsAliases(): void
    {
        // Create test person
        $personId = $this->factory->createPerson();
        
        // Get aliases
        $aliases = $this->personService->getPersonAliases($personId);
        
        // Assert
        $this->assertIsArray($aliases);
    }
    
    /**
     * Test getCasesAsSuspect returns cases
     */
    public function testGetCasesAsSuspectReturnsCases(): void
    {
        // Create test person
        $personId = $this->factory->createPerson();
        
        // Get cases as suspect
        $cases = $this->personService->getCasesAsSuspect($personId);
        
        // Assert
        $this->assertIsArray($cases);
    }
    
    /**
     * Test getCasesAsWitness returns cases
     */
    public function testGetCasesAsWitnessReturnsCases(): void
    {
        // Create test person
        $personId = $this->factory->createPerson();
        
        // Get cases as witness
        $cases = $this->personService->getCasesAsWitness($personId);
        
        // Assert
        $this->assertIsArray($cases);
    }
}
