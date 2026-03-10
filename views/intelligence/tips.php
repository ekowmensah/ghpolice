<?php
ob_start();
?>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Public Intelligence Tips</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="Pending" <?= $selected_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Verified" <?= $selected_status == 'Verified' ? 'selected' : '' ?>>Verified</option>
                                    <option value="False" <?= $selected_status == 'False' ? 'selected' : '' ?>>False</option>
                                    <option value="Cannot Verify" <?= $selected_status == 'Cannot Verify' ? 'selected' : '' ?>>Cannot Verify</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="category" class="form-control" placeholder="Filter by category" value="<?= htmlspecialchars($selected_category ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($tips)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No intelligence tips found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Tip #</th>
                                        <th>Source</th>
                                        <th>Category</th>
                                        <th>Content Preview</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th>Received</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tips as $tip): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($tip['tip_number']) ?></strong></td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?= htmlspecialchars($tip['tip_source']) ?>
                                                </span>
                                                <?php if ($tip['is_anonymous']): ?>
                                                    <br><small class="text-muted">Anonymous</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($tip['tip_category'] ?? 'N/A') ?></td>
                                            <td>
                                                <?= htmlspecialchars(substr($tip['tip_content'], 0, 100)) ?>
                                                <?= strlen($tip['tip_content']) > 100 ? '...' : '' ?>
                                            </td>
                                            <td>
                                                <?php
                                                $urgencyClass = [
                                                    'Critical' => 'danger',
                                                    'High' => 'warning',
                                                    'Medium' => 'info',
                                                    'Low' => 'secondary'
                                                ];
                                                $class = $urgencyClass[$tip['urgency']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($tip['urgency']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'Pending' => 'warning',
                                                    'Verified' => 'success',
                                                    'False' => 'danger',
                                                    'Cannot Verify' => 'secondary'
                                                ];
                                                $class = $statusClass[$tip['verification_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($tip['verification_status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('Y-m-d H:i', strtotime($tip['received_at'])) ?></td>
                                            <td>
                                                <a href="<?= url('/tips/' . $tip['id']) ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
$content = ob_get_clean();

$title = 'Public Intelligence Tips';
$breadcrumbs = [
    ['title' => 'Intelligence', 'url' => '/intelligence'],
    ['title' => 'Tips']
];

include __DIR__ . '/../layouts/main.php';
?>
