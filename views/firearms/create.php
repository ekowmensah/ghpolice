<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-crosshairs"></i> Register Firearm</h3>
                <div class="card-tools">
                    <a href="' . url('/firearms') . '" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
            </div>
            <form action="' . url('/firearms/store') . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                
                <div class="card-body">';

if (isset($_SESSION['success'])) {
    $content .= '
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success']) . '
                    </div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $content .= '
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['error']) . '
                    </div>';
    unset($_SESSION['error']);
}

$content .= '
                    <h5 class="mb-3">Firearm Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Serial Number <span class="text-danger">*</span></label>
                                <input type="text" name="serial_number" class="form-control" required value="' . old('serial_number') . '" placeholder="Enter serial number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Firearm Type <span class="text-danger">*</span></label>
                                <select name="firearm_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="Pistol" ' . (old('firearm_type') == 'Pistol' ? 'selected' : '') . '>Pistol</option>
                                    <option value="Rifle" ' . (old('firearm_type') == 'Rifle' ? 'selected' : '') . '>Rifle</option>
                                    <option value="Shotgun" ' . (old('firearm_type') == 'Shotgun' ? 'selected' : '') . '>Shotgun</option>
                                    <option value="Submachine Gun" ' . (old('firearm_type') == 'Submachine Gun' ? 'selected' : '') . '>Submachine Gun</option>
                                    <option value="Other" ' . (old('firearm_type') == 'Other' ? 'selected' : '') . '>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Make</label>
                                <input type="text" name="make" class="form-control" value="' . old('make') . '" placeholder="Manufacturer">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" name="model" class="form-control" value="' . old('model') . '" placeholder="Model">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ammunition Type / Caliber</label>
                                <select name="ammunition_type_id" class="form-control select2" style="width: 100%;">
                                    <option value="">Select Ammunition Type</option>';

foreach ($ammunition_types as $ammoType) {
    $selected = old('ammunition_type_id') == $ammoType['id'] ? 'selected' : '';
    $content .= '<option value="' . $ammoType['id'] . '" ' . $selected . '>' 
              . htmlspecialchars($ammoType['type']) . ' - ' 
              . htmlspecialchars($ammoType['caliber']) 
              . '</option>';
}

$content .= '
                                </select>
                                <small class="form-text text-muted">Ammunition type for this firearm</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Acquisition Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Acquisition Date</label>
                                <input type="date" name="acquisition_date" class="form-control" value="' . old('acquisition_date', date('Y-m-d')) . '">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Acquisition Source</label>
                                <input type="text" name="acquisition_source" class="form-control" value="' . old('acquisition_source') . '" placeholder="Where was it acquired from?">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Station <span class="text-danger">*</span></label>
                                <select name="station_id" class="form-control select2" required>
                                    <option value="">Select Station</option>';

foreach ($stations as $station) {
    $selected = old('station_id') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . htmlspecialchars($station['station_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Additional notes">' . old('remarks') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Register Firearm
                    </button>
                    <a href="' . url('/firearms') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".select2").select2({
        theme: "bootstrap4",
        width: "100%"
    });
});
</script>';

include __DIR__ . '/../layouts/main.php';
?>
