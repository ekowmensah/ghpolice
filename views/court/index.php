<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Court Proceedings - ' . sanitize($case['case_number']) . '</h3>
                <div class="card-tools">
                    <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Case
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Court Proceedings -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Court Proceedings</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addProceedingModal">
                        <i class="fas fa-plus"></i> Add Proceeding
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($proceedings)) {
    foreach ($proceedings as $proceeding) {
        $content .= '
                <div class="card mb-2">
                    <div class="card-body">
                        <h6>' . sanitize($proceeding['court_name']) . '</h6>
                        <p><strong>Hearing Type:</strong> ' . sanitize($proceeding['hearing_type']) . '</p>
                        <p><strong>Date:</strong> ' . format_date($proceeding['hearing_date'], 'd M Y H:i') . '</p>
                        <p><strong>Judge:</strong> ' . sanitize($proceeding['judge_name'] ?? 'N/A') . '</p>';
        
        if ($proceeding['outcome']) {
            $content .= '<p><strong>Outcome:</strong> ' . sanitize($proceeding['outcome']) . '</p>';
        }
        
        if ($proceeding['next_hearing_date']) {
            $content .= '<p class="text-info"><strong>Next Hearing:</strong> ' . format_date($proceeding['next_hearing_date'], 'd M Y H:i') . '</p>';
        }
        
        $content .= '
                        <small class="text-muted">Recorded by ' . sanitize($proceeding['recorded_by_name']) . '</small>
                    </div>
                </div>';
    }
} else {
    $content .= '<p class="text-muted">No court proceedings recorded</p>';
}

$content .= '
            </div>
        </div>

        <!-- Warrants -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Warrants</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#issueWarrantModal">
                        <i class="fas fa-file-alt"></i> Issue Warrant
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($warrants)) {
    $content .= '
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Suspect</th>
                            <th>Issue Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($warrants as $warrant) {
        $statusClass = $warrant['status'] === 'Active' ? 'badge-danger' : 'badge-secondary';
        $content .= '
                        <tr>
                            <td>' . sanitize($warrant['warrant_type']) . '</td>
                            <td>' . sanitize($warrant['suspect_name'] ?? 'N/A') . '</td>
                            <td>' . format_date($warrant['issue_date'], 'd M Y') . '</td>
                            <td><span class="badge ' . $statusClass . '">' . sanitize($warrant['status']) . '</span></td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No warrants issued</p>';
}

$content .= '
            </div>
        </div>
    </div>

    <!-- Charges & Bail -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Charges</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#fileChargesModal">
                        <i class="fas fa-balance-scale"></i> File Charges
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($charges)) {
    foreach ($charges as $charge) {
        $content .= '
                <div class="card mb-2 border-left-danger">
                    <div class="card-body">
                        <h6>' . sanitize($charge['charge_description']) . '</h6>
                        <p><strong>Type:</strong> ' . sanitize($charge['charge_type']) . '</p>
                        <p><strong>Suspect:</strong> ' . sanitize($charge['suspect_name'] ?? 'N/A') . '</p>
                        <p><strong>Filed:</strong> ' . format_date($charge['filed_date'], 'd M Y') . '</p>';
        
        if ($charge['statute_reference']) {
            $content .= '<p><small><strong>Statute:</strong> ' . sanitize($charge['statute_reference']) . '</small></p>';
        }
        
        $content .= '
                        <span class="badge badge-' . ($charge['status'] === 'Filed' ? 'warning' : 'success') . '">' . sanitize($charge['status']) . '</span>
                    </div>
                </div>';
    }
} else {
    $content .= '<p class="text-muted">No charges filed</p>';
}

$content .= '
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bail Records</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#recordBailModal">
                        <i class="fas fa-hand-holding-usd"></i> Record Bail
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($bail)) {
    $content .= '
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Suspect</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($bail as $record) {
        $content .= '
                        <tr>
                            <td>' . sanitize($record['suspect_name'] ?? 'N/A') . '</td>
                            <td>GH₵ ' . number_format($record['bail_amount'], 2) . '</td>
                            <td>' . format_date($record['granted_date'], 'd M Y') . '</td>
                            <td><span class="badge badge-success">' . sanitize($record['status']) . '</span></td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No bail records</p>';
}

$content .= '
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="addProceedingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Court Proceeding</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/court/proceedings') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Court Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="court_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hearing Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="hearing_type" required>
                                    <option value="Arraignment">Arraignment</option>
                                    <option value="Bail Hearing">Bail Hearing</option>
                                    <option value="Trial">Trial</option>
                                    <option value="Sentencing">Sentencing</option>
                                    <option value="Appeal">Appeal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hearing Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="hearing_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Judge Name</label>
                                <input type="text" class="form-control" name="judge_name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Outcome</label>
                                <input type="text" class="form-control" name="outcome">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Next Hearing Date</label>
                                <input type="datetime-local" class="form-control" name="next_hearing_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Proceeding</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="fileChargesModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">File Charges</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/court/charges') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="form-group">
                        <label>Suspect ID</label>
                        <input type="number" class="form-control" name="suspect_id">
                    </div>
                    <div class="form-group">
                        <label>Charge Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="charge_type" required>
                            <option value="Felony">Felony</option>
                            <option value="Misdemeanor">Misdemeanor</option>
                            <option value="Infraction">Infraction</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Charge Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="charge_description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Statute Reference</label>
                        <input type="text" class="form-control" name="statute_reference">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">File Charges</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="issueWarrantModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Issue Warrant</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/court/warrants') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="form-group">
                        <label>Suspect ID</label>
                        <input type="number" class="form-control" name="suspect_id">
                    </div>
                    <div class="form-group">
                        <label>Warrant Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="warrant_type" required>
                            <option value="Arrest Warrant">Arrest Warrant</option>
                            <option value="Search Warrant">Search Warrant</option>
                            <option value="Bench Warrant">Bench Warrant</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Issued By <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="issued_by" required>
                    </div>
                    <div class="form-group">
                        <label>Warrant Details <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="warrant_details" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Issue Warrant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="recordBailModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Record Bail</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/court/bail') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="form-group">
                        <label>Suspect ID</label>
                        <input type="number" class="form-control" name="suspect_id">
                    </div>
                    <div class="form-group">
                        <label>Bail Amount (GH₵) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="bail_amount" required>
                    </div>
                    <div class="form-group">
                        <label>Granted By <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="granted_by" required>
                    </div>
                    <div class="form-group">
                        <label>Bail Conditions</label>
                        <textarea class="form-control" name="bail_conditions" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Bail</button>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number'], 'url' => '/cases/' . $case['id']],
    ['title' => 'Court']
];

include __DIR__ . '/../layouts/main.php';
?>
