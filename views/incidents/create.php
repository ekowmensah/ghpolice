<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Incident Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/incidents') ?>">Incidents</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Incident Information</h3>
                </div>
                <form id="incidentForm">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incident Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="incident_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Accident">Accident</option>
                                        <option value="Disturbance">Disturbance</option>
                                        <option value="Lost Property">Lost Property</option>
                                        <option value="Found Property">Found Property</option>
                                        <option value="Public Nuisance">Public Nuisance</option>
                                        <option value="Suspicious Activity">Suspicious Activity</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incident Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="incident_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Incident Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="incident_location" required placeholder="Enter incident location">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Station <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="station_id" required>
                                        <option value="">Select Station</option>
                                        <?php foreach ($stations as $station): ?>
                                        <option value="<?= $station['id'] ?>"><?= sanitize($station['station_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Attending Officer <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="attending_officer_id" required>
                                        <option value="">Select Officer</option>
                                        <?php foreach ($officers as $officer): ?>
                                        <option value="<?= $officer['id'] ?>"><?= sanitize($officer['officer_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reported By Name</label>
                                    <input type="text" class="form-control" name="reported_by_name" placeholder="Name of reporter">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reporter Contact</label>
                                    <input type="tel" class="form-control" name="reported_by_contact" placeholder="Contact number">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="6" required placeholder="Detailed description of the incident"></textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Report
                        </button>
                        <a href="<?= url('/incidents') ?>" class="btn btn-secondary">
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

    $('#incidentForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/incidents/store') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/incidents/view/') ?>' + response.incident_id;
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
