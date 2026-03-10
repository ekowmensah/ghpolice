<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Region - ' . sanitize($region['region_name']) . '</h3>
            </div>
            <form method="POST" action="' . url('/regions/' . $region['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="region_name">Region Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="region_name" name="region_name" value="' . sanitize($region['region_name']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="region_code">Region Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="region_code" name="region_code" value="' . sanitize($region['region_code']) . '" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Region
                    </button>
                    <a href="' . url('/regions/' . $region['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Regions', 'url' => '/regions'],
    ['title' => $region['region_name'], 'url' => '/regions/' . $region['id']],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
