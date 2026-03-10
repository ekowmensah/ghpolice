<?php
$content = '';

if (isset($officer)) {
    $content .= '
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Officer Information</h3>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> ' . sanitize($officer['first_name'] . ' ' . $officer['last_name']) . '</p>
                <p><strong>Service Number:</strong> ' . sanitize($officer['service_number']) . '</p>
                <p><strong>Rank:</strong> ' . sanitize($officer['rank_name']) . '</p>
            </div>
        </div>
    </div>
</div>';
}

$content .= '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Posting History</h3>
            </div>
            <div class="card-body">
                <table id="postingsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Officer</th>
                            <th>Posting Type</th>
                            <th>Station</th>
                            <th>District</th>
                            <th>Division</th>
                            <th>Region</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Current</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($postings as $posting) {
    $content .= '
                        <tr>
                            <td>' . sanitize($posting['officer_name'] ?? '') . ' (' . sanitize($posting['rank_name'] ?? '') . ')</td>
                            <td>' . sanitize($posting['posting_type']) . '</td>
                            <td>' . sanitize($posting['station_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($posting['district_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($posting['division_name'] ?? 'N/A') . '</td>
                            <td>' . sanitize($posting['region_name'] ?? 'N/A') . '</td>
                            <td>' . date('d M Y', strtotime($posting['start_date'])) . '</td>
                            <td>' . ($posting['end_date'] ? date('d M Y', strtotime($posting['end_date'])) : 'Present') . '</td>
                            <td>' . ($posting['is_current'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>') . '</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $("#postingsTable").DataTable({
        "responsive": true,
        "order": [[6, "desc"]]
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Officers', 'url' => '/officers'],
    ['title' => 'Postings']
];

include __DIR__ . '/../../layouts/main.php';
?>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Officer Postings' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item active">Postings</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($officer)): ?>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Officer Information</h3>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= sanitize($officer['first_name'] . ' ' . $officer['last_name']) ?></p>
                    <p><strong>Service Number:</strong> <?= sanitize($officer['service_number']) ?></p>
                    <p><strong>Rank:</strong> <?= sanitize($officer['rank_name']) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Posting History</h3>
                </div>
                <div class="card-body">
                    <table id="postingsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th>Posting Type</th>
                                <th>Station</th>
                                <th>District</th>
                                <th>Division</th>
                                <th>Region</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Current</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($postings as $posting): ?>
                            <tr>
                                <td><?= sanitize($posting['officer_name'] ?? '') ?> (<?= sanitize($posting['rank_name'] ?? '') ?>)</td>
                                <td><?= sanitize($posting['posting_type']) ?></td>
                                <td><?= sanitize($posting['station_name'] ?? 'N/A') ?></td>
                                <td><?= sanitize($posting['district_name'] ?? 'N/A') ?></td>
                                <td><?= sanitize($posting['division_name'] ?? 'N/A') ?></td>
                                <td><?= sanitize($posting['region_name'] ?? 'N/A') ?></td>
                                <td><?= date('d M Y', strtotime($posting['start_date'])) ?></td>
                                <td><?= $posting['end_date'] ? date('d M Y', strtotime($posting['end_date'])) : 'Present' ?></td>
                                <td>
                                    <?php if ($posting['is_current']): ?>
                                        <span class="badge badge-success">Current</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../partials/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#postingsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[6, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#postingsTable_wrapper .col-md-6:eq(0)');
});
</script>
