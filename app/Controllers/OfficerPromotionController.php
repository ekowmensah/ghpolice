<?php

namespace App\Controllers;

use App\Models\OfficerPromotion;
use App\Models\Officer;
use App\Models\PoliceRank;

class OfficerPromotionController extends BaseController
{
    private OfficerPromotion $promotionModel;
    private Officer $officerModel;
    private PoliceRank $rankModel;
    
    public function __construct()
    {
        $this->promotionModel = new OfficerPromotion();
        $this->officerModel = new Officer();
        $this->rankModel = new PoliceRank();
    }
    
    public function index(): string
    {
        $officerId = $_GET['officer_id'] ?? null;
        
        if ($officerId) {
            $promotions = $this->promotionModel->getByOfficerId($officerId);
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
            $promotions = $this->promotionModel->query("
                SELECT 
                    op.*,
                    CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name) as officer_name,
                    o.service_number,
                    pr1.rank_name as from_rank_name,
                    pr2.rank_name as to_rank_name
                FROM officer_promotions op
                JOIN officers o ON op.officer_id = o.id
                JOIN police_ranks pr1 ON op.from_rank_id = pr1.id
                JOIN police_ranks pr2 ON op.to_rank_id = pr2.id
                ORDER BY op.promotion_date DESC
                LIMIT 100
            ");
            $officer = null;
        }
        
        return $this->view('officers/promotions/index', [
            'title' => 'Officer Promotions',
            'promotions' => $promotions,
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
                pr.rank_level
            FROM officers o
            JOIN police_ranks pr ON o.rank_id = pr.id
            WHERE o.id = ?
        ", [$officerId]);
        
        if (empty($officer)) {
            $this->setFlash('error', 'Officer not found');
            $this->redirect('/officers');
        }
        
        $higherRanks = $this->rankModel->getHigherRanks($officer[0]['rank_level']);
        
        return $this->view('officers/promotions/create', [
            'title' => 'Promote Officer',
            'officer' => $officer[0],
            'higher_ranks' => $higherRanks
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'officer_id' => 'required',
            'from_rank_id' => 'required',
            'to_rank_id' => 'required',
            'promotion_date' => 'required',
            'effective_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $success = $this->promotionModel->promoteOfficer($_POST['officer_id'], [
                'from_rank_id' => $_POST['from_rank_id'],
                'to_rank_id' => $_POST['to_rank_id'],
                'promotion_date' => $_POST['promotion_date'],
                'promotion_order_number' => $_POST['promotion_order_number'] ?? null,
                'effective_date' => $_POST['effective_date'],
                'remarks' => $_POST['remarks'] ?? null,
                'approved_by' => auth_id()
            ]);
            
            if ($success) {
                logger("Officer promoted: Officer ID {$_POST['officer_id']}", 'info');
                $this->json(['success' => true, 'message' => 'Officer promoted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to promote officer'], 500);
            }
        } catch (\Exception $e) {
            logger("Error promoting officer: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to promote officer'], 500);
        }
    }
}
