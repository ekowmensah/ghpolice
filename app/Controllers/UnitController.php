<?php

namespace App\Controllers;

use App\Models\Unit;
use App\Models\Station;

class UnitController extends BaseController
{
    private Unit $unitModel;
    private Station $stationModel;
    
    public function __construct()
    {
        $this->unitModel = new Unit();
        $this->stationModel = new Station();
    }
    
    /**
     * Display list of units
     */
    public function index(): string
    {
        $stationId = $_GET['station'] ?? null;
        
        $units = $stationId 
            ? $this->unitModel->getByStation($stationId)
            : $this->unitModel->getAllWithStats();
        
        $stations = $this->stationModel->all();
        
        return $this->view('units/index', [
            'title' => 'Unit Management',
            'units' => $units,
            'stations' => $stations,
            'selected_station' => $stationId
        ]);
    }
    
    /**
     * Show unit creation form
     */
    public function create(): string
    {
        $stations = $this->stationModel->all();
        
        return $this->view('units/create', [
            'title' => 'Create Unit',
            'stations' => $stations
        ]);
    }
    
    /**
     * Store new unit
     */
    public function store(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/units/create');
        }
        
        $data = [
            'unit_name' => $_POST['unit_name'] ?? '',
            'unit_code' => $_POST['unit_code'] ?? '',
            'unit_type' => $_POST['unit_type'] ?? 'General',
            'station_id' => $_POST['station_id'] ?? null,
            'description' => $_POST['description'] ?? null
        ];
        
        $errors = $this->validate($data, [
            'unit_name' => 'required|min:3',
            'unit_code' => 'required',
            'station_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/units/create');
        }
        
        try {
            $unitId = $this->unitModel->create($data);
            $this->setFlash('success', 'Unit created successfully');
            $this->redirect('/units/' . $unitId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create unit: ' . $e->getMessage());
            $_SESSION['old'] = $data;
            $this->redirect('/units/create');
        }
    }
    
    /**
     * Show unit details
     */
    public function show(int $id): string
    {
        $unit = $this->unitModel->getWithStation($id);
        
        if (!$unit) {
            $this->setFlash('error', 'Unit not found');
            $this->redirect('/units');
        }
        
        return $this->view('units/view', [
            'title' => 'Unit Details',
            'unit' => $unit
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $unit = $this->unitModel->find($id);
        
        if (!$unit) {
            $this->setFlash('error', 'Unit not found');
            $this->redirect('/units');
        }
        
        $stations = $this->stationModel->all();
        
        return $this->view('units/edit', [
            'title' => 'Edit Unit',
            'unit' => $unit,
            'stations' => $stations
        ]);
    }
    
    /**
     * Update unit
     */
    public function update(int $id): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token');
            $this->redirect('/units/' . $id . '/edit');
        }
        
        $data = [
            'unit_name' => $_POST['unit_name'] ?? '',
            'unit_code' => $_POST['unit_code'] ?? '',
            'unit_type' => $_POST['unit_type'] ?? 'General',
            'description' => $_POST['description'] ?? null
        ];
        
        $success = $this->unitModel->update($id, $data);
        
        if ($success) {
            $this->setFlash('success', 'Unit updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update unit');
        }
        
        $this->redirect('/units/' . $id);
    }
}
