<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Create New District</h3>
            </div>
            <form method="POST" action="' . url('/districts') . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district_name">District Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="district_name" name="district_name" value="' . old('district_name') . '" required>
                                ' . (isset($_SESSION['errors']['district_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['district_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district_code">District Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="district_code" name="district_code" value="' . old('district_code') . '" required>
                                ' . (isset($_SESSION['errors']['district_code']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['district_code']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_id">Division <span class="text-danger">*</span></label>
                                <select class="form-control" id="division_id" name="division_id" required>
                                    <option value="">Select Division</option>';

foreach ($divisions as $division) {
    $selected = old('division_id') == $division['id'] ? 'selected' : '';
    $content .= '<option value="' . $division['id'] . '" ' . $selected . '>' . sanitize($division['division_name']) . ' (' . sanitize($division['region_name']) . ')</option>';
}

$content .= '
                                </select>
                                ' . (isset($_SESSION['errors']['division_id']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['division_id']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create District
                    </button>
                    <a href="' . url('/districts') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Districts', 'url' => '/districts'],
    ['title' => 'Create']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
