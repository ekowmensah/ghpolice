<?php

namespace App\Models;

class PersonBiometric extends BaseModel
{
    protected string $table = 'person_biometrics';
    
    /**
     * Create new biometric record
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (
            person_id, 
            biometric_type, 
            biometric_data, 
            file_path, 
            capture_device, 
            capture_quality, 
            captured_by, 
            verification_status, 
            remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['person_id'],
            $data['biometric_type'],
            $data['biometric_data'] ?? null,
            $data['file_path'] ?? null,
            $data['capture_device'] ?? null,
            $data['capture_quality'],
            $data['captured_by'],
            $data['verification_status'] ?? 'Pending',
            $data['remarks'] ?? null
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Find biometric by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get all biometrics for a person
     */
    public function getByPersonId(int $personId): array
    {
        $sql = "SELECT pb.*, 
                       CONCAT_WS(' ', u.first_name, u.last_name) as captured_by_name
                FROM {$this->table} pb
                LEFT JOIN users u ON pb.captured_by = u.id
                WHERE pb.person_id = ?
                ORDER BY pb.captured_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$personId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get biometrics by type
     */
    public function getByType(int $personId, string $type): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE person_id = ? AND biometric_type = ?
                ORDER BY captured_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$personId, $type]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Update verification status
     */
    public function updateVerificationStatus(int $id, string $status): bool
    {
        $sql = "UPDATE {$this->table} 
                SET verification_status = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Delete biometric record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Get fingerprint completion percentage for person
     */
    public function getFingerprintCompletion(int $personId): int
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE person_id = ? AND biometric_type = 'Fingerprint'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$personId]);
        $result = $stmt->fetch();
        
        $count = $result['count'] ?? 0;
        return min(100, ($count / 10) * 100); // 10 fingers = 100%
    }
    
    /**
     * Check if person has any biometrics
     */
    public function hasBiometrics(int $personId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE person_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$personId]);
        $result = $stmt->fetch();
        
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Search persons by biometric similarity (placeholder for future ML integration)
     */
    public function searchSimilar(string $biometricTemplate): array
    {
        // TODO: Implement biometric matching algorithm
        // This would integrate with fingerprint matching libraries like NBIS, SourceAFIS, etc.
        return [];
    }
}
