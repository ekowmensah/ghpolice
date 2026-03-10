<?php

namespace App\Controllers;

use App\Models\PublicComplaint;
use App\Models\Officer;
use App\Models\Station;

class PublicComplaintController extends BaseController
{
    private PublicComplaint $complaintModel;
    private Officer $officerModel;
    private Station $stationModel;
    
    public function __construct()
    {
        $this->complaintModel = new PublicComplaint();
        $this->officerModel = new Officer();
        $this->stationModel = new Station();
    }
    
    public function index(): string
    {
        $status = $_GET['status'] ?? null;
        
        $complaints = $status
            ? $this->complaintModel->getByStatus($status)
            : $this->complaintModel->query("
                SELECT 
                    pc.*,
                    s.station_name,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name
                FROM public_complaints pc
                JOIN stations s ON pc.station_id = s.id
                LEFT JOIN officers o ON pc.officer_complained_against = o.id
                ORDER BY pc.created_at DESC
                LIMIT 100
            ");
        
        return $this->view('public_complaints/index', [
            'title' => 'Public Complaints',
            'complaints' => $complaints,
            'selected_status' => $status
        ]);
    }
    
    public function show(int $id): string
    {
        $complaint = $this->complaintModel->query("
            SELECT 
                pc.*,
                s.station_name,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                pr.rank_name,
                o.service_number
            FROM public_complaints pc
            JOIN stations s ON pc.station_id = s.id
            LEFT JOIN officers o ON pc.officer_complained_against = o.id
            LEFT JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE pc.id = ?
        ", [$id]);
        
        if (empty($complaint)) {
            $this->setFlash('error', 'Complaint not found');
            $this->redirect('/public-complaints');
        }
        
        return $this->view('public_complaints/view', [
            'title' => 'Complaint Details',
            'complaint' => $complaint[0]
        ]);
    }
    
    public function create(): string
    {
        $stations = $this->stationModel->all();
        $officers = $this->officerModel->query("
            SELECT 
                o.id,
                CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', o.service_number) as officer_name
            FROM officers o
            WHERE o.employment_status = 'Active'
            ORDER BY o.first_name
        ");
        
        return $this->view('public_complaints/create', [
            'title' => 'File Public Complaint',
            'stations' => $stations,
            'officers' => $officers
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/public-complaints/create');
        }
        
        $errors = $this->validate($_POST, [
            'complainant_name' => 'required',
            'complaint_type' => 'required',
            'complaint_details' => 'required',
            'station_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('/public-complaints/create');
        }
        
        try {
            $complaintNumber = 'PC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $complaintId = $this->complaintModel->create([
                'complaint_number' => $complaintNumber,
                'complainant_name' => $_POST['complainant_name'],
                'complaint_type' => $_POST['complaint_type'],
                'complaint_details' => $_POST['complaint_details'],
                'officer_complained_against' => $_POST['officer_complained_against'] ?? null,
                'station_id' => $_POST['station_id'],
                'complaint_status' => 'Received'
            ]);
            
            logger("Public complaint filed: {$complaintNumber}", 'info');
            
            $this->setFlash('success', 'Complaint filed successfully. Complaint Number: ' . $complaintNumber);
            $this->redirect('/public-complaints/' . $complaintId);
        } catch (\Exception $e) {
            logger("Error filing complaint: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to file complaint: ' . $e->getMessage());
            $_SESSION['old'] = $_POST;
            $this->redirect('/public-complaints/create');
        }
    }
    
    public function investigate(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/public-complaints/' . $id);
        }
        
        try {
            $this->complaintModel->update($id, [
                'complaint_status' => 'Under Investigation'
            ]);
            
            logger("Complaint investigation started: ID {$id}", 'info');
            
            $this->setFlash('success', 'Investigation started successfully');
            $this->redirect('/public-complaints/' . $id);
        } catch (\Exception $e) {
            logger("Error starting investigation: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to start investigation: ' . $e->getMessage());
            $this->redirect('/public-complaints/' . $id);
        }
    }
    
    public function resolve(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/public-complaints/' . $id);
        }
        
        $status = $_POST['status'] ?? '';
        $resolution = $_POST['resolution'] ?? '';
        
        if (empty($status)) {
            $this->setFlash('error', 'Status is required');
            $this->redirect('/public-complaints/' . $id);
        }
        
        if (empty($resolution)) {
            $this->setFlash('error', 'Resolution details are required');
            $this->redirect('/public-complaints/' . $id);
        }
        
        try {
            $this->complaintModel->update($id, [
                'complaint_status' => $status
            ]);
            
            logger("Complaint resolved: ID {$id}, Status: {$status}", 'info');
            
            $this->setFlash('success', 'Complaint ' . strtolower($status) . ' successfully');
            $this->redirect('/public-complaints/' . $id);
        } catch (\Exception $e) {
            logger("Error resolving complaint: " . $e->getMessage(), 'error');
            $this->setFlash('error', 'Failed to update complaint: ' . $e->getMessage());
            $this->redirect('/public-complaints/' . $id);
        }
    }
}
