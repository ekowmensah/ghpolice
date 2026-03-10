<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Start Patrol</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/patrol-logs">Patrol Logs</a></li>
                        <li class="breadcrumb-item active">Start Patrol</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Patrol Details</h3>
                </div>
                <form action="<?= url('/patrol-logs/store') ?>" method="POST">
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
                                    <label>Station <span class="text-danger">*</span></label>
                                    <select name="station_id" id="station_id" class="form-control select2" required>
                                        <option value="">Select Station</option>
                                        <?php foreach ($stations as $station): ?>
                                            <option value="<?= $station['id'] ?>" 
                                                <?= (old('station_id') ?? $selected_station) == $station['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($station['station_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Patrol Type <span class="text-danger">*</span></label>
                                    <select name="patrol_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="Foot Patrol" <?= old('patrol_type') == 'Foot Patrol' ? 'selected' : '' ?>>Foot Patrol</option>
                                        <option value="Vehicle Patrol" <?= old('patrol_type') == 'Vehicle Patrol' ? 'selected' : '' ?>>Vehicle Patrol</option>
                                        <option value="Motorcycle Patrol" <?= old('patrol_type') == 'Motorcycle Patrol' ? 'selected' : '' ?>>Motorcycle Patrol</option>
                                        <option value="Bicycle Patrol" <?= old('patrol_type') == 'Bicycle Patrol' ? 'selected' : '' ?>>Bicycle Patrol</option>
                                        <option value="Community Patrol" <?= old('patrol_type') == 'Community Patrol' ? 'selected' : '' ?>>Community Patrol</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Patrol Area <span class="text-danger">*</span></label>
                                    <input type="text" name="patrol_area" class="form-control" 
                                           value="<?= old('patrol_area') ?>" 
                                           placeholder="e.g., Downtown District, Market Area" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="start_time" class="form-control" 
                                           value="<?= old('start_time') ?? date('Y-m-d\TH:i') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Patrol Leader <span class="text-danger">*</span></label>
                                    <select name="patrol_leader_id" class="form-control select2" required>
                                        <option value="">Select Patrol Leader</option>
                                        <?php foreach ($officers as $officer): ?>
                                            <option value="<?= $officer['id'] ?>" 
                                                <?= old('patrol_leader_id') == $officer['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($officer['rank'] . ' ' . $officer['full_name'] . ' (' . $officer['service_number'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vehicle (Optional)</label>
                                    <select name="vehicle_id" class="form-control select2">
                                        <option value="">No Vehicle</option>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <option value="<?= $vehicle['id'] ?>" 
                                                <?= old('vehicle_id') == $vehicle['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($vehicle['vehicle_registration'] . ' - ' . $vehicle['vehicle_type']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Patrol Officers</label>
                            <select name="officer_ids[]" class="form-control select2" multiple>
                                <?php foreach ($officers as $officer): ?>
                                    <option value="<?= $officer['id'] ?>">
                                        <?= htmlspecialchars($officer['rank'] . ' ' . $officer['full_name'] . ' (' . $officer['service_number'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Select additional officers assigned to this patrol</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play"></i> Start Patrol
                        </button>
                        <a href="/patrol-logs" class="btn btn-default">
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
    
    $('#station_id').change(function() {
        const stationId = $(this).val();
        if (stationId) {
            window.location.href = `/patrol-logs/create?station=${stationId}`;
        }
    });
    
    $('select[name="patrol_type"]').change(function() {
        const vehicleSelect = $('select[name="vehicle_id"]');
        const patrolType = $(this).val();
        
        if (patrolType === 'Foot Patrol' || patrolType === 'Bicycle Patrol') {
            vehicleSelect.val('').prop('disabled', true);
        } else {
            vehicleSelect.prop('disabled', false);
        }
    });
});
</script>
