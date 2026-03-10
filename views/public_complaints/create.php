<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> File Public Complaint</h3>
                <div class="card-tools">
                    <a href="' . url('/public-complaints') . '" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
            </div>
            <form action="' . url('/public-complaints/store') . '" method="POST">
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

$content .= '
                    <h5 class="mb-3">Complainant Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Complainant Name <span class="text-danger">*</span></label>
                                <input type="text" name="complainant_name" class="form-control" required value="' . old('complainant_name') . '" placeholder="Full name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="tel" name="complainant_contact" class="form-control" value="' . old('complainant_contact') . '" placeholder="Phone number">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Complaint Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Complaint Type <span class="text-danger">*</span></label>
                                <select name="complaint_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="Misconduct" ' . (old('complaint_type') == 'Misconduct' ? 'selected' : '') . '>Misconduct</option>
                                    <option value="Excessive Force" ' . (old('complaint_type') == 'Excessive Force' ? 'selected' : '') . '>Excessive Force</option>
                                    <option value="Corruption" ' . (old('complaint_type') == 'Corruption' ? 'selected' : '') . '>Corruption</option>
                                    <option value="Negligence" ' . (old('complaint_type') == 'Negligence' ? 'selected' : '') . '>Negligence</option>
                                    <option value="Unprofessional Conduct" ' . (old('complaint_type') == 'Unprofessional Conduct' ? 'selected' : '') . '>Unprofessional Conduct</option>
                                    <option value="Other" ' . (old('complaint_type') == 'Other' ? 'selected' : '') . '>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Station <span class="text-danger">*</span></label>
                                <select name="station_id" class="form-control select2" required>
                                    <option value="">Select Station</option>';

foreach ($stations as $station) {
    $selected = old('station_id') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . htmlspecialchars($station['station_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Officer Complained Against <small class="text-muted">(Optional)</small></label>
                        <select name="officer_complained_against" class="form-control select2">
                            <option value="">Select Officer (if known)</option>';

foreach ($officers as $officer) {
    $selected = old('officer_complained_against') == $officer['id'] ? 'selected' : '';
    $content .= '<option value="' . $officer['id'] . '" ' . $selected . '>' . htmlspecialchars($officer['officer_name']) . '</option>';
}

$content .= '
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Incident Date</label>
                                <input type="date" name="incident_date" class="form-control" value="' . old('incident_date') . '">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Incident Location</label>
                                <input type="text" name="incident_location" class="form-control" value="' . old('incident_location') . '" placeholder="Where did this occur?">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Complaint Details <span class="text-danger">*</span></label>
                        <textarea name="complaint_details" class="form-control" rows="6" required placeholder="Provide detailed description of the complaint">' . old('complaint_details') . '</textarea>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Complaint
                    </button>
                    <a href="' . url('/public-complaints') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".select2").select2({
        theme: "bootstrap4",
        width: "100%"
    });
});
</script>';

include __DIR__ . '/../layouts/main.php';
?>
