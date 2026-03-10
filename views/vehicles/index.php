<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-car"></i> Vehicle Registry</h3>
                <div class="card-tools">
                    <a href="' . url('/vehicles/create') . '" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Register Vehicle
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="Registered" ' . ($selected_status == 'Registered' ? 'selected' : '') . '>Registered</option>
                                <option value="Stolen" ' . ($selected_status == 'Stolen' ? 'selected' : '') . '>Stolen</option>
                                <option value="Recovered" ' . ($selected_status == 'Recovered' ? 'selected' : '') . '>Recovered</option>
                                <option value="Impounded" ' . ($selected_status == 'Impounded' ? 'selected' : '') . '>Impounded</option>
                                <option value="Evidence" ' . ($selected_status == 'Evidence' ? 'selected' : '') . '>Evidence</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search registration, make, model..." value="' . htmlspecialchars($search ?? '') . '">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="' . url('/vehicles') . '" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Registration</th>
                            <th>Make/Model</th>
                            <th>Year</th>
                            <th>Color</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($vehicles as $vehicle) {
    $statusClass = match($vehicle['vehicle_status']) {
        'Registered' => 'success',
        'Stolen' => 'danger',
        'Recovered' => 'info',
        'Impounded' => 'warning',
        'Evidence' => 'secondary',
        default => 'secondary'
    };
    
    $content .= '
                        <tr>
                            <td><strong>' . htmlspecialchars($vehicle['registration_number']) . '</strong></td>
                            <td>' . htmlspecialchars($vehicle['vehicle_make'] . ' ' . $vehicle['vehicle_model']) . '</td>
                            <td>' . htmlspecialchars($vehicle['vehicle_year'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($vehicle['vehicle_color'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($vehicle['owner_name'] ?? 'Unknown') . '</td>
                            <td><span class="badge badge-' . $statusClass . '">' . htmlspecialchars($vehicle['vehicle_status']) . '</span></td>
                            <td>
                                <a href="' . url('/vehicles/' . $vehicle['id']) . '" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>';
}

if (empty($vehicles)) {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center text-muted">No vehicles found</td>
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
