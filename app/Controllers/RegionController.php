<?php

namespace App\Controllers;

use App\Models\Region;

class RegionController extends BaseController
{
    private Region $regionModel;
    
    public function __construct()
    {
        $this->regionModel = new Region();
    }
    
    /**
     * Display list of regions
     */
    public function index(): string
    {
        $regions = $this->regionModel->getAllWithStats();
        
        return $this->view('regions/index', [
            'title' => 'Region Management',
            'regions' => $regions
        ]);
    }
    
    /**
     * Show region creation form
     */
    public function create(): string
    {
        return $this->view('regions/create', [
            'title' => 'Create Region'
        ]);
    }
    
    /**
     * Store new region
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/regions/create');
        }
        
        $data = [
            'region_name' => $_POST['region_name'] ?? '',
            'region_code' => $_POST['region_code'] ?? ''
        ];
        
        $errors = $this->validate($data, [
            'region_name' => 'required|min:3',
            'region_code' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/regions/create');
        }
        
        try {
            $regionId = $this->regionModel->create($data);
            $this->setFlash('success', 'Region created successfully');
            $this->redirect('/regions/' . $regionId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create region: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/regions/create');
        }
    }
    
    /**
     * Show region details
     */
    public function show(int $id): string
    {
        $region = $this->regionModel->getWithDivisions($id);
        
        if (!$region) {
            $this->setFlash('error', 'Region not found');
            $this->redirect('/regions');
        }
        
        return $this->view('regions/view', [
            'title' => 'Region Details',
            'region' => $region
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $region = $this->regionModel->find($id);
        
        if (!$region) {
            $this->setFlash('error', 'Region not found');
            $this->redirect('/regions');
        }
        
        return $this->view('regions/edit', [
            'title' => 'Edit Region',
            'region' => $region
        ]);
    }
    
    /**
     * Update region
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/regions/' . $id . '/edit');
        }
        
        $data = [
            'region_name' => $_POST['region_name'] ?? '',
            'region_code' => $_POST['region_code'] ?? ''
        ];
        
        $success = $this->regionModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Region updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update region');
        }
        
        $this->redirect('/regions/' . $id);
    }
}
