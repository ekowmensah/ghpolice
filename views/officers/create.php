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
                    <!-- Service Information -->
                    <h5 class="text-primary"><i class="fas fa-id-card"></i> Service Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="service_number">Service Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="service_number" name="service_number" value="' . old('service_number') . '" required>
                                ' . (isset($_SESSION['errors']['service_number']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['service_number']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="badge_number">Badge Number</label>
                                <input type="text" class="form-control" id="badge_number" name="badge_number" value="' . old('badge_number') . '">
                            </div>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="current_unit_id">Current Unit</label>
                                <select class="form-control" id="current_unit_id" name="current_unit_id">
                                    <option value="">Select Unit</option>';

if (isset($units)) {
    foreach ($units as $unit) {
        $selected = old('current_unit_id') == $unit['id'] ? 'selected' : '';
        $content .= '<option value="' . $unit['id'] . '" ' . $selected . '>' . sanitize($unit['unit_name']) . '</option>';
    }
}

$content .= '
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Personal Information -->
                    <h5 class="text-primary"><i class="fas fa-user"></i> Personal Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="' . old('first_name') . '" required>
                                ' . (isset($_SESSION['errors']['first_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['first_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="' . old('middle_name') . '">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="' . old('last_name') . '" required>
                                ' . (isset($_SESSION['errors']['last_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['last_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" ' . (old('gender') === 'Male' ? 'selected' : '') . '>Male</option>
                                    <option value="Female" ' . (old('gender') === 'Female' ? 'selected' : '') . '>Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="' . old('date_of_birth') . '">
                                ' . (isset($_SESSION['errors']['date_of_birth']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['date_of_birth']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_of_enlistment">Date of Enlistment</label>
                                <input type="date" class="form-control" id="date_of_enlistment" name="date_of_enlistment" value="' . old('date_of_enlistment') . '">
                                ' . (isset($_SESSION['errors']['date_of_enlistment']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['date_of_enlistment']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="ghana_card_number">Ghana Card Number</label>
                                <input type="text" class="form-control" id="ghana_card_number" name="ghana_card_number" value="' . old('ghana_card_number') . '" placeholder="GHA-XXXXXXXXX">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="passport_number">Passport Number</label>
                                <input type="text" class="form-control" id="passport_number" name="passport_number" value="' . old('passport_number') . '">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Contact Information -->
                    <h5 class="text-primary"><i class="fas fa-address-book"></i> Contact Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="' . old('phone_number') . '" placeholder="0XX XXX XXXX">
                                ' . (isset($_SESSION['errors']['phone_number']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['phone_number']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="' . old('email') . '">
                                ' . (isset($_SESSION['errors']['email']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['email']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="alternative_contact">Alternative Contact</label>
                                <input type="text" class="form-control" id="alternative_contact" name="alternative_contact" value="' . old('alternative_contact') . '" placeholder="Emergency contact number">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="drivers_license">Driver\'s License Number</label>
                                <input type="text" class="form-control" id="drivers_license" name="drivers_license" value="' . old('drivers_license') . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="residential_address">Residential Address</label>
                                <textarea class="form-control" id="residential_address" name="residential_address" rows="2">' . old('residential_address') . '</textarea>
                                ' . (isset($_SESSION['errors']['residential_address']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['residential_address']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Current Assignment -->
                    <h5 class="text-primary"><i class="fas fa-map-marker-alt"></i> Current Assignment</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="current_station_id">Current Station <span class="text-danger">*</span></label>
                                <select class="form-control" id="current_station_id" name="current_station_id" required>
                                    <option value="">Select Station</option>';

foreach ($stations as $station) {
    $selected = old('current_station_id') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . sanitize($station['station_name']) . '</option>';
}

$content .= '
                                </select>
                                ' . (isset($_SESSION['errors']['current_station_id']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['current_station_id']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="current_district_id">Current District</label>
                                <select class="form-control" id="current_district_id" name="current_district_id">
                                    <option value="">Select District</option>';

if (isset($districts)) {
    foreach ($districts as $district) {
        $selected = old('current_district_id') == $district['id'] ? 'selected' : '';
        $content .= '<option value="' . $district['id'] . '" ' . $selected . '>' . sanitize($district['district_name']) . '</option>';
    }
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="current_division_id">Current Division</label>
                                <select class="form-control" id="current_division_id" name="current_division_id">
                                    <option value="">Select Division</option>';

if (isset($divisions)) {
    foreach ($divisions as $division) {
        $selected = old('current_division_id') == $division['id'] ? 'selected' : '';
        $content .= '<option value="' . $division['id'] . '" ' . $selected . '>' . sanitize($division['division_name']) . '</option>';
    }
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="current_region_id">Current Region</label>
                                <select class="form-control" id="current_region_id" name="current_region_id">
                                    <option value="">Select Region</option>';

if (isset($regions)) {
    foreach ($regions as $region) {
        $selected = old('current_region_id') == $region['id'] ? 'selected' : '';
        $content .= '<option value="' . $region['id'] . '" ' . $selected . '>' . sanitize($region['region_name']) . '</option>';
    }
}

$content .= '
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Additional Information -->
                    <h5 class="text-primary"><i class="fas fa-info-circle"></i> Additional Information</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="employment_status">Employment Status</label>
                                <select class="form-control" id="employment_status" name="employment_status">
                                    <option value="Active" ' . (old('employment_status') === 'Active' ? 'selected' : '') . '>Active</option>
                                    <option value="On Leave" ' . (old('employment_status') === 'On Leave' ? 'selected' : '') . '>On Leave</option>
                                    <option value="Suspended" ' . (old('employment_status') === 'Suspended' ? 'selected' : '') . '>Suspended</option>
                                    <option value="Retired" ' . (old('employment_status') === 'Retired' ? 'selected' : '') . '>Retired</option>
                                    <option value="Deceased" ' . (old('employment_status') === 'Deceased' ? 'selected' : '') . '>Deceased</option>
                                    <option value="Dismissed" ' . (old('employment_status') === 'Dismissed' ? 'selected' : '') . '>Dismissed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="specialization">Specialization/Training</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" value="' . old('specialization') . '" placeholder="e.g., CID, Traffic, SWAT, etc.">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="next_of_kin">Next of Kin</label>
                                <input type="text" class="form-control" id="next_of_kin" name="next_of_kin" value="' . old('next_of_kin') . '" placeholder="Emergency contact person">
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
