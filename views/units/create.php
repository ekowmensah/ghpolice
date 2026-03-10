<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Create New Unit</h3>
            </div>
            <form method="POST" action="' . url('/units') . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_name">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit_name" name="unit_name" value="' . old('unit_name') . '" required>
                                ' . (isset($_SESSION['errors']['unit_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['unit_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_code">Unit Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit_code" name="unit_code" value="' . old('unit_code') . '" required>
                                ' . (isset($_SESSION['errors']['unit_code']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['unit_code']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_type">Unit Type</label>
                                <select class="form-control" id="unit_type" name="unit_type">
                                    <option value="General">General</option>
                                    <option value="CID">CID (Criminal Investigation)</option>
                                    <option value="Traffic">Traffic</option>
                                    <option value="SWAT">SWAT</option>
                                    <option value="K9">K9 Unit</option>
                                    <option value="Cybercrime">Cybercrime</option>
                                    <option value="Narcotics">Narcotics</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="station_id">Station <span class="text-danger">*</span></label>
                                <select class="form-control" id="station_id" name="station_id" required>
                                    <option value="">Select Station</option>';

foreach ($stations as $station) {
    $selected = old('station_id') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . sanitize($station['station_name']) . '</option>';
}

$content .= '
                                </select>
                                ' . (isset($_SESSION['errors']['station_id']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['station_id']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">' . old('description') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Unit
                    </button>
                    <a href="' . url('/units') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Units', 'url' => '/units'],
    ['title' => 'Create']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
