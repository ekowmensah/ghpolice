<?php
$content = '
        <div class="container-fluid">
            <div class="card">
                <form action="' . url('/intelligence/reports/store') . '" method="POST">
                    <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Report Type <span class="text-danger">*</span></label>
                                    <select name="report_type" class="form-control" required>
                                        <option value="Strategic">Strategic</option>
                                        <option value="Tactical">Tactical</option>
                                        <option value="Operational" selected>Operational</option>
                                        <option value="Crime Pattern">Crime Pattern</option>
                                        <option value="Threat Assessment">Threat Assessment</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Classification <span class="text-danger">*</span></label>
                                    <select name="classification" class="form-control" required>
                                        <option value="Unclassified">Unclassified</option>
                                        <option value="Confidential" selected>Confidential</option>
                                        <option value="Secret">Secret</option>
                                        <option value="Top Secret">Top Secret</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Report Date</label>
                            <input type="date" name="report_date" class="form-control" value="' . date('Y-m-d') . '">
                        </div>

                        <div class="form-group">
                            <label>Summary <span class="text-danger">*</span></label>
                            <textarea name="summary" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Detailed Analysis</label>
                            <textarea name="detailed_analysis" class="form-control" rows="6"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Sources</label>
                            <textarea name="sources" class="form-control" rows="3" placeholder="List intelligence sources"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Recommendations</label>
                            <textarea name="recommendations" class="form-control" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Report
                        </button>
                        <a href="' . url('/intelligence/reports') . '" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>';

$title = 'Create Intelligence Report';
$breadcrumbs = [
    ['title' => 'Intelligence', 'url' => '/intelligence'],
    ['title' => 'Reports', 'url' => '/intelligence/reports'],
    ['title' => 'Create']
];

include __DIR__ . '/../layouts/main.php';
?>
