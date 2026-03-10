<?php

namespace App\Models;

class Arrest extends BaseModel
{
    protected string $table = 'arrests';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                a.*,
                c.case_number,
                c.status as case_status,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.date_of_birth,
                p.contact,
                p.address,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as arresting_officer_name,
                pr.rank_name,
                o.service_number
            FROM arrests a
            JOIN cases c ON a.case_id = c.id
            JOIN suspects s ON a.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            JOIN officers o ON a.arresting_officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE a.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                a.id,
                a.case_id,
                a.suspect_id,
                a.arresting_officer_id,
                a.arrest_date,
                a.arrest_location,
                a.arrest_type,
                a.warrant_number,
                a.reason,
                p.first_name,
                p.middle_name,
                p.last_name,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as full_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as arresting_officer_name,
                pr.rank_name
            FROM arrests a
            JOIN suspects s ON a.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            JOIN officers o ON a.arresting_officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE a.case_id = ?
            ORDER BY a.arrest_date DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getBySuspectId(int $suspectId): array
    {
        $sql = "
            SELECT 
                a.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as arresting_officer_name
            FROM arrests a
            JOIN cases c ON a.case_id = c.id
            JOIN officers o ON a.arresting_officer_id = o.id
            WHERE a.suspect_id = ?
            ORDER BY a.arrest_date DESC
        ";
        
        return $this->query($sql, [$suspectId]);
    }
    
    public function recordArrest(array $data): int
    {
        $this->db->beginTransaction();
        
        try {
            $arrestId = $this->create($data);
            
            $this->execute(
                "UPDATE suspects SET current_status = 'Arrested', arrest_date = ? WHERE id = ?",
                [$data['arrest_date'], $data['suspect_id']]
            );
            
            $this->db->commit();
            return $arrestId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
