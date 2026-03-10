<?php
$content = '
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Record Arrest</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="' . url('/dashboard') . '">Home</a></li>
                        <li class="breadcrumb-item"><a href="' . url('/arrests') . '">Arrests</a></li>
                        <li class="breadcrumb-item active">Record</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Arrest Information</h3>
                        </div>
                        <form id="arrestForm">
                            ' . csrf_field() . '
                            <input type="hidden" name="case_id" value="' . $case['id'] . '">';

if ($suspect) {
    $content .= '
                            <input type="hidden" name="suspect_id" value="' . $suspect['id'] . '">';
} else {
    $content .= '
                            <div class="card-body">
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
                                </div>
                            </div>';
}

$content .= '
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Arrest Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="arrest_date" required>
                                </div>

                                <div class="form-group">
                                    <label>Arrest Location <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="arrest_location" required placeholder="Enter arrest location">
                                </div>

                                <div class="form-group">
                                    <label>Arresting Officer <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="arresting_officer_id" required>
                                        <option value="">Select Officer</option>';

foreach ($officers as $officer) {
    $content .= '
                                        <option value="' . $officer['id'] . '">' . sanitize($officer['officer_name']) . '</option>';
}

$content .= '
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Arrest Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="arrest_type" required>
                                        <option value="With Warrant">With Warrant</option>
                                        <option value="Without Warrant">Without Warrant</option>
                                    </select>
                                </div>

                                <div class="form-group" id="warrantNumberGroup" style="display:none;">
                                    <label>Warrant Number</label>
                                    <input type="text" class="form-control" name="warrant_number" placeholder="Enter warrant number">
                                </div>

                                <div class="form-group">
                                    <label>Reason for Arrest <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="reason" rows="4" required placeholder="Enter reason for arrest"></textarea>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Record Arrest
                                </button>
                                <a href="' . url('/cases/view/' . $case['id']) . '" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
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
                    </div>';

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
        </div>
    </section>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $(\".select2\").select2();
    
    $(\"select[name=arrest_type]\").change(function() {
        if ($(this).val() === \"With Warrant\") {
            $(\"#warrantNumberGroup\").show();
            $(\"input[name=warrant_number]\").attr(\"required\", true);
        } else {
            $(\"#warrantNumberGroup\").hide();
            $(\"input[name=warrant_number]\").attr(\"required\", false);
        }
    });

    $(\"#arrestForm\").submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: \"' . url('/arrests/store') . '\",
            method: \"POST\",
            data: $(this).serialize(),
            dataType: \"json\",
            success: function(response) {
                if (response.success) {
                    Swal.fire(\"Success\", response.message, \"success\").then(() => {
                        window.location.href = \"' . url('/arrests/view/') . '\" + response.arrest_id;
                    });
                } else {
                    Swal.fire(\"Error\", response.message, \"error\");
                }
            },
            error: function() {
                Swal.fire(\"Error\", \"Failed to record arrest\", \"error\");
            }
        });
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Arrests', 'url' => '/arrests'],
    ['title' => 'Record']
];

include __DIR__ . '/../layouts/main.php';
?>
