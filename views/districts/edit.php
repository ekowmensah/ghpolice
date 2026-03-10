<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit District - ' . sanitize($district['district_name']) . '</h3>
            </div>
            <form method="POST" action="' . url('/districts/' . $district['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district_name">District Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="district_name" name="district_name" value="' . sanitize($district['district_name']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district_code">District Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="district_code" name="district_code" value="' . sanitize($district['district_code']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_id">Division <span class="text-danger">*</span></label>
                                <select class="form-control" id="division_id" name="division_id" required>
                                    <option value="">Select Division</option>';

foreach ($divisions as $division) {
    $selected = $district['division_id'] == $division['id'] ? 'selected' : '';
    $content .= '<option value="' . $division['id'] . '" ' . $selected . '>' . sanitize($division['division_name']) . ' (' . sanitize($division['region_name']) . ')</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update District
                    </button>
                    <a href="' . url('/districts/' . $district['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Districts', 'url' => '/districts'],
    ['title' => $district['district_name'], 'url' => '/districts/' . $district['id']],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
