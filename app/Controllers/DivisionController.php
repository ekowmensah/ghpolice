<?php

namespace App\Controllers;

use App\Models\Division;
use App\Models\Region;

class DivisionController extends BaseController
{
    private Division $divisionModel;
    private Region $regionModel;
    
    public function __construct()
    {
        $this->divisionModel = new Division();
        $this->regionModel = new Region();
    }
    
    /**
     * Display list of divisions
     */
    public function index(): string
    {
        $regionId = $_GET['region'] ?? null;
        
        $divisions = $regionId 
            ? $this->divisionModel->getByRegion($regionId)
            : $this->divisionModel->getAllWithStats();
        
        $regions = $this->regionModel->all();
        
        return $this->view('divisions/index', [
            'title' => 'Division Management',
            'divisions' => $divisions,
            'regions' => $regions,
            'selected_region' => $regionId
        ]);
    }
    
    /**
     * Show division creation form
     */
    public function create(): string
    {
        $regions = $this->regionModel->all();
        
        return $this->view('divisions/create', [
            'title' => 'Create Division',
            'regions' => $regions
        ]);
    }
    
    /**
     * Store new division
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/divisions/create');
        }
        
        $data = [
            'division_name' => $_POST['division_name'] ?? '',
            'division_code' => $_POST['division_code'] ?? '',
            'region_id' => $_POST['region_id'] ?? null
        ];
        
        $errors = $this->validate($data, [
            'division_name' => 'required|min:3',
            'division_code' => 'required',
            'region_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/divisions/create');
        }
        
        try {
            $divisionId = $this->divisionModel->create($data);
            $this->setFlash('success', 'Division created successfully');
            $this->redirect('/divisions/' . $divisionId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create division: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/divisions/create');
        }
    }
    
    /**
     * Show division details
     */
    public function show(int $id): string
    {
        $division = $this->divisionModel->getWithRegion($id);
        
        if (!$division) {
            $this->setFlash('error', 'Division not found');
            $this->redirect('/divisions');
        }
        
        return $this->view('divisions/view', [
            'title' => 'Division Details',
            'division' => $division
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $division = $this->divisionModel->find($id);
        
        if (!$division) {
            $this->setFlash('error', 'Division not found');
            $this->redirect('/divisions');
        }
        
        $regions = $this->regionModel->all();
        
        return $this->view('divisions/edit', [
            'title' => 'Edit Division',
            'division' => $division,
            'regions' => $regions
        ]);
    }
    
    /**
     * Update division
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/divisions/' . $id . '/edit');
        }
        
        $data = [
            'division_name' => $_POST['division_name'] ?? '',
            'division_code' => $_POST['division_code'] ?? '',
            'region_id' => $_POST['region_id'] ?? null
        ];
        
        $success = $this->divisionModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Division updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update division');
        }
        
        $this->redirect('/divisions/' . $id);
    }
}
