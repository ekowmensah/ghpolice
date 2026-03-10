<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Plan Operation</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/operations') ?>">Operations</a></li>
                        <li class="breadcrumb-item active">Plan</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Operation Details</h3>
                </div>
                <form id="operationForm">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="operation_name" required placeholder="Enter operation name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="operation_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Raid">Raid</option>
                                        <option value="Patrol">Patrol</option>
                                        <option value="Checkpoint">Checkpoint</option>
                                        <option value="Search">Search</option>
                                        <option value="Arrest">Arrest</option>
                                        <option value="Rescue">Rescue</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Operation Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="operation_date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Time (Estimated)</label>
                                    <input type="time" class="form-control" name="end_time">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Commander <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="operation_commander_id" required>
                                        <option value="">Select Commander</option>
                                        <?php foreach ($officers as $officer): ?>
                                        <option value="<?= $officer['id'] ?>"><?= sanitize($officer['officer_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
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
                        </div>

                        <div class="form-group">
                            <label>Target Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="target_location" required placeholder="Enter target location">
                        </div>

                        <div class="form-group">
                            <label>Objectives</label>
                            <textarea class="form-control" name="objectives" rows="4" placeholder="Enter operation objectives"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Team Members</label>
                            <select class="form-control select2" name="team_members[]" multiple>
                                <?php foreach ($officers as $officer): ?>
                                <option value="<?= $officer['id'] ?>"><?= sanitize($officer['officer_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Select officers to be part of this operation</small>
                        </div>

                        <div class="form-group">
                            <label>Officers Deployed</label>
                            <input type="number" class="form-control" name="officers_deployed" min="1" placeholder="Number of officers">
                        </div>

                        <div class="form-group">
                            <label>Related Case (Optional)</label>
                            <input type="text" class="form-control" name="case_id" placeholder="Enter case ID if related">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Plan Operation
                        </button>
                        <a href="<?= url('/operations') ?>" class="btn btn-secondary">
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

    $('#operationForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/operations/store') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/operations/view/') ?>' + response.operation_id;
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to plan operation', 'error');
            }
        });
    });
});
</script>
