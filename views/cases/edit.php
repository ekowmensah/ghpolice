<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Case - ' . sanitize($case['case_number']) . '</h3>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="case_type">Case Type</label>
                                <select class="form-control" id="case_type" name="case_type">
                                    <option value="Complaint" ' . ($case['case_type'] === 'Complaint' ? 'selected' : '') . '>Complaint</option>
                                    <option value="Incident Report" ' . ($case['case_type'] === 'Incident Report' ? 'selected' : '') . '>Incident Report</option>
                                    <option value="Investigation" ' . ($case['case_type'] === 'Investigation' ? 'selected' : '') . '>Investigation</option>
                                    <option value="Intelligence" ' . ($case['case_type'] === 'Intelligence' ? 'selected' : '') . '>Intelligence</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="case_priority">Priority</label>
                                <select class="form-control" id="case_priority" name="case_priority">
                                    <option value="Low" ' . ($case['case_priority'] === 'Low' ? 'selected' : '') . '>Low</option>
                                    <option value="Medium" ' . ($case['case_priority'] === 'Medium' ? 'selected' : '') . '>Medium</option>
                                    <option value="High" ' . ($case['case_priority'] === 'High' ? 'selected' : '') . '>High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="Open" ' . ($case['status'] === 'Open' ? 'selected' : '') . '>Open</option>
                                    <option value="Under Investigation" ' . ($case['status'] === 'Under Investigation' ? 'selected' : '') . '>Under Investigation</option>
                                    <option value="Closed" ' . ($case['status'] === 'Closed' ? 'selected' : '') . '>Closed</option>
                                    <option value="Suspended" ' . ($case['status'] === 'Suspended' ? 'selected' : '') . '>Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="location">Incident Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="' . sanitize($case['location'] ?? '') . '">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Case Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="6" required>' . sanitize($case['description']) . '</textarea>
                                ' . (isset($_SESSION['errors']['description']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['description']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Case
                    </button>
                    <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number'], 'url' => '/cases/' . $case['id']],
    ['title' => 'Edit']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
