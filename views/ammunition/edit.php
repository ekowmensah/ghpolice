<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Ammunition Stock</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/ammunition') ?>">Ammunition</a></li>
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
                    <h3 class="card-title">Edit Stock Details</h3>
                </div>
                <form id="editAmmunitionForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $stock['id'] ?>">
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label>Station <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="station_id" required>
                                <?php foreach ($stations as $station): ?>
                                <option value="<?= $station['id'] ?>" <?= $station['id'] == $stock['station_id'] ? 'selected' : '' ?>>
                                    <?= sanitize($station['station_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Ammunition Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="ammunition_type" required>
                                <option value="9mm" <?= $stock['ammunition_type'] == '9mm' ? 'selected' : '' ?>>9mm</option>
                                <option value=".38 Special" <?= $stock['ammunition_type'] == '.38 Special' ? 'selected' : '' ?>>.38 Special</option>
                                <option value=".45 ACP" <?= $stock['ammunition_type'] == '.45 ACP' ? 'selected' : '' ?>>.45 ACP</option>
                                <option value="5.56mm" <?= $stock['ammunition_type'] == '5.56mm' ? 'selected' : '' ?>>5.56mm</option>
                                <option value="7.62mm" <?= $stock['ammunition_type'] == '7.62mm' ? 'selected' : '' ?>>7.62mm</option>
                                <option value="12 Gauge" <?= $stock['ammunition_type'] == '12 Gauge' ? 'selected' : '' ?>>12 Gauge</option>
                                <option value="Other" <?= $stock['ammunition_type'] == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Caliber</label>
                            <input type="text" class="form-control" name="caliber" 
                                   value="<?= sanitize($stock['caliber'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Quantity in Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity_in_stock" min="0" 
                                   value="<?= $stock['quantity_in_stock'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Minimum Threshold <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="minimum_threshold" min="0" 
                                   value="<?= $stock['minimum_threshold'] ?>" required>
                            <small class="form-text text-muted">Alert will be triggered when stock falls below this level</small>
                        </div>

                        <div class="form-group">
                            <label>Storage Location</label>
                            <input type="text" class="form-control" name="storage_location" 
                                   value="<?= sanitize($stock['storage_location'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Stock
                        </button>
                        <a href="<?= url('/ammunition') ?>" class="btn btn-secondary">
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

    $('#editAmmunitionForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/ammunition/update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/ammunition') ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>
