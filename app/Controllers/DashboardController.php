<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\Person;
use App\Models\Officer;
use App\Config\Database;

class DashboardController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function index(): string
    {
        $caseModel = new CaseModel();
        $personModel = new Person();
        $officerModel = new Officer();
        
        // Get statistics
        $stats = [
            'total_cases' => $caseModel->count(),
            'open_cases' => $caseModel->countByStatus('Open'),
            'high_priority_cases' => $this->db->query("SELECT COUNT(*) as count FROM cases WHERE case_priority IN ('High', 'Critical')")->fetch()['count'] ?? 0,
            'closed_cases' => $caseModel->countByStatus('Closed'),
            'total_persons' => $personModel->count(),
            'wanted_persons' => $personModel->countWanted(),
            'missing_persons' => $this->db->query("SELECT COUNT(*) as count FROM missing_persons WHERE status = 'Missing'")->fetch()['count'] ?? 0,
            'total_suspects' => $this->db->query("SELECT COUNT(*) as count FROM suspects")->fetch()['count'] ?? 0,
            'total_evidence' => $this->db->query("SELECT COUNT(*) as count FROM evidence")->fetch()['count'] ?? 0,
            'total_vehicles' => $this->db->query("SELECT COUNT(*) as count FROM vehicles")->fetch()['count'] ?? 0,
            'total_firearms' => $this->db->query("SELECT COUNT(*) as count FROM firearms")->fetch()['count'] ?? 0,
            'total_officers' => $officerModel->count(),
            'recent_cases' => $caseModel->getRecent(5),
            'recent_missing_persons' => $this->db->query("SELECT * FROM missing_persons ORDER BY created_at DESC LIMIT 5")->fetchAll(),
            'high_priority_cases_list' => $this->db->query("SELECT * FROM cases WHERE case_priority IN ('High', 'Critical') ORDER BY created_at DESC LIMIT 3")->fetchAll(),
            'wanted_persons_list' => $this->db->query("SELECT * FROM persons WHERE is_wanted = 1 LIMIT 3")->fetchAll(),
            'recent_evidence' => $this->db->query("SELECT * FROM evidence ORDER BY collection_date DESC LIMIT 3")->fetchAll()
        ];
        
        return $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats
        ]);
    }
}
