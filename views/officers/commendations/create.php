<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Record Commendation' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers/commendations') ?>">Commendations</a></li>
                        <li class="breadcrumb-item active">Record</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-award"></i> Record Officer Commendation</h3>
                </div>
                <form id="commendationForm">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="officer_id">Officer <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="officer_id" name="officer_id" required>
                                        <option value="">Select Officer</option>
                                        <?php foreach ($officers as $off): ?>
                                            <option value="<?= $off['id'] ?>" <?= ($officer && $officer['id'] == $off['id']) ? 'selected' : '' ?>>
                                                <?= sanitize($off['service_number'] ?? '') ?> - <?= sanitize($off['first_name'] . ' ' . $off['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="commendation_type">Commendation Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="commendation_type" name="commendation_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Bravery Award">Bravery Award</option>
                                        <option value="Meritorious Service">Meritorious Service</option>
                                        <option value="Excellence Award">Excellence Award</option>
                                        <option value="Long Service Award">Long Service Award</option>
                                        <option value="Presidential Award">Presidential Award</option>
                                        <option value="Commendation Letter">Commendation Letter</option>
                                        <option value="Certificate of Recognition">Certificate of Recognition</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="commendation_title">Commendation Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="commendation_title" name="commendation_title" placeholder="e.g., Outstanding Performance in Crime Prevention" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="commendation_date">Date Awarded <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="commendation_date" name="commendation_date" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="awarded_by">Awarded By</label>
                                    <input type="text" class="form-control" id="awarded_by" name="awarded_by" placeholder="e.g., Inspector General of Police">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="certificate_number">Certificate Number</label>
                                    <input type="text" class="form-control" id="certificate_number" name="certificate_number" placeholder="e.g., CERT-2024-001">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description / Reason for Commendation</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the actions or achievements that led to this commendation..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Note:</strong> This commendation will be recorded in the officer's service record and may be used for promotion considerations.
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Commendation
                        </button>
                        <a href="<?= url('/officers/commendations') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Select Officer',
        allowClear: true
    });

    // Form submission
    $('#commendationForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Recording...');
        
        $.ajax({
            url: '<?= url('/officers/commendations/store') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '<?= url('/officers/commendations') ?>';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to record commendation'
                    });
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
