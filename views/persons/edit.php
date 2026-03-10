<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-edit"></i> Edit Person Profile</h3>
            </div>
            <form method="POST" action="' . url('/persons/' . $person['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="' . sanitize($person['first_name']) . '" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="' . sanitize($person['middle_name'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="' . sanitize($person['last_name']) . '" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" ' . ($person['gender'] === 'Male' ? 'selected' : '') . '>Male</option>
                                    <option value="Female" ' . ($person['gender'] === 'Female' ? 'selected' : '') . '>Female</option>
                                    <option value="Other" ' . ($person['gender'] === 'Other' ? 'selected' : '') . '>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="' . sanitize($person['date_of_birth'] ?? '') . '">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Contact Information</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contact">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contact" name="contact" value="' . sanitize($person['contact'] ?? '') . '" placeholder="0244123456" maxlength="10" pattern="[0-9]{10}" required>
                                <small class="form-text text-muted">Exactly 10 digits required</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="alternative_contact">Alternative Phone</label>
                                <input type="text" class="form-control" id="alternative_contact" name="alternative_contact" value="' . sanitize($person['alternative_contact'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="' . sanitize($person['email'] ?? '') . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Residential Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2">' . sanitize($person['address'] ?? '') . '</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Identification Documents</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ghana_card_number">Ghana Card Number</label>
                                <input type="text" class="form-control" id="ghana_card_number" name="ghana_card_number" value="' . sanitize($person['ghana_card_number'] ?? '') . '" placeholder="GHA-123456789-0" maxlength="17">
                                <small class="form-text text-muted">Format: GHA-XXXXXXXXX-X</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="passport_number">Passport Number</label>
                                <input type="text" class="form-control" id="passport_number" name="passport_number" value="' . sanitize($person['passport_number'] ?? '') . '" placeholder="G1234567">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="drivers_license">Driver\'s License</label>
                                <input type="text" class="form-control" id="drivers_license" name="drivers_license" value="' . sanitize($person['drivers_license'] ?? '') . '" placeholder="DL123456">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Person
                    </button>
                    <a href="' . url('/persons/' . $person['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
';

$breadcrumbs = [
    ['title' => 'Persons', 'url' => '/persons'],
    ['title' => sanitize($person['first_name'] . ' ' . $person['last_name']), 'url' => '/persons/' . $person['id']],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>

<script>
$(document).ready(function() {
    // Phone number validation - exactly 10 digits
    $("#contact").on("input", function() {
        let value = $(this).val().replace(/\D/g, ""); // Remove non-digits
        $(this).val(value.substring(0, 10)); // Limit to 10 digits
    });
    
    // Ghana Card auto-formatting
    $("#ghana_card_number").on("input", function() {
        let value = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, "");
        value = value.substring(0, 13);
        
        let formatted = value;
        if (value.length > 3) {
            formatted = value.substring(0, 3) + "-" + value.substring(3);
        }
        if (value.length > 12) {
            formatted = value.substring(0, 3) + "-" + value.substring(3, 12) + "-" + value.substring(12);
        }
        
        $(this).val(formatted);
    });
});
</script>
