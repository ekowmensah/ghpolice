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
                <p><strong>Current Rank:</strong> ' . sanitize($officer['rank_name']) . '</p>
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
                <h3 class="card-title"><i class="fas fa-level-up-alt"></i> Promotion History</h3>
            </div>
            <div class="card-body">
                <table id="promotionsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Officer</th>
                            <th>From Rank</th>
                            <th>To Rank</th>
                            <th>Promotion Date</th>
                            <th>Effective Date</th>
                            <th>Order Number</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($promotions as $promotion) {
    $content .= '
                        <tr>
                            <td>' . sanitize($promotion['officer_name'] ?? '') . ' (' . sanitize($promotion['service_number'] ?? '') . ')</td>
                            <td>
                                <span class="badge badge-secondary">
                                    ' . sanitize($promotion['from_rank_name']) . '
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    ' . sanitize($promotion['to_rank_name']) . '
                                </span>
                            </td>
                            <td>' . date('d M Y', strtotime($promotion['promotion_date'])) . '</td>
                            <td>' . date('d M Y', strtotime($promotion['effective_date'])) . '</td>
                            <td>' . sanitize($promotion['order_number'] ?? 'N/A') . '</td>
                            <td>' . sanitize($promotion['approved_by_name'] ?? 'N/A') . '</td>
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
    $("#promotionsTable").DataTable({
        "responsive": true,
        "order": [[3, "desc"]]
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Officers', 'url' => '/officers'],
    ['title' => 'Promotions']
];

include __DIR__ . '/../../layouts/main.php';
?>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?? 'Officer Promotions' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/officers') ?>">Officers</a></li>
                        <li class="breadcrumb-item active">Promotions</li>
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
                    <p><strong>Current Rank:</strong> <?= sanitize($officer['rank_name']) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Promotion History</h3>
                </div>
                <div class="card-body">
                    <table id="promotionsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th>From Rank</th>
                                <th>To Rank</th>
                                <th>Promotion Date</th>
                                <th>Effective Date</th>
                                <th>Order Number</th>
                                <th>Approved By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promotions as $promotion): ?>
                            <tr>
                                <td><?= sanitize($promotion['officer_name'] ?? '') ?> (<?= sanitize($promotion['service_number'] ?? '') ?>)</td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?= sanitize($promotion['from_rank_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= sanitize($promotion['to_rank_name']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($promotion['promotion_date'])) ?></td>
                                <td><?= date('d M Y', strtotime($promotion['effective_date'])) ?></td>
                                <td><?= sanitize($promotion['promotion_order_number'] ?? 'N/A') ?></td>
                                <td><?= sanitize($promotion['approved_by_name'] ?? 'N/A') ?></td>
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
    $('#promotionsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[3, "desc"]],
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#promotionsTable_wrapper .col-md-6:eq(0)');
});
</script>
