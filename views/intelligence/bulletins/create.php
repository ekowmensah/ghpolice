<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Issue Intelligence Bulletin</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/intelligence') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/intelligence/bulletins') ?>">Bulletins</a></li>
                        <li class="breadcrumb-item active">Issue</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Bulletin Information</h3>
                </div>
                <form id="bulletinForm">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bulletin Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="bulletin_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Crime Alert">Crime Alert</option>
                                        <option value="Wanted Person">Wanted Person</option>
                                        <option value="Stolen Vehicle">Stolen Vehicle</option>
                                        <option value="Missing Person">Missing Person</option>
                                        <option value="Public Safety">Public Safety</option>
                                        <option value="Operational">Operational</option>
                                        <option value="Intelligence Update">Intelligence Update</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priority <span class="text-danger">*</span></label>
                                    <select class="form-control" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="Critical">Critical</option>
                                        <option value="High">High</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" required placeholder="Enter bulletin subject">
                        </div>

                        <div class="form-group">
                            <label>Bulletin Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="bulletin_content" rows="8" required placeholder="Enter detailed bulletin content"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Action Required</label>
                            <textarea class="form-control" name="action_required" rows="3" placeholder="Enter any action required from recipients"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valid From <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="valid_from" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valid Until</label>
                                    <input type="date" class="form-control" name="valid_until">
                                    <small class="form-text text-muted">Leave empty for indefinite validity</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Target Audience</label>
                            <input type="text" class="form-control" name="target_audience" value="All Stations" placeholder="e.g., All Stations, CID Units, Traffic Division">
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isPublic" name="is_public" value="1">
                                <label class="custom-control-label" for="isPublic">
                                    Make this bulletin public (visible to public-facing systems)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Issue Bulletin
                        </button>
                        <a href="<?= url('/intelligence/bulletins') ?>" class="btn btn-secondary">
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
    $('#bulletinForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/intelligence/bulletins/store') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/intelligence/bulletins/view/') ?>' + response.bulletin_id;
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to issue bulletin', 'error');
            }
        });
    });
});
</script>
