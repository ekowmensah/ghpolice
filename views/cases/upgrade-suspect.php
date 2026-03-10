<?php
/**
 * Upgrade Unknown Suspect to Known Person
 */

$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> Upgrade Unknown Suspect to Known Person</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Current Suspect Information</h5>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Description:</strong> ' . sanitize($suspect['unknown_description'] ?? 'N/A') . '</p>
                            <p><strong>Status:</strong> <span class="badge badge-warning">' . sanitize($suspect['current_status']) . '</span></p>
                            <p><strong>Estimated Age:</strong> ' . sanitize($suspect['estimated_age'] ?? 'N/A') . '</p>
                            <p><strong>Gender:</strong> ' . sanitize($suspect['unknown_gender'] ?? 'N/A') . '</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Height/Build:</strong> ' . sanitize($suspect['height_build'] ?? 'N/A') . '</p>
                            <p><strong>Complexion:</strong> ' . sanitize($suspect['complexion'] ?? 'N/A') . '</p>
                            <p><strong>Last Seen Wearing:</strong> ' . sanitize($suspect['clothing'] ?? 'N/A') . '</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="' . url('/cases/' . $case['id'] . '/suspects/' . $suspect['id'] . '/upgrade') . '">
                    ' . csrf_field() . '
                    
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-search"></i> Link to Known Person</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Search Person <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="person_search" placeholder="Search by name, Ghana Card, or phone number..." autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#personModal">
                                            <i class="fas fa-user-plus"></i> New Person
                                        </button>
                                    </div>
                                </div>
                                <div id="person_results" class="list-group mt-2" style="position: absolute; z-index: 1000; max-height: 250px; overflow-y: auto; width: 95%; display: none;"></div>
                            </div>
                            
                            <div id="selected_person_info" class="alert alert-success" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <strong>Selected Person:</strong>
                                        <div id="person_display" class="mt-2"></div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="button" class="btn btn-sm btn-warning" onclick="clearPersonSelection()">
                                            <i class="fas fa-times"></i> Change
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="person_id" id="person_id" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Upgrade to Known Person
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Person Creation Modal -->
<div class="modal fade" id="personModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Register New Person</h5>
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
                                <input type="text" class="form-control" name="contact" required>
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
    // Person search
    let searchTimeout;
    $("#person_search").on("keyup", function() {
        clearTimeout(searchTimeout);
        let keyword = $(this).val().trim();
        
        if (keyword.length < 2) {
            $("#person_results").hide();
            return;
        }
        
        $("#person_results").html(\'<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Searching...</div>\').show();
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "' . url('/persons/search') . '",
                data: { q: keyword },
                success: function(response) {
                    if (response.success && response.persons.length > 0) {
                        let html = "";
                        response.persons.forEach(function(person) {
                            html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectPerson(${person.id}, \'${person.full_name}\', \'${person.ghana_card_number || \'N/A\'}\', \'${person.phone_number || \'N/A\'}\'); return false;">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>${person.full_name}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-id-card"></i> ${person.ghana_card_number || \'N/A\'} | 
                                            <i class="fas fa-phone"></i> ${person.phone_number || \'N/A\'}
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <i class="fas fa-arrow-right text-primary"></i>
                                    </div>
                                </div>
                            </a>`;
                        });
                        $("#person_results").html(html).show();
                    } else {
                        $("#person_results").html(\'<div class="list-group-item text-center"><i class="fas fa-user-slash"></i> No persons found. Click "New Person" to register.</div>\').show();
                    }
                },
                error: function() {
                    $("#person_results").html(\'<div class="list-group-item text-danger"><i class="fas fa-exclamation-triangle"></i> Search failed. Please try again.</div>\').show();
                }
            });
        }, 300);
    });
    
    $(document).on("click", function(e) {
        if (!$(e.target).closest("#person_search, #person_results").length) {
            $("#person_results").hide();
        }
    });
});

function selectPerson(id, name, ghanaCard, phone) {
    $("#person_id").val(id);
    $("#person_display").html(`
        <div class="media">
            <div class="media-body">
                <h5 class="mt-0">${name}</h5>
                <p class="mb-0">
                    <i class="fas fa-id-card text-primary"></i> <strong>Ghana Card:</strong> ${ghanaCard}<br>
                    <i class="fas fa-phone text-success"></i> <strong>Phone:</strong> ${phone}
                </p>
            </div>
        </div>
    `);
    $("#selected_person_info").show();
    $("#person_search").val("");
    $("#person_results").hide();
}

function clearPersonSelection() {
    $("#person_id").val("");
    $("#selected_person_info").hide();
    $("#person_search").val("").focus();
}

function savePerson() {
    const formData = $("#personForm").serialize();
    
    $.ajax({
        url: "' . url('/persons/ajax-create') . '",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                selectPerson(response.person.id, response.person.full_name, response.person.ghana_card_number || \'N/A\', response.person.phone_number || response.person.contact || \'N/A\');
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
    ['title' => $case['case_number'], 'url' => '/cases/' . $case['id']],
    ['title' => 'Upgrade Suspect']
];

include __DIR__ . '/../layouts/main.php';
