<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> Register New Officer</h3>
            </div>
            <form method="POST" action="' . url('/officers') . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <h5>Service Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="service_number">Service Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="service_number" name="service_number" value="' . old('service_number') . '" required>
                                ' . (isset($_SESSION['errors']['service_number']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['service_number']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="badge_number">Badge Number</label>
                                <input type="text" class="form-control" id="badge_number" name="badge_number" value="' . old('badge_number') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rank_id">Rank <span class="text-danger">*</span></label>
                                <select class="form-control" id="rank_id" name="rank_id" required>
                                    <option value="">Select Rank</option>';

foreach ($ranks as $rank) {
    $selected = old('rank_id') == $rank['id'] ? 'selected' : '';
    $content .= '<option value="' . $rank['id'] . '" ' . $selected . '>' . sanitize($rank['rank_name']) . '</option>';
}

$content .= '
                                </select>
                                ' . (isset($_SESSION['errors']['rank_id']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['rank_id']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Personal Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="' . old('first_name') . '" required>
                                ' . (isset($_SESSION['errors']['first_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['first_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="' . old('middle_name') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="' . old('last_name') . '" required>
                                ' . (isset($_SESSION['errors']['last_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['last_name']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" ' . (old('gender') === 'Male' ? 'selected' : '') . '>Male</option>
                                    <option value="Female" ' . (old('gender') === 'Female' ? 'selected' : '') . '>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="' . old('date_of_birth') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date_of_enlistment">Date of Enlistment</label>
                                <input type="date" class="form-control" id="date_of_enlistment" name="date_of_enlistment" value="' . old('date_of_enlistment') . '">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Contact Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contact">Phone Number</label>
                                <input type="text" class="form-control" id="contact" name="contact" value="' . old('contact') . '" placeholder="0XX XXX XXXX">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="' . old('email') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="current_station_id">Current Station</label>
                                <select class="form-control" id="current_station_id" name="current_station_id">
                                    <option value="">Select Station</option>';

foreach ($stations as $station) {
    $selected = old('current_station_id') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . sanitize($station['station_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="residential_address">Residential Address</label>
                                <textarea class="form-control" id="residential_address" name="residential_address" rows="2">' . old('residential_address') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Register Officer
                    </button>
                    <a href="' . url('/officers') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Officers', 'url' => '/officers'],
    ['title' => 'Register']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
