<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Court Calendar</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Court Calendar</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <?= date('F Y', strtotime($selected_month . '-01')) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="/court-calendar/upcoming" class="btn btn-info btn-sm">
                            <i class="fas fa-calendar-alt"></i> Upcoming Hearings
                        </a>
                        <a href="/court-calendar/daily" class="btn btn-primary btn-sm">
                            <i class="fas fa-calendar-day"></i> Daily Schedule
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Month</label>
                                    <input type="month" name="month" class="form-control" 
                                           value="<?= htmlspecialchars($selected_month) ?>">
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
                            <i class="fas fa-info-circle"></i> No hearings scheduled for this month.
                        </div>
                    <?php else: ?>
                        <?php
                        // Group hearings by date
                        $hearingsByDate = [];
                        foreach ($hearings as $hearing) {
                            $date = date('Y-m-d', strtotime($hearing['hearing_date']));
                            if (!isset($hearingsByDate[$date])) {
                                $hearingsByDate[$date] = [];
                            }
                            $hearingsByDate[$date][] = $hearing;
                        }
                        
                        foreach ($hearingsByDate as $date => $dayHearings):
                        ?>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-day"></i> 
                                        <?= date('l, F d, Y', strtotime($date)) ?>
                                        <span class="badge badge-primary float-right">
                                            <?= count($dayHearings) ?> Hearings
                                        </span>
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Case Number</th>
                                                <th>Court</th>
                                                <th>Hearing Type</th>
                                                <th>Judge</th>
                                                <th>Suspect</th>
                                                <th>Priority</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dayHearings as $hearing): ?>
                                                <tr>
                                                    <td><?= date('H:i', strtotime($hearing['hearing_date'])) ?></td>
                                                    <td>
                                                        <a href="/cases/<?= $hearing['case_id'] ?>">
                                                            <?= htmlspecialchars($hearing['case_number']) ?>
                                                        </a>
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
                                                        $priorityClass = [
                                                            'High' => 'danger',
                                                            'Medium' => 'warning',
                                                            'Low' => 'success'
                                                        ];
                                                        $class = $priorityClass[$hearing['case_priority']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge badge-<?= $class ?>">
                                                            <?= htmlspecialchars($hearing['case_priority']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="/cases/<?= $hearing['case_id'] ?>/court" 
                                                           class="btn btn-xs btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
