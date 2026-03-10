<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Patrol Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/patrol-logs">Patrol Logs</a></li>
                        <li class="breadcrumb-item active">View Patrol</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <strong><?= htmlspecialchars($patrol['patrol_number']) ?></strong>
                            </h3>
                            <div class="card-tools">
                                <?php if ($patrol['patrol_status'] == 'In Progress'): ?>
                                    <button class="btn btn-success btn-sm" id="complete-patrol">
                                        <i class="fas fa-check"></i> Complete Patrol
                                    </button>
                                <?php endif; ?>
                                <a href="/patrol-logs/<?= $patrol['id'] ?>/edit" class="btn btn-info btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Station:</strong> <?= htmlspecialchars($patrol['station_name']) ?></p>
                                    <p><strong>Patrol Type:</strong> 
                                        <span class="badge badge-info"><?= htmlspecialchars($patrol['patrol_type']) ?></span>
                                    </p>
                                    <p><strong>Patrol Area:</strong> <?= htmlspecialchars($patrol['patrol_area']) ?></p>
                                    <p><strong>Patrol Leader:</strong> 
                                        <?= htmlspecialchars($patrol['leader_rank'] . ' ' . $patrol['leader_name']) ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($patrol['leader_service_number']) ?></small>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Start Time:</strong> <?= date('Y-m-d H:i', strtotime($patrol['start_time'])) ?></p>
                                    <p><strong>End Time:</strong> 
                                        <?= $patrol['end_time'] ? date('Y-m-d H:i', strtotime($patrol['end_time'])) : 'Ongoing' ?>
                                    </p>
                                    <p><strong>Status:</strong> 
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
                                    </p>
                                    <?php if ($patrol['vehicle_registration']): ?>
                                        <p><strong>Vehicle:</strong> 
                                            <?= htmlspecialchars($patrol['vehicle_registration'] . ' (' . $patrol['vehicle_type'] . ')') ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($patrol['report_summary']): ?>
                                <hr>
                                <h5>Patrol Summary</h5>
                                <p><?= nl2br(htmlspecialchars($patrol['report_summary'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Patrol Incidents</h3>
                            <div class="card-tools">
                                <?php if ($patrol['patrol_status'] == 'In Progress'): ?>
                                    <button class="btn btn-primary btn-sm" id="add-incident">
                                        <i class="fas fa-plus"></i> Add Incident
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($incidents)): ?>
                                <p class="text-muted">No incidents reported during this patrol.</p>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($incidents as $incident): ?>
                                        <div>
                                            <i class="fas fa-exclamation-triangle bg-warning"></i>
                                            <div class="timeline-item">
                                                <span class="time">
                                                    <i class="fas fa-clock"></i> 
                                                    <?= date('H:i', strtotime($incident['incident_time'])) ?>
                                                </span>
                                                <h3 class="timeline-header">
                                                    <?= htmlspecialchars($incident['incident_type']) ?>
                                                </h3>
                                                <div class="timeline-body">
                                                    <p><strong>Location:</strong> <?= htmlspecialchars($incident['incident_location']) ?></p>
                                                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($incident['incident_description'])) ?></p>
                                                    <?php if ($incident['action_taken']): ?>
                                                        <p><strong>Action Taken:</strong> <?= nl2br(htmlspecialchars($incident['action_taken'])) ?></p>
                                                    <?php endif; ?>
                                                    <?php if ($incident['case_number']): ?>
                                                        <p><strong>Case:</strong> 
                                                            <a href="/cases/<?= $incident['case_id'] ?>">
                                                                <?= htmlspecialchars($incident['case_number']) ?>
                                                            </a>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Patrol Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Incidents Reported</span>
                                    <span class="info-box-number"><?= $patrol['incidents_reported'] ?></span>
                                </div>
                            </div>

                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-handcuffs"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Arrests Made</span>
                                    <span class="info-box-number"><?= $patrol['arrests_made'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Patrol Officers</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($officers as $officer): ?>
                                    <li class="list-group-item">
                                        <strong><?= htmlspecialchars($officer['rank']) ?></strong>
                                        <?= htmlspecialchars($officer['officer_name']) ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($officer['service_number']) ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#complete-patrol').click(function() {
        Swal.fire({
            title: 'Complete Patrol?',
            html: '<textarea id="report-summary" class="swal2-textarea" placeholder="Enter patrol summary report" required></textarea>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Complete Patrol',
            preConfirm: () => {
                const summary = document.getElementById('report-summary').value;
                if (!summary) {
                    Swal.showValidationMessage('Please enter a summary report');
                }
                return summary;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/patrol-logs/<?= $patrol['id'] ?>/complete',
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

    $('#add-incident').click(function() {
        Swal.fire({
            title: 'Report Incident',
            html: `
                <div class="form-group text-left">
                    <label>Incident Type</label>
                    <input type="text" id="incident-type" class="swal2-input" placeholder="e.g., Suspicious Activity">
                </div>
                <div class="form-group text-left">
                    <label>Location</label>
                    <input type="text" id="incident-location" class="swal2-input" placeholder="Incident location">
                </div>
                <div class="form-group text-left">
                    <label>Time</label>
                    <input type="datetime-local" id="incident-time" class="swal2-input" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div class="form-group text-left">
                    <label>Description</label>
                    <textarea id="incident-description" class="swal2-textarea" placeholder="Describe the incident"></textarea>
                </div>
                <div class="form-group text-left">
                    <label>Action Taken</label>
                    <textarea id="action-taken" class="swal2-textarea" placeholder="What action was taken?"></textarea>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Report Incident',
            preConfirm: () => {
                return {
                    incident_type: document.getElementById('incident-type').value,
                    incident_location: document.getElementById('incident-location').value,
                    incident_time: document.getElementById('incident-time').value,
                    incident_description: document.getElementById('incident-description').value,
                    action_taken: document.getElementById('action-taken').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/patrol-logs/<?= $patrol['id'] ?>/add-incident',
                    method: 'POST',
                    data: {
                        csrf_token: '<?= csrf_token() ?>',
                        ...result.value
                    },
                    success: function(response) {
                        Swal.fire('Reported!', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to report incident', 'error');
                    }
                });
            }
        });
    });
});
</script>
