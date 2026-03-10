<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Warrant Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Warrants</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Warrants</h3>
                    <div class="card-tools">
                        <a href="/warrants/active" class="btn btn-danger btn-sm">
                            <i class="fas fa-exclamation-triangle"></i> Active Warrants
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="Active" <?= $selected_status == 'Active' ? 'selected' : '' ?>>Active</option>
                                        <option value="Executed" <?= $selected_status == 'Executed' ? 'selected' : '' ?>>Executed</option>
                                        <option value="Cancelled" <?= $selected_status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="Arrest Warrant" <?= $selected_type == 'Arrest Warrant' ? 'selected' : '' ?>>Arrest Warrant</option>
                                        <option value="Search Warrant" <?= $selected_type == 'Search Warrant' ? 'selected' : '' ?>>Search Warrant</option>
                                        <option value="Bench Warrant" <?= $selected_type == 'Bench Warrant' ? 'selected' : '' ?>>Bench Warrant</option>
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

                    <?php if (empty($warrants)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No warrants found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Warrant Type</th>
                                        <th>Case Number</th>
                                        <th>Suspect</th>
                                        <th>Issue Date</th>
                                        <th>Issued By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($warrants as $warrant): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($warrant['warrant_type']) ?></strong>
                                            </td>
                                            <td>
                                                <a href="/cases/<?= $warrant['case_id'] ?>">
                                                    <?= htmlspecialchars($warrant['case_number']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($warrant['suspect_name'] ?? 'N/A') ?>
                                                <?php if ($warrant['ghana_card_number']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($warrant['ghana_card_number']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($warrant['issue_date'])) ?></td>
                                            <td><?= htmlspecialchars($warrant['issued_by']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'Active' => 'danger',
                                                    'Executed' => 'success',
                                                    'Cancelled' => 'secondary'
                                                ];
                                                $class = $statusClass[$warrant['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($warrant['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/warrants/<?= $warrant['id'] ?>" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($warrant['status'] == 'Active'): ?>
                                                    <button class="btn btn-sm btn-success execute-warrant" 
                                                            data-id="<?= $warrant['id'] ?>" title="Execute">
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
    </section>
</div>

<script>
$(document).ready(function() {
    $('.execute-warrant').click(function() {
        const warrantId = $(this).data('id');
        
        Swal.fire({
            title: 'Execute Warrant',
            html: `
                <div class="form-group text-left">
                    <label>Execution Date & Time</label>
                    <input type="datetime-local" id="execution-date" class="swal2-input" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div class="form-group text-left">
                    <label>Execution Location</label>
                    <input type="text" id="execution-location" class="swal2-input" placeholder="Location where warrant was executed">
                </div>
                <div class="form-group text-left">
                    <label>Notes</label>
                    <textarea id="execution-notes" class="swal2-textarea" placeholder="Execution details and notes"></textarea>
                </div>
            `,
            width: '500px',
            showCancelButton: true,
            confirmButtonText: 'Execute Warrant',
            confirmButtonColor: '#28a745',
            preConfirm: () => {
                return {
                    execution_date: document.getElementById('execution-date').value,
                    execution_location: document.getElementById('execution-location').value,
                    execution_notes: document.getElementById('execution-notes').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/warrants/${warrantId}/execute`,
                    method: 'POST',
                    data: {
                        csrf_token: '<?= csrf_token() ?>',
                        ...result.value
                    },
                    success: function(response) {
                        Swal.fire('Executed!', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to execute warrant', 'error');
                    }
                });
            }
        });
    });
});
</script>
