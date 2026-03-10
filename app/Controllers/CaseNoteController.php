<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class CaseNoteController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Add note to case
     */
    public function addNote(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/cases/' . $caseId);
        }
        
        $noteText = $_POST['note_text'] ?? '';
        $noteType = $_POST['note_type'] ?? 'General';
        $isPrivate = isset($_POST['is_private']) ? 1 : 0;
        
        if (empty($noteText)) {
            $this->setFlash('error', 'Note text is required');
            $this->redirect('/cases/' . $caseId);
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO case_notes (case_id, note_type, note_text, is_private, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$caseId, $noteType, $noteText, $isPrivate, auth_id()]);
            
            logger("Note added to case {$caseId} by user " . auth_id());
            
            $this->setFlash('success', 'Note added successfully');
        } catch (\Exception $e) {
            logger("Failed to add note: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to add note');
        }
        
        $this->redirect('/cases/' . $caseId);
    }
    
    /**
     * Get case notes
     */
    public function getCaseNotes(int $caseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cn.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as created_by_name,
                u.role as created_by_role
            FROM case_notes cn
            LEFT JOIN users u ON cn.created_by = u.id
            WHERE cn.case_id = ?
            ORDER BY cn.created_at DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update note
     */
    public function updateNote(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $noteText = $_POST['note_text'] ?? '';
        
        if (empty($noteText)) {
            $this->json(['success' => false, 'message' => 'Note text is required'], 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE case_notes 
                SET note_text = ?, updated_at = NOW()
                WHERE id = ? AND created_by = ?
            ");
            
            $stmt->execute([$noteText, $id, auth_id()]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['success' => true, 'message' => 'Note updated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Note not found or unauthorized'], 403);
            }
        } catch (\Exception $e) {
            logger("Failed to update note: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Update failed'], 500);
        }
    }
    
    /**
     * Delete note
     */
    public function deleteNote(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                DELETE FROM case_notes 
                WHERE id = ? AND created_by = ?
            ");
            
            $stmt->execute([$id, auth_id()]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['success' => true, 'message' => 'Note deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Note not found or unauthorized'], 403);
            }
        } catch (\Exception $e) {
            logger("Failed to delete note: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Deletion failed'], 500);
        }
    }
}
