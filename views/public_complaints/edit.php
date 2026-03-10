<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Public Complaint</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/public_complaints') ?>">Complaints</a></li>
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
                    <h3 class="card-title">Edit Complaint Details</h3>
                </div>
                <form id="editComplaintForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $complaint['id'] ?>">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Complainant Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="complainant_name" 
                                           value="<?= sanitize($complaint['complainant_name']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="complainant_contact" 
                                           value="<?= sanitize($complaint['complainant_contact']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" class="form-control" name="complainant_email" 
                                   value="<?= sanitize($complaint['complainant_email'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Complaint Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="complaint_type" required>
                                        <option value="Misconduct" <?= $complaint['complaint_type'] == 'Misconduct' ? 'selected' : '' ?>>Misconduct</option>
                                        <option value="Corruption" <?= $complaint['complaint_type'] == 'Corruption' ? 'selected' : '' ?>>Corruption</option>
                                        <option value="Abuse of Power" <?= $complaint['complaint_type'] == 'Abuse of Power' ? 'selected' : '' ?>>Abuse of Power</option>
                                        <option value="Negligence" <?= $complaint['complaint_type'] == 'Negligence' ? 'selected' : '' ?>>Negligence</option>
                                        <option value="Excessive Force" <?= $complaint['complaint_type'] == 'Excessive Force' ? 'selected' : '' ?>>Excessive Force</option>
                                        <option value="Discrimination" <?= $complaint['complaint_type'] == 'Discrimination' ? 'selected' : '' ?>>Discrimination</option>
                                        <option value="Other" <?= $complaint['complaint_type'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Station <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="station_id" required>
                                        <?php foreach ($stations as $station): ?>
                                        <option value="<?= $station['id'] ?>" <?= $station['id'] == $complaint['station_id'] ? 'selected' : '' ?>>
                                            <?= sanitize($station['station_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Officer Complained Against</label>
                            <select class="form-control select2" name="officer_id">
                                <option value="">Select Officer (if known)</option>
                                <?php foreach ($officers as $officer): ?>
                                <option value="<?= $officer['id'] ?>" <?= $officer['id'] == $complaint['officer_id'] ? 'selected' : '' ?>>
                                    <?= sanitize($officer['officer_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incident Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="incident_date" 
                                           value="<?= date('Y-m-d', strtotime($complaint['incident_date'])) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incident Location <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="incident_location" 
                                           value="<?= sanitize($complaint['incident_location']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Complaint Details <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="complaint_details" rows="6" required><?= sanitize($complaint['complaint_details']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="Pending" <?= $complaint['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Under Investigation" <?= $complaint['status'] == 'Under Investigation' ? 'selected' : '' ?>>Under Investigation</option>
                                <option value="Resolved" <?= $complaint['status'] == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                <option value="Dismissed" <?= $complaint['status'] == 'Dismissed' ? 'selected' : '' ?>>Dismissed</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Complaint
                        </button>
                        <a href="<?= url('/public_complaints/view/' . $complaint['id']) ?>" class="btn btn-secondary">
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

    $('#editComplaintForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/public_complaints/update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/public_complaints/view/' . $complaint['id']) ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
