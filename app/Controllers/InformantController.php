<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class InformantController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * List informants (handler only sees their own)
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $handlerId = auth_id(); // Only show informants handled by current officer
        
        $informants = $this->getInformants($handlerId, $status);
        
        return $this->view('informants/index', [
            'title' => 'Informant Management',
            'informants' => $informants,
            'selected_status' => $status
        ]);
    }
    
    /**
     * Register new informant
     */
    public function create(): string
    {
        return $this->view('informants/create', [
            'title' => 'Register Informant'
        ]);
    }
    
    /**
     * Store informant
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/informants/create');
        }
        
        $informantCode = $this->generateInformantCode();
        
        $data = [
            'informant_code' => $informantCode,
            'handler_officer_id' => auth_id(),
            'contact_method' => $_POST['contact_method'] ?? 'Phone',
            'reliability_rating' => $_POST['reliability_rating'] ?? 'Unproven',
            'recruitment_date' => $_POST['recruitment_date'] ?? date('Y-m-d'),
            'area_of_operation' => $_POST['area_of_operation'] ?? '',
            'specialization' => $_POST['specialization'] ?? null,
            'status' => 'Active',
            'notes' => $_POST['notes'] ?? null
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO informants (
                    informant_code, handler_officer_id, contact_method,
                    reliability_rating, recruitment_date, area_of_operation,
                    specialization, status, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['informant_code'],
                $data['handler_officer_id'],
                $data['contact_method'],
                $data['reliability_rating'],
                $data['recruitment_date'],
                $data['area_of_operation'],
                $data['specialization'],
                $data['status'],
                $data['notes']
            ]);
            
            $informantId = (int)$this->db->lastInsertId();
            
            $this->setFlash('success', 'Informant registered successfully. Code: ' . $informantCode);
            $this->redirect('/informants/' . $informantId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to register informant: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/informants/create');
        }
    }
    
    /**
     * View informant details (handler only)
     */
    public function show(int $id): string
    {
        $informant = $this->getInformantDetails($id);
        
        if (!$informant) {
            $this->setFlash('error', 'Informant not found');
            $this->redirect('/informants');
        }
        
        // Security: Only handler can view
        if ($informant['handler_officer_id'] != auth_id()) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/informants');
        }
        
        $intelligence = $this->getInformantIntelligence($id);
        
        return $this->view('informants/view', [
            'title' => 'Informant Details',
            'informant' => $informant,
            'intelligence' => $intelligence
        ]);
    }
    
    /**
     * Add intelligence from informant
     */
    public function addIntelligence(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        // Verify handler
        $informant = $this->getInformantDetails($id);
        if (!$informant || $informant['handler_officer_id'] != auth_id()) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        $data = [
            'informant_id' => $id,
            'intelligence_date' => $_POST['intelligence_date'] ?? date('Y-m-d H:i:s'),
            'intelligence_type' => $_POST['intelligence_type'] ?? 'General',
            'intelligence_summary' => $_POST['intelligence_summary'] ?? '',
            'reliability_assessment' => $_POST['reliability_assessment'] ?? 'Unverified',
            'action_taken' => $_POST['action_taken'] ?? null,
            'recorded_by' => auth_id()
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO informant_intelligence (
                    informant_id, intelligence_date, intelligence_type,
                    intelligence_summary, reliability_assessment, action_taken, recorded_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['informant_id'],
                $data['intelligence_date'],
                $data['intelligence_type'],
                $data['intelligence_summary'],
                $data['reliability_assessment'],
                $data['action_taken'],
                $data['recorded_by']
            ]);
            
            $this->json(['success' => true, 'message' => 'Intelligence recorded successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update informant status
     */
    public function updateStatus(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        // Verify handler
        $informant = $this->getInformantDetails($id);
        if (!$informant || $informant['handler_officer_id'] != auth_id()) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        try {
            $stmt = $this->db->prepare("
                UPDATE informants
                SET status = ?, notes = CONCAT(COALESCE(notes, ''), '\n\n', ?)
                WHERE id = ?
            ");
            
            $stmt->execute([$status, date('Y-m-d H:i') . ': ' . $notes, $id]);
            
            $this->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function getInformants(int $handlerId, ?string $status): array
    {
        $sql = "
            SELECT i.*, CONCAT_WS(' ', o.first_name, o.last_name) as handler_name,
                   (SELECT COUNT(*) FROM informant_intelligence WHERE informant_id = i.id) as intel_count
            FROM informants i
            JOIN officers o ON i.handler_officer_id = o.id
            WHERE i.handler_officer_id = ?
        ";
        
        $params = [$handlerId];
        
        if ($status) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY i.registration_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getInformantDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT i.*, CONCAT_WS(' ', o.first_name, o.last_name) as handler_name,
                   o.rank as handler_rank
            FROM informants i
            JOIN officers o ON i.handler_officer_id = o.id
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    private function getInformantIntelligence(int $informantId): array
    {
        $stmt = $this->db->prepare("
            SELECT ii.*, CONCAT_WS(' ', u.first_name, u.last_name) as recorded_by_name
            FROM informant_intelligence ii
            LEFT JOIN users u ON ii.recorded_by = u.id
            WHERE ii.informant_id = ?
            ORDER BY ii.intelligence_date DESC
        ");
        $stmt->execute([$informantId]);
        return $stmt->fetchAll();
    }
    
    private function generateInformantCode(): string
    {
        $prefix = 'INF';
        $date = date('Ymd');
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM informants 
            WHERE informant_code LIKE ?
        ");
        $stmt->execute(["{$prefix}-{$date}-%"]);
        $count = $stmt->fetch()['count'] + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }
}
