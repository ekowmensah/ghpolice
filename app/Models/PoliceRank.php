<?php

namespace App\Models;

class PoliceRank extends BaseModel
{
    protected string $table = 'police_ranks';
    
    public function getByCategory(string $category): array
    {
        $sql = "
            SELECT * FROM police_ranks
            WHERE rank_category = ?
            ORDER BY rank_level DESC
        ";
        
        return $this->query($sql, [$category]);
    }
    
    public function getHigherRanks(int $currentRankLevel): array
    {
        $sql = "
            SELECT * FROM police_ranks
            WHERE rank_level > ?
            ORDER BY rank_level ASC
        ";
        
        return $this->query($sql, [$currentRankLevel]);
    }
    
    public function getNextRank(int $currentRankId): ?array
    {
        $currentRank = $this->find($currentRankId);
        
        if (!$currentRank) {
            return null;
        }
        
        $sql = "
            SELECT * FROM police_ranks
            WHERE rank_level > ?
            ORDER BY rank_level ASC
            LIMIT 1
        ";
        
        $result = $this->query($sql, [$currentRank['rank_level']]);
        return $result[0] ?? null;
    }
}
