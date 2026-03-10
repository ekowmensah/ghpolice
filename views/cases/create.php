<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-folder-plus"></i> Register New Case</h3>
            </div>
            <form method="POST" action="' . url('/cases') . '" id="caseForm">
                ' . csrf_field() . '
                <div class="card-body">
                    <h5><i class="fas fa-folder"></i> Case Details</h5>
                    <hr>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="case_type">Case Type (Legacy) <span class="text-danger">*</span></label>
                                <select class="form-control" id="case_type" name="case_type" required>
                                    <option value="Complaint">Complaint</option>
                                    <option value="Police Initiated">Police Initiated</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="case_origin">Case Origin <span class="text-danger">*</span></label>
                                <select class="form-control" id="case_origin" name="case_origin" required>
                                    <option value="">-- Select Origin --</option>
                                    <!-- Options populated dynamically based on case_type -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="case_category">Case Category</label>
                                <select class="form-control" id="case_category" name="case_category">
                                    <option value="">-- Select Category --</option>
                                    <option value="General Crime">General Crime</option>
                                    <option value="Domestic Violence">Domestic Violence</option>
                                    <option value="Sexual Offence">Sexual Offence</option>
                                    <option value="Cybercrime">Cybercrime</option>
                                    <option value="Drug Offence">Drug Offence</option>
                                    <option value="Organized Crime">Organized Crime</option>
                                    <option value="Traffic Offence">Traffic Offence</option>
                                    <option value="Administrative">Administrative</option>
                                    <option value="Armed Robbery">Armed Robbery</option>
                                    <option value="Fraud">Fraud</option>
                                    <option value="Theft">Theft</option>
                                    <option value="Assault">Assault</option>
                                    <option value="Murder">Murder</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="case_priority">Priority <span class="text-danger">*</span></label>
                                <select class="form-control" id="case_priority" name="case_priority" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="specialized_unit">Specialized Unit</label>
                                <select class="form-control" id="specialized_unit" name="specialized_unit">
                                    <option value="">-- None --</option>
                                    <option value="DOVVSU">DOVVSU - Domestic Violence & Victim Support</option>
                                    <option value="CID">CID - Criminal Investigation Department</option>
                                    <option value="CYBER">Cybercrime Unit</option>
                                    <option value="NARCO">Narcotics Control</option>
                                    <option value="SWAT">SWAT - Special Weapons and Tactics</option>
                                    <option value="INTEL">Intelligence Unit</option>
                                    <option value="TRAFFIC">Motor Traffic & Transport</option>
                                    <option value="FRAUD">Fraud Unit</option>
                                    <option value="HOMICIDE">Homicide Unit</option>
                                    <option value="ROBBERY">Armed Robbery Unit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="complainant_present">Complainant Present?</label>
                                <select class="form-control" id="complainant_present" name="complainant_present">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="arrest_made">Arrest Made?</label>
                                <select class="form-control" id="arrest_made" name="arrest_made">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_dovvsu_case" name="is_dovvsu_case" value="1">
                                    <label class="custom-control-label" for="is_dovvsu_case">
                                        <i class="fas fa-shield-alt text-danger"></i> This is a DOVVSU case (requires special handling)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <!-- Complainant Section (shown only for Complaint cases) -->
                    <div id="complainant_section" class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user"></i> Complainant Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="complainant_search">Search Complainant <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="complainant_search" placeholder="Type name, Ghana Card, or phone number..." autocomplete="off">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#personModal">
                                                    <i class="fas fa-plus"></i> New Person
                                                </button>
                                            </div>
                                        </div>
                                        <div id="complainant_results" class="list-group mt-2" style="position: absolute; z-index: 1000; max-height: 300px; overflow-y: auto; width: 95%; display: none;"></div>
                                    </div>
                                    <div id="selected_complainant" class="alert alert-success" style="display: none;">
                                        <strong>Selected:</strong> <span id="complainant_display"></span>
                                        <button type="button" class="close" onclick="clearComplainant()">&times;</button>
                                    </div>
                                    <input type="hidden" name="complainant_person_id" id="complainant_person_id">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="complainant_type">Complainant Type</label>
                                        <select class="form-control" id="complainant_type" name="complainant_type">
                                            <option value="Individual">Individual</option>
                                            <option value="Organization">Organization</option>
                                            <option value="Government">Government Agency</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization/Agency Name Field (shown when type is Organization or Government) -->
                            <div class="row" id="organization_name_row" style="display: none;">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="organization_name">
                                            <span id="org_label">Organization Name</span> <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="organization_name" name="organization_name" placeholder="Enter organization or agency name">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Enter the full name of the organization or government agency filing the complaint
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Station Selection Section -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-building"></i> Station & Location</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="region_id">Region <span class="text-danger">*</span></label>
                                        <select class="form-control" id="region_id" name="region_id" required>
                                            <option value="">Select Region</option>';

