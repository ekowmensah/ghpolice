<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Operation</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/operations') ?>">Operations</a></li>
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
                    <h3 class="card-title">Edit Operation Details</h3>
                </div>
                <form id="editOperationForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $operation['id'] ?>">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="operation_name" 
                                           value="<?= sanitize($operation['operation_name']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="operation_type" required>
                                        <option value="Raid" <?= $operation['operation_type'] == 'Raid' ? 'selected' : '' ?>>Raid</option>
                                        <option value="Patrol" <?= $operation['operation_type'] == 'Patrol' ? 'selected' : '' ?>>Patrol</option>
                                        <option value="Surveillance" <?= $operation['operation_type'] == 'Surveillance' ? 'selected' : '' ?>>Surveillance</option>
                                        <option value="Arrest" <?= $operation['operation_type'] == 'Arrest' ? 'selected' : '' ?>>Arrest</option>
                                        <option value="Search" <?= $operation['operation_type'] == 'Search' ? 'selected' : '' ?>>Search</option>
                                        <option value="Rescue" <?= $operation['operation_type'] == 'Rescue' ? 'selected' : '' ?>>Rescue</option>
                                        <option value="Other" <?= $operation['operation_type'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="start_datetime" 
                                           value="<?= date('Y-m-d\TH:i', strtotime($operation['start_datetime'])) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="end_datetime" 
                                           value="<?= $operation['end_datetime'] ? date('Y-m-d\TH:i', strtotime($operation['end_datetime'])) : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Target Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="target_location" 
                                   value="<?= sanitize($operation['target_location']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Operation Commander <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="commander_id" required>
                                <?php foreach ($officers as $officer): ?>
                                <option value="<?= $officer['id'] ?>" <?= $officer['id'] == $operation['commander_id'] ? 'selected' : '' ?>>
                                    <?= sanitize($officer['officer_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Objectives <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="objectives" rows="4" required><?= sanitize($operation['objectives']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Operation Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="operation_status" required>
                                <option value="Planned" <?= $operation['operation_status'] == 'Planned' ? 'selected' : '' ?>>Planned</option>
                                <option value="In Progress" <?= $operation['operation_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= $operation['operation_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $operation['operation_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Operation
                        </button>
                        <a href="<?= url('/operations/view/' . $operation['id']) ?>" class="btn btn-secondary">
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

    $('#editOperationForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/operations/update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/operations/view/' . $operation['id']) ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
