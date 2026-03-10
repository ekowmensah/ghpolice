<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Export Data</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/reports">Reports</a></li>
                        <li class="breadcrumb-item active">Export</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-folder-open"></i> Export Cases</h3>
                        </div>
                        <div class="card-body">
                            <form action="/export/cases" method="GET">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-01') ?>">
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="Open">Open</option>
                                        <option value="Under Investigation">Under Investigation</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-download"></i> Export to CSV
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users"></i> Export Persons</h3>
                        </div>
                        <div class="card-body">
                            <p>Export all persons in the registry to CSV format.</p>
                            <a href="/export/persons" class="btn btn-success btn-block">
                                <i class="fas fa-download"></i> Export to CSV
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-shield"></i> Export Officers</h3>
                        </div>
                        <div class="card-body">
                            <p>Export all officers to CSV format.</p>
                            <a href="/export/officers" class="btn btn-info btn-block">
                                <i class="fas fa-download"></i> Export to CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-pdf"></i> PDF Reports</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="/export/pdf?type=cases" target="_blank" class="btn btn-danger btn-block">
                                <i class="fas fa-file-pdf"></i> Cases Report (PDF)
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/export/pdf?type=persons" target="_blank" class="btn btn-danger btn-block">
                                <i class="fas fa-file-pdf"></i> Persons Report (PDF)
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/export/pdf?type=officers" target="_blank" class="btn btn-danger btn-block">
                                <i class="fas fa-file-pdf"></i> Officers Report (PDF)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
