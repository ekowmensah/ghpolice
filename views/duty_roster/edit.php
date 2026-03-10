<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Duty Assignment</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/duty-roster">Duty Roster</a></li>
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
                    <h3 class="card-title">Update Duty Details</h3>
                </div>
                <form action="/duty-roster/<?= $roster['id'] ?>" method="POST">
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
                                    <label>Shift <span class="text-danger">*</span></label>
                                    <select name="shift_id" class="form-control" required>
                                        <?php foreach ($shifts as $shift): ?>
                                            <option value="<?= $shift['id'] ?>" 
                                                <?= $roster['shift_id'] == $shift['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($shift['shift_name'] . ' (' . 
                                                    date('H:i', strtotime($shift['start_time'])) . ' - ' . 
                                                    date('H:i', strtotime($shift['end_time'])) . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Duty Type</label>
                                    <select name="duty_type" class="form-control">
                                        <option value="Regular" <?= $roster['duty_type'] == 'Regular' ? 'selected' : '' ?>>Regular</option>
                                        <option value="Overtime" <?= $roster['duty_type'] == 'Overtime' ? 'selected' : '' ?>>Overtime</option>
                                        <option value="Special Assignment" <?= $roster['duty_type'] == 'Special Assignment' ? 'selected' : '' ?>>Special Assignment</option>
                                        <option value="Court Duty" <?= $roster['duty_type'] == 'Court Duty' ? 'selected' : '' ?>>Court Duty</option>
                                        <option value="Training" <?= $roster['duty_type'] == 'Training' ? 'selected' : '' ?>>Training</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Duty Location</label>
                                    <input type="text" name="duty_location" class="form-control" 
                                           value="<?= htmlspecialchars($roster['duty_location'] ?? '') ?>" 
                                           placeholder="Specific location or area">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Scheduled" <?= $roster['status'] == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                        <option value="On Duty" <?= $roster['status'] == 'On Duty' ? 'selected' : '' ?>>On Duty</option>
                                        <option value="Completed" <?= $roster['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="Cancelled" <?= $roster['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Supervisor</label>
                            <select name="supervisor_id" class="form-control select2">
                                <option value="">Select Supervisor (Optional)</option>
                                <?php foreach ($officers as $officer): ?>
                                    <option value="<?= $officer['id'] ?>" 
                                        <?= $roster['supervisor_id'] == $officer['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($officer['rank'] . ' ' . $officer['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Additional notes or instructions"><?= htmlspecialchars($roster['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Duty
                        </button>
                        <a href="/duty-roster" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
