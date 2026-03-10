<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <form id="bailForm">
            ' . csrf_field() . '
            <input type="hidden" name="case_id" value="' . $case['id'] . '">
            <input type="hidden" name="suspect_id" value="' . $suspect['id'] . '">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Bail Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Bail Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="bail_date" required>
                                </div>

                                <div class="form-group">
                                    <label>Bail Amount (GHS)</label>
                                    <input type="number" class="form-control" name="bail_amount" step="0.01" min="0" placeholder="Enter bail amount">
                                </div>

                                <div class="form-group">
                                    <label>Bail Conditions</label>
                                    <textarea class="form-control" name="bail_conditions" rows="4" placeholder="Enter bail conditions"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Approved By</label>
                                    <select class="form-control select2" name="approved_by">
                                        <option value="">Select Officer</option>
';

foreach ($officers as $officer) {
    $content .= '
                                        <option value="' . $officer['id'] . '">' . sanitize($officer['officer_name']) . '</option>';
}

$content .= '
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Grant Bail
                                </button>
                                <a href="' . url('/cases/view/' . $case['id']) . '" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Case Information</h3>
                            </div>
                            <div class="card-body">
                                <dl>
                                    <dt>Case Number:</dt>
                                    <dd>' . sanitize($case['case_number']) . '</dd>
                                    <dt>Description:</dt>
                                    <dd>' . sanitize($case['description']) . '</dd>
                                </dl>
                            </div>
                        </div>

                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Suspect Information</h3>
                            </div>
                            <div class="card-body">
                                <dl>
                                    <dt>Name:</dt>
                                    <dd>' . sanitize($suspect['first_name'] . ' ' . $suspect['last_name']) . '</dd>
                                    <dt>Ghana Card:</dt>
                                    <dd>' . sanitize($suspect['ghana_card_number'] ?? 'N/A') . '</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $(".select2").select2();

    $("#bailForm").submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "' . url('/bail/store') . '",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire("Success", response.message, "success").then(() => {
                        window.location.href = "' . url('/bail/view/') . '" + response.bail_id;
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
    ['title' => 'Grant Bail']
];

include __DIR__ . '/../layouts/main.php';
?>
