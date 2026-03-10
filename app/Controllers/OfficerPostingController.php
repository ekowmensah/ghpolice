<?php

namespace App\Controllers;

use App\Models\OfficerPosting;
use App\Models\Officer;
use App\Models\Station;
use App\Models\District;
use App\Models\Division;
use App\Models\Region;

class OfficerPostingController extends BaseController
{
    private OfficerPosting $postingModel;
    private Officer $officerModel;
    
    public function __construct()
    {
        $this->postingModel = new OfficerPosting();
        $this->officerModel = new Officer();
    }
    
    public function index(): string
    {
        $officerId = $_GET['officer_id'] ?? null;
        
        if ($officerId) {
            $postings = $this->postingModel->getByOfficerId($officerId);
            $officer = $this->officerModel->query("
                SELECT 
                    o.*,
                    pr.rank_name
                FROM officers o
                JOIN police_ranks pr ON o.rank_id = pr.id
                WHERE o.id = ?
            ", [$officerId]);
            $officer = $officer[0] ?? null;
        } else {
            $postings = $this->postingModel->query("
                SELECT 
                    op.*,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                    pr.rank_name,
                    o.service_number,
                    s.station_name
                FROM officer_postings op
                JOIN officers o ON op.officer_id = o.id
                JOIN police_ranks pr ON o.rank_id = pr.id
                LEFT JOIN stations s ON op.station_id = s.id
                WHERE op.is_current = 1
                ORDER BY o.first_name
            ");
            $officer = null;
        }
        
        return $this->view('officers/postings/index', [
            'title' => 'Officer Postings',
            'postings' => $postings,
            'officer' => $officer
        ]);
    }
    
    public function create(): string
    {
        $officerId = $_GET['officer_id'] ?? null;
        
        if (!$officerId) {
            $this->setFlash('error', 'Officer ID is required');
            $this->redirect('/officers');
        }
        
        $officer = $this->officerModel->query("
            SELECT 
                o.*,
                pr.rank_name,
                s.station_name
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            LEFT JOIN stations s ON o.current_station_id = s.id
            WHERE o.id = ?
        ", [$officerId]);
        
        if (empty($officer)) {
            $this->setFlash('error', 'Officer not found');
            $this->redirect('/officers');
        }
        
        $stations = $this->postingModel->query("SELECT id, station_name FROM stations ORDER BY station_name");
        $districts = $this->postingModel->query("SELECT id, district_name FROM districts ORDER BY district_name");
        $divisions = $this->postingModel->query("SELECT id, division_name FROM divisions ORDER BY division_name");
        $regions = $this->postingModel->query("SELECT id, region_name FROM regions ORDER BY region_name");
        
        return $this->view('officers/postings/create', [
            'title' => 'Transfer Officer',
            'officer' => $officer[0],
            'stations' => $stations,
            'districts' => $districts,
            'divisions' => $divisions,
            'regions' => $regions
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'officer_id' => 'required',
            'posting_type' => 'required',
            'start_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $success = $this->postingModel->transferOfficer($_POST['officer_id'], [
                'station_id' => $_POST['station_id'] ?? null,
                'district_id' => $_POST['district_id'] ?? null,
                'division_id' => $_POST['division_id'] ?? null,
                'region_id' => $_POST['region_id'] ?? null,
                'posting_type' => $_POST['posting_type'],
                'position_title' => $_POST['position_title'] ?? null,
                'start_date' => $_POST['start_date'],
                'posting_order_number' => $_POST['posting_order_number'] ?? null,
                'remarks' => $_POST['remarks'] ?? null,
                'posted_by' => auth_id()
            ]);
            
            if ($success) {
                logger("Officer transferred: Officer ID {$_POST['officer_id']}", 'info');
                $this->json(['success' => true, 'message' => 'Officer transferred successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to transfer officer'], 500);
            }
        } catch (\Exception $e) {
            logger("Error transferring officer: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to transfer officer'], 500);
        }
    }
}
