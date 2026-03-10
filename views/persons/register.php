<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> Register New Person</h3>
            </div>
            <form method="POST" action="' . url('/persons') . '" id="registerPersonForm">
                ' . csrf_field() . '
                <div class="card-body">
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
                                    <option value="Other" ' . (old('gender') === 'Other' ? 'selected' : '') . '>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="' . old('date_of_birth') . '">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Contact Information</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contact">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="contact" name="contact" value="' . old('contact') . '" placeholder="0244123456" maxlength="10" pattern="[0-9]{10}" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="contact_status"></span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Exactly 10 digits required</small>
                                <div id="contact_duplicate" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="alternative_contact">Alternative Phone</label>
                                <input type="text" class="form-control" id="alternative_contact" name="alternative_contact" value="' . old('alternative_contact') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="' . old('email') . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Residential Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2">' . old('address') . '</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Identification Documents</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ghana_card_number">Ghana Card Number</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="ghana_card_number" name="ghana_card_number" value="' . old('ghana_card_number') . '" placeholder="GHA-123456789-0" maxlength="17">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="ghana_card_status"></span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Format: GHA-XXXXXXXXX-X (15 characters)</small>
                                <div id="ghana_card_duplicate" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="passport_number">Passport Number</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="passport_number" name="passport_number" value="' . old('passport_number') . '" placeholder="G1234567">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="passport_status"></span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Unique identifier</small>
                                <div id="passport_duplicate" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="drivers_license">Driver\'s License</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="drivers_license" name="drivers_license" value="' . old('drivers_license') . '" placeholder="DL123456">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="drivers_license_status"></span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Unique identifier</small>
                                <div id="drivers_license_duplicate" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Duplicate Detection:</strong> The system will automatically check for existing persons using Ghana Card, phone number, passport, or driver\'s license.
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Register Person
                    </button>
                    <a href="' . url('/persons') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Duplicate Person Modal -->
<div class="modal fade" id="duplicatePersonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle"></i> Person Already Exists</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i> <strong>A person with this information already exists in the system.</strong>
                </div>
                <div id="duplicate_person_info">
                    <!-- Person details will be loaded here -->
                </div>
                <p class="mb-0">Would you like to view their profile instead?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="view_duplicate_profile" class="btn btn-info" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Profile
                </a>
            </div>
        </div>
    </div>
</div>
';

