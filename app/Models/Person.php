<?php

namespace App\Models;

class Person extends BaseModel
{
    protected string $table = 'persons';
    
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        return (int)$stmt->fetch()['total'];
    }
    
    public function countWanted(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE is_wanted = 1");
        return (int)$stmt->fetch()['total'];
    }
    
    public function search(string $keyword): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE first_name LIKE ? 
               OR last_name LIKE ?
               OR ghana_card_number LIKE ?
               OR contact LIKE ?
               OR passport_number LIKE ?
               OR drivers_license LIKE ?
            LIMIT 50
        ");
        $searchTerm = "%{$keyword}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    public function checkCriminalRecord(array $params): array
    {
        $stmt = $this->db->prepare("CALL sp_check_person_criminal_record(?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $params['ghana_card'] ?? null,
            $params['contact'] ?? null,
            $params['passport'] ?? null,
            $params['drivers_license'] ?? null,
            $params['first_name'] ?? null,
            $params['last_name'] ?? null
        ]);
        
        $results = [];
        do {
            $results[] = $stmt->fetchAll();
        } while ($stmt->nextRowset());
        
        return $results;
    }
    
    public function registerPerson(array $data): array
    {
        // Direct insert - frontend already handles duplicate detection
        // Stored procedure's duplicate detection is too aggressive
        try {
            // Convert empty strings to NULL to avoid false duplicates on unique constraints
            $cleanData = [
                'first_name' => $data['first_name'],
                'middle_name' => !empty($data['middle_name']) ? $data['middle_name'] : null,
                'last_name' => $data['last_name'],
                'gender' => !empty($data['gender']) ? $data['gender'] : null,
                'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                'contact' => !empty($data['contact']) ? $data['contact'] : null,
                'email' => !empty($data['email']) ? $data['email'] : null,
                'address' => !empty($data['address']) ? $data['address'] : null,
                'ghana_card' => !empty($data['ghana_card']) ? $data['ghana_card'] : null,
                'passport' => !empty($data['passport']) ? $data['passport'] : null,
                'drivers_license' => !empty($data['drivers_license']) ? $data['drivers_license'] : null,
                'alternative_contact' => !empty($data['alternative_contact']) ? $data['alternative_contact'] : null
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO persons (
                    first_name, middle_name, last_name, gender, date_of_birth,
                    contact, email, address, ghana_card_number, passport_number, 
                    drivers_license, alternative_contact
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $cleanData['first_name'],
                $cleanData['middle_name'],
                $cleanData['last_name'],
                $cleanData['gender'],
                $cleanData['date_of_birth'],
                $cleanData['contact'],
                $cleanData['email'],
                $cleanData['address'],
                $cleanData['ghana_card'],
                $cleanData['passport'],
                $cleanData['drivers_license'],
                $cleanData['alternative_contact']
            ]);
            
            $personId = $this->db->lastInsertId();
            
            return [
                'person_id' => $personId,
                'is_duplicate' => false,
                'message' => 'New person registered successfully'
            ];
        } catch (\PDOException $e) {
            // If duplicate key error, it means a unique constraint was violated
            if ($e->getCode() == 23000) {
                // Try to find the existing person based on the unique fields
                $existingPerson = null;
                
                // Check by Ghana Card if provided
                if (!empty($data['ghana_card'])) {
                    $stmt = $this->db->prepare("SELECT id FROM persons WHERE ghana_card_number = ? LIMIT 1");
                    $stmt->execute([$data['ghana_card']]);
                    $existingPerson = $stmt->fetch();
                }
                
                // Check by contact if Ghana Card not found and contact provided
                if (!$existingPerson && !empty($data['contact'])) {
                    $stmt = $this->db->prepare("SELECT id FROM persons WHERE contact = ? LIMIT 1");
                    $stmt->execute([$data['contact']]);
                    $existingPerson = $stmt->fetch();
                }
                
                // Check by passport if still not found and passport provided
                if (!$existingPerson && !empty($data['passport'])) {
                    $stmt = $this->db->prepare("SELECT id FROM persons WHERE passport_number = ? LIMIT 1");
                    $stmt->execute([$data['passport']]);
                    $existingPerson = $stmt->fetch();
                }
                
                return [
                    'person_id' => $existingPerson['id'] ?? null,
                    'is_duplicate' => true,
                    'message' => 'Person with this information already exists'
                ];
            }
            throw $e;
        }
    }
    
    // ==================== RELATIONSHIP METHODS ====================
    
    /**
     * Get criminal history for this person
     */
    public function getCriminalHistory(int $person_id): array
    {
        $stmt = $this->db->prepare("
            SELECT pch.*, 
                   c.case_number, c.case_type, c.status as case_status,
                   c.incident_date, c.description as case_description
            FROM person_criminal_history pch
            LEFT JOIN cases c ON pch.case_id = c.id
            WHERE pch.person_id = ?
            ORDER BY pch.case_date DESC
        ");
        $stmt->execute([$person_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active alerts for this person
     */
    public function getAlerts(int $person_id): array
    {
        $stmt = $this->db->prepare("
            SELECT pa.*, 
                   CONCAT_WS(' ', u.first_name, u.last_name) as issued_by_name
            FROM person_alerts pa
            LEFT JOIN users u ON pa.issued_by = u.id
            WHERE pa.person_id = ? AND pa.is_active = 1
            ORDER BY pa.alert_priority DESC, pa.issued_date DESC
        ");
        $stmt->execute([$person_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get known aliases for this person
     */
    public function getAliases(int $person_id): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM person_aliases
            WHERE person_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$person_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get person relationships
     */
    public function getRelationships(int $person_id): array
    {
        $stmt = $this->db->prepare("
            SELECT pr.*, 
                   CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as related_person_name,
                   p.ghana_card_number as related_ghana_card
            FROM person_relationships pr
            INNER JOIN persons p ON pr.person_id_2 = p.id
            WHERE pr.person_id_1 = ?
            ORDER BY pr.relationship_type
        ");
        $stmt->execute([$person_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get cases where person is a suspect
     */
    public function getCasesAsSuspect(int $person_id): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, s.current_status as suspect_status, cs.added_date,
                   st.station_name
            FROM suspects s
            INNER JOIN case_suspects cs ON s.id = cs.suspect_id
            INNER JOIN cases c ON cs.case_id = c.id
            LEFT JOIN stations st ON c.station_id = st.id
            WHERE s.person_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$person_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get cases where person is a witness
     */
    public function getCasesAsWitness(int $person_id): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, w.witness_type, cw.added_date,
                   st.station_name
            FROM witnesses w
            INNER JOIN case_witnesses cw ON w.id = cw.witness_id
            INNER JOIN cases c ON cw.case_id = c.id
            LEFT JOIN stations st ON c.station_id = st.id
            WHERE w.person_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$person_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get cases where person is a complainant
     */
    public function getCasesAsComplainant(int $person_id): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, comp.complainant_type,
                   st.station_name
            FROM complainants comp
            INNER JOIN cases c ON comp.id = c.complainant_id
            LEFT JOIN stations st ON c.station_id = st.id
            WHERE comp.person_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$person_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Find potential duplicate persons using stored procedure
     */
    public function findSimilarPersons(array $data): array
    {
        $stmt = $this->db->prepare("CALL sp_find_similar_persons(?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['contact'] ?? null
        ]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Add person to case with specific role
     */
    public function addToCase(int $person_id, int $case_id, string $role, int $added_by): bool
    {
        $this->db->beginTransaction();
        
        try {
            if ($role === 'Suspect') {
                // Create suspect record if doesn't exist
                $stmt = $this->db->prepare("
                    INSERT INTO suspects (person_id, current_status)
                    SELECT ?, 'Suspect'
                    WHERE NOT EXISTS (SELECT 1 FROM suspects WHERE person_id = ?)
                ");
                $stmt->execute([$person_id, $person_id]);
                
                // Get suspect_id
                $stmt = $this->db->prepare("SELECT id FROM suspects WHERE person_id = ? LIMIT 1");
                $stmt->execute([$person_id]);
                $suspect = $stmt->fetch();
                
                if ($suspect) {
                    // Use stored procedure to add suspect to case
                    $stmt = $this->db->prepare("CALL sp_add_suspect_to_case(?, ?, ?)");
                    $stmt->execute([$case_id, $suspect['id'], $added_by]);
                }
            } elseif ($role === 'Witness') {
                // Create witness record if doesn't exist
                $stmt = $this->db->prepare("
                    INSERT INTO witnesses (person_id, witness_type)
                    SELECT ?, 'Eye Witness'
                    WHERE NOT EXISTS (SELECT 1 FROM witnesses WHERE person_id = ?)
                ");
                $stmt->execute([$person_id, $person_id]);
                
                // Get witness_id
                $stmt = $this->db->prepare("SELECT id FROM witnesses WHERE person_id = ? LIMIT 1");
                $stmt->execute([$person_id]);
                $witness = $stmt->fetch();
                
                if ($witness) {
                    // Add to case_witnesses
                    $stmt = $this->db->prepare("
                        INSERT INTO case_witnesses (case_id, witness_id, added_date)
                        VALUES (?, ?, NOW())
                    ");
                    $stmt->execute([$case_id, $witness['id']]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get full person profile with all relationships
     */
    public function getFullProfile(int $person_id): ?array
    {
        $person = $this->find($person_id);
        if (!$person) {
            return null;
        }
        
        // Add all relationships
        $person['criminal_history'] = $this->getCriminalHistory($person_id);
        $person['alerts'] = $this->getAlerts($person_id);
        $person['aliases'] = $this->getAliases($person_id);
        $person['relationships'] = $this->getRelationships($person_id);
        $person['cases_as_suspect'] = $this->getCasesAsSuspect($person_id);
        $person['cases_as_witness'] = $this->getCasesAsWitness($person_id);
        $person['cases_as_complainant'] = $this->getCasesAsComplainant($person_id);
        
        return $person;
    }
}
