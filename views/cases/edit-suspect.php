<?php
/**
 * Edit Suspect Details
 */

$isUnknown = empty($suspect['person_id']);

$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Suspect Details</h3>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/suspects/' . $suspect['id'] . '/edit') . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Suspect Type:</strong> ' . ($isUnknown ? '<i class="fas fa-user-secret"></i> Unknown Suspect' : '<i class="fas fa-user-check"></i> Known Person') . '
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Current Status <span class="text-danger">*</span></label>
                                <select class="form-control" name="current_status" required>
                                    <option value="Suspect" ' . ($suspect['current_status'] === 'Suspect' ? 'selected' : '') . '>Suspect</option>
                                    <option value="Arrested" ' . ($suspect['current_status'] === 'Arrested' ? 'selected' : '') . '>Arrested</option>
                                    <option value="Charged" ' . ($suspect['current_status'] === 'Charged' ? 'selected' : '') . '>Charged</option>
                                    <option value="Discharged" ' . ($suspect['current_status'] === 'Discharged' ? 'selected' : '') . '>Discharged</option>
                                    <option value="Acquitted" ' . ($suspect['current_status'] === 'Acquitted' ? 'selected' : '') . '>Acquitted</option>
                                    <option value="Convicted" ' . ($suspect['current_status'] === 'Convicted' ? 'selected' : '') . '>Convicted</option>
                                    <option value="Released" ' . ($suspect['current_status'] === 'Released' ? 'selected' : '') . '>Released</option>
                                    <option value="Deceased" ' . ($suspect['current_status'] === 'Deceased' ? 'selected' : '') . '>Deceased</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alias/Nickname</label>
                                <input type="text" class="form-control" name="alias" value="' . sanitize($suspect['alias'] ?? '') . '">
                            </div>
                        </div>
                    </div>';

if ($isUnknown) {
    $content .= '
                    <div class="card card-warning mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-secret"></i> Unknown Suspect Description</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Description/Nickname</label>
                                        <input type="text" class="form-control" name="unknown_description" value="' . sanitize($suspect['unknown_description'] ?? '') . '">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estimated Age</label>
                                        <input type="text" class="form-control" name="estimated_age" value="' . sanitize($suspect['estimated_age'] ?? '') . '">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select class="form-control" name="unknown_gender">
                                            <option value="">Unknown</option>
                                            <option value="Male" ' . ($suspect['unknown_gender'] === 'Male' ? 'selected' : '') . '>Male</option>
                                            <option value="Female" ' . ($suspect['unknown_gender'] === 'Female' ? 'selected' : '') . '>Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Height/Build</label>
                                        <input type="text" class="form-control" name="height_build" value="' . sanitize($suspect['height_build'] ?? '') . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Complexion</label>
                                        <input type="text" class="form-control" name="complexion" value="' . sanitize($suspect['complexion'] ?? '') . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Last Seen Wearing</label>
                                        <input type="text" class="form-control" name="clothing" value="' . sanitize($suspect['clothing'] ?? '') . '">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
}

$content .= '
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Known Location</label>
                                <input type="text" class="form-control" name="last_known_location" value="' . sanitize($suspect['last_known_location'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Arrest Date</label>
                                <input type="date" class="form-control" name="arrest_date" value="' . ($suspect['arrest_date'] ?? '') . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Identifying Marks/Features</label>
                                <textarea class="form-control" name="identifying_marks" rows="3">' . sanitize($suspect['identifying_marks'] ?? '') . '</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notes/Observations</label>
                                <textarea class="form-control" name="notes" rows="3">' . sanitize($suspect['notes'] ?? '') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Suspect
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number'], 'url' => '/cases/' . $case['id']],
    ['title' => 'Edit Suspect']
];

include __DIR__ . '/../layouts/main.php';
