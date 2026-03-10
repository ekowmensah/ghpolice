<?php
$title = 'Warrant Details';

// Safety check - ensure warrant data is available
if (!isset($warrant) || !$warrant) {
    echo '<div class="alert alert-danger">Warrant not found or data not available.</div>';
    return;
}

$content = '
<style>
:root {
    --gp-navy: #112c4d;
    --gp-navy-2: #1a406d;
    --gp-navy-3: #243a5a;
    --gp-gold: #c7a13f;
    --gp-gold-2: #d4af37;
    --gp-red: #d94a3a;
    --gp-green: #1f7a3d;
    --gp-teal: #17a2b8;
    --gp-purple: #6f42c1;
    --gp-pink: #f093fb;
    --gp-bg: #eef3f9;
    --gp-text: #1c2630;
    --gp-muted: #607086;
    --gp-border: #d4deea;
    --gp-light: #f5f8fc;
}

.gp-warrant-header {
    background: linear-gradient(135deg, var(--gp-navy) 0%, var(--gp-navy-2) 50%, var(--gp-gold) 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(17, 44, 77, 0.3);
}

.gp-warrant-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Cpath fill=\'rgba(255,255,255,0.03)\' d=\'M0 50L50 0L100 50L50 100Z\'/%3E%3C/svg%3E");
    background-size: 30px 30px;
}

.gp-warrant-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    text-align: center;
}

.gp-warrant-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    text-align: center;
    margin-bottom: 2rem;
}

.gp-section-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid var(--gp-border);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gp-section-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.gp-section-header {
    padding: 1.5rem 2rem;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.gp-section-header::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gp-gold);
}

