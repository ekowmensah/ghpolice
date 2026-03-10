<?php
$content = '
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Arrest Record</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/arrests') ?>">Arrests</a></li>
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
                    <h3 class="card-title">Edit Arrest Information</h3>
                </div>
                <form id="editArrestForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $arrest['id'] ?>">
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label>Arrest Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="arrest_date" 
                                   value="<?= date('Y-m-d\TH:i', strtotime($arrest['arrest_date'])) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Arrest Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="arrest_location" 
                                   value="<?= sanitize($arrest['arrest_location']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Arresting Officer <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="arresting_officer_id" required>
                                <?php foreach ($officers as $officer): ?>
                                <option value="<?= $officer['id'] ?>" <?= $officer['id'] == $arrest['arresting_officer_id'] ? 'selected' : '' ?>>
                                    <?= sanitize($officer['officer_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Arrest Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="arrest_type" required>
                                <option value="With Warrant" <?= $arrest['arrest_type'] == 'With Warrant' ? 'selected' : '' ?>>With Warrant</option>
                                <option value="Without Warrant" <?= $arrest['arrest_type'] == 'Without Warrant' ? 'selected' : '' ?>>Without Warrant</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Warrant Number</label>
                            <input type="text" class="form-control" name="warrant_number" 
                                   value="<?= sanitize($arrest['warrant_number'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Reason for Arrest <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="reason" rows="4" required><?= sanitize($arrest['reason']) ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Arrest
                        </button>
                        <a href="<?= url('/arrests/view/' . $arrest['id']) ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#editArrestForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= url('/arrests/update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = '<?= url('/arrests/view/' . $arrest['id']) ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Arrests', 'url' => '/arrests'],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
