<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Charge Information</h3>
            </div>
            <form id="editChargeForm">
                ' . csrf_field() . '
                <input type="hidden" name="id" value="' . $charge['id'] . '">
                <div class="card-body">
                        <div class="form-group">
                            <label>Offence Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="offence_name" 
                                   value="' . sanitize($charge['offence_name']) . '" required>
                        </div>

                        <div class="form-group">
                            <label>Law Section</label>
                            <input type="text" class="form-control" name="law_section" 
                                   value="' . sanitize($charge['law_section'] ?? '') . '">
                        </div>

                        <div class="form-group">
                            <label>Charge Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="charge_date" 
                                   value="' . date('Y-m-d', strtotime($charge['charge_date'])) . '" required>
                        </div>

                        <div class="form-group">
                            <label>Charge Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="charge_status" required>
                                <option value="Pending" ' . ($charge['charge_status'] == 'Pending' ? 'selected' : '') . '>Pending</option>
                                <option value="Filed" ' . ($charge['charge_status'] == 'Filed' ? 'selected' : '') . '>Filed</option>
                                <option value="Withdrawn" ' . ($charge['charge_status'] == 'Withdrawn' ? 'selected' : '') . '>Withdrawn</option>
                                <option value="Dismissed" ' . ($charge['charge_status'] == 'Dismissed' ? 'selected' : '') . '>Dismissed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Charged By <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="charged_by" required>
';

foreach ($officers as $officer) {
    $selected = $officer['id'] == $charge['charged_by'] ? 'selected' : '';
    $content .= '
                                <option value="' . $officer['id'] . '" ' . $selected . '>
                                    ' . sanitize($officer['officer_name']) . '
                                </option>';
}

$content .= '
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Charge
                        </button>
                        <a href="' . url('/charges/view/' . $charge['id']) . '" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
            </form>
        </div>
    </div>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#editChargeForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "' . url('/charges/update') . '",
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = "' . url('/charges/view/' . $charge['id']) . '";
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
    ['title' => 'Charges', 'url' => '/charges'],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
