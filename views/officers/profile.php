<?php
$content = '
<div class="row">
    <div class="col-md-4">
        <!-- Officer Info Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="' . url('/AdminLTE/dist/img/user2-160x160.jpg') . '" 
                         alt="Officer profile picture">
                </div>

                <h3 class="profile-username text-center">' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</h3>

                <p class="text-muted text-center">' . sanitize($officer['rank_name'] ?? 'N/A') . '</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Service Number</b> <a class="float-right">' . sanitize($officer['service_number']) . '</a>
                    </li>
                    <li class="list-group-item">
                        <b>Badge Number</b> <a class="float-right">' . sanitize($officer['badge_number'] ?? 'N/A') . '</a>
                    </li>
                    <li class="list-group-item">
                        <b>Employment Status</b> 
                        <span class="float-right badge badge-' . match($officer['employment_status']) {
                            'Active' => 'success',
                            'On Leave' => 'warning',
                            'Retired' => 'secondary',
                            'Suspended' => 'danger',
                            default => 'info'
                        } . '">' . sanitize($officer['employment_status']) . '</span>
                    </li>
                    <li class="list-group-item">
                        <b>Date of Enlistment</b> <a class="float-right">' . ($officer['date_of_enlistment'] ? format_date($officer['date_of_enlistment'], 'd M Y') : 'N/A') . '</a>
                    </li>
                </ul>

                <a href="' . url('/officers/' . $officer['id'] . '/edit') . '" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Contact Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Contact Information</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-phone mr-1"></i> Phone</strong>
                <p class="text-muted">' . sanitize($officer['contact'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                <p class="text-muted">' . sanitize($officer['email'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted">' . sanitize($officer['address'] ?? 'N/A') . '</p>

                <strong><i class="fas fa-building mr-1"></i> Current Station</strong>
                <p class="text-muted">' . sanitize($officer['station_name'] ?? 'Unassigned') . '</p>
            </div>
        </div>

        <!-- Performance Card -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Performance Metrics</h3>
            </div>
            <div class="card-body">
                <strong>Total Cases Assigned</strong>
                <p class="text-muted">' . ($performance['total_cases'] ?? 0) . '</p>

                <strong>Cases Closed</strong>
                <p class="text-muted">' . ($performance['closed_cases'] ?? 0) . '</p>

                <strong>Active Cases</strong>
                <p class="text-muted">' . ($performance['active_cases'] ?? 0) . '</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Posting History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Posting History</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#transferModal">
                        <i class="fas fa-exchange-alt"></i> Transfer Officer
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($postings)) {
    $content .= '
                <div class="timeline">';
    
    foreach ($postings as $posting) {
        $isCurrent = empty($posting['end_date']);
        $iconClass = $isCurrent ? 'bg-success' : 'bg-secondary';
        
        $content .= '
                    <div>
                        <i class="fas fa-building ' . $iconClass . '"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> ' . format_date($posting['start_date'], 'd M Y') . 
                                ($posting['end_date'] ? ' - ' . format_date($posting['end_date'], 'd M Y') : ' - Present') . '
                            </span>
                            <h3 class="timeline-header">' . sanitize($posting['station_name']) . '</h3>
                            <div class="timeline-body">
                                <strong>Station Code:</strong> ' . sanitize($posting['station_code']) . '<br>
                                <strong>District:</strong> ' . sanitize($posting['district_name'] ?? 'N/A') . '<br>
                                <strong>Region:</strong> ' . sanitize($posting['region_name'] ?? 'N/A') . '
                            </div>
                        </div>
                    </div>';
    }
    
    $content .= '
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>';
} else {
    $content .= '<p class="text-muted">No posting history available</p>';
}

$content .= '
            </div>
        </div>

        <!-- Promotion History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-arrow-up"></i> Promotion History</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#promoteModal">
                        <i class="fas fa-star"></i> Promote Officer
                    </button>
                </div>
            </div>
            <div class="card-body">';

if (!empty($promotions)) {
    $content .= '
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From Rank</th>
                            <th>To Rank</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($promotions as $promotion) {
        $content .= '
                        <tr>
                            <td>' . format_date($promotion['promotion_date'], 'd M Y') . '</td>
                            <td><span class="badge badge-secondary">' . sanitize($promotion['old_rank']) . '</span></td>
                            <td><span class="badge badge-success">' . sanitize($promotion['new_rank']) . '</span></td>
                            <td>' . sanitize($promotion['notes'] ?? '') . '</td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No promotion history</p>';
}

$content .= '
            </div>
        </div>

        <!-- Case Assignments -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-folder"></i> Recent Case Assignments</h3>
            </div>
            <div class="card-body">';

if (!empty($assignments)) {
    $content .= '
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($assignments as $assignment) {
        $content .= '
                        <tr>
                            <td><a href="' . url('/cases/' . $assignment['case_id']) . '">' . sanitize($assignment['case_number']) . '</a></td>
                            <td>' . sanitize($assignment['role']) . '</td>
                            <td><span class="badge badge-info">' . sanitize($assignment['case_status']) . '</span></td>
                            <td><span class="badge badge-' . match($assignment['case_priority']) {
                                'High' => 'danger',
                                'Medium' => 'warning',
                                'Low' => 'info',
                                default => 'secondary'
                            } . '">' . sanitize($assignment['case_priority']) . '</span></td>
                            <td>' . format_date($assignment['assigned_at'], 'd M Y') . '</td>
                        </tr>';
    }
    
    $content .= '
                    </tbody>
                </table>';
} else {
    $content .= '<p class="text-muted">No case assignments</p>';
}

$content .= '
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Transfer Officer</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="transferForm">
                    ' . csrf_field() . '
                    <div class="form-group">
                        <label>New Station</label>
                        <select class="form-control" name="station_id" required>
                            <option value="">Select Station</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Effective Date</label>
                        <input type="date" class="form-control" name="effective_date" value="' . date('Y-m-d') . '" required>
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <textarea class="form-control" name="reason" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitTransfer()">Transfer</button>
            </div>
        </div>
    </div>
</div>

<!-- Promote Modal -->
<div class="modal fade" id="promoteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Promote Officer</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="promoteForm">
                    ' . csrf_field() . '
                    <div class="form-group">
                        <label>New Rank</label>
                        <select class="form-control" name="new_rank" required>
                            <option value="">Select Rank</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Effective Date</label>
                        <input type="date" class="form-control" name="effective_date" value="' . date('Y-m-d') . '" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitPromotion()">Promote</button>
            </div>
        </div>
    </div>
</div>

<script>
function submitTransfer() {
    const formData = new FormData(document.getElementById("transferForm"));
    fetch("' . url('/officers/' . $officer['id'] . '/transfer') . '", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          } else {
              alert(data.message);
          }
      });
}

function submitPromotion() {
    const formData = new FormData(document.getElementById("promoteForm"));
    fetch("' . url('/officers/' . $officer['id'] . '/promote') . '", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          } else {
              alert(data.message);
          }
      });
}
</script>';

$breadcrumbs = [
    ['title' => 'Officers', 'url' => '/officers'],
    ['title' => $officer['service_number']]
];

include __DIR__ . '/../layouts/main.php';
?>
