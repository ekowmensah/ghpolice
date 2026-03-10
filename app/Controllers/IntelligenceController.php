<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class IntelligenceController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Intelligence dashboard
     */
    public function index(): string
    {
        $stats = $this->getIntelligenceStats();
        $recentReports = $this->getRecentReports(10);
        $activeSurveillance = $this->getActiveSurveillance();
        $recentBulletins = $this->getRecentBulletins(5);
        
        return $this->view('intelligence/index', [
            'title' => 'Intelligence Dashboard',
            'stats' => $stats,
            'recent_reports' => $recentReports,
            'active_surveillance' => $activeSurveillance,
            'recent_bulletins' => $recentBulletins
        ]);
    }
    
    /**
     * List intelligence reports
     */
    public function reports(): string
    {
        $type = $_GET['type'] ?? null;
        $classification = $_GET['classification'] ?? null;
        
        $reports = $this->getReports($type, $classification);
        
        return $this->view('intelligence/reports', [
            'title' => 'Intelligence Reports',
            'reports' => $reports,
            'selected_type' => $type,
            'selected_classification' => $classification
        ]);
    }
    
    /**
     * Create intelligence report
     */
    public function createReport(): string
    {
        return $this->view('intelligence/create_report', [
            'title' => 'Create Intelligence Report'
        ]);
    }
    
    /**
     * Store intelligence report
     */
    public function storeReport(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/intelligence/reports/create');
        }
        
        $reportNumber = $this->generateReportNumber();
        
        $data = [
            'report_number' => $reportNumber,
            'report_type' => $_POST['report_type'] ?? 'Operational',
            'title' => $_POST['title'] ?? '',
            'summary' => $_POST['summary'] ?? '',
            'detailed_analysis' => $_POST['detailed_analysis'] ?? '',
            'sources' => $_POST['sources'] ?? '',
            'recommendations' => $_POST['recommendations'] ?? '',
            'classification' => $_POST['classification'] ?? 'Confidential',
            'report_date' => $_POST['report_date'] ?? date('Y-m-d'),
            'prepared_by' => auth_id(),
            'status' => 'Draft'
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO intelligence_reports (
                    report_number, report_type, title, summary, detailed_analysis,
                    sources, recommendations, classification, report_date, prepared_by, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['report_number'],
                $data['report_type'],
                $data['title'],
                $data['summary'],
                $data['detailed_analysis'],
                $data['sources'],
                $data['recommendations'],
                $data['classification'],
                $data['report_date'],
                $data['prepared_by'],
                $data['status']
            ]);
            
            $reportId = (int)$this->db->lastInsertId();
            
            $this->setFlash('success', 'Intelligence report created successfully');
            $this->redirect('/intelligence/reports/' . $reportId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create report: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/intelligence/reports/create');
        }
    }
    
    /**
     * View intelligence report
     */
    public function viewReport(int $id): string
    {
        $report = $this->getReportDetails($id);
        
        if (!$report) {
            $this->setFlash('error', 'Report not found');
            $this->redirect('/intelligence/reports');
        }
        
        return $this->view('intelligence/view_report', [
            'title' => 'Intelligence Report',
            'report' => $report
        ]);
    }
    
    /**
     * List surveillance operations
     */
    public function surveillance(): string
    {
        $status = $_GET['status'] ?? null;
        
        $operations = $this->getSurveillanceOperations($status);
        
        return $this->view('intelligence/surveillance', [
            'title' => 'Surveillance Operations',
            'operations' => $operations,
            'selected_status' => $status
        ]);
    }
    
    /**
     * Create surveillance operation
     */
    public function createSurveillance(): string
    {
        $officers = $this->getAvailableOfficers();
        
        return $this->view('intelligence/create_surveillance', [
            'title' => 'Create Surveillance Operation',
            'officers' => $officers
        ]);
    }
    
    /**
     * Store surveillance operation
     */
    public function storeSurveillance(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/intelligence/surveillance/create');
        }
        
        $operationCode = $this->generateOperationCode();
        
        $data = [
            'operation_code' => $operationCode,
            'operation_name' => $_POST['operation_name'] ?? '',
            'operation_type' => $_POST['operation_type'] ?? 'Surveillance',
            'target_description' => $_POST['target_description'] ?? '',
            'location' => $_POST['location'] ?? '',
            'start_date' => $_POST['start_date'] ?? date('Y-m-d H:i:s'),
            'planned_end_date' => $_POST['planned_end_date'] ?? null,
            'lead_officer_id' => $_POST['lead_officer_id'] ?? auth_id(),
            'classification' => $_POST['classification'] ?? 'Confidential',
            'status' => 'Active'
        ];
        
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO surveillance_operations (
                    operation_code, operation_name, operation_type, target_description,
                    location, start_date, planned_end_date, lead_officer_id,
                    classification, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['operation_code'],
                $data['operation_name'],
                $data['operation_type'],
                $data['target_description'],
                $data['location'],
                $data['start_date'],
                $data['planned_end_date'],
                $data['lead_officer_id'],
                $data['classification'],
                $data['status']
            ]);
            
            $operationId = (int)$this->db->lastInsertId();
            
            // Add officers to operation
            if (!empty($_POST['officer_ids'])) {
                $this->addSurveillanceOfficers($operationId, $_POST['officer_ids']);
            }
            
            $this->db->commit();
            
            $this->setFlash('success', 'Surveillance operation created successfully');
            $this->redirect('/intelligence/surveillance/' . $operationId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', 'Failed to create operation: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/intelligence/surveillance/create');
        }
    }
    
    /**
     * View surveillance operation
     */
    public function viewSurveillance(int $id): string
    {
        $operation = $this->getSurveillanceDetails($id);
        
        if (!$operation) {
            $this->setFlash('error', 'Operation not found');
            $this->redirect('/intelligence/surveillance');
        }
        
        $officers = $this->getSurveillanceOfficers($id);
        
        return $this->view('intelligence/view_surveillance', [
            'title' => 'Surveillance Operation',
            'operation' => $operation,
            'officers' => $officers
        ]);
    }
    
    /**
     * Intelligence bulletins
     */
    public function bulletins(): string
    {
        $type = $_GET['type'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $bulletins = $this->getBulletins($type, $status);
        
        return $this->view('intelligence/bulletins', [
            'title' => 'Intelligence Bulletins',
            'bulletins' => $bulletins,
            'selected_type' => $type,
            'selected_status' => $status
        ]);
    }
    
    /**
     * Create bulletin
     */
    public function createBulletin(): string
    {
        return $this->view('intelligence/create_bulletin', [
            'title' => 'Create Intelligence Bulletin'
        ]);
    }
    
    /**
     * Store bulletin
     */
    public function storeBulletin(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/intelligence/bulletins/create');
        }
        
        $bulletinNumber = $this->generateBulletinNumber();
        
        $data = [
            'bulletin_number' => $bulletinNumber,
            'bulletin_type' => $_POST['bulletin_type'] ?? 'Intelligence Update',
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'priority' => $_POST['priority'] ?? 'Medium',
            'valid_from' => $_POST['valid_from'] ?? date('Y-m-d'),
            'valid_until' => $_POST['valid_until'] ?? null,
            'issued_by' => auth_id(),
            'status' => 'Active'
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO intelligence_bulletins (
                    bulletin_number, bulletin_type, title, content, priority,
                    valid_from, valid_until, issued_by, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['bulletin_number'],
                $data['bulletin_type'],
                $data['title'],
                $data['content'],
                $data['priority'],
                $data['valid_from'],
                $data['valid_until'],
                $data['issued_by'],
                $data['status']
            ]);
            
            $bulletinId = (int)$this->db->lastInsertId();
            
            $this->setFlash('success', 'Bulletin created successfully');
            $this->redirect('/intelligence/bulletins/' . $bulletinId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create bulletin: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/intelligence/bulletins/create');
        }
    }
    
    /**
     * Public tips
     */
    public function tips(): string
    {
        $status = $_GET['status'] ?? null;
        
        $tips = $this->getPublicTips($status);
        
        return $this->view('intelligence/tips', [
            'title' => 'Public Intelligence Tips',
            'tips' => $tips,
            'selected_status' => $status
        ]);
    }
    
    // Helper methods
    
    private function getIntelligenceStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                (SELECT COUNT(*) FROM intelligence_reports WHERE status = 'Approved') as active_reports,
                (SELECT COUNT(*) FROM surveillance_operations WHERE operation_status = 'Active') as active_surveillance,
                (SELECT COUNT(*) FROM intelligence_bulletins WHERE status = 'Active') as active_bulletins,
                (SELECT COUNT(*) FROM public_intelligence_tips WHERE verification_status = 'Pending') as pending_tips
        ");
        return $stmt->fetch() ?: [];
    }
    
    private function getRecentReports(int $limit): array
    {
        $stmt = $this->db->prepare("
            SELECT ir.*, CONCAT_WS(' ', u.first_name, u.last_name) as created_by_name
            FROM intelligence_reports ir
            LEFT JOIN users u ON ir.created_by = u.id
            ORDER BY ir.report_date DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    private function getActiveSurveillance(): array
    {
        $stmt = $this->db->query("
            SELECT so.*, CONCAT_WS(' ', o.first_name, o.last_name) as commander_name
            FROM surveillance_operations so
            LEFT JOIN officers o ON so.operation_commander_id = o.id
            WHERE so.operation_status = 'Active'
            ORDER BY so.start_date DESC
        ");
        return $stmt->fetchAll();
    }
    
    private function getRecentBulletins(int $limit): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM intelligence_bulletins
            WHERE status = 'Active'
            ORDER BY valid_from DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    private function getReports(?string $type, ?string $classification): array
    {
        $sql = "SELECT ir.*, CONCAT_WS(' ', u.first_name, u.last_name) as created_by_name
                FROM intelligence_reports ir
                LEFT JOIN users u ON ir.created_by = u.id
                WHERE 1=1";
        $params = [];
        
        if ($type) {
            $sql .= " AND ir.report_type = ?";
            $params[] = $type;
        }
        
        if ($classification) {
            $sql .= " AND ir.classification_level = ?";
            $params[] = $classification;
        }
        
        $sql .= " ORDER BY ir.report_date DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getReportDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT ir.*, CONCAT_WS(' ', u.first_name, u.last_name) as created_by_name
            FROM intelligence_reports ir
            LEFT JOIN users u ON ir.created_by = u.id
            WHERE ir.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    private function getSurveillanceOperations(?string $status): array
    {
        $sql = "SELECT so.*, CONCAT_WS(' ', o.first_name, o.last_name) as commander_name
                FROM surveillance_operations so
                LEFT JOIN officers o ON so.operation_commander_id = o.id
                WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND so.operation_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY so.start_date DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getSurveillanceDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT so.*, CONCAT_WS(' ', o.first_name, o.last_name) as commander_name,
                   o.service_number
            FROM surveillance_operations so
            LEFT JOIN officers o ON so.operation_commander_id = o.id
            WHERE so.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    private function getSurveillanceOfficers(int $operationId): array
    {
        $stmt = $this->db->prepare("
            SELECT so.*, CONCAT_WS(' ', o.first_name, o.last_name) as officer_name,
                   o.rank, o.service_number
            FROM surveillance_officers so
            JOIN officers o ON so.officer_id = o.id
            WHERE so.surveillance_id = ?
        ");
        $stmt->execute([$operationId]);
        return $stmt->fetchAll();
    }
    
    private function addSurveillanceOfficers(int $operationId, array $officerIds): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO surveillance_officers (surveillance_id, officer_id) VALUES (?, ?)
        ");
        
        foreach ($officerIds as $officerId) {
            $stmt->execute([$operationId, $officerId]);
        }
    }
    
    private function getBulletins(?string $type, ?string $status): array
    {
        $sql = "SELECT ib.*, CONCAT_WS(' ', u.first_name, u.last_name) as issued_by_name
                FROM intelligence_bulletins ib
                LEFT JOIN users u ON ib.issued_by = u.id
                WHERE 1=1";
        $params = [];
        
        if ($type) {
            $sql .= " AND ib.bulletin_type = ?";
            $params[] = $type;
        }
        
        if ($status) {
            $sql .= " AND ib.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ib.valid_from DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getPublicTips(?string $status): array
    {
        $sql = "SELECT * FROM public_intelligence_tips WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY received_date DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getAvailableOfficers(): array
    {
        $stmt = $this->db->query("
            SELECT o.id, CONCAT_WS(' ', pr.rank_name, o.first_name, o.last_name) as full_name, o.service_number
            FROM officers o
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY pr.rank_level DESC, o.last_name
        ");
        return $stmt->fetchAll();
    }
    
    private function generateReportNumber(): string
    {
        $date = date('Ymd');
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM intelligence_reports 
            WHERE report_number LIKE ?
        ");
        $stmt->execute(["INTEL-{$date}-%"]);
        $count = $stmt->fetch()['count'] + 1;
        return sprintf('INTEL-%s-%03d', $date, $count);
    }
    
    private function generateOperationCode(): string
    {
        $date = date('Ymd');
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM surveillance_operations 
            WHERE operation_code LIKE ?
        ");
        $stmt->execute(["OP-{$date}-%"]);
        $count = $stmt->fetch()['count'] + 1;
        return sprintf('OP-%s-%03d', $date, $count);
    }
    
    private function generateBulletinNumber(): string
    {
        $date = date('Ymd');
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM intelligence_bulletins 
            WHERE bulletin_number LIKE ?
        ");
        $stmt->execute(["BULL-{$date}-%"]);
        $count = $stmt->fetch()['count'] + 1;
        return sprintf('BULL-%s-%03d', $date, $count);
    }
}
