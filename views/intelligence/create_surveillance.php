<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="card">
                <form action="<?= url('/intelligence/surveillance/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Code <span class="text-danger">*</span></label>
                                    <input type="text" name="operation_code" class="form-control" required placeholder="e.g., SUR-2025-001">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Name <span class="text-danger">*</span></label>
                                    <input type="text" name="operation_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Surveillance Type <span class="text-danger">*</span></label>
                                    <select name="surveillance_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="Physical">Physical</option>
                                        <option value="Electronic">Electronic</option>
                                        <option value="Aerial">Aerial</option>
                                        <option value="Vehicle">Vehicle</option>
                                        <option value="Covert">Covert</option>
                                        <option value="Overt">Overt</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Target Type <span class="text-danger">*</span></label>
                                    <select name="target_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="Person">Person</option>
                                        <option value="Location">Location</option>
                                        <option value="Vehicle">Vehicle</option>
                                        <option value="Organization">Organization</option>
                                        <option value="Event">Event</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Target Description <span class="text-danger">*</span></label>
                            <textarea name="target_description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Target Location</label>
                            <input type="text" name="target_location" class="form-control">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Operation Commander <span class="text-danger">*</span></label>
                                    <select name="operation_commander_id" class="form-control" required>
                                        <option value="">Select Officer</option>
                                        <?php foreach ($officers as $officer): ?>
                                            <option value="<?= $officer['id'] ?>">
                                                <?= htmlspecialchars($officer['full_name']) ?> (<?= htmlspecialchars($officer['service_number']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Authorization Level <span class="text-danger">*</span></label>
                                    <select name="authorization_level" class="form-control" required>
                                        <option value="">Select Level</option>
                                        <option value="Station">Station</option>
                                        <option value="District">District</option>
                                        <option value="Division">Division</option>
                                        <option value="Region">Region</option>
                                        <option value="Court Order">Court Order</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="start_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="datetime-local" name="end_date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Authorization Reference</label>
                            <input type="text" name="authorization_reference" class="form-control" placeholder="e.g., Court Order #12345">
                        </div>

                        <div class="form-group">
                            <label>Objectives <span class="text-danger">*</span></label>
                            <textarea name="objectives" class="form-control" rows="4" required placeholder="Describe the objectives of this surveillance operation"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Operation Status</label>
                            <select name="operation_status" class="form-control">
                                <option value="Planned" selected>Planned</option>
                                <option value="Active">Active</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Completed">Completed</option>
                                <option value="Aborted">Aborted</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Create Operation
                        </button>
                        <a href="<?= url('/intelligence/surveillance') ?>" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
<?php
$content = ob_get_clean();

$title = 'Create Surveillance Operation';
$breadcrumbs = [
    ['title' => 'Intelligence', 'url' => '/intelligence'],
    ['title' => 'Surveillance', 'url' => '/intelligence/surveillance'],
    ['title' => 'Create']
];

include __DIR__ . '/../layouts/main.php';
?>
