<?php

namespace App\Models;

class CourtProceeding extends BaseModel
{
    protected string $table = 'court_proceedings';
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                cp.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as recorded_by_name
            FROM court_proceedings cp
            JOIN cases c ON cp.case_id = c.id
            JOIN suspects s ON cp.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            LEFT JOIN users u ON cp.recorded_by = u.id
            WHERE cp.id = ?
        ";
        
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                cp.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM court_proceedings cp
            JOIN suspects s ON cp.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            WHERE cp.case_id = ?
            ORDER BY cp.court_date DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getBySuspectId(int $suspectId): array
    {
        $sql = "
            SELECT 
                cp.*,
                c.case_number,
                c.description as case_description
            FROM court_proceedings cp
            JOIN cases c ON cp.case_id = c.id
            WHERE cp.suspect_id = ?
            ORDER BY cp.court_date DESC
        ";
        
        return $this->query($sql, [$suspectId]);
    }
    
    public function getUpcomingHearings(?int $caseId = null): array
    {
        $sql = "
            SELECT 
                cp.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM court_proceedings cp
            JOIN cases c ON cp.case_id = c.id
            JOIN suspects s ON cp.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            WHERE cp.next_hearing_date >= CURDATE()
        ";
        
        $params = [];
        
        if ($caseId) {
            $sql .= " AND cp.case_id = ?";
            $params[] = $caseId;
        }
        
        $sql .= " ORDER BY cp.next_hearing_date ASC";
        
        return $this->query($sql, $params);
    }
    
    public function getByCourtAndDate(string $courtName, string $date): array
    {
        $sql = "
            SELECT 
                cp.*,
                c.case_number,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name
            FROM court_proceedings cp
            JOIN cases c ON cp.case_id = c.id
            JOIN suspects s ON cp.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            WHERE cp.court_name = ? AND DATE(cp.court_date) = ?
            ORDER BY cp.court_date ASC
        ";
        
        return $this->query($sql, [$courtName, $date]);
    }
}
