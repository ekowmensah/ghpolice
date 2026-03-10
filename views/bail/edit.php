<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Bail Record</h3>
            </div>
            <form id="editBailForm">
                ' . csrf_field() . '
                <input type="hidden" name="id" value="' . $bail['id'] . '">
        <div class="container-fluid">
            <div class="row mb-2">
                    
                <div class="card-body">
                    <div class="form-group">
                        <label>Bail Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="bail_date" 
                               value="' . date('Y-m-d', strtotime($bail['bail_date'])) . '" required>
                        </div>

                        <div class="form-group">
                        <label>Bail Status <span class="text-danger">*</span></label>
                        <select class="form-control" name="bail_status" required>
                            <option value="Granted" ' . ($bail['bail_status'] == 'Granted' ? 'selected' : '') . '>Granted</option>
                            <option value="Denied" ' . ($bail['bail_status'] == 'Denied' ? 'selected' : '') . '>Denied</option>
                            <option value="Revoked" ' . ($bail['bail_status'] == 'Revoked' ? 'selected' : '') . '>Revoked</option>
                        </select>
                        </div>

                        <div class="form-group">
                            <label>Bail Amount (GHS)</label>
                            <input type="number" class="form-control" name="bail_amount" step="0.01" 
                                   value="' . $bail['bail_amount'] . '">
                        </div>

                        <div class="form-group">
                            <label>Bail Conditions</label>
                        <textarea class="form-control" name="bail_conditions" rows="4">' . sanitize($bail['bail_conditions'] ?? '') . '</textarea>
                        </div>

                        <div class="form-group">
                            <label>Approved By</label>
                            <select class="form-control select2" name="approved_by">
                                <option value="">Select Officer</option>
';

foreach ($officers as $officer) {
    $selected = $officer['id'] == $bail['approved_by'] ? 'selected' : '';
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
                        <i class="fas fa-save"></i> Update Bail
                    </button>
                    <a href="' . url('/bail/view/' . $bail['id']) . '" class="btn btn-secondary">
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
    $(".select2").select2();

    $("#editBailForm").submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "' . url('/bail/update') . '",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire("Success", response.message, "success").then(() => {
                        window.location.href = "' . url('/bail/view/' . $bail['id']) . '";
                    });
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            }
        });
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Bail', 'url' => '/bail'],
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
