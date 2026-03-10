<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Create New Division</h3>
            </div>
            <form method="POST" action="' . url('/divisions') . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_name">Division Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="division_name" name="division_name" value="' . old('division_name') . '" required>
                                ' . (isset($_SESSION['errors']['division_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['division_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_code">Division Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="division_code" name="division_code" value="' . old('division_code') . '" required>
                                ' . (isset($_SESSION['errors']['division_code']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['division_code']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="region_id">Region <span class="text-danger">*</span></label>
                                <select class="form-control" id="region_id" name="region_id" required>
                                    <option value="">Select Region</option>';

foreach ($regions as $region) {
    $selected = old('region_id') == $region['id'] ? 'selected' : '';
    $content .= '<option value="' . $region['id'] . '" ' . $selected . '>' . sanitize($region['region_name']) . '</option>';
}

$content .= '
                                </select>
                                ' . (isset($_SESSION['errors']['region_id']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['region_id']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Division
                    </button>
                    <a href="' . url('/divisions') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Divisions', 'url' => '/divisions'],
    ['title' => 'Create']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
