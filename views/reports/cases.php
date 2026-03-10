<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-folder"></i> Case Reports</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="' . url('/reports/cases') . '" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="' . ($filters['start_date'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" class="form-control" name="end_date" value="' . ($filters['end_date'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status">
                                    <option value="">All</option>
                                    <option value="Open" ' . (($filters['status'] ?? '') === 'Open' ? 'selected' : '') . '>Open</option>
                                    <option value="Under Investigation" ' . (($filters['status'] ?? '') === 'Under Investigation' ? 'selected' : '') . '>Under Investigation</option>
                                    <option value="Closed" ' . (($filters['status'] ?? '') === 'Closed' ? 'selected' : '') . '>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Station</label>
                                <select class="form-control" name="station_id">
                                    <option value="">All</option>';

foreach ($stations as $station) {
    $selected = ($filters['station_id'] ?? '') == $station['id'] ? 'selected' : '';
    $content .= '<option value="' . $station['id'] . '" ' . $selected . '>' . sanitize($station['station_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Generate Report</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>' . ($stats['total'] ?? 0) . '</h3>
                                <p>Total Cases</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-folder"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>' . ($stats['open'] ?? 0) . '</h3>
                                <p>Open</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>' . ($stats['investigating'] ?? 0) . '</h3>
                                <p>Under Investigation</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>' . ($stats['closed'] ?? 0) . '</h3>
                                <p>Closed</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">By Priority</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>High:</strong> ' . ($stats['high_priority'] ?? 0) . '</p>
                                <p><strong>Medium:</strong> ' . ($stats['medium_priority'] ?? 0) . '</p>
                                <p><strong>Low:</strong> ' . ($stats['low_priority'] ?? 0) . '</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Report Period</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>From:</strong> ' . format_date($filters['start_date'], 'd M Y') . '</p>
                                <p><strong>To:</strong> ' . format_date($filters['end_date'], 'd M Y') . '</p>
                                <button class="btn btn-success" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Reports', 'url' => '/reports'],
    ['title' => 'Case Reports']
];

include __DIR__ . '/../layouts/main.php';
?>
