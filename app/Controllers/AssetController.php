<?php

namespace App\Controllers;

use App\Models\Asset;

class AssetController extends BaseController
{
    private Asset $assetModel;
    
    public function __construct()
    {
        $this->assetModel = new Asset();
    }
    
    public function index(): string
    {
        $type = $_GET['type'] ?? null;
        $location = $_GET['location'] ?? null;
        
        if ($type) {
            $assets = $this->assetModel->getByType($type);
        } elseif ($location) {
            $assets = $this->assetModel->getByLocation($location);
        } else {
            $assets = $this->assetModel->query("
                SELECT 
                    a.*,
                    c.case_number
                FROM assets a
                LEFT JOIN cases c ON a.case_id = c.id
                ORDER BY a.asset_name
            ");
        }
        
        return $this->view('assets/index', [
            'title' => 'Asset Management',
            'assets' => $assets,
            'selected_type' => $type,
            'selected_location' => $location
        ]);
    }
    
    public function show(int $id): string
    {
        $asset = $this->assetModel->query("
            SELECT 
                a.*,
                c.case_number
            FROM assets a
            LEFT JOIN cases c ON a.case_id = c.id
            WHERE a.id = ?
        ", [$id]);
        
        if (empty($asset)) {
            $this->setFlash('error', 'Asset not found');
            $this->redirect('/assets');
        }
        
        $movements = $this->assetModel->getMovementHistory($id);
        
        return $this->view('assets/view', [
            'title' => 'Asset Details',
            'asset' => $asset[0],
            'movements' => $movements
        ]);
    }
    
    public function create(): string
    {
        return $this->view('assets/create', [
            'title' => 'Register Asset'
        ]);
    }
    
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'asset_name' => 'required',
            'asset_type' => 'required',
            'current_location' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $assetId = $this->assetModel->create([
                'asset_name' => $_POST['asset_name'],
                'serial_number' => $_POST['serial_number'] ?? null,
                'asset_type' => $_POST['asset_type'],
                'condition_status' => $_POST['condition_status'] ?? null,
                'current_location' => $_POST['current_location'],
                'case_id' => $_POST['case_id'] ?? null,
                'description' => $_POST['description'] ?? null,
                'purchase_date' => $_POST['purchase_date'] ?? null,
                'purchase_value' => $_POST['purchase_value'] ?? null
            ]);
            
            logger("Asset registered: ID {$assetId} - {$_POST['asset_name']}", 'info');
            
            $this->json([
                'success' => true,
                'message' => 'Asset registered successfully',
                'asset_id' => $assetId
            ]);
        } catch (\Exception $e) {
            logger("Error registering asset: " . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Failed to register asset'], 500);
        }
    }
    
    public function move(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $errors = $this->validate($_POST, [
            'moved_to' => 'required',
            'moved_by' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }
        
        try {
            $asset = $this->assetModel->find($id);
            
            $this->assetModel->recordMovement($id, [
                'moved_from' => $asset['current_location'],
                'moved_to' => $_POST['moved_to'],
                'moved_by' => $_POST['moved_by'],
                'purpose' => $_POST['purpose'] ?? null
            ]);
            
            logger("Asset moved: ID {$id} from {$asset['current_location']} to {$_POST['moved_to']}", 'info');
            
            $this->json(['success' => true, 'message' => 'Asset moved successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to move asset'], 500);
        }
    }
    
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        try {
            $this->assetModel->update($id, [
                'asset_name' => $_POST['asset_name'],
                'serial_number' => $_POST['serial_number'] ?? null,
                'condition_status' => $_POST['condition_status'] ?? null,
                'description' => $_POST['description'] ?? null
            ]);
            
            $this->json(['success' => true, 'message' => 'Asset updated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update asset'], 500);
        }
    }
}
