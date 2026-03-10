<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daily Duty Roster</h3>
                    <div class="card-tools">
                        <a href="<?= url('/duty-roster/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Schedule Duty
                        </a>
                        <a href="<?= url('/duty-roster/weekly') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-calendar-week"></i> Weekly View
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
                                    <label>Date</label>
                                    <input type="date" name="date" class="form-control" 
                                           value="<?= htmlspecialchars($selected_date) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Shift</label>
                                    <select name="shift" class="form-control">
                                        <option value="">All Shifts</option>
                                        <?php foreach ($shifts as $shift): ?>
                                            <option value="<?= $shift['id'] ?>" 
                                                <?= $selected_shift == $shift['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($shift['shift_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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

                    <?php if (empty($roster)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No duty assignments found for the selected date.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Officer</th>
                                        <th>Rank</th>
                                        <th>Service No.</th>
                                        <th>Station</th>
                                        <th>Shift</th>
                                        <th>Time</th>
                                        <th>Duty Type</th>
                                        <th>Location</th>
                                        <th>Supervisor</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roster as $duty): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($duty['officer_name']) ?></td>
                                            <td><?= htmlspecialchars($duty['rank']) ?></td>
                                            <td><?= htmlspecialchars($duty['service_number']) ?></td>
                                            <td><?= htmlspecialchars($duty['station_name']) ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($duty['shift_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('H:i', strtotime($duty['start_time'])) ?> - 
                                                <?= date('H:i', strtotime($duty['end_time'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?= htmlspecialchars($duty['duty_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($duty['duty_location'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($duty['supervisor_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'Scheduled' => 'warning',
                                                    'On Duty' => 'success',
                                                    'Completed' => 'info',
                                                    'Cancelled' => 'danger'
                                                ];
                                                $class = $statusClass[$duty['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($duty['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= url('/duty-roster/' . $duty['id'] . '/edit') ?>" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger delete-duty" 
                                                        data-id="<?= $duty['id'] ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

$title = 'Duty Roster';
$breadcrumbs = [
    ['title' => 'Duty Roster']
];

include __DIR__ . '/../layouts/main.php';
?>

<script>
$(document).ready(function() {
    $('.select2').select2();
    
    $('.delete-duty').click(function() {
        const dutyId = $(this).data('id');
        
        Swal.fire({
            title: 'Delete Duty Assignment?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `<?= url('/duty-roster/') ?>${dutyId}/delete`,
                    method: 'POST',
                    data: {
                        csrf_token: '<?= csrf_token() ?>'
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    });
});
</script>
