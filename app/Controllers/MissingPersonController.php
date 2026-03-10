<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class MissingPersonController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * List missing persons
     */
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $ageGroup = $_GET['age_group'] ?? null;
        
        $persons = $this->getMissingPersons($status, $ageGroup);
        
        return $this->view('missing_persons/index', [
            'title' => 'Missing Persons Registry',
            'persons' => $persons,
            'selected_status' => $status,
            'selected_age_group' => $ageGroup
        ]);
    }
    
    /**
     * Create missing person report
     */
    public function create(): string
    {
        return $this->view('missing_persons/create', [
            'title' => 'Report Missing Person'
        ]);
    }
    
    /**
     * Store missing person report
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/missing-persons/create');
        }
        
        $reportNumber = $this->generateReportNumber();
        
        // Get user's station_id from session, fallback to district/division/region
        $stationId = $_SESSION['user']['station_id'] ?? null;
        
        // If no station_id, try to get a default station from user's district
        if (!$stationId && isset($_SESSION['user']['district_id'])) {
            $stmt = $this->db->prepare("SELECT id FROM stations WHERE district_id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user']['district_id']]);
            $station = $stmt->fetch();
            $stationId = $station['id'] ?? null;
        }
        
        // If still no station, use a system default or first available station
        if (!$stationId) {
            $stmt = $this->db->prepare("SELECT id FROM stations ORDER BY id ASC LIMIT 1");
            $stmt->execute();
            $station = $stmt->fetch();
            $stationId = $station['id'] ?? null;
        }
        
        if (!$stationId) {
            $this->setFlash('error', 'No police station found in the system. Please contact administrator to set up stations.');
            $this->redirect('/missing-persons/create');
            return;
        }
        
        $data = [
            'report_number' => $reportNumber,
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'height' => $_POST['height'] ?? null,
            'weight' => $_POST['weight'] ?? null,
            'complexion' => $_POST['complexion'] ?? null,
            'distinguishing_marks' => $_POST['distinguishing_marks'] ?? null,
            'last_seen_date' => $_POST['last_seen_date'] ?? null,
            'last_seen_location' => $_POST['last_seen_location'] ?? '',
            'last_seen_wearing' => $_POST['last_seen_wearing'] ?? null,
            'circumstances' => $_POST['circumstances'] ?? '',
            'reported_by_name' => $_POST['reported_by_name'] ?? '',
            'reported_by_contact' => $_POST['reported_by_contact'] ?? '',
            'relationship_to_missing' => $_POST['relationship_to_missing'] ?? null,
            'station_id' => $stationId,
            'case_id' => $_POST['case_id'] ?? null,
            'status' => 'Missing'
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO missing_persons (
                    report_number, first_name, middle_name, last_name, date_of_birth,
                    gender, height, weight, complexion, distinguishing_marks,
                    last_seen_date, last_seen_location, last_seen_wearing, circumstances,
                    reported_by_name, reported_by_contact, relationship_to_missing,
                    station_id, case_id, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['report_number'], $data['first_name'], $data['middle_name'],
                $data['last_name'], $data['date_of_birth'], $data['gender'],
                $data['height'], $data['weight'], $data['complexion'],
                $data['distinguishing_marks'], $data['last_seen_date'],
                $data['last_seen_location'], $data['last_seen_wearing'],
                $data['circumstances'], $data['reported_by_name'],
                $data['reported_by_contact'], $data['relationship_to_missing'],
                $data['station_id'], $data['case_id'], $data['status']
            ]);
            
            $personId = (int)$this->db->lastInsertId();
            
            $this->setFlash('success', 'Missing person report created successfully');
            $this->redirect('/missing-persons/' . $personId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create report: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/missing-persons/create');
        }
    }
    
    /**
     * View missing person details
     */
    public function show(int $id): string
    {
        $person = $this->getMissingPersonDetails($id);
        
        if (!$person) {
            $this->setFlash('error', 'Missing person record not found');
            $this->redirect('/missing-persons');
        }
        
        // Fetch open cases for linking
        $stmt = $this->db->prepare("SELECT id, case_number, description FROM cases WHERE status IN ('Open', 'Under Investigation') ORDER BY created_at DESC LIMIT 100");
        $stmt->execute();
        $openCases = $stmt->fetchAll();
        
        return $this->view('missing_persons/view', [
            'title' => 'Missing Person Details',
            'person' => $person,
            'openCases' => $openCases
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $person = $this->getMissingPersonDetails($id);
        
        if (!$person) {
            $this->setFlash('error', 'Missing person record not found');
            $this->redirect('/missing-persons');
        }
        
        return $this->view('missing_persons/edit', [
            'title' => 'Edit Missing Person Report',
            'person' => $person
        ]);
    }
    
    /**
     * Update missing person report
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/missing-persons/' . $id . '/edit');
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'height' => $_POST['height'] ?? null,
            'weight' => $_POST['weight'] ?? null,
            'complexion' => $_POST['complexion'] ?? null,
            'distinguishing_marks' => $_POST['distinguishing_marks'] ?? null,
            'last_seen_date' => $_POST['last_seen_date'] ?? null,
            'last_seen_location' => $_POST['last_seen_location'] ?? '',
            'last_seen_wearing' => $_POST['last_seen_wearing'] ?? null,
            'circumstances' => $_POST['circumstances'] ?? '',
            'reported_by_name' => $_POST['reported_by_name'] ?? '',
            'reported_by_contact' => $_POST['reported_by_contact'] ?? '',
            'relationship_to_missing' => $_POST['relationship_to_missing'] ?? null
        ];
        
        try {
            $stmt = $this->db->prepare("
                UPDATE missing_persons
                SET first_name = ?, middle_name = ?, last_name = ?, date_of_birth = ?,
                    gender = ?, height = ?, weight = ?, complexion = ?,
                    distinguishing_marks = ?, last_seen_date = ?, last_seen_location = ?,
                    last_seen_wearing = ?, circumstances = ?, reported_by_name = ?,
                    reported_by_contact = ?, relationship_to_missing = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['first_name'], $data['middle_name'], $data['last_name'],
                $data['date_of_birth'], $data['gender'], $data['height'],
                $data['weight'], $data['complexion'], $data['distinguishing_marks'],
                $data['last_seen_date'], $data['last_seen_location'],
                $data['last_seen_wearing'], $data['circumstances'],
                $data['reported_by_name'], $data['reported_by_contact'],
                $data['relationship_to_missing'], $id
            ]);
            
            $this->setFlash('success', 'Missing person report updated successfully');
            $this->redirect('/missing-persons/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update report: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/missing-persons/' . $id . '/edit');
        }
    }
    
    /**
     * Print missing person report
     */
    public function print(int $id): string
    {
        $person = $this->getMissingPersonDetails($id);
        
        if (!$person) {
            $this->setFlash('error', 'Missing person record not found');
            $this->redirect('/missing-persons');
        }
        
        return $this->view('missing_persons/print', [
            'title' => 'Print Missing Person Report',
            'person' => $person
        ]);
    }
    
    /**
     * Link missing person to case
     */
    public function linkCase(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/missing-persons/' . $id);
        }
        
        $caseId = $_POST['case_id'] ?? null;
        
        if (!$caseId) {
            $this->setFlash('error', 'Please select a case');
            $this->redirect('/missing-persons/' . $id);
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE missing_persons
                SET case_id = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$caseId, $id]);
            
            $this->setFlash('success', 'Missing person linked to case successfully');
            $this->redirect('/missing-persons/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to link case: ' . $e->getMessage());
            $this->redirect('/missing-persons/' . $id);
        }
    }
    
    /**
     * Update status
     */
    public function updateStatus(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/missing-persons/' . $id);
        }
        
        $status = $_POST['status'] ?? '';
        $foundDate = $_POST['found_date'] ?? null;
        $foundLocation = $_POST['found_location'] ?? null;
        
        if (empty($status)) {
            $this->setFlash('error', 'Please select a status');
            $this->redirect('/missing-persons/' . $id);
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE missing_persons
                SET status = ?, found_date = ?, found_location = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $foundDate, $foundLocation, $id]);
            
            $this->setFlash('success', 'Status updated successfully');
            $this->redirect('/missing-persons/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update status: ' . $e->getMessage());
            $this->redirect('/missing-persons/' . $id);
        }
    }
    
    private function getMissingPersons(?string $status, ?string $ageGroup): array
    {
        $sql = "SELECT mp.*, c.case_number,
                CASE 
                    WHEN mp.date_of_birth IS NOT NULL THEN TIMESTAMPDIFF(YEAR, mp.date_of_birth, CURDATE())
                    ELSE mp.age_at_disappearance
                END as calculated_age
                FROM missing_persons mp
                LEFT JOIN cases c ON mp.case_id = c.id
                WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND mp.status = ?";
            $params[] = $status;
        }
        
        if ($ageGroup) {
            // Calculate age group based on date_of_birth or age_at_disappearance
            switch ($ageGroup) {
                case 'Child':
                    $sql .= " AND (
                        (mp.date_of_birth IS NOT NULL AND TIMESTAMPDIFF(YEAR, mp.date_of_birth, CURDATE()) <= 12)
                        OR (mp.date_of_birth IS NULL AND mp.age_at_disappearance <= 12)
                    )";
                    break;
                case 'Teenager':
                    $sql .= " AND (
                        (mp.date_of_birth IS NOT NULL AND TIMESTAMPDIFF(YEAR, mp.date_of_birth, CURDATE()) BETWEEN 13 AND 17)
                        OR (mp.date_of_birth IS NULL AND mp.age_at_disappearance BETWEEN 13 AND 17)
                    )";
                    break;
                case 'Adult':
                    $sql .= " AND (
                        (mp.date_of_birth IS NOT NULL AND TIMESTAMPDIFF(YEAR, mp.date_of_birth, CURDATE()) BETWEEN 18 AND 59)
                        OR (mp.date_of_birth IS NULL AND mp.age_at_disappearance BETWEEN 18 AND 59)
                    )";
                    break;
                case 'Senior':
                    $sql .= " AND (
                        (mp.date_of_birth IS NOT NULL AND TIMESTAMPDIFF(YEAR, mp.date_of_birth, CURDATE()) >= 60)
                        OR (mp.date_of_birth IS NULL AND mp.age_at_disappearance >= 60)
                    )";
                    break;
            }
        }
        
        $sql .= " ORDER BY mp.created_at DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getMissingPersonDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT mp.*, c.case_number
            FROM missing_persons mp
            LEFT JOIN cases c ON mp.case_id = c.id
            WHERE mp.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    private function generateReportNumber(): string
    {
        $date = date('Ymd');
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM missing_persons 
            WHERE report_number LIKE ?
        ");
        $stmt->execute(["MP-{$date}-%"]);
        $count = $stmt->fetch()['count'] + 1;
        return sprintf('MP-%s-%04d', $date, $count);
    }
}
