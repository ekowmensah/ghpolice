<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Intelligence Bulletins</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/intelligence">Intelligence</a></li>
                        <li class="breadcrumb-item active">Bulletins</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Bulletins</h3>
                    <div class="card-tools">
                        <a href="/intelligence/bulletins/create" class="btn btn-danger btn-sm">
                            <i class="fas fa-plus"></i> New Bulletin
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="Crime Alert" <?= $selected_type == 'Crime Alert' ? 'selected' : '' ?>>Crime Alert</option>
                                    <option value="Wanted Person" <?= $selected_type == 'Wanted Person' ? 'selected' : '' ?>>Wanted Person</option>
                                    <option value="Stolen Vehicle" <?= $selected_type == 'Stolen Vehicle' ? 'selected' : '' ?>>Stolen Vehicle</option>
                                    <option value="Missing Person" <?= $selected_type == 'Missing Person' ? 'selected' : '' ?>>Missing Person</option>
                                    <option value="Public Safety" <?= $selected_type == 'Public Safety' ? 'selected' : '' ?>>Public Safety</option>
                                    <option value="Operational" <?= $selected_type == 'Operational' ? 'selected' : '' ?>>Operational</option>
                                    <option value="Intelligence Update" <?= $selected_type == 'Intelligence Update' ? 'selected' : '' ?>>Intelligence Update</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="Active" <?= $selected_status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Expired" <?= $selected_status == 'Expired' ? 'selected' : '' ?>>Expired</option>
                                    <option value="Cancelled" <?= $selected_status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($bulletins)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No bulletins found.
                        </div>
                    <?php else: ?>
                        <?php foreach ($bulletins as $bulletin): ?>
                            <div class="alert alert-<?= $bulletin['priority'] == 'High' ? 'danger' : ($bulletin['priority'] == 'Medium' ? 'warning' : 'info') ?> alert-dismissible">
                                <h5>
                                    <i class="icon fas fa-bullhorn"></i> 
                                    <?= htmlspecialchars($bulletin['bulletin_number']) ?> - <?= htmlspecialchars($bulletin['title']) ?>
                                </h5>
                                <p><?= nl2br(htmlspecialchars($bulletin['content'])) ?></p>
                                <small>
                                    <strong>Type:</strong> <?= htmlspecialchars($bulletin['bulletin_type']) ?> | 
                                    <strong>Priority:</strong> <?= htmlspecialchars($bulletin['priority']) ?> | 
                                    <strong>Valid:</strong> <?= date('Y-m-d', strtotime($bulletin['valid_from'])) ?>
                                    <?php if ($bulletin['valid_until']): ?>
                                        to <?= date('Y-m-d', strtotime($bulletin['valid_until'])) ?>
                                    <?php endif; ?>
                                     | 
                                    <strong>Issued by:</strong> <?= htmlspecialchars($bulletin['issued_by_name']) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
