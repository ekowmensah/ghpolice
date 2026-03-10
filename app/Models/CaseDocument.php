<?php

namespace App\Models;

class CaseDocument extends BaseModel
{
    protected string $table = 'case_documents';
    
    public function getByCaseId(int $caseId): array
    {
        $sql = "
            SELECT 
                cd.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as uploaded_by_name
            FROM case_documents cd
            LEFT JOIN users u ON cd.uploaded_by = u.id
            WHERE cd.case_id = ?
            ORDER BY cd.uploaded_at DESC
        ";
        
        return $this->query($sql, [$caseId]);
    }
    
    public function getByType(int $caseId, string $type): array
    {
        $sql = "
            SELECT * FROM case_documents
            WHERE case_id = ? AND document_type = ?
            ORDER BY uploaded_at DESC
        ";
        
        return $this->query($sql, [$caseId, $type]);
    }
}
