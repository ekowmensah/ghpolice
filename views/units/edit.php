<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Unit - ' . sanitize($unit['unit_name']) . '</h3>
            </div>
            <form method="POST" action="' . url('/units/' . $unit['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_name">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit_name" name="unit_name" value="' . sanitize($unit['unit_name']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_code">Unit Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit_code" name="unit_code" value="' . sanitize($unit['unit_code']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_type">Unit Type</label>
                                <select class="form-control" id="unit_type" name="unit_type">
                                    <option value="General" ' . ($unit['unit_type'] === 'General' ? 'selected' : '') . '>General</option>
                                    <option value="CID" ' . ($unit['unit_type'] === 'CID' ? 'selected' : '') . '>CID (Criminal Investigation)</option>
                                    <option value="Traffic" ' . ($unit['unit_type'] === 'Traffic' ? 'selected' : '') . '>Traffic</option>
                                    <option value="SWAT" ' . ($unit['unit_type'] === 'SWAT' ? 'selected' : '') . '>SWAT</option>
                                    <option value="K9" ' . ($unit['unit_type'] === 'K9' ? 'selected' : '') . '>K9 Unit</option>
                                    <option value="Cybercrime" ' . ($unit['unit_type'] === 'Cybercrime' ? 'selected' : '') . '>Cybercrime</option>
                                    <option value="Narcotics" ' . ($unit['unit_type'] === 'Narcotics' ? 'selected' : '') . '>Narcotics</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">' . sanitize($unit['description'] ?? '') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Unit
                    </button>
                    <a href="' . url('/units/' . $unit['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Units', 'url' => '/units'],
    ['title' => $unit['unit_name'], 'url' => '/units/' . $unit['id']],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
