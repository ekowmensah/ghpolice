<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class PublicTipController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Public tip submission form (no auth required)
     */
    public function create(): string
    {
        return $this->view('public/submit_tip', [
            'title' => 'Submit Anonymous Tip',
            'layout' => 'public' // Use public layout without auth
        ]);
    }
    
    /**
     * Store public tip (no auth required)
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/submit-tip');
        }
        
        $tipNumber = $this->generateTipNumber();
        
        $data = [
            'tip_number' => $tipNumber,
            'tip_source' => $_POST['tip_source'] ?? 'Web Form',
            'tip_content' => $_POST['tip_content'] ?? '',
            'tip_category' => $_POST['tip_category'] ?? 'General',
            'location' => $_POST['location'] ?? null,
            'contact_information' => $_POST['contact_information'] ?? null,
            'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0,
            'received_date' => date('Y-m-d H:i:s'),
            'status' => 'Pending'
        ];
        
        $errors = $this->validate($data, [
            'tip_content' => 'required|min:20'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/submit-tip');
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO public_intelligence_tips (
                    tip_number, tip_source, tip_content, tip_category,
                    location, contact_information, is_anonymous,
                    received_date, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['tip_number'],
                $data['tip_source'],
                $data['tip_content'],
                $data['tip_category'],
                $data['location'],
                $data['contact_information'],
                $data['is_anonymous'],
                $data['received_date'],
                $data['status']
            ]);
            
            $this->setFlash('success', 'Thank you! Your tip has been submitted. Reference: ' . $tipNumber);
            $this->redirect('/submit-tip');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to submit tip. Please try again.');
            $_SESSION['old'] = $data;
            $this->redirect('/submit-tip');
        }
    }
    
    /**
     * Admin view of all tips (auth required)
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $category = $_GET['category'] ?? null;
        
        $tips = $this->getTips($status, $category);
        
        return $this->view('intelligence/tips', [
            'title' => 'Public Intelligence Tips',
            'tips' => $tips,
            'selected_status' => $status,
            'selected_category' => $category
        ]);
    }
    
    /**
     * View tip details (auth required)
     */
    public function show(int $id): string
    {
        $tip = $this->getTipDetails($id);
        
        if (!$tip) {
            $this->setFlash('error', 'Tip not found');
            $this->redirect('/intelligence/tips');
        }
        
        return $this->view('intelligence/view_tip', [
            'title' => 'Tip Details',
            'tip' => $tip
        ]);
    }
    
    /**
     * Assign tip to officer (auth required)
     */
    public function assign(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $officerId = $_POST['officer_id'] ?? null;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE public_intelligence_tips
                SET assigned_to = ?, status = 'Under Review'
                WHERE id = ?
            ");
            
            $stmt->execute([$officerId, $id]);
            
            $this->json(['success' => true, 'message' => 'Tip assigned successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update tip status (auth required)
     */
    public function updateStatus(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        try {
            $stmt = $this->db->prepare("
                UPDATE public_intelligence_tips
                SET status = ?, action_taken = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $notes, $id]);
            
            $this->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function getTips(?string $status, ?string $category): array
    {
        $sql = "
            SELECT pt.*, CONCAT_WS(' ', o.first_name, o.last_name) as assigned_to_name
            FROM public_intelligence_tips pt
            LEFT JOIN officers o ON pt.assigned_to = o.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND pt.status = ?";
            $params[] = $status;
        }
        
        if ($category) {
            $sql .= " AND pt.tip_category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY pt.received_at DESC LIMIT 200";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getTipDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT pt.*, CONCAT_WS(' ', o.first_name, o.last_name) as assigned_to_name
            FROM public_intelligence_tips pt
            LEFT JOIN officers o ON pt.assigned_to = o.id
            WHERE pt.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    private function generateTipNumber(): string
    {
        $date = date('Ymd');
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM public_intelligence_tips 
            WHERE tip_number LIKE ?
        ");
        $stmt->execute(["TIP-{$date}-%"]);
        $count = $stmt->fetch()['count'] + 1;
        return sprintf('TIP-%s-%05d', $date, $count);
    }
}