.gp-section-details { background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2)); }
.gp-section-suspect { background: linear-gradient(135deg, var(--gp-teal), #20c997); }
.gp-section-logs { background: linear-gradient(135deg, var(--gp-purple), #8b5cf6); }
.gp-section-actions { background: linear-gradient(135deg, var(--gp-green), #20c997); }

.gp-section-body {
    padding: 2rem;
}

.gp-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.gp-info-item {
    background: var(--gp-light);
    border-radius: 15px;
    padding: 1.5rem;
    border-left: 4px solid var(--gp-navy);
    transition: all 0.3s ease;
}

.gp-info-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.gp-info-label {
    font-size: 0.85rem;
    color: var(--gp-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.gp-info-value {
    font-size: 1.1rem;
    color: var(--gp-text);
    font-weight: 500;
}

.gp-badge {
    display: inline-block;
    padding: 0.3rem 0.6rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.gp-badge-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
.gp-badge-warning { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
.gp-badge-danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.gp-badge-info { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }
.gp-badge-secondary { background: linear-gradient(135deg, #6c757d, #545b62); color: white; }

.gp-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.gp-table th {
    background: var(--gp-light);
    color: var(--gp-navy);
    font-weight: 600;
    padding: 0.75rem;
    text-align: left;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--gp-border);
}

.gp-table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--gp-border);
    color: var(--gp-text);
    font-size: 0.9rem;
}

.gp-table tr:hover {
    background: var(--gp-light);
}

.gp-btn {
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    border: none;
    border-radius: 25px;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(17, 44, 77, 0.2);
    text-decoration: none;
    display: inline-block;
}

.gp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(17, 44, 77, 0.3);
    color: white;
}

.gp-btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.gp-btn-success:hover {
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.gp-btn-danger {
    background: linear-gradient(135deg, var(--gp-red), #e74c3c);
}

.gp-btn-danger:hover {
    box-shadow: 0 8px 25px rgba(217, 74, 58, 0.3);
}

.gp-text-area {
    background: var(--gp-light);
    border: 1px solid var(--gp-border);
    border-radius: 10px;
    padding: 1rem;
    min-height: 100px;
    resize: vertical;
    font-family: inherit;
}

@media (max-width: 768px) {
    .gp-warrant-header {
        padding: 2rem 1rem;
    }
    
    .gp-warrant-title {
        font-size: 2rem;
    }
    
    .gp-info-grid {
        grid-template-columns: 1fr;
    }
    
    .gp-section-body {
        padding: 1.5rem;
    }
}
</style>

<!-- Ghana Police Warrant Details Header -->
<div class="gp-warrant-header text-center">
    <div class="position-relative" style="z-index: 2;">
        <h1 class="gp-warrant-title">
            <i class="fas fa-shield-alt"></i> Warrant Details
        </h1>
        <div class="gp-warrant-subtitle">
            Judicial Warrant Information System
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Warrant Information -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-details">
            <div>
                <i class="fas fa-file-alt"></i> Warrant Information
            </div>
            <div>
                <span class="gp-badge gp-badge-' . ($warrant['status'] === 'Active' ? 'danger' : ($warrant['status'] === 'Executed' ? 'success' : 'secondary')) . '">
                    ' . htmlspecialchars($warrant['status']) . '
                </span>
            </div>
        </div>
        <div class="gp-section-body">
            <div class="gp-info-grid">
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-hashtag"></i> Warrant Number
                    </div>
                    <div class="gp-info-value">
                        <strong>' . htmlspecialchars($warrant['warrant_number']) . '</strong>
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-tag"></i> Warrant Type
                    </div>
                    <div class="gp-info-value">
                        <strong>' . htmlspecialchars($warrant['warrant_type']) . '</strong>
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-folder"></i> Case Number
                    </div>
                    <div class="gp-info-value">
                        <a href="' . url('/cases/view/' . $warrant['case_id']) . '" class="text-primary font-weight-bold">
                            ' . htmlspecialchars($warrant['case_number']) . '
                        </a>
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-calendar"></i> Issue Date
                    </div>
                    <div class="gp-info-value">
                        ' . date('F d, Y', strtotime($warrant['issue_date'])) . '
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-calendar-times"></i> Expiry Date
                    </div>
                    <div class="gp-info-value">
                        ' . ($warrant['expiry_date'] ? date('F d, Y', strtotime($warrant['expiry_date'])) : 'No expiry date') . '
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-gavel"></i> Issuing Court
                    </div>
                    <div class="gp-info-value">
                        ' . ($warrant['issuing_court'] ? htmlspecialchars($warrant['issuing_court']) : 'Not specified') . '
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-user-shield"></i> Issued By
                    </div>
                    <div class="gp-info-value">
                        ' . htmlspecialchars($warrant['issued_by']) . '
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-clock"></i> Executed Date
                    </div>
                    <div class="gp-info-value">
                        ' . ($warrant['executed_date'] ? date('F d, Y H:i', strtotime($warrant['executed_date'])) : 'Not executed') . '
                    </div>
                </div>
            </div>
            
            <div class="gp-info-item" style="margin-top: 1rem;">
                <div class="gp-info-label">
                    <i class="fas fa-info-circle"></i> Warrant Details
                </div>
                <div class="gp-info-value">
                    <div class="gp-text-area">' . nl2br(htmlspecialchars($warrant['warrant_details'] ?? 'No details provided')) . '</div>
                </div>
            </div>
            
            ' . ($warrant['execution_instructions'] ? '
            <div class="gp-info-item" style="margin-top: 1rem;">
                <div class="gp-info-label">
                    <i class="fas fa-list-ol"></i> Execution Instructions
                </div>
                <div class="gp-info-value">
                    <div class="gp-text-area">' . nl2br(htmlspecialchars($warrant['execution_instructions'])) . '</div>
                </div>
            </div>' : '') . '
        </div>
    </div>';

if ($warrant['suspect_name']) {
    $content .= '
    <!-- Suspect Information -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-suspect">
            <div>
                <i class="fas fa-user"></i> Suspect Information
            </div>
        </div>
        <div class="gp-section-body">
            <div class="gp-info-grid">
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-user"></i> Name
                    </div>
                    <div class="gp-info-value">
                        <strong>' . htmlspecialchars($warrant['suspect_name']) . '</strong>
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-id-card"></i> Ghana Card Number
                    </div>
                    <div class="gp-info-value">
                        ' . ($warrant['ghana_card_number'] ? htmlspecialchars($warrant['ghana_card_number']) : 'Not available') . '
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-birthday-cake"></i> Date of Birth
                    </div>
                    <div class="gp-info-value">
                        ' . ($warrant['date_of_birth'] ? date('F d, Y', strtotime($warrant['date_of_birth'])) : 'Not available') . '
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-phone"></i> Contact
                    </div>
                    <div class="gp-info-value">
                        ' . ($warrant['contact'] ? htmlspecialchars($warrant['contact']) : 'Not available') . '
                    </div>
                </div>
                <div class="gp-info-item">
                    <div class="gp-info-label">
                        <i class="fas fa-home"></i> Address
                    </div>
                    <div class="gp-info-value">
                        ' . ($warrant['address'] ? htmlspecialchars($warrant['address']) : 'Not available') . '
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

$executionLogs = isset($execution_logs) ? $execution_logs : [];

if (!empty($executionLogs)) {
    $content .= '
    <!-- Execution Logs -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-logs">
            <div>
                <i class="fas fa-history"></i> Execution Logs
            </div>
            <div>
                <span class="gp-badge gp-badge-info">' . count($executionLogs) . ' Logs</span>
            </div>
        </div>
        <div class="gp-section-body">
            <div class="table-responsive">
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Execution Date</th>
                            <th>Executed By</th>
                            <th>Rank</th>
                            <th>Service Number</th>
                            <th>Location</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>';

    foreach ($executionLogs as $log) {
        $content .= '
                        <tr>
                            <td>' . date('M d, Y H:i', strtotime($log['execution_date'])) . '</td>
                            <td>' . htmlspecialchars($log['executed_by_name']) . '</td>
                            <td>' . htmlspecialchars($log['rank'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($log['service_number'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($log['execution_location'] ?? 'N/A') . '</td>
                            <td>' . ($log['notes'] ? htmlspecialchars($log['notes']) : 'N/A') . '</td>
                        </tr>';
    }

    $content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
}

$content .= '
    <!-- Action Buttons -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-actions">
            <div>
                <i class="fas fa-tools"></i> Actions
            </div>
        </div>
        <div class="gp-section-body text-center">
            <a href="' . url('/warrants') . '" class="gp-btn gp-btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Warrants
            </a>';

if ($warrant['status'] === 'Active') {
    $content .= '
            <button class="gp-btn gp-btn-danger execute-warrant" data-id="' . $warrant['id'] . '">
                <i class="fas fa-check"></i> Execute Warrant
            </button>';
}

$content .= '
            <a href="' . url('/warrants/' . $warrant['id'] . '/edit') . '" class="gp-btn">
                <i class="fas fa-edit"></i> Edit Warrant
            </a>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Warrants', 'link' => url('/warrants')],
    ['title' => 'Warrant Details']
];

include __DIR__ . '/../layouts/main.php';
?>

<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const WARRANTS_BASE_URL = "<?php echo url('/warrants/'); ?>";
</script>

<style>
.execute-warrant-modal .swal2-popup {
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.execute-warrant-modal .swal2-header {
    background: linear-gradient(135deg, #112c4d, #1a406d);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 1.5rem;
}

.execute-warrant-modal .swal2-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
}

.execute-warrant-modal .form-label {
    color: #112c4d;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
}

.execute-warrant-modal .form-control {
    border: 2px solid #d4deea;
    border-radius: 10px;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.execute-warrant-modal .form-control:focus {
    border-color: #112c4d;
    box-shadow: 0 0 0 3px rgba(17, 44, 77, 0.1);
}

.execute-warrant-modal .swal2-actions {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0 0 20px 20px;
    margin: 0;
}

.execution-success {
    text-align: left;
    padding: 1rem;
}

.execution-success p {
    margin-bottom: 0.5rem;
}

.execution-details p {
    color: #607086;
    font-size: 0.9rem;
}

.execution-error {
    text-align: left;
    padding: 1rem;
}

.execution-error p {
    margin-bottom: 0.5rem;
}

.officer-search-container {
    position: relative;
}

.officer-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #d4deea;
    border-radius: 8px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.officer-search-results .officer-result {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.officer-search-results .officer-result:hover {
    background-color: #f8f9fa;
}

.officer-search-results .officer-result:last-child {
    border-bottom: none;
}

.officer-search-results .officer-name {
    font-weight: 600;
    color: #112c4d;
}

.officer-search-results .officer-details {
    font-size: 0.85rem;
    color: #607086;
    margin-top: 2px;
}

.evidence-item {
    margin-bottom: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.evidence-item .row {
    display: flex;
    gap: 10px;
    align-items: center;
}

.evidence-item .col-md-4 {
    flex: 1;
}

.evidence-item .col-md-3 {
    flex: 0.8;
}

.evidence-item .col-md-1 {
    flex: 0.2;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.mt-2 {
    margin-top: 8px;
}
</style>

<script>
$(document).ready(function() {
    // Handle warrant execution
    $('.execute-warrant').click(function() {
        const warrantId = $(this).data('id');
        
        Swal.fire({
            title: '<i class="fas fa-gavel"></i> Execute Warrant',
            html: `
                <div class="execute-warrant-form">
                    <div class="form-group text-left mb-3">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i> Execution Date & Time</label>
                        <input type="datetime-local" id="execution-date" class="swal2-input form-control" value="' . date('Y-m-d\TH:i') . '" required>
                    </div>
                    <input type="hidden" id="csrf-token" value="">
                    <div class="form-group text-left mb-3">
                        <label class="form-label"><i class="fas fa-map-marker-alt"></i> Execution Location</label>
                        <input type="text" id="execution-location" class="swal2-input form-control" placeholder="Enter execution location" required>
                    </div>
                    <div class="form-group text-left mb-3">
                        <label class="form-label"><i class="fas fa-user-shield"></i> Executing Officer</label>
                        <div class="officer-search-container">
                            <input type="text" id="executing-officer" class="swal2-input form-control" placeholder="Search by service number or name" required>
                            <div id="officer-search-results" class="officer-search-results"></div>
                        </div>
                        <input type="hidden" id="executing-officer-id" value="">
                        <small class="text-muted">Click to search, click selected officer to lock, click again to clear</small>
                    </div>
                    <div class="form-group text-left mb-3">
                        <label class="form-label"><i class="fas fa-list-alt"></i> Execution Details</label>
                        <textarea id="execution-notes" class="swal2-textarea form-control" rows="4" placeholder="Describe execution circumstances, resistance encountered, etc."></textarea>
                    </div>
                    <div class="form-group text-left mb-3">
                        <label class="form-label"><i class="fas fa-users"></i> Suspect Status</label>
                        <select id="suspect-status" class="swal2-input form-control">
                            <option value="arrested">Arrested</option>
                            <option value="detained">Detained</option>
                            <option value="released">Released</option>
                            <option value="escaped">Escaped</option>
                            <option value="not_found">Not Found</option>
                        </select>
                    </div>
                    <div class="form-group text-left mb-3">
                        <label class="form-label"><i class="fas fa-shield-alt"></i> Evidence Seized</label>
                        <div id="evidence-container">
                            <div class="evidence-item">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" class="swal2-input form-control evidence-type" placeholder="Type (e.g., Weapon, Drugs)">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="swal2-input form-control evidence-description" placeholder="Description">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="swal2-input form-control evidence-quantity" placeholder="Quantity">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-danger remove-evidence" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-evidence-btn" class="btn btn-sm btn-success mt-2">
                            <i class="fas fa-plus"></i> Add Evidence
                        </button>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Execute Warrant',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            customClass: {
                popup: 'execute-warrant-modal',
                header: 'execute-warrant-header',
                title: 'execute-warrant-title'
            },
            preConfirm: () => {
                const executionDate = document.getElementById('execution-date')?.value;
                const executionLocation = document.getElementById('execution-location')?.value;
                const executingOfficer = document.getElementById('executing-officer')?.value;
                const executionNotes = document.getElementById('execution-notes')?.value;
                const suspectStatus = document.getElementById('suspect-status')?.value;
                
                if (!executionDate || !executionLocation || !executingOfficer) {
                    Swal.showValidationMessage('Please fill in all required fields: Execution Date, Location, and Executing Officer');
                    return false;
                }
                
                return {
                    execution_date: executionDate,
                    execution_location: executionLocation,
                    executing_officer: executingOfficer,
                    execution_notes: executionNotes || '',
                    suspect_status: suspectStatus || 'arrested'
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Collect evidence data
                const evidenceData = [];
                $('.evidence-item').each(function() {
                    const type = $(this).find('.evidence-type').val();
                    const description = $(this).find('.evidence-description').val();
                    const quantity = $(this).find('.evidence-quantity').val();
                    
                    if (type && description) {
                        evidenceData.push({
                            type: type,
                            description: description,
                            quantity: quantity || '1'
                        });
                    }
                });
                
                // Get CSRF token from main page
                let csrfToken = $("input[name=csrf_token]").first().val();
                if (!csrfToken) {
                    csrfToken = $("input[name=_token]").first().val();
                }
                
                // Show loading
                Swal.fire({
                    title: 'Executing Warrant...',
                    html: '<i class="fas fa-spinner fa-spin"></i> Processing execution...',
                    allowOutsideClick: false,
                    showConfirmButton: false
                });
                
                $.ajax({
                    url: WARRANTS_BASE_URL + warrantId + '/execute',
                    method: 'POST',
                    data: {
                        csrf_token: csrfToken,
                        ...result.value,
                        executing_officer_id: $('#executing-officer-id').val(),
                        evidence_items: JSON.stringify(evidenceData)
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '<i class="fas fa-check-circle"></i> Warrant Executed!',
                            html: `
                                <div class="execution-success">
                                    <p><strong>Warrant executed successfully!</strong></p>
                                    <div class="execution-details">
                                        <p><i class="fas fa-clock"></i> <strong>Time:</strong> ${new Date().toLocaleString()}</p>
                                        <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> ${result.value.execution_location}</p>
                                        <p><i class="fas fa-user-shield"></i> <strong>Officer:</strong> ${result.value.executing_officer}</p>
                                        <p><i class="fas fa-info-circle"></i> <strong>Status:</strong> ${result.value.suspect_status}</p>
                                    </div>
                                </div>
                            `,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'View Warrant Details'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to execute warrant';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: '<i class="fas fa-exclamation-triangle"></i> Execution Failed',
                            html: `
                                <div class="execution-error">
                                    <p><strong>Error:</strong> ${errorMessage}</p>
                                    <p>Please check the warrant details and try again.</p>
                                </div>
                            `,
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'Close'
                        });
                    }
                });
            }
        });
    });
    
    // Officer search functionality
    $(document).on('input', '#executing-officer', function() {
        const searchTerm = $(this).val();
        const resultsContainer = $('#officer-search-results');
        
        if (searchTerm.length < 2) {
            resultsContainer.hide().empty();
            return;
        }
        
        $.ajax({
            url: '<?php echo url('/officers/search/'); ?>' + encodeURIComponent(searchTerm),
            method: 'GET',
            success: function(response) {
                resultsContainer.empty();
                
                if (response.success && response.officers.length > 0) {
                    response.officers.forEach(function(officer) {
                        const resultDiv = $('<div class="officer-result">')
                            .html(`
                                <div class="officer-name">${officer.name}</div>
                                <div class="officer-details">Service No: ${officer.service_number} | Rank: ${officer.rank}</div>
                            `)
                            .click(function() {
                                $('#executing-officer').val(officer.name);
                                $('#executing-officer-id').val(officer.id);
                                $('#executing-officer').prop('readonly', true);
                                resultsContainer.hide().empty();
                            });
                        
                        resultsContainer.append(resultDiv);
                    });
                    resultsContainer.show();
                } else {
                    resultsContainer.hide().empty();
                }
            },
            error: function() {
                resultsContainer.hide().empty();
            }
        });
    });
    
    // Hide officer search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.officer-search-container').length) {
            $('#officer-search-results').hide();
        }
    });
    
    // Handle officer field click to clear selection
    $(document).on('click', '#executing-officer', function() {
        if ($(this).prop('readonly')) {
            // Clear selection and make editable again
            $(this).prop('readonly', false);
            $(this).val('');
            $('#executing-officer-id').val('');
            $(this).focus();
        }
    });
    
    // Evidence management functionality
    $(document).on('click', '#add-evidence-btn', function() {
        const evidenceItem = `
            <div class="evidence-item">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="swal2-input form-control evidence-type" placeholder="Type (e.g., Weapon, Drugs)">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="swal2-input form-control evidence-description" placeholder="Description">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="swal2-input form-control evidence-quantity" placeholder="Quantity">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger remove-evidence">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#evidence-container').append(evidenceItem);
        updateEvidenceRemoveButtons();
    });
    
    $(document).on('click', '.remove-evidence', function() {
        $(this).closest('.evidence-item').remove();
        updateEvidenceRemoveButtons();
    });
    
    function updateEvidenceRemoveButtons() {
        const evidenceItems = $('.evidence-item');
        $('.remove-evidence').toggle(evidenceItems.length > 1);
    }
});
</script>
