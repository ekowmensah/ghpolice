<?php

namespace App\Models;

class CaseModel extends BaseModel
{
    protected string $table = 'cases';
    
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        return (int)$stmt->fetch()['total'];
    }
    
    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetch()['total'];
    }
    
    public function getRecent(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as complainant_name,
                   s.station_name
            FROM cases c
            LEFT JOIN complainants comp ON c.complainant_id = comp.id
            LEFT JOIN persons p ON comp.person_id = p.id
            LEFT JOIN stations s ON c.station_id = s.id
            ORDER BY c.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getByPriority(string $priority, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as complainant_name,
                   s.station_name
            FROM cases c
            LEFT JOIN complainants comp ON c.complainant_id = comp.id
            LEFT JOIN persons p ON comp.person_id = p.id
            LEFT JOIN stations s ON c.station_id = s.id
            WHERE c.case_priority = ? AND c.status = 'Open'
            ORDER BY c.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$priority, $limit]);
        return $stmt->fetchAll();
    }
    
    // ==================== RELATIONSHIP METHODS ====================
    
    /**
     * Get all suspects for this case
     */
    public function getSuspects(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   p.first_name, p.middle_name, p.last_name, p.gender, p.date_of_birth,
                   p.contact, p.ghana_card_number, p.photo_path,
                   p.fingerprint_captured, p.face_captured,
                   cs.added_date,
                   cs.removed_at,
                   cs.removed_by,
                   cs.removal_reason,
                   CONCAT_WS(' ', u.first_name, u.last_name) as removed_by_name,
                   (SELECT COUNT(*) FROM person_biometrics pb 
                    WHERE pb.person_id = p.id AND pb.biometric_type = 'Fingerprint') as fingerprint_count,
                   (SELECT COUNT(*) FROM person_biometrics pb 
                    WHERE pb.person_id = p.id AND pb.biometric_type = 'Face') as face_count
            FROM case_suspects cs
            INNER JOIN suspects s ON cs.suspect_id = s.id
            INNER JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON cs.removed_by = u.id
            WHERE cs.case_id = ?
            ORDER BY cs.removed_at IS NULL DESC, cs.added_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all witnesses for this case
     */
    public function getWitnesses(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT w.*, 
                   p.first_name, p.middle_name, p.last_name, p.gender, p.contact,
                   cw.added_date
            FROM case_witnesses cw
            INNER JOIN witnesses w ON cw.witness_id = w.id
            INNER JOIN persons p ON w.person_id = p.id
            WHERE cw.case_id = ?
            ORDER BY cw.added_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all assigned officers for this case
     */
    public function getAssignedOfficers(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT ca.*, 
                   o.service_number,
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                   pr.rank_name,
                   s.station_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as assigned_by_name
            FROM case_assignments ca
            INNER JOIN officers o ON ca.assigned_to = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            LEFT JOIN users u ON ca.assigned_by = u.id
            WHERE ca.case_id = ?
            ORDER BY ca.assignment_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all evidence for this case
     */
    public function getEvidence(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, 
                   CONCAT_WS(' ', u.first_name, u.last_name) as uploaded_by_name
            FROM evidence e
            LEFT JOIN users u ON e.uploaded_by = u.id
            WHERE e.case_id = ?
            ORDER BY e.collection_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all exhibits for this case
     */
    public function getExhibits(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT ex.*, 
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as seized_by_name,
                   pr.rank_name as seized_by_rank
            FROM exhibits ex
            LEFT JOIN officers o ON ex.seized_by = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE ex.case_id = ?
            ORDER BY ex.seized_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all statements for this case
     */
    public function getStatements(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT st.*, 
                   CASE 
                       WHEN st.suspect_id IS NOT NULL THEN CONCAT_WS(' ', ps.first_name, ps.last_name)
                       WHEN st.witness_id IS NOT NULL THEN CONCAT_WS(' ', pw.first_name, pw.last_name)
                       WHEN st.complainant_id IS NOT NULL THEN CONCAT_WS(' ', pc.first_name, pc.last_name)
                   END as person_name,
                   CASE 
                       WHEN st.suspect_id IS NOT NULL THEN 'Suspect'
                       WHEN st.witness_id IS NOT NULL THEN 'Witness'
                       WHEN st.complainant_id IS NOT NULL THEN 'Complainant'
                   END as person_role,
                   CONCAT_WS(' ', u.first_name, u.last_name) as recorded_by_name
            FROM statements st
            LEFT JOIN suspects s ON st.suspect_id = s.id
            LEFT JOIN persons ps ON s.person_id = ps.id
            LEFT JOIN witnesses w ON st.witness_id = w.id
            LEFT JOIN persons pw ON w.person_id = pw.id
            LEFT JOIN complainants comp ON st.complainant_id = comp.id
            LEFT JOIN persons pc ON comp.person_id = pc.id
            LEFT JOIN users u ON st.recorded_by = u.id
            WHERE st.case_id = ?
            ORDER BY st.recorded_at DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get investigation timeline for this case
     */
    public function getTimeline(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cit.*, 
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as completed_by_name,
                   pr.rank_name
            FROM case_investigation_timeline cit
            LEFT JOIN officers o ON cit.completed_by = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE cit.case_id = ?
            ORDER BY cit.activity_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get investigation tasks for this case
     */
    public function getTasks(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cit.*, 
                   CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as assigned_to_name,
                   pr.rank_name,
                   CONCAT_WS(' ', u.first_name, u.last_name) as assigned_by_name
            FROM case_investigation_tasks cit
            LEFT JOIN officers o ON cit.assigned_to = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN users u ON cit.assigned_by = u.id
            WHERE cit.case_id = ?
            ORDER BY cit.due_date ASC, cit.priority DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get case updates/notes
     */
    public function getUpdates(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT cu.*, 
                   CONCAT_WS(' ', u.first_name, u.last_name) as updated_by_name
            FROM case_updates cu
            LEFT JOIN users u ON cu.updated_by = u.id
            WHERE cu.case_id = ?
            ORDER BY cu.update_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get case status history
     */
    public function getStatusHistory(int $case_id): array
    {
        $stmt = $this->db->prepare("
            SELECT csh.*, 
                   CONCAT_WS(' ', u.first_name, u.last_name) as changed_by_name
            FROM case_status_history csh
            LEFT JOIN users u ON csh.changed_by = u.id
            WHERE csh.case_id = ?
            ORDER BY csh.change_date DESC
        ");
        $stmt->execute([$case_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add suspect to case
     */
    public function addSuspect(int $case_id, int $suspect_id, int $added_by): bool
    {
        // Use stored procedure for proper handling
        $stmt = $this->db->prepare("CALL sp_add_suspect_to_case(?, ?, ?)");
        return $stmt->execute([$case_id, $suspect_id, $added_by]);
    }
    
    /**
     * Add witness to case
     */
    public function addWitness(int $case_id, int $witness_id, int $added_by): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_witnesses (case_id, witness_id, added_date)
            VALUES (?, ?, NOW())
        ");
        return $stmt->execute([$case_id, $witness_id]);
    }
    
    /**
     * Assign officer to case
     */
    public function assignOfficer(int $case_id, int $officer_id, int $assigned_by, string $role = 'Investigator'): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_assignments (case_id, assigned_to, assigned_by, assignment_date, status, role)
            VALUES (?, ?, ?, NOW(), 'Active', ?)
        ");
        return $stmt->execute([$case_id, $officer_id, $assigned_by, $role]);
    }
    
    /**
     * Update case status with history tracking
     */
    public function updateStatus(int $case_id, string $new_status, int $changed_by, string $remarks = ''): bool
    {
        // Get current status
        $current = $this->find($case_id, ['status']);
        if (!$current) {
            return false;
        }
        
        $old_status = $current['status'];
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update case status
            $stmt = $this->db->prepare("UPDATE cases SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $case_id]);
            
            // Record status history
            $stmt = $this->db->prepare("
                INSERT INTO case_status_history (case_id, old_status, new_status, changed_by, change_date, remarks)
                VALUES (?, ?, ?, ?, NOW(), ?)
            ");
            $stmt->execute([$case_id, $old_status, $new_status, $changed_by, $remarks]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Add case update/note
     */
    public function addUpdate(int $case_id, string $update_note, int $updated_by): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO case_updates (case_id, update_note, updated_by, update_date)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$case_id, $update_note, $updated_by]);
    }
    
    /**
     * Get full case details with all relationships
     */
    public function getFullDetails(int $case_id): ?array
    {
        $case = $this->find($case_id);
        if (!$case) {
            return null;
        }
        
        // Add all relationships
        $case['suspects'] = $this->getSuspects($case_id);
        $case['witnesses'] = $this->getWitnesses($case_id);
        $case['assigned_officers'] = $this->getAssignedOfficers($case_id);
        $case['evidence'] = $this->getEvidence($case_id);
        $case['exhibits'] = $this->getExhibits($case_id);
        $case['statements'] = $this->getStatements($case_id);
        $case['timeline'] = $this->getTimeline($case_id);
        $case['tasks'] = $this->getTasks($case_id);
        $case['updates'] = $this->getUpdates($case_id);
        $case['status_history'] = $this->getStatusHistory($case_id);
        
        return $case;
    }
}
