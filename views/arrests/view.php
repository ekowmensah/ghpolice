<?php
$content = '
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Arrest Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="' . url('/dashboard') . '">Home</a></li>
                        <li class="breadcrumb-item"><a href="' . url('/arrests') . '">Arrests</a></li>
                        <li class="breadcrumb-item active">Details</li>
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
                                    <a href="' . url('/cases/view/' . $arrest['case_id']) . '">
                                        ' . sanitize($arrest['case_number']) . '
                                    </a>
                                </dd>

                                <dt class="col-sm-4">Arrest Date:</dt>
                                <dd class="col-sm-8">' . date('l, d F Y H:i', strtotime($arrest['arrest_date'])) . '</dd>

                                <dt class="col-sm-4">Arrest Type:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-' . ($arrest['arrest_type'] === 'With Warrant' ? 'success' : 'warning') . '">
                                        ' . sanitize($arrest['arrest_type']) . '
                                    </span>
                                </dd>
';

if ($arrest['warrant_number']) {
    $content .= '
                                <dt class="col-sm-4">Warrant Number:</dt>
                                <dd class="col-sm-8">' . sanitize($arrest['warrant_number']) . '</dd>
';
}

$content .= '
                                <dt class="col-sm-4">Arrest Location:</dt>
                                <dd class="col-sm-8">' . sanitize($arrest['arrest_location']) . '</dd>

                                <dt class="col-sm-4">Reason for Arrest:</dt>
                                <dd class="col-sm-8">' . sanitize($arrest['reason']) . '</dd>
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
                                <dd class="col-sm-8"><strong>' . sanitize($arrest['suspect_name']) . '</strong></dd>

                                <dt class="col-sm-4">Ghana Card:</dt>
                                <dd class="col-sm-8">' . sanitize($arrest['ghana_card_number'] ?? 'N/A') . '</dd>

                                <dt class="col-sm-4">Date of Birth:</dt>
                                <dd class="col-sm-8">' . ($arrest['date_of_birth'] ? date('d M Y', strtotime($arrest['date_of_birth'])) : 'N/A') . '</dd>

                                <dt class="col-sm-4">Contact:</dt>
                                <dd class="col-sm-8">' . sanitize($arrest['contact'] ?? 'N/A') . '</dd>

                                <dt class="col-sm-4">Address:</dt>
                                <dd class="col-sm-8">' . sanitize($arrest['address'] ?? 'N/A') . '</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Arresting Officer</h3>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>Name:</dt>
                                <dd>' . sanitize($arrest['arresting_officer_name']) . '</dd>

                                <dt>Rank:</dt>
                                <dd>' . sanitize($arrest['rank_name']) . '</dd>

                                <dt>Service Number:</dt>
                                <dd>' . sanitize($arrest['service_number']) . '</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Case Information</h3>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>Case Status:</dt>
                                <dd><span class="badge badge-info">' . sanitize($arrest['case_status']) . '</span></dd>

                                <dt>Description:</dt>
                                <dd>' . sanitize($arrest['case_description']) . '</dd>
                            </dl>
                            <a href="' . url('/cases/view/' . $arrest['case_id']) . '" class="btn btn-sm btn-info btn-block">
                                <i class="fas fa-folder-open"></i> View Full Case
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="' . url('/arrests') . '" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <a href="' . url('/custody/create?arrest_id=' . $arrest['id']) . '" class="btn btn-warning btn-block">
                                <i class="fas fa-lock"></i> Record Custody
                            </a>
                            <a href="' . url('/charges/create?case_id=' . $arrest['case_id'] . '&suspect_id=' . $arrest['suspect_id']) . '" class="btn btn-danger btn-block">
                                <i class="fas fa-gavel"></i> File Charge
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
';

$breadcrumbs = [
    ['title' => 'Arrests', 'url' => '/arrests'],
    ['title' => 'Details']
];

include __DIR__ . '/../layouts/main.php';
?>