foreach ($regions as $region) {
    $content .= '<option value="' . $region['id'] . '">' . sanitize($region['region_name']) . '</option>';
}

$content .= '
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="division_id">Division <span class="text-danger">*</span></label>
                                        <select class="form-control" id="division_id" name="division_id" required disabled>
                                            <option value="">Select Division</option>';

foreach ($divisions as $division) {
    $content .= '<option value="' . $division['id'] . '" data-region="' . $division['region_id'] . '">' . sanitize($division['division_name']) . '</option>';
}

$content .= '
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="district_id">District <span class="text-danger">*</span></label>
                                        <select class="form-control" id="district_id" name="district_id" required disabled>
                                            <option value="">Select District</option>';

foreach ($districts as $district) {
    $content .= '<option value="' . $district['id'] . '" data-division="' . $district['division_id'] . '">' . sanitize($district['district_name']) . '</option>';
}

$content .= '
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="station_id">Station <span class="text-danger">*</span></label>
                                        <select class="form-control" id="station_id" name="station_id" required disabled>
                                            <option value="">Select Station</option>';

foreach ($stations as $station) {
    $content .= '<option value="' . $station['id'] . '" data-district="' . $station['district_id'] . '">' . sanitize($station['station_name']) . '</option>';
}

$content .= '
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Incident Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="' . old('location') . '" placeholder="Address or GPS coordinates">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="incident_date">Incident Date & Time</label>
                                <input type="datetime-local" class="form-control" id="incident_date" name="incident_date" value="' . old('incident_date') . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Case Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" required placeholder="Detailed description of the incident or complaint...">' . old('description') . '</textarea>
                                ' . (isset($_SESSION['errors']['description']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['description']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Register Case
                    </button>
                    <a href="' . url('/cases') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Person Creation Modal -->
<div class="modal fade" id="personModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Register New Person</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="personForm">
                    ' . csrf_field() . '
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ghana Card Number</label>
                                <input type="text" class="form-control" name="ghana_card_number" placeholder="GHA-XXXXXXXXX-X">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone_number" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender</label>
                                <select class="form-control" name="gender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="savePerson()">
                    <i class="fas fa-save"></i> Save Person
                </button>
            </div>
        </div>
    </div>
</div>
';

