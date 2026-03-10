<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box"></i> Evidence Management - ' . sanitize($case['case_number']) . '</h3>
                <div class="card-tools">
                    <a href="' . url('/cases/' . $case['id']) . '" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Case
                    </a>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addEvidenceModal">
                        <i class="fas fa-plus"></i> Add Evidence
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Collected By</th>
                            <th>Date Collected</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

if (!empty($evidence)) {
    foreach ($evidence as $item) {
        $statusClass = match($item['status']) {
            'Collected' => 'badge-info',
            'In Storage' => 'badge-primary',
            'In Lab' => 'badge-warning',
            'In Court' => 'badge-success',
            'Returned' => 'badge-secondary',
            'Destroyed' => 'badge-danger',
            default => 'badge-secondary'
        };
        
        $content .= '
                        <tr>
                            <td><strong>#' . $item['id'] . '</strong></td>
                            <td>' . sanitize($item['evidence_type']) . '</td>
                            <td>' . sanitize(substr($item['evidence_description'], 0, 50)) . '...</td>
                            <td>' . sanitize($item['collected_by_name'] ?? 'N/A') . '</td>
                            <td>' . format_date($item['collected_date'], 'd M Y') . '</td>
                            <td><span class="badge ' . $statusClass . '">' . sanitize($item['status']) . '</span></td>
                            <td>
                                <a href="' . url('/evidence/' . $item['id']) . '" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>';
    }
} else {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center">No evidence collected yet</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Evidence Modal -->
<div class="modal fade" id="addEvidenceModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Evidence</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="' . url('/cases/' . $case['id'] . '/evidence') . '">
                ' . csrf_field() . '
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Evidence Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="evidence_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Physical">Physical</option>
                                    <option value="Digital">Digital</option>
                                    <option value="Documentary">Documentary</option>
                                    <option value="Biological">Biological</option>
                                    <option value="Testimonial">Testimonial</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Collection Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="collected_date" value="' . date('Y-m-d\TH:i') . '" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="evidence_description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Collection Location</label>
                                <input type="text" class="form-control" name="collected_location">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Storage Location</label>
                                <input type="text" class="form-control" name="storage_location">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Evidence</button>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Cases', 'url' => '/cases'],
    ['title' => $case['case_number'], 'url' => '/cases/' . $case['id']],
    ['title' => 'Evidence']
];

include __DIR__ . '/../layouts/main.php';
?>
