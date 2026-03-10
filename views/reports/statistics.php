<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Statistics Dashboard</h3>
                <div class="card-tools">
                    <form method="GET" action="' . url('/reports/statistics') . '" class="form-inline">
                        <select name="period" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="day" ' . (($period ?? 'month') === 'day' ? 'selected' : '') . '>Daily</option>
                            <option value="week" ' . (($period ?? 'month') === 'week' ? 'selected' : '') . '>Weekly</option>
                            <option value="month" ' . (($period ?? 'month') === 'month' ? 'selected' : '') . '>Monthly</option>
                            <option value="year" ' . (($period ?? 'month') === 'year' ? 'selected' : '') . '>Yearly</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Case Statistics</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Total</th>
                                            <th>Closed</th>
                                            <th>Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

if (!empty($stats['cases'])) {
    foreach ($stats['cases'] as $row) {
        $rate = $row['total'] > 0 ? round(($row['closed'] / $row['total']) * 100, 1) : 0;
        $content .= '
                                        <tr>
                                            <td>' . sanitize($row['period']) . '</td>
                                            <td>' . $row['total'] . '</td>
                                            <td>' . $row['closed'] . '</td>
                                            <td><span class="badge badge-' . ($rate >= 70 ? 'success' : ($rate >= 50 ? 'warning' : 'danger')) . '">' . $rate . '%</span></td>
                                        </tr>';
    }
} else {
    $content .= '
                                        <tr>
                                            <td colspan="4" class="text-center">No data available</td>
                                        </tr>';
}

$content .= '
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Person Registry</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header">' . ($stats['persons']['total_registered'] ?? 0) . '</h5>
                                            <span class="description-text">Total Registered</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header">' . ($stats['persons']['with_records'] ?? 0) . '</h5>
                                            <span class="description-text">With Records</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header text-danger">' . ($stats['persons']['wanted'] ?? 0) . '</h5>
                                            <span class="description-text">Wanted</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header text-warning">' . ($stats['persons']['active_alerts'] ?? 0) . '</h5>
                                            <span class="description-text">Active Alerts</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Officer Statistics</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header">' . ($stats['officers']['total_officers'] ?? 0) . '</h5>
                                            <span class="description-text">Total Officers</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header text-success">' . ($stats['officers']['active'] ?? 0) . '</h5>
                                            <span class="description-text">Active</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header text-warning">' . ($stats['officers']['on_leave'] ?? 0) . '</h5>
                                            <span class="description-text">On Leave</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header">' . ($stats['officers']['assigned_to_cases'] ?? 0) . '</h5>
                                            <span class="description-text">Assigned</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Crime Trends (Last 6 Months)</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Case Type</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

if (!empty($stats['crime_trends'])) {
    $total = array_sum(array_column($stats['crime_trends'], 'count'));
    foreach ($stats['crime_trends'] as $trend) {
        $percentage = $total > 0 ? round(($trend['count'] / $total) * 100, 1) : 0;
        $content .= '
                                        <tr>
                                            <td>' . sanitize($trend['case_type']) . '</td>
                                            <td>' . $trend['count'] . '</td>
                                            <td>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-primary" style="width: ' . $percentage . '%"></div>
                                                </div>
                                                ' . $percentage . '%
                                            </td>
                                        </tr>';
    }
} else {
    $content .= '
                                        <tr>
                                            <td colspan="3" class="text-center">No data available</td>
                                        </tr>';
}

$content .= '
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Crime Trends Chart
const ctx = document.getElementById("crimeTrendsChart");
if (ctx) {
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: <?= json_encode(array_column($stats["crime_trends"] ?? [], "case_type")) ?>,
            datasets: [{
                label: "Number of Cases",
                data: <?= json_encode(array_column($stats["crime_trends"] ?? [], "count")) ?>,
                backgroundColor: "rgba(54, 162, 235, 0.5)",
                borderColor: "rgba(54, 162, 235, 1)",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// Case Trends Chart
const ctx2 = document.getElementById("caseTrendsChart");
if (ctx2) {
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: <?= json_encode(array_column($stats["cases"] ?? [], "period")) ?>,
            datasets: [{
                label: "Total Cases",
                data: <?= json_encode(array_column($stats["cases"] ?? [], "total")) ?>,
                borderColor: "rgba(75, 192, 192, 1)",
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                tension: 0.1
            }, {
                label: "Closed Cases",
                data: <?= json_encode(array_column($stats["cases"] ?? [], "closed")) ?>,
                borderColor: "rgba(54, 162, 235, 1)",
                backgroundColor: "rgba(54, 162, 235, 0.2)",
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
</script>';

$breadcrumbs = [
    ['title' => 'Reports', 'url' => '/reports'],
    ['title' => 'Statistics']
];

include __DIR__ . '/../layouts/main.php';
?>
