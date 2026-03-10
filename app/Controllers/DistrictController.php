<?php

namespace App\Controllers;

use App\Models\District;
use App\Models\Division;

class DistrictController extends BaseController
{
    private District $districtModel;
    private Division $divisionModel;
    
    public function __construct()
    {
        $this->districtModel = new District();
        $this->divisionModel = new Division();
    }
    
    /**
     * Display list of districts
     */
    public function index(): string
    {
        $divisionId = $_GET['division'] ?? null;
        
        $districts = $divisionId 
            ? $this->districtModel->getByDivision($divisionId)
            : $this->districtModel->getAllWithStats();
        
        $divisions = $this->divisionModel->getAllWithStats();
        
        return $this->view('districts/index', [
            'title' => 'District Management',
            'districts' => $districts,
            'divisions' => $divisions,
            'selected_division' => $divisionId
        ]);
    }
    
    /**
     * Show district creation form
     */
    public function create(): string
    {
        $divisions = $this->divisionModel->getAllWithStats();
        
        return $this->view('districts/create', [
            'title' => 'Create District',
            'divisions' => $divisions
        ]);
    }
    
    /**
     * Store new district
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/districts/create');
        }
        
        $data = [
            'district_name' => $_POST['district_name'] ?? '',
            'district_code' => $_POST['district_code'] ?? '',
            'division_id' => $_POST['division_id'] ?? null
        ];
        
        $errors = $this->validate($data, [
            'district_name' => 'required|min:3',
            'district_code' => 'required',
            'division_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/districts/create');
        }
        
        try {
            $districtId = $this->districtModel->create($data);
            $this->setFlash('success', 'District created successfully');
            $this->redirect('/districts/' . $districtId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create district: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/districts/create');
        }
    }
    
    /**
     * Show district details
     */
    public function show(int $id): string
    {
        $district = $this->districtModel->getWithRegion($id);
        
        if (!$district) {
            $this->setFlash('error', 'District not found');
            $this->redirect('/districts');
        }
        
        return $this->view('districts/view', [
            'title' => 'District Details',
            'district' => $district
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $district = $this->districtModel->find($id);
        
        if (!$district) {
            $this->setFlash('error', 'District not found');
            $this->redirect('/districts');
        }
        
        $divisions = $this->divisionModel->getAllWithStats();
        
        return $this->view('districts/edit', [
            'title' => 'Edit District',
            'district' => $district,
            'divisions' => $divisions
        ]);
    }
    
    /**
     * Update district
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/districts/' . $id . '/edit');
        }
        
        $data = [
            'district_name' => $_POST['district_name'] ?? '',
            'district_code' => $_POST['district_code'] ?? '',
            'division_id' => $_POST['division_id'] ?? null
        ];
        
        $success = $this->districtModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'District updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update district');
        }
        
        $this->redirect('/districts/' . $id);
    }
}
