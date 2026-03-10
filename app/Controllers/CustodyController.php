<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Config\Database;
use PDO;

class CustodyController extends BaseController
{
    private CaseModel $caseModel;
    private PDO $db;
    
    public function __construct()
    {
        $this->caseModel = new CaseModel();
        $this->db = Database::getConnection();
    }
    
    /**
     * Display custody records
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $stationId = $_GET['station'] ?? (auth()['current_station_id'] ?? null);
        
        $records = $this->getCustodyRecords($status, $stationId);
        
        return $this->view('custody/index', [
            'title' => 'Custody Records',
            'records' => $records,
            'selected_status' => $status,
            'selected_station' => $stationId
        ]);
    }
    
    /**
     * Show custody record details
     */
    public function show(int $id): string
    {
        $record = $this->getCustodyRecord($id);
        
        if (!$record) {
            $this->setFlash('error', 'Custody record not found');
            $this->redirect('/custody');
        }
        
        // TODO: Implement custody logs when custody_logs table is created
        // $logs = $this->getCustodyLogs($id);
        $logs = [];
        
        return $this->view('custody/view', [
            'title' => 'Custody Record Details',
            'record' => $record,
            'logs' => $logs
        ]);
    }
    
    /**
     * Add suspect to custody
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $data = [
            'suspect_id' => $_POST['suspect_id'] ?? null,
            'case_id' => $_POST['case_id'] ?? null,
            'custody_start' => $_POST['custody_start'] ?? date('Y-m-d H:i:s'),
            'custody_location' => $_POST['custody_location'] ?? '',
            'personal_items' => $_POST['personal_items'] ?? '',
            'reason' => $_POST['reason'] ?? '',
            'custody_status' => 'In Custody'
        ];
        
        $errors = $this->validate($data, [
            'suspect_id' => 'required',
            'case_id' => 'required',
            'custody_location' => 'required',
            'reason' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO custody_records (
                    suspect_id, case_id, custody_start,
                    custody_location, reason, custody_status
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['suspect_id'],
                $data['case_id'],
                $data['custody_start'],
                $data['custody_location'],
                $data['reason'],
                $data['custody_status']
            ]);
            
            $custodyId = (int)$this->db->lastInsertId();
            
            $this->db->commit();
            
            logger("Custody record created: ID {$custodyId} for suspect {$data['suspect_id']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Suspect placed in custody successfully',
                'custody_id' => $custodyId
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            logger("Error creating custody record: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to place suspect in custody: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Release suspect from custody
     */
    public function release(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $releaseType = $_POST['release_type'] ?? 'Released';
        $releaseReason = $_POST['release_reason'] ?? '';
        $releaseDate = $_POST['release_date'] ?? date('Y-m-d H:i:s');
        
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                UPDATE custody_records
                SET status = ?, detention_end = ?, release_reason = ?, released_by = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$releaseType, $releaseDate, $releaseReason, auth_id(), $id]);
            
            // Log the release
            $this->addCustodyLog($id, 'Released', $releaseReason, auth_id());
            
            $this->db->commit();
            
            $this->json(['success' => true, 'message' => 'Suspect released from custody']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Add custody log entry
     */
    public function addLog(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $action = $_POST['action'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        try {
            $this->addCustodyLog($id, $action, $notes, auth_id());
            $this->json(['success' => true, 'message' => 'Log entry added successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get custody records with filters
     */
    private function getCustodyRecords(?string $status, ?int $stationId): array
    {
        $sql = "
            SELECT 
                cr.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                c.case_number,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as arresting_officer_name,
                pr.rank_name as officer_rank
            FROM custody_records cr
            JOIN suspects s ON cr.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            JOIN cases c ON cr.case_id = c.id
            LEFT JOIN arrests a ON cr.case_id = a.case_id AND cr.suspect_id = a.suspect_id
            LEFT JOIN officers o ON a.arresting_officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND cr.status = ?";
            $params[] = $status;
        }
        
        if ($stationId) {
            $sql .= " AND c.station_id = ?";
            $params[] = $stationId;
        }
        
        $sql .= " ORDER BY cr.custody_start DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get single custody record
     */
    private function getCustodyRecord(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cr.*,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) as suspect_name,
                p.ghana_card_number,
                p.date_of_birth,
                c.case_number,
                c.description as case_description,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as arresting_officer_name,
                pr.rank_name as officer_rank,
                o.service_number,
                CONCAT_WS(' ', rel.first_name, rel.middle_name, rel.last_name) as released_by_name
            FROM custody_records cr
            JOIN suspects s ON cr.suspect_id = s.id
            JOIN persons p ON s.person_id = p.id
            JOIN cases c ON cr.case_id = c.id
            LEFT JOIN arrests a ON cr.case_id = a.case_id AND cr.suspect_id = a.suspect_id
            LEFT JOIN officers o ON a.arresting_officer_id = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN users rel ON cr.released_by = rel.id
            WHERE cr.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get custody logs
     */
    private function getCustodyLogs(int $custodyId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cl.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as logged_by_name
            FROM custody_logs cl
            LEFT JOIN users u ON cl.logged_by = u.id
            WHERE cl.custody_id = ?
            ORDER BY cl.log_time DESC
        ");
        $stmt->execute([$custodyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add custody log entry
     */
    private function addCustodyLog(int $custodyId, string $action, string $notes, int $userId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO custody_logs (custody_id, action_taken, notes, logged_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$custodyId, $action, $notes, $userId]);
        return (int)$this->db->lastInsertId();
    }
}
