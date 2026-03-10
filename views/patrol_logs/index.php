<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Patrol Management</h3>
                    <div class="card-tools">
                        <a href="<?= url('/patrol-logs/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Start Patrol
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Station</label>
                                    <select name="station" class="form-control select2">
                                        <option value="">All Stations</option>
                                        <?php foreach ($stations as $station): ?>
                                            <option value="<?= $station['id'] ?>" 
                                                <?= $selected_station == $station['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($station['station_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="In Progress" <?= $selected_status == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="Completed" <?= $selected_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="Interrupted" <?= $selected_status == 'Interrupted' ? 'selected' : '' ?>>Interrupted</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="date" class="form-control" 
                                           value="<?= htmlspecialchars($selected_date ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($patrols)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No patrol logs found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Patrol Number</th>
                                        <th>Station</th>
                                        <th>Type</th>
                                        <th>Area</th>
                                        <th>Leader</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                        <th>Incidents</th>
                                        <th>Arrests</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patrols as $patrol): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('/patrol-logs/' . $patrol['id']) ?>">
                                                    <strong><?= htmlspecialchars($patrol['patrol_number']) ?></strong>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($patrol['station_name']) ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($patrol['patrol_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($patrol['patrol_area']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($patrol['leader_rank'] . ' ' . $patrol['leader_name']) ?>
                                            </td>
                                            <td><?= date('Y-m-d H:i', strtotime($patrol['start_time'])) ?></td>
                                            <td>
                                                <?= $patrol['end_time'] ? date('Y-m-d H:i', strtotime($patrol['end_time'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'In Progress' => 'warning',
                                                    'Completed' => 'success',
                                                    'Interrupted' => 'danger'
                                                ];
                                                $class = $statusClass[$patrol['patrol_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($patrol['patrol_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    <?= $patrol['incidents_reported'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    <?= $patrol['arrests_made'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= url('/patrol-logs/' . $patrol['id']) ?>" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($patrol['patrol_status'] == 'In Progress'): ?>
                                                    <button class="btn btn-sm btn-success complete-patrol" 
                                                            data-id="<?= $patrol['id'] ?>" title="Complete">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
$content = ob_get_clean();

$title = 'Patrol Logs';
$breadcrumbs = [
    ['title' => 'Patrol Logs']
];

include __DIR__ . '/../layouts/main.php';
?>

<script>
$(document).ready(function() {
    $('.select2').select2();
    
    $('.complete-patrol').click(function() {
        const patrolId = $(this).data('id');
        
        Swal.fire({
            title: 'Complete Patrol?',
            html: '<textarea id="report-summary" class="swal2-textarea" placeholder="Enter patrol summary report"></textarea>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Complete Patrol',
            preConfirm: () => {
                return document.getElementById('report-summary').value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `<?= url('/patrol-logs/') ?>${patrolId}/complete`,
                    method: 'POST',
                    data: {
                        csrf_token: '<?= csrf_token() ?>',
                        report_summary: result.value
                    },
                    success: function(response) {
                        Swal.fire('Completed!', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to complete patrol', 'error');
                    }
                });
            }
        });
    });
});
</script>
