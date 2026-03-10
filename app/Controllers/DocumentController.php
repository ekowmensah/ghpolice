<?php

namespace App\Controllers;

use App\Helpers\FileHelper;
use App\Config\Database;
use PDO;

class DocumentController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Upload document for case
     */
    public function uploadCaseDocument(int $caseId): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        if (!isset($_FILES['document'])) {
            $this->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }
        
        $documentType = $_POST['document_type'] ?? 'General';
        $description = $_POST['description'] ?? '';
        
        try {
            $result = FileHelper::upload($_FILES['document'], 'cases/' . $caseId . '/');
            
            if (!$result['success']) {
                $this->json(['success' => false, 'message' => implode(', ', $result['errors'])], 400);
            }
            
            // Save to database
            $stmt = $this->db->prepare("
                INSERT INTO case_documents (case_id, document_type, file_name, file_path, file_size, description, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $caseId,
                $documentType,
                $result['filename'],
                $result['path'],
                $result['size'],
                $description,
                auth_id()
            ]);
            
            $documentId = (int)$this->db->lastInsertId();
            
            logger("Document uploaded for case {$caseId}: {$result['filename']}");
            
            $this->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document_id' => $documentId,
                'filename' => $result['filename']
            ]);
        } catch (\Exception $e) {
            logger("Document upload failed: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get case documents
     */
    public function getCaseDocuments(int $caseId): void
    {
        $stmt = $this->db->prepare("
            SELECT 
                cd.*,
                CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name) as uploaded_by_name
            FROM case_documents cd
            LEFT JOIN users u ON cd.uploaded_by = u.id
            WHERE cd.case_id = ?
            ORDER BY cd.uploaded_at DESC
        ");
        $stmt->execute([$caseId]);
        $documents = $stmt->fetchAll();
        
        $this->json(['success' => true, 'documents' => $documents]);
    }
    
    /**
     * Delete document
     */
    public function deleteDocument(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            // Get document info
            $stmt = $this->db->prepare("SELECT * FROM case_documents WHERE id = ?");
            $stmt->execute([$id]);
            $document = $stmt->fetch();
            
            if (!$document) {
                $this->json(['success' => false, 'message' => 'Document not found'], 404);
            }
            
            // Delete file
            FileHelper::delete($document['file_path']);
            
            // Delete from database
            $stmt = $this->db->prepare("DELETE FROM case_documents WHERE id = ?");
            $stmt->execute([$id]);
            
            logger("Document deleted: {$document['file_name']}");
            
            $this->json(['success' => true, 'message' => 'Document deleted successfully']);
        } catch (\Exception $e) {
            logger("Document deletion failed: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Deletion failed'], 500);
        }
    }
}
