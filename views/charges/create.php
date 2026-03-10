<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <form id="chargeForm">
            ' . csrf_field() . '
            <input type="hidden" name="case_id" value="' . $case['id'] . '">';

if ($suspect) {
    $content .= '
            <input type="hidden" name="suspect_id" value="' . $suspect['id'] . '">';
}

$content .= '
            <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Charge Details</h3>
                            </div>
                            <div class="card-body">';

if (!$suspect) {
    $content .= '
                                <div class="form-group">
                                    <label>Select Suspect <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="suspect_id" required>
                                        <option value="">Select Suspect</option>';
    foreach ($suspects as $s) {
        $suspectName = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
        $content .= '
                                        <option value="' . $s['id'] . '">' . sanitize($suspectName) . '</option>';
    }
    $content .= '
                                    </select>
                                </div>';
}

$content .= '
                                <div class="form-group">
                                    <label>Offence Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="offence_name" required placeholder="Enter offence name">
                                </div>

                                <div class="form-group">
                                    <label>Law Section</label>
                                    <input type="text" class="form-control" name="law_section" placeholder="e.g., Section 123 of Criminal Code">
                                </div>

                                <div class="form-group">
                                    <label>Charge Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="charge_date" required>
                                </div>

                                <div class="form-group">
                                    <label>Charged By <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="charged_by" required>
                                        <option value="">Select Officer</option>';

// Get officers list
$db = \App\Config\Database::getConnection();
$stmt = $db->query("SELECT o.id, CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name, ' - ', pr.rank_name) as officer_name FROM officers o JOIN police_ranks pr ON o.rank_id = pr.id WHERE o.employment_status = 'Active' ORDER BY o.first_name");
$officers = $stmt->fetchAll();

foreach ($officers as $officer) {
    $content .= '
                                        <option value="' . $officer['id'] . '">' . sanitize($officer['officer_name']) . '</option>';
}

$content .= '
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-gavel"></i> File Charge
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

';

if ($suspect) {
    $content .= '
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
                        </div>';
}

$content .= '
                    </div>
                </div>
        </form>
    </div>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $(".select2").select2();

    $("#chargeForm").submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "' . url('/charges/store') . '",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire("Success", response.message, "success").then(() => {
                        window.location.href = "' . url('/charges/view/') . '" + response.charge_id;
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
    ['title' => 'Charges', 'url' => '/charges'],
    ['title' => 'File Charge']
];

include __DIR__ . '/../layouts/main.php';
?>
