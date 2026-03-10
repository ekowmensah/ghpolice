<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Upcoming Court Hearings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/court-calendar">Court Calendar</a></li>
                        <li class="breadcrumb-item active">Upcoming</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Next <?= $days ?> Days</h3>
                    <div class="card-tools">
                        <a href="/court-calendar" class="btn btn-default btn-sm">
                            <i class="fas fa-calendar"></i> Monthly View
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Days Ahead</label>
                                    <select name="days" class="form-control">
                                        <option value="7" <?= $days == 7 ? 'selected' : '' ?>>Next 7 Days</option>
                                        <option value="14" <?= $days == 14 ? 'selected' : '' ?>>Next 14 Days</option>
                                        <option value="30" <?= $days == 30 ? 'selected' : '' ?>>Next 30 Days</option>
                                        <option value="60" <?= $days == 60 ? 'selected' : '' ?>>Next 60 Days</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Court</label>
                                    <select name="court" class="form-control">
                                        <option value="">All Courts</option>
                                        <?php foreach ($courts as $court): ?>
                                            <option value="<?= htmlspecialchars($court) ?>" 
                                                <?= $selected_court == $court ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($court) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($hearings)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No upcoming hearings found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Case Number</th>
                                        <th>Description</th>
                                        <th>Court</th>
                                        <th>Hearing Type</th>
                                        <th>Judge</th>
                                        <th>Suspect</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hearings as $hearing): ?>
                                        <tr>
                                            <td>
                                                <strong><?= date('M d, Y', strtotime($hearing['hearing_date'])) ?></strong>
                                                <br>
                                                <small><?= date('H:i', strtotime($hearing['hearing_date'])) ?></small>
                                            </td>
                                            <td>
                                                <a href="/cases/<?= $hearing['case_id'] ?>">
                                                    <?= htmlspecialchars($hearing['case_number']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars(substr($hearing['case_description'], 0, 50)) ?>...</small>
                                            </td>
                                            <td><?= htmlspecialchars($hearing['court_name']) ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($hearing['hearing_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($hearing['judge_name'] ?? 'TBA') ?></td>
                                            <td><?= htmlspecialchars($hearing['suspect_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'Under Investigation' => 'warning',
                                                    'In Court' => 'info',
                                                    'Closed' => 'success'
                                                ];
                                                $class = $statusClass[$hearing['case_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= htmlspecialchars($hearing['case_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/cases/<?= $hearing['case_id'] ?>/court" 
                                                   class="btn btn-sm btn-info" title="View Court Details">
                                                    <i class="fas fa-gavel"></i>
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
