<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Patrol</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/patrol-logs">Patrol Logs</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Update Patrol Details</h3>
                </div>
                <form action="/patrol-logs/<?= $patrol['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="card-body">
                        <?php if (isset($_SESSION['errors'])): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($_SESSION['errors'] as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php unset($_SESSION['errors']); ?>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Patrol Type <span class="text-danger">*</span></label>
                                    <select name="patrol_type" class="form-control" required>
                                        <option value="Foot Patrol" <?= $patrol['patrol_type'] == 'Foot Patrol' ? 'selected' : '' ?>>Foot Patrol</option>
                                        <option value="Vehicle Patrol" <?= $patrol['patrol_type'] == 'Vehicle Patrol' ? 'selected' : '' ?>>Vehicle Patrol</option>
                                        <option value="Motorcycle Patrol" <?= $patrol['patrol_type'] == 'Motorcycle Patrol' ? 'selected' : '' ?>>Motorcycle Patrol</option>
                                        <option value="Bicycle Patrol" <?= $patrol['patrol_type'] == 'Bicycle Patrol' ? 'selected' : '' ?>>Bicycle Patrol</option>
                                        <option value="Community Patrol" <?= $patrol['patrol_type'] == 'Community Patrol' ? 'selected' : '' ?>>Community Patrol</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Patrol Area <span class="text-danger">*</span></label>
                                    <input type="text" name="patrol_area" class="form-control" 
                                           value="<?= htmlspecialchars($patrol['patrol_area']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="patrol_status" class="form-control">
                                        <option value="In Progress" <?= $patrol['patrol_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="Completed" <?= $patrol['patrol_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="Interrupted" <?= $patrol['patrol_status'] == 'Interrupted' ? 'selected' : '' ?>>Interrupted</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Time</label>
                                    <input type="datetime-local" name="end_time" class="form-control" 
                                           value="<?= $patrol['end_time'] ? date('Y-m-d\TH:i', strtotime($patrol['end_time'])) : '' ?>">
                                    <small class="form-text text-muted">Leave empty if patrol is still ongoing</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Patrol Summary Report</label>
                            <textarea name="report_summary" class="form-control" rows="5" 
                                      placeholder="Summary of patrol activities, observations, and outcomes"><?= htmlspecialchars($patrol['report_summary'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Patrol
                        </button>
                        <a href="/patrol-logs/<?= $patrol['id'] ?>" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
