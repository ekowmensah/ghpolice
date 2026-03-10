<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Division - ' . sanitize($division['division_name']) . '</h3>
            </div>
            <form method="POST" action="' . url('/divisions/' . $division['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_name">Division Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="division_name" name="division_name" value="' . sanitize($division['division_name']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_code">Division Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="division_code" name="division_code" value="' . sanitize($division['division_code']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="region_id">Region <span class="text-danger">*</span></label>
                                <select class="form-control" id="region_id" name="region_id" required>
                                    <option value="">Select Region</option>';

foreach ($regions as $region) {
    $selected = $division['region_id'] == $region['id'] ? 'selected' : '';
    $content .= '<option value="' . $region['id'] . '" ' . $selected . '>' . sanitize($region['region_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Division
                    </button>
                    <a href="' . url('/divisions/' . $division['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Divisions', 'url' => '/divisions'],
    ['title' => $division['division_name'], 'url' => '/divisions/' . $division['id']],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
