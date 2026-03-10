<?php
$fullName = trim(($person['first_name'] ?? '') . ' ' . ($person['middle_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));

$content = '
<div class="row">
    <div class="col-md-4">
        <!-- Person Info Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="' . url('/AdminLTE/dist/img/user2-160x160.jpg') . '" 
                         alt="User profile picture">
                </div>
                <h3 class="profile-username text-center">' . sanitize($fullName) . '</h3>
                <p class="text-muted text-center">Person ID: ' . $person['id'] . '</p>';

if ($person['is_wanted']) {
    $content .= '<div class="alert alert-danger text-center"><strong>⚠️ WANTED PERSON</strong></div>';
}

if ($person['has_criminal_record']) {
    $content .= '<div class="alert alert-warning text-center"><strong>Has Criminal Record</strong></div>';
}

$riskClass = match($person['risk_level']) {
    'Critical' => 'danger',
    'High' => 'warning',
    'Medium' => 'info',
    'Low' => 'secondary',
    default => 'success'
};

$content .= '
                <div class="text-center mb-3">
                    <span class="badge badge-' . $riskClass . ' badge-lg">Risk Level: ' . sanitize($person['risk_level']) . '</span>
                </div>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Gender</b> <span class="float-right">' . sanitize($person['gender'] ?? 'N/A') . '</span>
                    </li>
                    <li class="list-group-item">
                        <b>Date of Birth</b> <span class="float-right">' . format_date($person['date_of_birth'] ?? null, 'd M Y') . '</span>
                    </li>
                    <li class="list-group-item">
                        <b>Age</b> <span class="float-right">' . ($person['age'] ?? 'N/A') . '</span>
                    </li>
                </ul>

                <a href="' . url('/persons/' . $person['id'] . '/edit') . '" class="btn btn-primary btn-block"><i class="fas fa-edit"></i> Edit Profile</a>
                <a href="' . url('/persons/' . $person['id'] . '/crime-check') . '" class="btn btn-warning btn-block"><i class="fas fa-exclamation-triangle"></i> Crime Check</a>
            </div>
        </div>

        <!-- Biometrics Card -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-fingerprint"></i> Biometric Data</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b><i class="fas fa-fingerprint"></i> Fingerprints</b>
                        <span class="float-right">' . ($person['fingerprint_captured'] ? '<span class="badge badge-success"><i class="fas fa-check"></i> Captured</span>' : '<span class="badge badge-secondary">Not Captured</span>') . '</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-user-circle"></i> Face</b>
                        <span class="float-right">' . ($person['face_captured'] ? '<span class="badge badge-success"><i class="fas fa-check"></i> Captured</span>' : '<span class="badge badge-secondary">Not Captured</span>') . '</span>
                    </li>
                </ul>';

// Check if person is a suspect to show biometrics link
$db = \App\Config\Database::getConnection();
$stmt = $db->prepare("SELECT id FROM suspects WHERE person_id = ? LIMIT 1");
$stmt->execute([$person['id']]);
$suspect = $stmt->fetch();

if ($suspect) {
    $content .= '
                <a href="' . url('/suspects/' . $suspect['id'] . '/biometrics') . '" class="btn btn-info btn-block">
                    <i class="fas fa-fingerprint"></i> ' . ($person['fingerprint_captured'] || $person['face_captured'] ? 'View' : 'Capture') . ' Biometrics
                </a>';
}

$content .= '
            </div>
        </div>

        <!-- Contact Info Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Contact Information</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-phone mr-1"></i> Phone</strong>
                <p class="text-muted">' . sanitize($person['contact'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-phone mr-1"></i> Alternative Phone</strong>
                <p class="text-muted">' . sanitize($person['alternative_contact'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                <p class="text-muted">' . sanitize($person['email'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted">' . sanitize($person['address'] ?? 'N/A') . '</p>
            </div>
        </div>

        <!-- Identification Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Identification</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-id-card mr-1"></i> Ghana Card</strong>
                <p class="text-muted">' . sanitize($person['ghana_card_number'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-passport mr-1"></i> Passport</strong>
                <p class="text-muted">' . sanitize($person['passport_number'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-id-card mr-1"></i> Driver\'s License</strong>
                <p class="text-muted">' . sanitize($person['drivers_license'] ?? 'N/A') . '</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Active Alerts -->';

if (!empty($alerts)) {
    $content .= '
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Active Alerts</h3>
            </div>
            <div class="card-body">';
    
    foreach ($alerts as $alert) {
        $priorityClass = match($alert['alert_priority']) {
            'Critical' => 'danger',
            'High' => 'warning',
            'Medium' => 'info',
            default => 'secondary'
        };
        
        $content .= '
                <div class="alert alert-' . $priorityClass . '">
                    <h5><i class="icon fas fa-ban"></i> ' . sanitize($alert['alert_type']) . '</h5>
                    <strong>' . sanitize($alert['alert_message']) . '</strong><br>
                    ' . ($alert['alert_details'] ? '<p>' . sanitize($alert['alert_details']) . '</p>' : '') . '
                    <small>Issued by: ' . sanitize($alert['issued_by_name']) . ' on ' . format_date($alert['issued_date'], 'd M Y') . '</small>
                </div>';
    }
    
    $content .= '
            </div>
        </div>';
}

// Aliases
if (!empty($aliases)) {
    $content .= '
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-secret"></i> Known Aliases</h3>
            </div>
            <div class="card-body">
                <ul>';
    
    foreach ($aliases as $alias) {
        $content .= '<li>' . sanitize($alias['alias_name']) . '</li>';
    }
    
    $content .= '
                </ul>
            </div>
        </div>';
}

// Criminal History
$content .= '
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Criminal History</h3>
            </div>
            <div class="card-body">';

if (!empty($criminal_history)) {
    $content .= '
                <div class="timeline">';
    
    foreach ($criminal_history as $record) {
        $iconClass = match($record['involvement_type']) {
            'Convicted' => 'bg-danger',
            'Charged' => 'bg-warning',
            'Arrested' => 'bg-info',
            'Suspect' => 'bg-secondary',
            default => 'bg-primary'
        };
        
        $content .= '
                    <div>
                        <i class="fas fa-gavel ' . $iconClass . '"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> ' . format_date($record['case_date'], 'd M Y') . '</span>
                            <h3 class="timeline-header">
                                <span class="badge badge-' . str_replace('bg-', '', $iconClass) . '">' . sanitize($record['involvement_type']) . '</span>
                                ' . sanitize($record['case_number']) . '
                            </h3>
                            <div class="timeline-body">
                                <strong>Offence:</strong> ' . sanitize($record['offence_category'] ?? 'N/A') . '<br>
                                <strong>Description:</strong> ' . sanitize($record['case_description']) . '<br>
                                <strong>Status:</strong> ' . sanitize($record['case_status']) . '<br>
                                ' . ($record['outcome'] ? '<strong>Outcome:</strong> ' . sanitize($record['outcome']) : '') . '
                            </div>
                            <div class="timeline-footer">
                                <a href="' . url('/cases/' . $record['case_id']) . '" class="btn btn-sm btn-primary">View Case</a>
                            </div>
                        </div>
                    </div>';
    }
    
    $content .= '
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>';
} else {
    $content .= '
                <p class="text-muted">No criminal history recorded</p>';
}

$content .= '
            </div>
        </div>

        <!-- Relationships -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Relationships</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addRelationshipModal">
                        <i class="fas fa-plus"></i> Add Relationship
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($relationships)) {
    $content .= '
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Related Person</th>
                                <th>Relationship</th>
                                <th>Contact</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
    
    foreach ($relationships as $rel) {
        $content .= '
                            <tr>
                                <td>
                                    <a href="' . url('/persons/' . $rel['person_id_2']) . '">
                                        <strong>' . sanitize($rel['related_person_name']) . '</strong>
                                    </a>
                                    <br><small class="text-muted">Ghana Card: ' . sanitize($rel['ghana_card_number'] ?? 'N/A') . '</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">' . sanitize($rel['relationship_type']) . '</span>
                                </td>
                                <td>' . sanitize($rel['contact'] ?? 'N/A') . '</td>
                                <td>' . sanitize($rel['notes'] ?? '-') . '</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRelationship(' . $rel['id'] . ')" title="Delete Relationship">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>';
} else {
    $content .= '
                <p class="text-muted text-center py-3"><i class="fas fa-info-circle"></i> No relationships recorded</p>';
}

$content .= '
            </div>
        </div>
    </div>
</div>

<!-- Add Relationship Modal -->
<div class="modal fade" id="addRelationshipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-users"></i> Add Relationship</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addRelationshipForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                    
                    <div class="form-group">
                        <label>Search Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="relationship_person_search" placeholder="Search by name, Ghana Card, or phone number...">
                        <input type="hidden" name="related_person_id" id="related_person_id" required>
                        <div id="relationship_search_results" class="mt-2"></div>
                        <small class="form-text text-muted">Type at least 2 characters to search by name, Ghana Card number, or phone number</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Relationship Type <span class="text-danger">*</span></label>
                        <select name="relationship_type" class="form-control" required>
                            <option value="">Select Relationship</option>';

foreach ($relationship_types as $category => $types) {
    $content .= '
                            <optgroup label="' . $category . '">';
    foreach ($types as $type) {
        $content .= '
                                <option value="' . $type . '">' . $type . '</option>';
    }
    $content .= '
                            </optgroup>';
}

$content .= '
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional information about this relationship"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> This will automatically create a reciprocal relationship. For example, if you mark this person as "Father" of another person, that person will automatically be marked as "Child" of this person.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Relationship
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Persons', 'url' => '/persons'],
    ['title' => $fullName]
];

include __DIR__ . '/../layouts/main.php';
?>

<script>
$(document).ready(function() {
// Realtime search for person to add relationship
let relationshipSearchTimeout;
const currentPersonId = <?= $person['id'] ?>;

$("#relationship_person_search").on("keyup", function() {
    clearTimeout(relationshipSearchTimeout);
    const keyword = $(this).val().trim();
    
    if (keyword.length < 2) {
        $("#relationship_search_results").hide().empty();
        return;
    }
    
    // Show loading indicator
    const loadingDiv = $("<div>").addClass("list-group-item");
    loadingDiv.html("<i class=\"fas fa-spinner fa-spin\"></i> Searching...");
    $("#relationship_search_results").empty().append(loadingDiv).show();
    
    // Debounce search
    relationshipSearchTimeout = setTimeout(function() {
        $.ajax({
            url: "<?= url('/persons/search') ?>",
            method: "GET",
            data: { q: keyword },
            success: function(response) {
                $("#relationship_search_results").empty();
                
                if (response.success && response.persons && response.persons.length > 0) {
                    response.persons.forEach(function(person) {
                        // Skip current person
                        if (person.id === currentPersonId) {
                            return;
                        }
                        
                        const ghanaCard = person.ghana_card_number || "N/A";
                        const phoneNumber = person.phone_number || person.contact || "N/A";
                        
                        const item = $("<a>")
                            .attr("href", "#")
                            .addClass("list-group-item list-group-item-action")
                            .on("click", function(e) {
                                e.preventDefault();
                                selectRelatedPerson(person.id, person.full_name);
                            });
                        
                        const content = $("<div>").addClass("d-flex justify-content-between");
                        const leftDiv = $("<div>");
                        leftDiv.html("<strong>" + person.full_name + "</strong><br><small class=\"text-muted\"><i class=\"fas fa-id-card\"></i> " + ghanaCard + " | <i class=\"fas fa-phone\"></i> " + phoneNumber + "</small>");
                        
                        content.append(leftDiv);
                        item.append(content);
                        $("#relationship_search_results").append(item);
                    });
                    $("#relationship_search_results").show();
                } else {
                    const noResults = $("<div>").addClass("list-group-item text-muted");
                    noResults.html("<i class=\"fas fa-info-circle\"></i> No persons found");
                    $("#relationship_search_results").append(noResults).show();
                }
            },
            error: function() {
                $("#relationship_search_results").empty();
                const errorDiv = $("<div>").addClass("list-group-item text-danger");
                errorDiv.html("<i class=\"fas fa-exclamation-circle\"></i> Search failed. Please try again.");
                $("#relationship_search_results").append(errorDiv).show();
            }
        });
    }, 300); // 300ms debounce
});

function selectRelatedPerson(personId, personName) {
    $("#related_person_id").val(personId);
    $("#relationship_person_search").val(personName);
    $("#relationship_search_results").html("");
}

// Submit relationship form
$("#addRelationshipForm").on("submit", function(e) {
    e.preventDefault();
    
    const relatedPersonId = $("#related_person_id").val();
    if (!relatedPersonId) {
        alert("Please search and select a person first");
        return;
    }
    
    $.ajax({
        url: "<?= url('/persons/' . $person['id'] . '/relationships') ?>",
        method: "POST",
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || "Failed to create relationship. Please try again.";
            alert(message);
        }
    });
});

// Delete relationship
function deleteRelationship(relationshipId) {
    if (!confirm("Are you sure you want to delete this relationship? This will also remove the reciprocal relationship.")) {
        return;
    }
    
    $.ajax({
        url: "<?= url('/persons/relationships/') ?>" + relationshipId + "/delete",
        method: "POST",
        data: { csrf_token: "<?= csrf_token() ?>" },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || "Failed to delete relationship. Please try again.";
            alert(message);
        }
    });
}
}); // End document.ready
</script>
