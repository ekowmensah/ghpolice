<?php
$title = 'Leave Management';

$content = '
<style>
.leave-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border-left: 4px solid;
}

.leave-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.leave-pending { border-left-color: #ffc107; }
.leave-approved { border-left-color: #28a745; }
.leave-rejected { border-left-color: #dc3545; }
.leave-active { border-left-color: #17a2b8; }
.leave-cancelled { border-left-color: #6c757d; }

.leave-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
}

.stat-item {
    text-align: center;
    border-right: 1px solid rgba(255,255,255,0.2);
}

.stat-item:last-child {
    border-right: none;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    display: block;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.leave-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
}

.leave-duration {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.calendar-view {
    background: white;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.calendar-month {
    text-align: center;
    font-weight: bold;
    color: #333;
    margin-bottom: 1rem;
}

.leave-timeline {
    position: relative;
    padding-left: 2rem;
}

.leave-timeline::before {
    content: "";
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item::before {
    content: "";
    position: absolute;
    left: -1.75rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    border: 2px solid white;
}

.timeline-item.pending::before { background: #ffc107; }
.timeline-item.approved::before { background: #28a745; }
.timeline-item.rejected::before { background: #dc3545; }
.timeline-item.active::before { background: #17a2b8; }

.status-badge {
    font-weight: 600;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.875rem;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-approved { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }
.status-active { background: #d1ecf1; color: #0c5460; }
.status-cancelled { background: #e2e3e5; color: #383d41; }

.filter-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.officer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.875rem;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-6">
                <h1 class="text-white"><i class="fas fa-calendar-alt"></i> Leave Management</h1>
            </div>
            <div class="col-md-6 text-right">
                <a href="' . url('/officers/leave/create') . '" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Request Leave
                </a>
                <div class="btn-group ml-2">
                    <a href="' . url('/officers/leave?status=pending') . '" class="btn btn-warning">
                        <i class="fas fa-clock"></i> Pending
                    </a>
                    <a href="' . url('/officers/leave?status=active') . '" class="btn btn-success">
                        <i class="fas fa-play-circle"></i> Active
                    </a>
                    <a href="' . url('/officers/leave') . '" class="btn btn-secondary">
                        <i class="fas fa-list"></i> All
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Officer Information (if specific officer) -->
';

if (isset($officer)) {
    $content .= '
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-user-shield fa-3x text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge badge-dark">' . sanitize($officer['rank_name']) . '</span>
                            <span class="ml-2">Service No: ' . sanitize($officer['service_number']) . '</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>';
}

$content .= '
        <!-- Statistics Cards -->
        <div class="leave-stats mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count($leaves) . '</span>
                        <span class="stat-label">Total Requests</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($leaves, fn($l) => $l['leave_status'] === 'Pending')) . '</span>
                        <span class="stat-label">Pending</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . count(array_filter($leaves, fn($l) => $l['leave_status'] === 'Active')) . '</span>
                        <span class="stat-label">On Leave</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">' . array_sum(array_column($leaves, 'total_days')) . '</span>
                        <span class="stat-label">Total Days</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-control" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Active">Active</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="typeFilter">
                        <option value="">All Leave Types</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Maternity Leave">Maternity Leave</option>
                        <option value="Paternity Leave">Paternity Leave</option>
                        <option value="Study Leave">Study Leave</option>
                        <option value="Compassionate Leave">Compassionate Leave</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="monthFilter">
                        <option value="">All Months</option>';

$months = array_unique(array_map(fn($l) => date('F Y', strtotime($l['start_date'])), $leaves));
foreach ($months as $month) {
    $content .= '<option value="' . $month . '">' . $month . '</option>';
}

$content .= '
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchFilter" placeholder="Search officer...">
                </div>
            </div>
        </div>

        <!-- Leave Requests Grid -->
        <div class="row" id="leaveGrid">';

foreach ($leaves as $leave) {
    $content .= '
            <div class="col-lg-6 col-xl-4 mb-4 leave-item" 
                 data-status="' . sanitize($leave['leave_status']) . '"
                 data-type="' . sanitize($leave['leave_type']) . '"
                 data-month="' . date('F Y', strtotime($leave['start_date'])) . '"
                 data-search="' . strtolower(sanitize(($leave['officer_name'] ?? '') . ' ' . $leave['leave_type'])) . '">
                <div class="card leave-card h-100 leave-' . strtolower($leave['leave_status']) . '">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="officer-avatar mr-2">
                                    ' . substr(sanitize($leave['officer_name'] ?? 'U'), 0, 1) . '
                                </div>
                                <div>
                                    <h6 class="card-title mb-0">' . sanitize($leave['officer_name'] ?? 'Unknown') . '</h6>
                                    <small class="text-muted">' . sanitize($leave['rank_name'] ?? '') . '</small>
                                </div>
                            </div>
                            <span class="status-badge status-' . strtolower($leave['leave_status']) . '">
                                ' . sanitize($leave['leave_status']) . '
                            </span>
                        </div>

                        <div class="mb-3">
                            <span class="leave-type-badge bg-info text-white">
                                ' . sanitize($leave['leave_type']) . '
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="leave-duration mb-2">
                                <i class="far fa-calendar"></i>
                                ' . date('d M Y', strtotime($leave['start_date'])) . ' - 
                                ' . date('d M Y', strtotime($leave['end_date'])) . '
                            </div>
                            <div class="text-muted small">
                                <strong>' . $leave['total_days'] . ' days</strong> of leave
                            </div>
                        </div>';

    if ($leave['station_name']) {
        $content .= '
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-building"></i> ' . sanitize($leave['station_name']) . '
                                </small>
                            </div>';
    }

    if ($leave['approved_by_name']) {
        $content .= '
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-user-check"></i> Approved by: ' . sanitize($leave['approved_by_name']) . '
                                </small>
                            </div>';
    }

    $content .= '
                        <div class="text-right">
                            ' . (($leave['leave_status'] === 'Pending') ? '
                                    <button class="btn btn-sm btn-success approve-leave" data-id="' . $leave['id'] . '">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-leave" data-id="' . $leave['id'] . '">
                                        <i class="fas fa-times"></i> Reject
                                    </button>' : '
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewLeaveDetails(' . $leave['id'] . ')">
                                        <i class="fas fa-eye"></i> Details
                                    </button>') . '
                                </div>
                    </div>
                </div>
            </div>';
}

$content .= '
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No leave requests found</h5>
            <p class="text-muted">Try adjusting your filters or search criteria</p>
        </div>
    </div>
</section>';

$scripts = '
<script>
$(document).ready(function() {
    // Filter functionality
    function applyFilters() {
        const statusFilter = $("#statusFilter").val();
        const typeFilter = $("#typeFilter").val();
        const monthFilter = $("#monthFilter").val();
        const searchFilter = $("#searchFilter").val().toLowerCase();
        
        let visibleCount = 0;
        
        $(".leave-item").each(function() {
            const $item = $(this);
            const status = $item.data("status");
            const type = $item.data("type");
            const month = $item.data("month");
            const search = $item.data("search");
            
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesType = !typeFilter || type === typeFilter;
            const matchesMonth = !monthFilter || month === monthFilter;
            const matchesSearch = !searchFilter || search.includes(searchFilter);
            
            if (matchesStatus && matchesType && matchesMonth && matchesSearch) {
                $item.show();
                visibleCount++;
            } else {
                $item.hide();
            }
        });
        
        $("#noResults").toggle(visibleCount === 0);
    }
    
    $("#statusFilter, #typeFilter, #monthFilter, #searchFilter").on("input change", applyFilters);
    
    // Approve/Reject leave handlers
    $(".approve-leave").click(function() {
        const id = $(this).data("id");
        if (confirm("Are you sure you want to approve this leave request?")) {
            // Implement approval logic
            console.log("Approve leave:", id);
        }
    });
    
    $(".reject-leave").click(function() {
        const id = $(this).data("id");
        if (confirm("Are you sure you want to reject this leave request?")) {
            // Implement rejection logic
            console.log("Reject leave:", id);
        }
    });
    
    // View leave details
    window.viewLeaveDetails = function(id) {
        // This would typically open a modal or navigate to detail page
        console.log("View leave details for ID:", id);
    };
});
</script>';

include __DIR__ . '/../../layouts/main.php';
?>
