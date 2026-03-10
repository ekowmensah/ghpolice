<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Custody Records</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Custody Records</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Custody Management</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="In Custody" <?= $selected_status == 'In Custody' ? 'selected' : '' ?>>In Custody</option>
                                        <option value="Released" <?= $selected_status == 'Released' ? 'selected' : '' ?>>Released</option>
                                        <option value="Transferred" <?= $selected_status == 'Transferred' ? 'selected' : '' ?>>Transferred</option>
                                        <option value="Bailed" <?= $selected_status == 'Bailed' ? 'selected' : '' ?>>Bailed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($records)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No custody records found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Suspect</th>
                                        <th>Case Number</th>
                                        <th>Custody Type</th>
                                        <th>Location</th>
                                        <th>Arresting Officer</th>
                                        <th>Detention Start</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($record['suspect_name']) ?></strong>
                                            </td>
                                            <td>
                                                <a href="/cases/<?= $record['case_id'] ?>">
                                                    <?= htmlspecialchars($record['case_number']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    Police Custody
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($record['custody_location'] ?? 'N/A') ?></td>
                                            <td>
                                                <?= htmlspecialchars(($record['officer_rank'] ?? '') . ' ' . ($record['arresting_officer_name'] ?? 'N/A')) ?>
                                            </td>
                                            <td><?= $record['custody_start'] ? date('Y-m-d H:i', strtotime($record['custody_start'])) : 'N/A' ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'In Custody' => 'warning',
                                                    'Released' => 'success',
                                                    'Transferred' => 'info',
                                                    'Escaped' => 'danger'
                                                ];
                                                $class = $statusClass[$record['custody_status'] ?? ''] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($record['custody_status'] ?? 'Unknown') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/custody/<?= $record['id'] ?>" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (($record['custody_status'] ?? '') == 'In Custody'): ?>
                                                    <button class="btn btn-sm btn-success release-custody" 
                                                            data-id="<?= $record['id'] ?>" title="Release">
                                                        <i class="fas fa-unlock"></i>
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
    </section>
</div>

<script>
$(document).ready(function() {
    $('.release-custody').click(function() {
        const custodyId = $(this).data('id');
        
        Swal.fire({
            title: 'Release from Custody',
            html: `
                <div class="form-group text-left">
                    <label>Release Type</label>
                    <select id="release-type" class="swal2-input">
                        <option value="Released">Released</option>
                        <option value="Bailed">Bailed</option>
                        <option value="Transferred">Transferred</option>
                    </select>
                </div>
                <div class="form-group text-left">
                    <label>Release Date & Time</label>
                    <input type="datetime-local" id="release-date" class="swal2-input" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div class="form-group text-left">
                    <label>Reason</label>
                    <textarea id="release-reason" class="swal2-textarea" placeholder="Reason for release"></textarea>
                </div>
            `,
            width: '500px',
            showCancelButton: true,
            confirmButtonText: 'Release',
            confirmButtonColor: '#28a745',
            preConfirm: () => {
                return {
                    release_type: document.getElementById('release-type').value,
                    release_date: document.getElementById('release-date').value,
                    release_reason: document.getElementById('release-reason').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/custody/${custodyId}/release`,
                    method: 'POST',
                    data: {
                        csrf_token: '<?= csrf_token() ?>',
                        ...result.value
                    },
                    success: function(response) {
                        Swal.fire('Released!', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to release', 'error');
                    }
                });
            }
        });
    });
});
</script>
