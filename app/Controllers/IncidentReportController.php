<?php

namespace App\Controllers;

use App\Models\IncidentReport;
use App\Models\Officer;
use App\Models\Station;

class IncidentReportController extends BaseController
{
    private IncidentReport $incidentModel;
    private Officer $officerModel;
    private Station $stationModel;
    
    public function __construct()
    {
        $this->incidentModel = new IncidentReport();
        $this->officerModel = new Officer();
        $this->stationModel = new Station();
    }
    
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        
        if ($status) {
            $incidents = $this->incidentModel->getByStatus($status);
        } elseif ($type) {
            $incidents = $this->incidentModel->getByType($type);
        } else {
            $incidents = $this->incidentModel->query("
                SELECT 
                    ir.*,
                    s.station_name,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as attending_officer_name
                FROM incident_reports ir
                JOIN stations s ON ir.station_id = s.id
                JOIN officers o ON ir.attending_officer_id = o.id
                ORDER BY ir.incident_date DESC
                LIMIT 100
            ");
        }
        
        return $this->view('incidents/index', [
            'title' => 'Incident Reports',
            'incidents' => $incidents,
            'selected_status' => $status,
            'selected_type' => $type
        ]);
    }
    
    public function show(int $id): string
    {
        $incident = $this->incidentModel->query("
            SELECT 
                ir.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as attending_officer_name,
                pr.rank_name,
                c.case_number
            FROM incident_reports ir
            JOIN stations s ON ir.station_id = s.id
            JOIN officers o ON ir.attending_officer_id = o.id
            JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN cases c ON ir.case_id = c.id
            WHERE ir.id = ?
        ", [$id]);
        
        if (empty($incident)) {
            $this->setFlash('error', 'Incident report not found');
            $this->redirect('/incidents');
        }
        
        return $this->view('incidents/view', [
            'title' => 'Incident Report Details',
            'incident' => $incident[0]
        ]);
    }
    
    public function create(): string
    {
        $stations = $this->stationModel->all();
        $officers = $this->officerModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.employment_status = 'Active'
            ORDER BY o.first_name
        ");
        
        return $this->view('incidents/create', [
            'title' => 'Create Incident Report',
            'stations' => $stations,
            'officers' => $officers
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'incident_type' => 'required',
            'incident_date' => 'required',
            'incident_location' => 'required',
            'description' => 'required',
            'station_id' => 'required',
            'attending_officer_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $incidentNumber = 'IR-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $incidentId = $this->incidentModel->create([
                'incident_number' => $incidentNumber,
                'incident_type' => $_POST['incident_type'],
                'incident_date' => $_POST['incident_date'],
                'incident_location' => $_POST['incident_location'],
                'reported_by_name' => $_POST['reported_by_name'] ?? null,
                'reported_by_contact' => $_POST['reported_by_contact'] ?? null,
                'description' => $_POST['description'],
                'station_id' => $_POST['station_id'],
                'attending_officer_id' => $_POST['attending_officer_id'],
                'status' => 'Open'
            ]);
            
            logger("Incident report created: {$incidentNumber}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Incident report created successfully',
                'incident_id' => $incidentId,
                'incident_number' => $incidentNumber
            ]);
        } catch (\Exception $e) {
            logger("Error creating incident report: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to create incident report'], 500);
        }
    }
    
    public function updateStatus(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $status = $_POST['status'] ?? '';
        $resolution = $_POST['resolution'] ?? null;
        
        if (empty($status)) {
            $this->json(['success' => false, 'message' => 'Status is required'], 422);
        }
        
        try {
            $data = ['status' => $status];
            if ($resolution) {
                $data['resolution'] = $resolution;
            }
            
            $this->incidentModel->update($id, $data);
            $this->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }
    
    public function escalateToCase(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $caseId = $_POST['case_id'] ?? null;
        
        if (!$caseId) {
            $this->json(['success' => false, 'message' => 'Case ID is required'], 422);
        }
        
        try {
            $this->incidentModel->escalateToCase($id, $caseId);
            $this->json(['success' => true, 'message' => 'Incident escalated to case successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to escalate incident'], 500);
        }
    }
}
