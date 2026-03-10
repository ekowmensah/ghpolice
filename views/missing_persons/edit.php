<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Missing Person Report</h3>
                <div class="card-tools">
                    <a href="' . url('/missing-persons/' . $person['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Details
                    </a>
                </div>
            </div>
            <form action="' . url('/missing-persons/' . $person['id'] . '/update') . '" method="POST">
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

if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])) {
    $content .= '
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Please fix the following errors:</h5>
                        <ul class="mb-0">';
    foreach ($_SESSION['errors'] as $error) {
        $content .= '<li>' . htmlspecialchars($error) . '</li>';
    }
    $content .= '
                        </ul>
                    </div>';
    unset($_SESSION['errors']);
}

$content .= '
                    <h5 class="mb-3">Personal Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required value="' . htmlspecialchars(old('first_name', $person['first_name'])) . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" value="' . htmlspecialchars(old('middle_name', $person['middle_name'] ?? '')) . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required value="' . htmlspecialchars(old('last_name', $person['last_name'])) . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="' . old('date_of_birth', $person['date_of_birth'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Male" ' . (old('gender', $person['gender'] ?? '') == 'Male' ? 'selected' : '') . '>Male</option>
                                    <option value="Female" ' . (old('gender', $person['gender'] ?? '') == 'Female' ? 'selected' : '') . '>Female</option>
                                    <option value="Other" ' . (old('gender', $person['gender'] ?? '') == 'Other' ? 'selected' : '') . '>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Height</label>
                                <input type="text" name="height" class="form-control" value="' . htmlspecialchars(old('height', $person['height'] ?? '')) . '" placeholder="e.g., 170 cm">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Weight</label>
                                <input type="text" name="weight" class="form-control" value="' . htmlspecialchars(old('weight', $person['weight'] ?? '')) . '" placeholder="e.g., 70 kg">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Complexion</label>
                                <input type="text" name="complexion" class="form-control" value="' . htmlspecialchars(old('complexion', $person['complexion'] ?? '')) . '" placeholder="e.g., Fair, Dark, Medium">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distinguishing Marks</label>
                                <textarea name="distinguishing_marks" class="form-control" rows="2" placeholder="Scars, tattoos, birthmarks, etc.">' . htmlspecialchars(old('distinguishing_marks', $person['distinguishing_marks'] ?? '')) . '</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Last Seen Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Seen Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="last_seen_date" class="form-control" required value="' . old('last_seen_date', isset($person['last_seen_date']) ? date('Y-m-d\TH:i', strtotime($person['last_seen_date'])) : '') . '">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Seen Location <span class="text-danger">*</span></label>
                                <input type="text" name="last_seen_location" class="form-control" required value="' . htmlspecialchars(old('last_seen_location', $person['last_seen_location'])) . '">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Last Seen Wearing</label>
                        <textarea name="last_seen_wearing" class="form-control" rows="2" placeholder="Description of clothing and accessories">' . htmlspecialchars(old('last_seen_wearing', $person['last_seen_wearing'] ?? '')) . '</textarea>
                    </div>

                    <div class="form-group">
                        <label>Circumstances of Disappearance <span class="text-danger">*</span></label>
                        <textarea name="circumstances" class="form-control" rows="3" required>' . htmlspecialchars(old('circumstances', $person['circumstances'])) . '</textarea>
                    </div>

                    <hr>

                    <h5 class="mb-3">Reporter Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Reporter Name <span class="text-danger">*</span></label>
                                <input type="text" name="reported_by_name" class="form-control" required value="' . htmlspecialchars(old('reported_by_name', $person['reported_by_name'])) . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Reporter Contact <span class="text-danger">*</span></label>
                                <input type="text" name="reported_by_contact" class="form-control" required value="' . htmlspecialchars(old('reported_by_contact', $person['reported_by_contact'] ?? '')) . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Relationship to Missing Person</label>
                                <select name="relationship_to_missing" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Parent" ' . (old('relationship_to_missing', $person['relationship_to_missing'] ?? '') == 'Parent' ? 'selected' : '') . '>Parent</option>
                                    <option value="Sibling" ' . (old('relationship_to_missing', $person['relationship_to_missing'] ?? '') == 'Sibling' ? 'selected' : '') . '>Sibling</option>
                                    <option value="Spouse" ' . (old('relationship_to_missing', $person['relationship_to_missing'] ?? '') == 'Spouse' ? 'selected' : '') . '>Spouse</option>
                                    <option value="Relative" ' . (old('relationship_to_missing', $person['relationship_to_missing'] ?? '') == 'Relative' ? 'selected' : '') . '>Relative</option>
                                    <option value="Friend" ' . (old('relationship_to_missing', $person['relationship_to_missing'] ?? '') == 'Friend' ? 'selected' : '') . '>Friend</option>
                                    <option value="Colleague" ' . (old('relationship_to_missing', $person['relationship_to_missing'] ?? '') == 'Colleague' ? 'selected' : '') . '>Colleague</option>
                                    <option value="Other" ' . (old('relationship_to_missing', $person['relationship_to_missing'] ?? '') == 'Other' ? 'selected' : '') . '>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Report
                    </button>
                    <a href="' . url('/missing-persons/' . $person['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

include __DIR__ . '/../layouts/main.php';
?>
