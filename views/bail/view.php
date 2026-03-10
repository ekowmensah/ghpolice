<?php
$badgeClass = match($bail['bail_status']) {
    'Granted' => 'success',
    'Denied' => 'danger',
    'Revoked' => 'warning',
    default => 'secondary'
};

$content = '
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bail Information</h3>
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
                        <a href="' . url('/cases/view/' . $bail['case_id']) . '">
                            ' . sanitize($bail['case_number']) . '
                        </a>
                    </dd>
                    <dt class="col-sm-4">Bail Status:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-' . $badgeClass . ' badge-lg">
                            ' . sanitize($bail['bail_status']) . '
                        </span>
                    </dd>
                    <dt class="col-sm-4">Bail Date:</dt>
                    <dd class="col-sm-8">' . date('l, d F Y', strtotime($bail['bail_date'])) . '</dd>';

if ($bail['bail_amount']) {
    $content .= '
                    <dt class="col-sm-4">Bail Amount:</dt>
                    <dd class="col-sm-8"><strong>GHS ' . number_format($bail['bail_amount'], 2) . '</strong></dd>';
}

if ($bail['bail_conditions']) {
    $content .= '
                    <dt class="col-sm-4">Bail Conditions:</dt>
                    <dd class="col-sm-8">' . nl2br(sanitize($bail['bail_conditions'])) . '</dd>';
}

$content .= '
                    <dt class="col-sm-4">Approved By:</dt>
                    <dd class="col-sm-8">' . sanitize($bail['approved_by_name'] ?? 'Pending') . '</dd>';

if ($bail['bail_status'] === 'Revoked') {
    $content .= '
                    <dt class="col-sm-4">Revocation Reason:</dt>
                    <dd class="col-sm-8">' . sanitize($bail['revocation_reason']) . '</dd>
                    <dt class="col-sm-4">Revoked Date:</dt>
                    <dd class="col-sm-8">' . date('d M Y', strtotime($bail['revoked_date'])) . '</dd>';
}

$content .= '
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
                    <dd class="col-sm-8"><strong>' . sanitize($bail['suspect_name']) . '</strong></dd>
                    <dt class="col-sm-4">Ghana Card:</dt>
                    <dd class="col-sm-8">' . sanitize($bail['ghana_card_number'] ?? 'N/A') . '</dd>
                    <dt class="col-sm-4">Contact:</dt>
                    <dd class="col-sm-8">' . sanitize($bail['contact'] ?? 'N/A') . '</dd>
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
                    <dd><span class="badge badge-info">' . sanitize($bail['case_status']) . '</span></dd>
                    <dt>Description:</dt>
                    <dd>' . sanitize($bail['case_description']) . '</dd>
                </dl>
                <a href="' . url('/cases/view/' . $bail['case_id']) . '" class="btn btn-sm btn-info btn-block">
                    <i class="fas fa-folder-open"></i> View Full Case
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <a href="' . url('/bail') . '" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>';

if ($bail['bail_status'] === 'Granted') {
    $content .= '
                <button class="btn btn-warning btn-block" id="revokeBailBtn">
                    <i class="fas fa-ban"></i> Revoke Bail
                </button>';
}

$content .= '
            </div>
        </div>
    </div>
</div>';

$scripts = '
<script>
$("#revokeBailBtn").click(function() {
    Swal.fire({
        title: "Revoke Bail?",
        input: "textarea",
        inputLabel: "Reason for revocation",
        inputPlaceholder: "Enter reason...",
        showCancelButton: true,
        confirmButtonText: "Revoke",
        confirmButtonColor: "#dc3545",
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage("Reason is required");
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("' . url('/bail/revoke/' . $bail['id']) . '", {
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
    ['title' => 'Bail', 'url' => '/bail'],
    ['title' => 'Details']
];

include __DIR__ . '/../layouts/main.php';
?>
