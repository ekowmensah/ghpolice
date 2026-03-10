<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Incident Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/incidents') ?>">Incidents</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Incident Details</h3>
                </div>
                <form id="editIncidentForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $incident['id'] ?>">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incident Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="incident_type" required>
                                        <option value="Accident" <?= $incident['incident_type'] == 'Accident' ? 'selected' : '' ?>>Accident</option>
                                        <option value="Disturbance" <?= $incident['incident_type'] == 'Disturbance' ? 'selected' : '' ?>>Disturbance</option>
                                        <option value="Emergency" <?= $incident['incident_type'] == 'Emergency' ? 'selected' : '' ?>>Emergency</option>
                                        <option value="Fire" <?= $incident['incident_type'] == 'Fire' ? 'selected' : '' ?>>Fire</option>
                                        <option value="Medical" <?= $incident['incident_type'] == 'Medical' ? 'selected' : '' ?>>Medical</option>
                                        <option value="Public Safety" <?= $incident['incident_type'] == 'Public Safety' ? 'selected' : '' ?>>Public Safety</option>
                                        <option value="Traffic" <?= $incident['incident_type'] == 'Traffic' ? 'selected' : '' ?>>Traffic</option>
                                        <option value="Suspicious Activity" <?= $incident['incident_type'] == 'Suspicious Activity' ? 'selected' : '' ?>>Suspicious Activity</option>
                                        <option value="Other" <?= $incident['incident_type'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incident Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="incident_datetime" 
                                           value="<?= date('Y-m-d\TH:i', strtotime($incident['incident_datetime'])) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Incident Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="incident_location" 
                                   value="<?= sanitize($incident['incident_location']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Station <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="station_id" required>
                                        <?php foreach ($stations as $station): ?>
                                        <option value="<?= $station['id'] ?>" <?= $station['id'] == $incident['station_id'] ? 'selected' : '' ?>>
                                            <?= sanitize($station['station_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Attending Officer <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="attending_officer_id" required>
                                        <?php foreach ($officers as $officer): ?>
                                        <option value="<?= $officer['id'] ?>" <?= $officer['id'] == $incident['attending_officer_id'] ? 'selected' : '' ?>>
                                            <?= sanitize($officer['officer_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reporter Name</label>
                                    <input type="text" class="form-control" name="reporter_name" 
                                           value="<?= sanitize($incident['reporter_name'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reporter Contact</label>
                                    <input type="tel" class="form-control" name="reporter_contact" 
                                           value="<?= sanitize($incident['reporter_contact'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Incident Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="incident_description" rows="6" required><?= sanitize($incident['incident_description']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Action Taken</label>
                            <textarea class="form-control" name="action_taken" rows="3"><?= sanitize($incident['action_taken'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select class="form-control" name="status" required>
                                        <option value="Reported" <?= $incident['status'] == 'Reported' ? 'selected' : '' ?>>Reported</option>
                                        <option value="Under Investigation" <?= $incident['status'] == 'Under Investigation' ? 'selected' : '' ?>>Under Investigation</option>
                                        <option value="Resolved" <?= $incident['status'] == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="Escalated to Case" <?= $incident['status'] == 'Escalated to Case' ? 'selected' : '' ?>>Escalated to Case</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Severity Level <span class="text-danger">*</span></label>
                                    <select class="form-control" name="severity_level" required>
                                        <option value="Low" <?= $incident['severity_level'] == 'Low' ? 'selected' : '' ?>>Low</option>
                                        <option value="Medium" <?= $incident['severity_level'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="High" <?= $incident['severity_level'] == 'High' ? 'selected' : '' ?>>High</option>
                                        <option value="Critical" <?= $incident['severity_level'] == 'Critical' ? 'selected' : '' ?>>Critical</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Incident
                        </button>
                        <a href="<?= url('/incidents/view/' . $incident['id']) ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#editIncidentForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/incidents/update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/incidents/view/' . $incident['id']) ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
