<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="card">
                <form action="<?= url('/intelligence/bulletins/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bulletin Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bulletin_number" class="form-control" required placeholder="e.g., BUL-2025-001">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bulletin Type <span class="text-danger">*</span></label>
                                    <select name="bulletin_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="Crime Alert">Crime Alert</option>
                                        <option value="Wanted Person">Wanted Person</option>
                                        <option value="Stolen Vehicle">Stolen Vehicle</option>
                                        <option value="Missing Person">Missing Person</option>
                                        <option value="Public Safety">Public Safety</option>
                                        <option value="Operational">Operational</option>
                                        <option value="Intelligence Update">Intelligence Update</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priority <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-control" required>
                                        <option value="Routine">Routine</option>
                                        <option value="Priority">Priority</option>
                                        <option value="Urgent">Urgent</option>
                                        <option value="Emergency">Emergency</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Target Audience <span class="text-danger">*</span></label>
                                    <select name="target_audience" class="form-control" required>
                                        <option value="All Stations" selected>All Stations</option>
                                        <option value="Regional">Regional</option>
                                        <option value="Divisional">Divisional</option>
                                        <option value="District">District</option>
                                        <option value="Specific Stations">Specific Stations</option>
                                        <option value="Public">Public</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Bulletin Content <span class="text-danger">*</span></label>
                            <textarea name="bulletin_content" class="form-control" rows="6" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Action Required</label>
                            <textarea name="action_required" class="form-control" rows="3" placeholder="Specify any actions officers should take"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valid From <span class="text-danger">*</span></label>
                                    <input type="date" name="valid_from" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valid Until</label>
                                    <input type="date" name="valid_until" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Draft">Draft</option>
                                        <option value="Active" selected>Active</option>
                                        <option value="Expired">Expired</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Public Bulletin</label>
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_public" name="is_public" value="1">
                                        <label class="custom-control-label" for="is_public">Make this bulletin public</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-bullhorn"></i> Issue Bulletin
                        </button>
                        <a href="<?= url('/intelligence/bulletins') ?>" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
<?php
$content = ob_get_clean();

$title = 'Create Intelligence Bulletin';
$breadcrumbs = [
    ['title' => 'Intelligence', 'url' => '/intelligence'],
    ['title' => 'Bulletins', 'url' => '/intelligence/bulletins'],
    ['title' => 'Create']
];

include __DIR__ . '/../layouts/main.php';
?>