$scripts = '
<script>
$(document).ready(function() {
    // Realtime Complainant Search
    let searchTimeout;
    $("#complainant_search").on("keyup", function() {
        clearTimeout(searchTimeout);
        let keyword = $(this).val().trim();
        
        if (keyword.length < 2) {
            $("#complainant_results").hide();
            return;
        }
        
        $("#complainant_results").html(\'<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Searching...</div>\').show();
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "' . url('/persons/search') . '",
                data: { q: keyword },
                success: function(response) {
                    if (response.success && response.persons.length > 0) {
                        let html = "";
                        response.persons.forEach(function(person) {
                            html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectComplainant(${person.id}, \'${person.full_name}\', \'${person.ghana_card_number || \'N/A\'}\', \'${person.phone_number || \'N/A\'}\'); return false;">
                                <strong>${person.full_name}</strong><br>
                                <small>Ghana Card: ${person.ghana_card_number || \'N/A\'} | Phone: ${person.phone_number || \'N/A\'}</small>
                            </a>`;
                        });
                        $("#complainant_results").html(html).show();
                    } else {
                        $("#complainant_results").html(\'<div class="list-group-item">No persons found. Click "New Person" to register.</div>\').show();
                    }
                },
                error: function() {
                    $("#complainant_results").html(\'<div class="list-group-item text-danger">Search failed. Please try again.</div>\').show();
                }
            });
        }, 300);
    });
    
    // Hide results when clicking outside
    $(document).on("click", function(e) {
        if (!$(e.target).closest("#complainant_search, #complainant_results").length) {
            $("#complainant_results").hide();
        }
    });
    
    // Cascading Dropdowns for Station Selection
    const regionSelect = $("#region_id");
    const divisionSelect = $("#division_id");
    const districtSelect = $("#district_id");
    const stationSelect = $("#station_id");
    
    const allDivisions = [];
    const allDistricts = [];
    const allStations = [];
    
    divisionSelect.find("option").each(function() {
        if ($(this).val()) {
            allDivisions.push({
                value: $(this).val(),
                text: $(this).text(),
                regionId: $(this).data("region")
            });
        }
    });
    
    districtSelect.find("option").each(function() {
        if ($(this).val()) {
            allDistricts.push({
                value: $(this).val(),
                text: $(this).text(),
                divisionId: $(this).data("division")
            });
        }
    });
    
    stationSelect.find("option").each(function() {
        if ($(this).val()) {
            allStations.push({
                value: $(this).val(),
                text: $(this).text(),
                districtId: $(this).data("district")
            });
        }
    });
    
    // Case Type drives Case Origin options
    $("#case_type").on("change", function() {
        const caseType = $(this).val();
        const caseOriginSelect = $("#case_origin");
        
        // Clear existing options
        caseOriginSelect.html(\'<option value="">-- Select Origin --</option>\');
        
        if (caseType === "Complaint") {
            // Only show Complaint option
            caseOriginSelect.append(\'<option value="Complaint">Complaint (Civilian Report)</option>\');
            caseOriginSelect.val("Complaint"); // Auto-select
            
            // Show complainant section
            $("#complainant_section").show();
            $("#complainant_person_id").prop("required", true);
            $("#complainant_present").val("Yes");
        } else if (caseType === "Police Initiated") {
            // Show all other options except Complaint
            caseOriginSelect.append(\'<option value="Arrest">Arrest (Police-Initiated)</option>\');
            caseOriginSelect.append(\'<option value="Intelligence">Intelligence-Led</option>\');
            caseOriginSelect.append(\'<option value="DOVVSU">DOVVSU Case</option>\');
            caseOriginSelect.append(\'<option value="Suspect-Only">Suspect-Only</option>\');
            caseOriginSelect.append(\'<option value="Exhibit-Based">Exhibit-Based</option>\');
            caseOriginSelect.append(\'<option value="Transferred">Transferred</option>\');
            caseOriginSelect.append(\'<option value="Court-Ordered">Court-Ordered</option>\');
            caseOriginSelect.append(\'<option value="Administrative">Administrative</option>\');
            
            // Hide complainant section by default
            $("#complainant_section").hide();
            $("#complainant_person_id").prop("required", false);
            $("#complainant_present").val("No");
        }
    });
    
    // Case Origin fine-tunes behavior
    $("#case_origin").on("change", function() {
        const origin = $(this).val();
        
        // Auto-set complainant present
        if (origin === "DOVVSU" || origin === "Transferred") {
            $("#complainant_present").val("Yes");
            $("#complainant_section").show();
            $("#complainant_person_id").prop("required", true);
        } else if (origin === "Arrest" || origin === "Intelligence" || origin === "Suspect-Only" || origin === "Exhibit-Based" || origin === "Court-Ordered" || origin === "Administrative") {
            $("#complainant_present").val("No");
            $("#complainant_section").hide();
            $("#complainant_person_id").prop("required", false);
        }
        
        // Auto-check DOVVSU flag
        if (origin === "DOVVSU") {
            $("#is_dovvsu_case").prop("checked", true);
            $("#specialized_unit").val("DOVVSU");
        }
    });
    
    // Auto-check DOVVSU flag when specialized unit is DOVVSU
    $("#specialized_unit").on("change", function() {
        if ($(this).val() === "DOVVSU") {
            $("#is_dovvsu_case").prop("checked", true);
        }
    });
    
    // Show/hide organization name field based on complainant type
    $("#complainant_type").on("change", function() {
        const type = $(this).val();
        
        if (type === "Organization") {
            $("#organization_name_row").show();
            $("#org_label").text("Organization Name");
            $("#organization_name").attr("placeholder", "Enter organization name (e.g., ABC Company Ltd)");
            $("#organization_name").prop("required", true);
        } else if (type === "Government") {
            $("#organization_name_row").show();
            $("#org_label").text("Government Agency Name");
            $("#organization_name").attr("placeholder", "Enter agency name (e.g., Ministry of Health)");
            $("#organization_name").prop("required", true);
        } else {
            $("#organization_name_row").hide();
            $("#organization_name").prop("required", false);
            $("#organization_name").val("");
        }
    });
    
    // Initialize on page load - trigger case_type change to set up form
    $("#case_type").trigger("change");
    
    regionSelect.on("change", function() {
        const regionId = $(this).val();
        divisionSelect.html(\'<option value="">Select Division</option>\');
        districtSelect.html(\'<option value="">Select District</option>\');
        stationSelect.html(\'<option value="">Select Station</option>\');
        districtSelect.prop("disabled", true);
        stationSelect.prop("disabled", true);
        
        if (regionId) {
            allDivisions.forEach(function(div) {
                if (div.regionId == regionId) {
                    divisionSelect.append($("<option></option>").attr("value", div.value).attr("data-region", div.regionId).text(div.text));
                }
            });
            divisionSelect.prop("disabled", false);
        } else {
            divisionSelect.prop("disabled", true);
        }
    });
    
    divisionSelect.on("change", function() {
        const divisionId = $(this).val();
        districtSelect.html(\'<option value="">Select District</option>\');
        stationSelect.html(\'<option value="">Select Station</option>\');
        stationSelect.prop("disabled", true);
        
        if (divisionId) {
            allDistricts.forEach(function(dist) {
                if (dist.divisionId == divisionId) {
                    districtSelect.append($("<option></option>").attr("value", dist.value).attr("data-division", dist.divisionId).text(dist.text));
                }
            });
            districtSelect.prop("disabled", false);
        } else {
            districtSelect.prop("disabled", true);
        }
    });
    
    districtSelect.on("change", function() {
        const districtId = $(this).val();
        stationSelect.html(\'<option value="">Select Station</option>\');
        
        if (districtId) {
            allStations.forEach(function(station) {
                if (station.districtId == districtId) {
                    stationSelect.append($("<option></option>").attr("value", station.value).attr("data-district", station.districtId).text(station.text));
                }
            });
            stationSelect.prop("disabled", false);
        } else {
            stationSelect.prop("disabled", true);
        }
    });
});

function selectComplainant(id, name, ghanaCard, phone) {
    $("#complainant_person_id").val(id);
    $("#complainant_display").html(`${name} <br><small>Ghana Card: ${ghanaCard} | Phone: ${phone}</small>`);
    $("#selected_complainant").show();
    $("#complainant_search").val("");
    $("#complainant_results").hide();
}

function clearComplainant() {
    $("#complainant_person_id").val("");
    $("#selected_complainant").hide();
    $("#complainant_search").val("").focus();
}

function savePerson() {
    const formData = $("#personForm").serialize();
    
    $.ajax({
        url: "' . url('/persons/ajax-create') . '",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                selectComplainant(response.person.id, response.person.full_name, response.person.ghana_card_number || \'N/A\', response.person.phone_number || \'N/A\');
                $("#personModal").modal("hide");
                $("#personForm")[0].reset();
                alert("Person registered successfully!");
            } else {
                alert("Error: " + (response.message || "Failed to register person"));
            }
        },
        error: function() {
            alert("Failed to register person. Please try again.");
        }
    });
}
</script>
';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => 'Register']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
