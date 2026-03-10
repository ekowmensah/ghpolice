<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Station - ' . sanitize($station['station_name']) . '</h3>
            </div>
            <form method="POST" action="' . url('/stations/' . $station['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="station_name">Station Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="station_name" name="station_name" value="' . sanitize($station['station_name']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="station_code">Station Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="station_code" name="station_code" value="' . sanitize($station['station_code']) . '" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2">' . sanitize($station['address'] ?? '') . '</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" value="' . sanitize($station['contact_number'] ?? '') . '">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Station
                    </button>
                    <a href="' . url('/stations/' . $station['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Stations', 'url' => '/stations'],
    ['title' => $station['station_name'], 'url' => '/stations/' . $station['id']],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