$breadcrumbs = [
    ['title' => 'Persons', 'url' => '/persons'],
    ['title' => 'Register']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>

<script>
$(document).ready(function() {
    let verificationTimeout;
    let duplicatePersonData = {
        contact: null,
        ghana_card: null,
        passport: null,
        drivers_license: null
    };
    
    // Phone number validation - exactly 10 digits
    $("#contact").on("input", function() {
        let value = $(this).val().replace(/\D/g, ""); // Remove non-digits
        $(this).val(value.substring(0, 10)); // Limit to 10 digits
        
        if (value.length === 10) {
            $("#contact_status").html("<i class='fas fa-spinner fa-spin text-info'></i>");
            clearTimeout(verificationTimeout);
            verificationTimeout = setTimeout(function() {
                verifyPerson("contact", value);
            }, 500);
        } else {
            $("#contact_status").html("");
            $("#contact_duplicate").hide();
            duplicatePersonData.contact = null;
        }
    });
    
    // Ghana Card auto-formatting with hyphens: GHA-123456789-0
    $("#ghana_card_number").on("input", function() {
        let value = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, ""); // Remove non-alphanumeric
        
        console.log("Ghana Card input - clean value:", value, "length:", value.length);
        
        // Limit to exactly 13 alphanumeric characters (3 + 9 + 1)
        value = value.substring(0, 13);
        
        // Auto-format: GHA-123456789-0 (3 letters, 9 digits, 1 digit)
        let formatted = value;
        if (value.length > 3) {
            formatted = value.substring(0, 3) + "-" + value.substring(3);
        }
        if (value.length > 12) {
            formatted = value.substring(0, 3) + "-" + value.substring(3, 12) + "-" + value.substring(12);
        }
        
        console.log("Ghana Card formatted:", formatted, "length:", formatted.length);
        $(this).val(formatted);
        
        // Verify if exactly 13 alphanumeric characters (3 + 9 + 1 = 13) and 15 total with hyphens
        // Format: GHA-123456789-0 = 15 characters total
        console.log("Checking verification condition - value.length:", value.length, "formatted.length:", formatted.length);
        if (value.length === 13 && formatted.length === 15) {
            console.log("Ghana Card verification triggered!");
            $("#ghana_card_status").html("<i class='fas fa-spinner fa-spin text-info'></i>");
            clearTimeout(verificationTimeout);
            verificationTimeout = setTimeout(function() {
                // Search with the formatted value (with hyphens) to match database format
                // Database stores Ghana Card as GHA-123456789-0
                verifyPerson("ghana_card", formatted);
            }, 500);
        } else {
            console.log("Ghana Card verification NOT triggered - condition not met");
            $("#ghana_card_status").html("");
            $("#ghana_card_duplicate").hide();
            duplicatePersonData.ghana_card = null;
        }
    });
    
    // Passport number verification
    $("#passport_number").on("input", function() {
        let value = $(this).val().trim().toUpperCase();
        $(this).val(value);
        
        if (value.length >= 6) {
            $("#passport_status").html("<i class='fas fa-spinner fa-spin text-info'></i>");
            clearTimeout(verificationTimeout);
            verificationTimeout = setTimeout(function() {
                verifyPerson("passport", value);
            }, 500);
        } else {
            $("#passport_status").html("");
            $("#passport_duplicate").hide();
            duplicatePersonData.passport = null;
        }
    });
    
    // Driver's License verification
    $("#drivers_license").on("input", function() {
        let value = $(this).val().trim().toUpperCase();
        $(this).val(value);
        
        if (value.length >= 6) {
            $("#drivers_license_status").html("<i class='fas fa-spinner fa-spin text-info'></i>");
            clearTimeout(verificationTimeout);
            verificationTimeout = setTimeout(function() {
                verifyPerson("drivers_license", value);
            }, 500);
        } else {
            $("#drivers_license_status").html("");
            $("#drivers_license_duplicate").hide();
            duplicatePersonData.drivers_license = null;
        }
    });
    
    // Verify person exists
    function verifyPerson(field, value) {
        console.log("Verifying field:", field, "with value:", value);
        
        $.ajax({
            url: "<?= url('/persons/search') ?>",
            method: "GET",
            data: { q: value },
            success: function(response) {
                console.log("Search response for", field, ":", response);
                console.log("Number of persons found:", response.persons ? response.persons.length : 0);
                if (response.persons && response.persons.length > 0) {
                    console.log("First person:", response.persons[0]);
                }
                
                const statusEl = $("#" + field + "_status");
                const duplicateEl = $("#" + field + "_duplicate");
                
                if (response.success && response.persons && response.persons.length > 0) {
                    // Person exists
                    const person = response.persons[0];
                    console.log("Duplicate found:", person);
                    
                    duplicatePersonData[field] = person;
                    statusEl.html("<i class='fas fa-exclamation-triangle text-warning' title='Person exists'></i>");
                    
                    const alertHtml = `<div class="alert alert-warning alert-dismissible fade show mt-2">
                        <i class="fas fa-user-check"></i> <strong>Person already exists:</strong> ${person.full_name}
                        <a href="<?= url('/persons/') ?>${person.id}" class="btn btn-sm btn-info ml-2" target="_blank" title="View Profile">
                            <i class="fas fa-external-link-alt"></i> View Profile
                        </a>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>`;
                    duplicateEl.html(alertHtml).show();
                } else {
                    // No duplicate found
                    console.log("No duplicate found for", field);
                    duplicatePersonData[field] = null;
                    statusEl.html("<i class='fas fa-check-circle text-success' title='Available'></i>");
                    duplicateEl.hide();
                }
            },
            error: function(xhr, status, error) {
                console.error("Verification error for", field, ":", error);
                $("#" + field + "_status").html("<i class='fas fa-times-circle text-danger' title='Verification failed'></i>");
            }
        });
    }
    
    // Prevent form submission if duplicate exists
    $("#registerPersonForm").on("submit", function(e) {
        console.log("=== FORM SUBMISSION ATTEMPT ===");
        console.log("duplicatePersonData:", duplicatePersonData);
        
        // Check if any field has a duplicate person
        const duplicatePerson = duplicatePersonData.contact || 
                               duplicatePersonData.ghana_card || 
                               duplicatePersonData.passport || 
                               duplicatePersonData.drivers_license;
        
        console.log("duplicatePerson:", duplicatePerson);
        console.log("Has ID?", duplicatePerson && duplicatePerson.id);
        
        // Only prevent submission if duplicate person object exists and has an id
        if (duplicatePerson && duplicatePerson.id) {
            console.log("BLOCKING SUBMISSION - Duplicate found");
            e.preventDefault();
            e.stopPropagation();
            
            // Populate modal with person info
            const personInfo = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${duplicatePerson.full_name}</h5>
                        <p class="mb-1"><strong>Ghana Card:</strong> ${duplicatePerson.ghana_card_number || 'N/A'}</p>
                        <p class="mb-0"><strong>Phone:</strong> ${duplicatePerson.phone_number || 'N/A'}</p>
                    </div>
                </div>
            `;
            $("#duplicate_person_info").html(personInfo);
            $("#view_duplicate_profile").attr("href", "<?= url('/persons/') ?>" + duplicatePerson.id);
            
            // Show modal
            $("#duplicatePersonModal").modal("show");
            
            return false;
        } else {
            console.log("ALLOWING SUBMISSION - No duplicate found");
            // Form will submit normally
        }
    });
});
</script>
