<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Officer - ' . sanitize($officer['service_number']) . '</h3>
            </div>
            <form method="POST" action="' . url('/officers/' . $officer['id']) . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <h5>Service Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rank_id">Rank</label>
                                <select class="form-control" id="rank_id" name="rank_id">
                                    <option value="">Select Rank</option>';

foreach ($ranks as $rank) {
    $selected = $officer['rank_id'] == $rank['id'] ? 'selected' : '';
    $content .= '<option value="' . $rank['id'] . '" ' . $selected . '>' . sanitize($rank['rank_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="badge_number">Badge Number</label>
                                <input type="text" class="form-control" id="badge_number" name="badge_number" value="' . sanitize($officer['badge_number'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="employment_status">Employment Status</label>
                                <select class="form-control" id="employment_status" name="employment_status">
                                    <option value="Active" ' . ($officer['employment_status'] === 'Active' ? 'selected' : '') . '>Active</option>
                                    <option value="On Leave" ' . ($officer['employment_status'] === 'On Leave' ? 'selected' : '') . '>On Leave</option>
                                    <option value="Suspended" ' . ($officer['employment_status'] === 'Suspended' ? 'selected' : '') . '>Suspended</option>
                                    <option value="Retired" ' . ($officer['employment_status'] === 'Retired' ? 'selected' : '') . '>Retired</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Personal Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="' . sanitize($officer['first_name']) . '" required>
                                ' . (isset($_SESSION['errors']['first_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['first_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="' . sanitize($officer['middle_name'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="' . sanitize($officer['last_name']) . '" required>
                                ' . (isset($_SESSION['errors']['last_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['last_name']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Contact Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="' . sanitize($officer['phone_number'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="' . sanitize($officer['email'] ?? '') . '">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Current Station</label>
                                <input type="text" class="form-control" value="' . sanitize($officer['station_name'] ?? 'Unassigned') . '" disabled>
                                <small class="form-text text-muted">Use "Transfer Officer" to change station</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="residential_address">Residential Address</label>
                                <textarea class="form-control" id="residential_address" name="residential_address" rows="2">' . sanitize($officer['residential_address'] ?? '') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Officer
                    </button>
                    <a href="' . url('/officers/' . $officer['id']) . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Officers', 'url' => '/officers'],
    ['title' => $officer['service_number'], 'url' => '/officers/' . $officer['id']],
    ['title' => 'Edit']
];

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
