<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-car"></i> Register Vehicle</h3>
                <div class="card-tools">
                    <a href="' . url('/vehicles') . '" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
            </div>
            <form action="' . url('/vehicles/store') . '" method="POST">
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
                    <h5 class="mb-3">Vehicle Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Registration Number <span class="text-danger">*</span></label>
                                <input type="text" name="registration_number" class="form-control" required value="' . old('registration_number') . '" placeholder="e.g., GR-1234-20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle Type</label>
                                <select name="vehicle_type" class="form-control">
                                    <option value="Sedan" ' . (old('vehicle_type', 'Sedan') == 'Sedan' ? 'selected' : '') . '>Sedan</option>
                                    <option value="SUV" ' . (old('vehicle_type') == 'SUV' ? 'selected' : '') . '>SUV</option>
                                    <option value="Pickup" ' . (old('vehicle_type') == 'Pickup' ? 'selected' : '') . '>Pickup</option>
                                    <option value="Van" ' . (old('vehicle_type') == 'Van' ? 'selected' : '') . '>Van</option>
                                    <option value="Motorcycle" ' . (old('vehicle_type') == 'Motorcycle' ? 'selected' : '') . '>Motorcycle</option>
                                    <option value="Bus" ' . (old('vehicle_type') == 'Bus' ? 'selected' : '') . '>Bus</option>
                                    <option value="Truck" ' . (old('vehicle_type') == 'Truck' ? 'selected' : '') . '>Truck</option>
                                    <option value="Other" ' . (old('vehicle_type') == 'Other' ? 'selected' : '') . '>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Make</label>
                                <input type="text" name="vehicle_make" class="form-control" value="' . old('vehicle_make') . '" placeholder="e.g., Toyota">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" name="vehicle_model" class="form-control" value="' . old('vehicle_model') . '" placeholder="e.g., Corolla">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="vehicle_year" class="form-control" value="' . old('vehicle_year') . '" placeholder="e.g., 2020" min="1900" max="' . date('Y') . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Color</label>
                                <input type="text" name="vehicle_color" class="form-control" value="' . old('vehicle_color') . '" placeholder="e.g., White">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Owner Type</label>
                                <select name="owner_type" class="form-control">
                                    <option value="Police" ' . (old('owner_type', 'Police') == 'Police' ? 'selected' : '') . '>Police</option>
                                    <option value="Government" ' . (old('owner_type') == 'Government' ? 'selected' : '') . '>Government</option>
                                    <option value="Private" ' . (old('owner_type') == 'Private' ? 'selected' : '') . '>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Chassis Number</label>
                                <input type="text" name="chassis_number" class="form-control" value="' . old('chassis_number') . '" placeholder="Vehicle chassis number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Engine Number</label>
                                <input type="text" name="engine_number" class="form-control" value="' . old('engine_number') . '" placeholder="Vehicle engine number">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Assignment Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Current Station</label>
                                <select name="current_station_id" class="form-control select2">
                                    <option value="">Select Station</option>';

foreach ($stations as $station) {
    $selected = old('current_station_id') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . htmlspecialchars($station['station_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Acquisition Date</label>
                                <input type="date" name="acquisition_date" class="form-control" value="' . old('acquisition_date', date('Y-m-d')) . '">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Register Vehicle
                    </button>
                    <a href="' . url('/vehicles') . '" class="btn btn-secondary">
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
