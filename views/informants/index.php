<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-secret"></i> Informant Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/intelligence') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item active">Informants</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Confidential:</strong> This information is highly sensitive. Only handlers can view their own informants.
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Informants</h3>
                    <div class="card-tools">
                        <a href="<?= url('/informants/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Register Informant
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="Active" <?= $selected_status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $selected_status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="Compromised" <?= $selected_status == 'Compromised' ? 'selected' : '' ?>>Compromised</option>
                                    <option value="Retired" <?= $selected_status == 'Retired' ? 'selected' : '' ?>>Retired</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($informants)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No informants registered.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Reliability</th>
                                        <th>Area of Operation</th>
                                        <th>Specialization</th>
                                        <th>Recruitment Date</th>
                                        <th>Intel Count</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($informants as $informant): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($informant['informant_code']) ?></strong></td>
                                            <td>
                                                <?php
                                                $reliabilityClass = [
                                                    'Reliable' => 'success',
                                                    'Usually Reliable' => 'info',
                                                    'Fairly Reliable' => 'warning',
                                                    'Unreliable' => 'danger',
                                                    'Unproven' => 'secondary'
                                                ];
                                                $class = $reliabilityClass[$informant['reliability_rating']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($informant['reliability_rating']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($informant['area_of_operation']) ?></td>
                                            <td><?= htmlspecialchars($informant['specialization'] ?? 'General') ?></td>
                                            <td><?= date('Y-m-d', strtotime($informant['recruitment_date'])) ?></td>
                                            <td><span class="badge badge-primary"><?= $informant['intel_count'] ?></span></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'Active' => 'success',
                                                    'Inactive' => 'secondary',
                                                    'Compromised' => 'danger',
                                                    'Retired' => 'info'
                                                ];
                                                $class = $statusClass[$informant['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($informant['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= url('/informants/' . $informant['id']) ?>" class="btn btn-sm btn-info">
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
    </section>
</div>
