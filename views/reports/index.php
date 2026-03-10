<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Reports & Analytics Dashboard</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>' . ($stats['total_cases'] ?? 0) . '</h3>
                                <p>Total Cases</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            <a href="' . url('/reports/cases') . '" class="small-box-footer">
                                View Details <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>' . ($stats['closed_cases'] ?? 0) . '</h3>
                                <p>Closed Cases</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>' . ($stats['open_cases'] ?? 0) . '</h3>
                                <p>Open Cases</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>' . ($stats['investigating_cases'] ?? 0) . '</h3>
                                <p>Under Investigation</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>' . ($stats['total_persons'] ?? 0) . '</h3>
                                <p>Registered Persons</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>' . ($stats['persons_with_records'] ?? 0) . '</h3>
                                <p>With Criminal Records</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-secret"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>' . ($stats['active_officers'] ?? 0) . '</h3>
                                <p>Active Officers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>' . ($stats['total_evidence'] ?? 0) . '</h3>
                                <p>Evidence Items</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Quick Reports</h3>
                            </div>
                            <div class="card-body">
                                <a href="' . url('/reports/cases') . '" class="btn btn-primary btn-block">
                                    <i class="fas fa-folder"></i> Case Reports
                                </a>
                                <a href="' . url('/reports/statistics') . '" class="btn btn-info btn-block">
                                    <i class="fas fa-chart-line"></i> Statistics Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">System Information</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Last Updated:</strong> ' . date('d M Y H:i') . '</p>
                                <p><strong>System Version:</strong> 1.0.0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Reports']
];

include __DIR__ . '/../layouts/main.php';
?>
