<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Surveillance Operation</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/surveillance') ?>">Surveillance</a></li>
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
                    <h3 class="card-title">Edit Surveillance Details</h3>
                </div>
                <form id="editSurveillanceForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $surveillance['id'] ?>">
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label>Target Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="target_name" 
                                   value="<?= sanitize($surveillance['target_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Target Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="target_location" 
                                   value="<?= sanitize($surveillance['target_location']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="start_datetime" 
                                           value="<?= date('Y-m-d\TH:i', strtotime($surveillance['start_datetime'])) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="end_datetime" 
                                           value="<?= $surveillance['end_datetime'] ? date('Y-m-d\TH:i', strtotime($surveillance['end_datetime'])) : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Lead Officer <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="lead_officer_id" required>
                                <?php foreach ($officers as $officer): ?>
                                <option value="<?= $officer['id'] ?>" <?= $officer['id'] == $surveillance['lead_officer_id'] ? 'selected' : '' ?>>
                                    <?= sanitize($officer['officer_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Surveillance Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="surveillance_type" required>
                                <option value="Physical" <?= $surveillance['surveillance_type'] == 'Physical' ? 'selected' : '' ?>>Physical</option>
                                <option value="Electronic" <?= $surveillance['surveillance_type'] == 'Electronic' ? 'selected' : '' ?>>Electronic</option>
                                <option value="Technical" <?= $surveillance['surveillance_type'] == 'Technical' ? 'selected' : '' ?>>Technical</option>
                                <option value="Combined" <?= $surveillance['surveillance_type'] == 'Combined' ? 'selected' : '' ?>>Combined</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Purpose <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="purpose" rows="3" required><?= sanitize($surveillance['purpose']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="Planned" <?= $surveillance['status'] == 'Planned' ? 'selected' : '' ?>>Planned</option>
                                <option value="Active" <?= $surveillance['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Completed" <?= $surveillance['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Suspended" <?= $surveillance['status'] == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Surveillance
                        </button>
                        <a href="<?= url('/surveillance/view/' . $surveillance['id']) ?>" class="btn btn-secondary">
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

    $('#editSurveillanceForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/surveillance/update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/surveillance/view/' . $surveillance['id']) ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
