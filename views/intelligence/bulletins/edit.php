<?php include __DIR__ . '/../../partials/header.php'; ?>
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Intelligence Bulletin</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/intelligence/bulletins') ?>">Bulletins</a></li>
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
                    <h3 class="card-title">Edit Bulletin Details</h3>
                </div>
                <form id="editBulletinForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $bulletin['id'] ?>">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bulletin Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="bulletin_type" required>
                                        <option value="Alert" <?= $bulletin['bulletin_type'] == 'Alert' ? 'selected' : '' ?>>Alert</option>
                                        <option value="Warning" <?= $bulletin['bulletin_type'] == 'Warning' ? 'selected' : '' ?>>Warning</option>
                                        <option value="Information" <?= $bulletin['bulletin_type'] == 'Information' ? 'selected' : '' ?>>Information</option>
                                        <option value="BOLO" <?= $bulletin['bulletin_type'] == 'BOLO' ? 'selected' : '' ?>>BOLO (Be On Lookout)</option>
                                        <option value="Threat" <?= $bulletin['bulletin_type'] == 'Threat' ? 'selected' : '' ?>>Threat</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priority Level <span class="text-danger">*</span></label>
                                    <select class="form-control" name="priority_level" required>
                                        <option value="Low" <?= $bulletin['priority_level'] == 'Low' ? 'selected' : '' ?>>Low</option>
                                        <option value="Medium" <?= $bulletin['priority_level'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="High" <?= $bulletin['priority_level'] == 'High' ? 'selected' : '' ?>>High</option>
                                        <option value="Critical" <?= $bulletin['priority_level'] == 'Critical' ? 'selected' : '' ?>>Critical</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" 
                                   value="<?= sanitize($bulletin['subject']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="6" required><?= sanitize($bulletin['content']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Action Required</label>
                            <textarea class="form-control" name="action_required" rows="3"><?= sanitize($bulletin['action_required'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valid From <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="valid_from" 
                                           value="<?= date('Y-m-d', strtotime($bulletin['valid_from'])) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valid Until</label>
                                    <input type="date" class="form-control" name="valid_until" 
                                           value="<?= $bulletin['valid_until'] ? date('Y-m-d', strtotime($bulletin['valid_until'])) : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="Active" <?= $bulletin['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Expired" <?= $bulletin['status'] == 'Expired' ? 'selected' : '' ?>>Expired</option>
                                <option value="Cancelled" <?= $bulletin['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Bulletin
                        </button>
                        <a href="<?= url('/intelligence/bulletins/view/' . $bulletin['id']) ?>" class="btn btn-secondary">
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
    $('#editBulletinForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/intelligence/bulletins/update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/intelligence/bulletins/view/' . $bulletin['id']) ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
