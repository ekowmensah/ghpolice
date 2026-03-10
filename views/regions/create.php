<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Create New Region</h3>
            </div>
            <form method="POST" action="' . url('/regions') . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="region_name">Region Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="region_name" name="region_name" value="' . old('region_name') . '" required>
                                ' . (isset($_SESSION['errors']['region_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['region_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="region_code">Region Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="region_code" name="region_code" value="' . old('region_code') . '" required>
                                ' . (isset($_SESSION['errors']['region_code']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['region_code']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Region
                    </button>
                    <a href="' . url('/regions') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Regions', 'url' => '/regions'],
    ['title' => 'Create']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
