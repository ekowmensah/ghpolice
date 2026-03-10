<?php

namespace App\Models;

class OfficerPromotion extends BaseModel
{
    protected string $table = 'officer_promotions';
    
    public function getByOfficerId(int $officerId): array
    {
        $sql = "
            SELECT 
                op.*,
                pr1.rank_name as from_rank_name,
                pr1.rank_level as from_rank_level,
                pr2.rank_name as to_rank_name,
                pr2.rank_level as to_rank_level,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as approved_by_name
            FROM officer_promotions op
            JOIN police_ranks pr1 ON op.from_rank_id = pr1.id
            JOIN police_ranks pr2 ON op.to_rank_id = pr2.id
            LEFT JOIN users u ON op.approved_by = u.id
            WHERE op.officer_id = ?
            ORDER BY op.promotion_date DESC
        ";
        
        return $this->query($sql, [$officerId]);
    }
    
    public function promoteOfficer(int $officerId, array $promotionData): bool
    {
        $this->db->beginTransaction();
        
        try {
            $this->create([
                'officer_id' => $officerId,
                'from_rank_id' => $promotionData['from_rank_id'],
                'to_rank_id' => $promotionData['to_rank_id'],
                'promotion_date' => $promotionData['promotion_date'],
                'promotion_order_number' => $promotionData['promotion_order_number'] ?? null,
                'effective_date' => $promotionData['effective_date'],
                'remarks' => $promotionData['remarks'] ?? null,
                'approved_by' => $promotionData['approved_by']
            ]);
            
            $this->execute(
                "UPDATE officers SET rank_id = ? WHERE id = ?",
                [$promotionData['to_rank_id'], $officerId]
            );
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
