<?php

namespace App\Services;

use App\Models\Person;
use App\Config\Database;
use PDO;

class PersonService
{
    private Person $personModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->personModel = new Person();
        $this->db = Database::getConnection();
    }
    
    /**
     * Register person with duplicate detection
     * Uses stored procedure sp_register_person
     */
    public function registerPerson(array $data): array
    {
        try {
            $result = $this->personModel->registerPerson($data);
            
            // Log the registration
            logger("Person registration: " . ($result['is_duplicate'] ? 'Duplicate found' : 'New person created') . " - ID: {$result['person_id']}");
            
            return [
                'person_id' => (int)$result['person_id'],
                'is_duplicate' => (bool)$result['is_duplicate'],
                'message' => $result['message']
            ];
        } catch (\Exception $e) {
            logger("Person registration failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Perform instant crime check
     * Uses stored procedure sp_check_person_criminal_record
     */
    public function performCrimeCheck(array $params): array
    {
        try {
            $results = $this->personModel->checkCriminalRecord($params);
            
            // Parse results from stored procedure
            // Result set 0: Person details with alert status
            // Result set 1: Active alerts
            // Result set 2: Criminal history
            // Result set 3: Current suspect cases
            
            $crimeCheck = [
                'found' => false,
                'person' => null,
                'alerts' => [],
                'criminal_history' => [],
                'current_cases' => []
            ];
            
            if (!empty($results[0]) && !empty($results[0][0])) {
                $personData = $results[0][0];
                
                if (isset($personData['alert_status']) && $personData['alert_status'] !== 'PERSON NOT FOUND IN SYSTEM') {
                    $crimeCheck['found'] = true;
                    $crimeCheck['person'] = $personData;
                }
            }
            
            if (!empty($results[1])) {
                $crimeCheck['alerts'] = $results[1];
            }
            
            if (!empty($results[2])) {
                $crimeCheck['criminal_history'] = $results[2];
            }
            
            if (!empty($results[3])) {
                $crimeCheck['current_cases'] = $results[3];
            }
            
            // Log crime check
            $identifier = $params['ghana_card'] ?? $params['contact'] ?? $params['first_name'] . ' ' . $params['last_name'];
            logger("Crime check performed: {$identifier} - " . ($crimeCheck['found'] ? 'FOUND' : 'NOT FOUND'));
            
            return $crimeCheck;
        } catch (\Exception $e) {
            logger("Crime check failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    // ==================== PHASE 1 INTEGRATION METHODS ====================
    
    /**
     * Get complete person profile with all relationships (Phase 1)
     */
    public function getPersonFullProfile(int $person_id): ?array
    {
        return $this->personModel->getFullProfile($person_id);
        // Returns: person + criminal_history + alerts + aliases + relationships +
        //          cases_as_suspect + cases_as_witness + cases_as_complainant
    }
    
    /**
     * Find similar persons using stored procedure (Phase 1)
     */
    public function findSimilarPersons(array $data): array
    {
        try {
            $similar = $this->personModel->findSimilarPersons($data);
            
            logger("Similar persons search: " . count($similar) . " matches found");
            
            return $similar;
        } catch (\Exception $e) {
            logger("Similar persons search failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Add person to case with specific role (Phase 1)
     */
    public function addPersonToCase(int $person_id, int $case_id, string $role, int $added_by): bool
    {
        try {
            $result = $this->personModel->addToCase($person_id, $case_id, $role, $added_by);
            
            if ($result) {
                logger("Person {$person_id} added to case {$case_id} as {$role}");
            }
            
            return $result;
        } catch (\Exception $e) {
            logger("Failed to add person to case: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get criminal history for a person (Phase 1)
     */
    public function getCriminalHistory(int $person_id): array
    {
        return $this->personModel->getCriminalHistory($person_id);
    }
    
    /**
     * Get active alerts for a person (Phase 1)
     */
    public function getActiveAlerts(int $person_id): array
    {
        return $this->personModel->getAlerts($person_id);
    }
    
    /**
     * Get person aliases (Phase 1)
     */
    public function getPersonAliases(int $person_id): array
    {
        return $this->personModel->getAliases($person_id);
    }
    
    /**
     * Get person relationships (Phase 1)
     */
    public function getPersonRelationships(int $person_id): array
    {
        return $this->personModel->getRelationships($person_id);
    }
    
    /**
     * Get cases where person is suspect (Phase 1)
     */
    public function getCasesAsSuspect(int $person_id): array
    {
        return $this->personModel->getCasesAsSuspect($person_id);
    }
    
    /**
     * Get cases where person is witness (Phase 1)
     */
    public function getCasesAsWitness(int $person_id): array
    {
        return $this->personModel->getCasesAsWitness($person_id);
    }
    
    /**
     * Get cases where person is complainant (Phase 1)
     */
    public function getCasesAsComplainant(int $person_id): array
    {
        return $this->personModel->getCasesAsComplainant($person_id);
    }
    
    /**
     * Add alias to person
     */
    public function addAlias(int $personId, string $aliasName): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO person_aliases (person_id, alias_name)
            VALUES (?, ?)
        ");
        return $stmt->execute([$personId, $aliasName]);
    }
    
    /**
     * Create alert for person
     */
    public function createAlert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO person_alerts (
                person_id, alert_type, alert_priority, alert_message,
                alert_details, issued_by, issued_date, expiry_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['person_id'],
            $data['alert_type'],
            $data['alert_priority'] ?? 'Medium',
            $data['alert_message'],
            $data['alert_details'] ?? null,
            $data['issued_by'],
            $data['issued_date'] ?? date('Y-m-d'),
            $data['expiry_date'] ?? null
        ]);
        
        $alertId = (int)$this->db->lastInsertId();
        
        // Update person risk level if needed
        $this->updatePersonRiskLevel($data['person_id']);
        
        logger("Alert created for person ID: {$data['person_id']}, Alert ID: {$alertId}");
        
        return $alertId;
    }
    
    /**
     * Deactivate alert
     */
    public function deactivateAlert(int $alertId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE person_alerts
            SET is_active = FALSE
            WHERE id = ?
        ");
        return $stmt->execute([$alertId]);
    }
    
    /**
     * Update person risk level based on alerts and criminal history
     */
    public function updatePersonRiskLevel(int $personId): void
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(CASE WHEN alert_priority = 'Critical' THEN 1 END) as critical_alerts,
                COUNT(CASE WHEN alert_priority = 'High' THEN 1 END) as high_alerts,
                COUNT(*) as total_alerts
            FROM person_alerts
            WHERE person_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$personId]);
        $alertStats = $stmt->fetch();
        
        $riskLevel = 'None';
        
        if ($alertStats['critical_alerts'] > 0) {
            $riskLevel = 'Critical';
        } elseif ($alertStats['high_alerts'] > 0) {
            $riskLevel = 'High';
        } elseif ($alertStats['total_alerts'] > 2) {
            $riskLevel = 'Medium';
        } elseif ($alertStats['total_alerts'] > 0) {
            $riskLevel = 'Low';
        }
        
        $stmt = $this->db->prepare("
            UPDATE persons
            SET risk_level = ?
            WHERE id = ?
        ");
        $stmt->execute([$riskLevel, $personId]);
    }
    
}
