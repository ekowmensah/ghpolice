<?php

namespace Tests\Factories;

use Tests\Helpers\TestHelper;
use PDO;

/**
 * Model Factory
 * 
 * Creates test data in the database
 */
class ModelFactory
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Create a user
     */
    public function createUser(array $data = []): int
    {
        $userData = TestHelper::createUserData($data);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (service_number, first_name, middle_name, last_name, username, email, password_hash, role_id, station_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userData['service_number'],
            $userData['first_name'],
            $userData['middle_name'],
            $userData['last_name'],
            $userData['username'],
            $userData['email'],
            $userData['password_hash'],
            $userData['role_id'],
            $userData['station_id'],
            $userData['status']
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Create a person
     */
    public function createPerson(array $data = []): int
    {
        $personData = TestHelper::createPersonData($data);
        
        $stmt = $this->db->prepare("
            INSERT INTO persons (first_name, middle_name, last_name, gender, date_of_birth, contact, email, address, ghana_card_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $personData['first_name'],
            $personData['middle_name'],
            $personData['last_name'],
            $personData['gender'],
            $personData['date_of_birth'],
            $personData['contact'],
            $personData['email'],
            $personData['address'],
            $personData['ghana_card_number']
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Create a case
     */
    public function createCase(array $data = []): int
    {
        $caseData = TestHelper::createCaseData($data);
        
        $stmt = $this->db->prepare("
            INSERT INTO cases (case_number, case_type, case_priority, description, incident_location, incident_date, station_id, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $caseData['case_number'],
            $caseData['case_type'],
            $caseData['case_priority'],
            $caseData['description'],
            $caseData['incident_location'],
            $caseData['incident_date'],
            $caseData['station_id'],
            $caseData['status'],
            $caseData['created_by']
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Create a suspect
     */
    public function createSuspect(int $personId, array $data = []): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO suspects (person_id, current_status)
            VALUES (?, ?)
        ");
        
        $stmt->execute([
            $personId,
            $data['current_status'] ?? 'Suspect'
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Create an officer
     */
    public function createOfficer(array $data = []): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO officers (service_number, first_name, middle_name, last_name, rank_id, gender, phone_number, email, current_station_id, employment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['service_number'] ?? TestHelper::randomServiceNumber(),
            $data['first_name'] ?? 'Test',
            $data['middle_name'] ?? 'Officer',
            $data['last_name'] ?? 'Name',
            $data['rank_id'] ?? 1,
            $data['gender'] ?? 'Male',
            $data['phone_number'] ?? TestHelper::randomPhone(),
            $data['email'] ?? TestHelper::randomEmail(),
            $data['current_station_id'] ?? 1,
            $data['employment_status'] ?? 'Active'
        ]);
        
        return (int)$this->db->lastInsertId();
    }
}
