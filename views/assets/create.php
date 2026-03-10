<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Register Asset</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/assets') ?>">Assets</a></li>
                        <li class="breadcrumb-item active">Register</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Asset Information</h3>
                </div>
                <form id="assetForm">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asset Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="asset_name" required placeholder="Enter asset name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Serial Number</label>
                                    <input type="text" class="form-control" name="serial_number" placeholder="Serial/ID number">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asset Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="asset_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Vehicle">Vehicle</option>
                                        <option value="Equipment">Equipment</option>
                                        <option value="Furniture">Furniture</option>
                                        <option value="Electronics">Electronics</option>
                                        <option value="Weapon">Weapon</option>
                                        <option value="Communication Device">Communication Device</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Condition Status</label>
                                    <select class="form-control" name="condition_status">
                                        <option value="">Select Condition</option>
                                        <option value="Excellent">Excellent</option>
                                        <option value="Good" selected>Good</option>
                                        <option value="Fair">Fair</option>
                                        <option value="Poor">Poor</option>
                                        <option value="Damaged">Damaged</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Current Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="current_location" required placeholder="Where is this asset located?">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Detailed description of the asset"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Date</label>
                                    <input type="date" class="form-control" name="purchase_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Value (GHS)</label>
                                    <input type="number" class="form-control" name="purchase_value" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Related Case (Optional)</label>
                            <input type="text" class="form-control" name="case_id" placeholder="Enter case ID if related">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Register Asset
                        </button>
                        <a href="<?= url('/assets') ?>" class="btn btn-secondary">
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
    $('#assetForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/assets/store') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/assets/view/') ?>' + response.asset_id;
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
