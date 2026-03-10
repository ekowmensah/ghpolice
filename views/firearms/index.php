<?php
// Determine organizational context
$user = $_SESSION['user'] ?? [];
$accessLevel = $user['access_level'] ?? 'Station';
$contextInfo = '';
$contextClass = 'info';

switch ($accessLevel) {
    case 'Own':
    case 'Unit':
    case 'Station':
        $contextInfo = '<i class="fas fa-building"></i> Viewing: ' . htmlspecialchars($user['station_name'] ?? 'Your Station');
        $contextClass = 'primary';
        break;
    case 'District':
        $contextInfo = '<i class="fas fa-map-marked-alt"></i> Viewing: ' . htmlspecialchars($user['district_name'] ?? 'Your District');
        $contextClass = 'info';
        break;
    case 'Division':
        $contextInfo = '<i class="fas fa-layer-group"></i> Viewing: ' . htmlspecialchars($user['division_name'] ?? 'Your Division');
        $contextClass = 'warning';
        break;
    case 'Region':
        $contextInfo = '<i class="fas fa-globe-africa"></i> Viewing: ' . htmlspecialchars($user['region_name'] ?? 'Your Region');
        $contextClass = 'success';
        break;
    case 'National':
        $contextInfo = '<i class="fas fa-flag"></i> Viewing: All Firearms (National Level)';
        $contextClass = 'danger';
        break;
}

$content = '
<div class="row">
    <div class="col-md-12">';

if ($contextInfo) {
    $content .= '
        <div class="alert alert-' . $contextClass . ' alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>' . $contextInfo . '</strong> - You are viewing firearms within your organizational scope.
        </div>';
}

$content .= '
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-crosshairs"></i> Firearms Registry</h3>
                <div class="card-tools">
                    <a href="' . url('/firearms/create') . '" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Register Firearm
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="In Service" ' . ($selected_status == 'In Service' ? 'selected' : '') . '>In Service</option>
                                <option value="In Armory" ' . ($selected_status == 'In Armory' ? 'selected' : '') . '>In Armory</option>
                                <option value="Under Repair" ' . ($selected_status == 'Under Repair' ? 'selected' : '') . '>Under Repair</option>
                                <option value="Decommissioned" ' . ($selected_status == 'Decommissioned' ? 'selected' : '') . '>Decommissioned</option>
                                <option value="Lost" ' . ($selected_status == 'Lost' ? 'selected' : '') . '>Lost</option>
                                <option value="Stolen" ' . ($selected_status == 'Stolen' ? 'selected' : '') . '>Stolen</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="Pistol" ' . ($selected_type == 'Pistol' ? 'selected' : '') . '>Pistol</option>
                                <option value="Rifle" ' . ($selected_type == 'Rifle' ? 'selected' : '') . '>Rifle</option>
                                <option value="Shotgun" ' . ($selected_type == 'Shotgun' ? 'selected' : '') . '>Shotgun</option>
                                <option value="Submachine Gun" ' . ($selected_type == 'Submachine Gun' ? 'selected' : '') . '>Submachine Gun</option>
                                <option value="Other" ' . ($selected_type == 'Other' ? 'selected' : '') . '>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="' . url('/firearms') . '" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Type</th>
                            <th>Make/Model</th>
                            <th>Caliber</th>
                            <th>Current Holder</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($firearms as $firearm) {
    $statusClass = match($firearm['firearm_status']) {
        'In Service' => 'success',
        'In Armory' => 'info',
        'Under Repair' => 'warning',
        'Decommissioned' => 'secondary',
        'Lost', 'Stolen' => 'danger',
        default => 'secondary'
    };
    
    $content .= '
                        <tr>
                            <td><strong>' . htmlspecialchars($firearm['serial_number']) . '</strong></td>
                            <td>' . htmlspecialchars($firearm['firearm_type']) . '</td>
                            <td>' . htmlspecialchars($firearm['make'] . ' ' . $firearm['model']) . '</td>
                            <td>' . (!empty($firearm['ammunition_caliber']) 
                                ? '<span class="badge badge-secondary">' . htmlspecialchars($firearm['ammunition_type'] . ' - ' . $firearm['ammunition_caliber']) . '</span>' 
                                : '<span class="text-muted">Not Specified</span>') . '</td>
                            <td>' . htmlspecialchars($firearm['holder_name'] ?? 'Not Assigned') . '</td>
                            <td><span class="badge badge-' . $statusClass . '">' . htmlspecialchars($firearm['firearm_status']) . '</span></td>
                            <td>
                                <a href="' . url('/firearms/' . $firearm['id']) . '" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>';
}

if (empty($firearms)) {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center text-muted">No firearms found</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';

include __DIR__ . '/../layouts/main.php';
?>
