<?php
$badgeClass = match($charge['charge_status']) {
    'Pending' => 'warning',
    'Filed' => 'success',
    'Withdrawn' => 'danger',
    'Dismissed' => 'secondary',
    default => 'info'
};

$content = '
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Charge Information</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Case Number:</dt>
                    <dd class="col-sm-8">
                        <a href="' . url('/cases/view/' . $charge['case_id']) . '">
                            ' . sanitize($charge['case_number']) . '
                        </a>
                    </dd>
                    <dt class="col-sm-4">Charge Status:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-' . $badgeClass . ' badge-lg">
                            ' . sanitize($charge['charge_status']) . '
                        </span>
                    </dd>
                    <dt class="col-sm-4">Offence Name:</dt>
                    <dd class="col-sm-8"><strong>' . sanitize($charge['offence_name']) . '</strong></dd>
                    <dt class="col-sm-4">Law Section:</dt>
                    <dd class="col-sm-8">' . sanitize($charge['law_section'] ?? 'N/A') . '</dd>
                    <dt class="col-sm-4">Charge Date:</dt>
                    <dd class="col-sm-8">' . date('l, d F Y', strtotime($charge['charge_date'])) . '</dd>
                    <dt class="col-sm-4">Charged By:</dt>
                    <dd class="col-sm-8">' . sanitize($charge['charged_by_name']) . '</dd>
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Suspect Information</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name:</dt>
                    <dd class="col-sm-8"><strong>' . sanitize($charge['suspect_name']) . '</strong></dd>
                    <dt class="col-sm-4">Ghana Card:</dt>
                    <dd class="col-sm-8">' . sanitize($charge['ghana_card_number'] ?? 'N/A') . '</dd>
                    <dt class="col-sm-4">Contact:</dt>
                    <dd class="col-sm-8">' . sanitize($charge['contact'] ?? 'N/A') . '</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Case Information</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Case Status:</dt>
                    <dd><span class="badge badge-info">' . sanitize($charge['case_status']) . '</span></dd>
                    <dt>Description:</dt>
                    <dd>' . sanitize($charge['case_description']) . '</dd>
                </dl>
                <a href="' . url('/cases/view/' . $charge['case_id']) . '" class="btn btn-sm btn-info btn-block">
                    <i class="fas fa-folder-open"></i> View Full Case
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <a href="' . url('/charges') . '" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>';

if ($charge['charge_status'] === 'Pending') {
    $content .= '
                <button class="btn btn-success btn-block" id="fileChargeBtn">
                    <i class="fas fa-file"></i> File Charge
                </button>';
}

if ($charge['charge_status'] === 'Filed') {
    $content .= '
                <button class="btn btn-danger btn-block" id="withdrawChargeBtn">
                    <i class="fas fa-times"></i> Withdraw Charge
                </button>';
}

$content .= '
            </div>
        </div>
    </div>
</div>';

$scripts = '
<script>
$("#fileChargeBtn").click(function() {
    Swal.fire({
        title: "File Charge in Court?",
        text: "This will officially file the charge",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "File Charge",
        confirmButtonColor: "#28a745"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("' . url('/charges/file/' . $charge['id']) . '", {
                csrf_token: "' . csrf_token() . '"
            }, function(response) {
                if (response.success) {
                    Swal.fire("Success", response.message, "success").then(() => location.reload());
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            });
        }
    });
});

$("#withdrawChargeBtn").click(function() {
    Swal.fire({
        title: "Withdraw Charge?",
        input: "textarea",
        inputLabel: "Reason for withdrawal",
        inputPlaceholder: "Enter reason...",
        showCancelButton: true,
        confirmButtonText: "Withdraw",
        confirmButtonColor: "#dc3545",
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage("Reason is required");
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("' . url('/charges/withdraw/' . $charge['id']) . '", {
                csrf_token: "' . csrf_token() . '",
                reason: result.value
            }, function(response) {
                if (response.success) {
                    Swal.fire("Success", response.message, "success").then(() => location.reload());
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            });
        }
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Charges', 'url' => '/charges'],
    ['title' => 'Details']
];

include __DIR__ . '/../layouts/main.php';
?>
