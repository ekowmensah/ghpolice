<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class WarrantController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Display all warrants
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        
        $warrants = $this->getWarrants($status, $type);
        
        return $this->view('warrants/index', [
            'title' => 'Warrant Management',
            'warrants' => $warrants,
            'selected_status' => $status,
            'selected_type' => $type
        ]);
    }
    
    /**
     * Show warrant details
     */
    public function show(int $id): string
    {
        $warrant = $this->getWarrantDetails($id);
        
        if (!$warrant) {
            $this->setFlash('error', 'Warrant not found');
            $this->redirect('/warrants');
        }
        
        $executionLogs = $this->getExecutionLogs($id);
        
        return $this->view('warrants/view', [
            'title' => 'Warrant Details',
            'warrant' => $warrant,
            'execution_logs' => $executionLogs
        ]);
    }
    
    /**
     * Execute warrant
     */
    public function execute(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $executionDate = $_POST['execution_date'] ?? date('Y-m-d H:i:s');
        $executedBy = $_POST['executed_by'] ?? auth_id();
        $executionNotes = $_POST['execution_notes'] ?? '';
        $executionLocation = $_POST['execution_location'] ?? '';
        
        try {
            $this->db->beginTransaction();
            
            // Update warrant status
            $stmt = $this->db->prepare("
                UPDATE warrants
                SET status = 'Executed', executed_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$executionDate, $id]);
            
            // Log execution
            $stmt = $this->db->prepare("
                INSERT INTO warrant_execution_logs (
                    warrant_id, executed_by, execution_date, execution_location, notes
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id, $executedBy, $executionDate, $executionLocation, $executionNotes]);
            
            $this->db->commit();
            
            $this->json(['success' => true, 'message' => 'Warrant executed successfully']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Cancel warrant
     */
    public function cancel(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $reason = $_POST['reason'] ?? '';
        
        try {
            $stmt = $this->db->prepare("
                UPDATE warrants
                SET status = 'Cancelled', cancellation_reason = ?, cancelled_date = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reason, $id]);
            
            $this->json(['success' => true, 'message' => 'Warrant cancelled successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get active warrants
     */
    public function active(): string
    {
        $warrants = $this->getWarrants('Active', null);
        
        return $this->view('warrants/active', [
            'title' => 'Active Warrants',
            'warrants' => $warrants
        ]);
    }
    
    /**
     * Get warrants with filters
     */
    private function getWarrants(?string $status, ?string $type): array
    {
        $sql = "
            SELECT 
                w.*,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.date_of_birth
            FROM warrants w
            JOIN cases c ON w.case_id = c.id
            LEFT JOIN suspects s ON w.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND w.status = ?";
            $params[] = $status;
        }
        
        if ($type) {
            $sql .= " AND w.warrant_type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY w.issue_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get warrant details
     */
    private function getWarrantDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                w.*,
                c.case_number,
                c.description as case_description,
                c.status as case_status,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.date_of_birth,
                p.contact_number,
                p.address
            FROM warrants w
            JOIN cases c ON w.case_id = c.id
            LEFT JOIN suspects s ON w.suspect_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE w.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get execution logs
     */
    private function getExecutionLogs(int $warrantId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                wel.*,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as executed_by_name,
                o.rank,
                o.service_number
            FROM warrant_execution_logs wel
            JOIN officers o ON wel.executed_by = o.id
            WHERE wel.warrant_id = ?
            ORDER BY wel.execution_date DESC
        ");
        $stmt->execute([$warrantId]);
        return $stmt->fetchAll();
    }
}
