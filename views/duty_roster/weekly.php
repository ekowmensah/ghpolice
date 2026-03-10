<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Weekly Duty Roster</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/duty-roster">Duty Roster</a></li>
                        <li class="breadcrumb-item active">Weekly View</li>
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
                        Week of <?= date('M d, Y', strtotime($start_date)) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="/duty-roster" class="btn btn-default btn-sm">
                            <i class="fas fa-calendar-day"></i> Daily View
                        </a>
                        <a href="/duty-roster/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Schedule Duty
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Station</label>
                                    <select name="station" class="form-control select2">
                                        <option value="">All Stations</option>
                                        <?php foreach ($stations as $station): ?>
                                            <option value="<?= $station['id'] ?>" 
                                                <?= $selected_station == $station['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($station['station_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Week Starting</label>
                                    <input type="date" name="start_date" class="form-control" 
                                           value="<?= htmlspecialchars($start_date) ?>">
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

                    <?php if (empty($roster)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No duty assignments found for this week.
                        </div>
                    <?php else: ?>
                        <?php
                        // Group roster by day
                        $rosterByDay = [];
                        foreach ($roster as $duty) {
                            $day = date('Y-m-d', strtotime($duty['duty_date']));
                            if (!isset($rosterByDay[$day])) {
                                $rosterByDay[$day] = [];
                            }
                            $rosterByDay[$day][] = $duty;
                        }
                        
                        // Generate 7 days starting from start_date
                        for ($i = 0; $i < 7; $i++) {
                            $currentDate = date('Y-m-d', strtotime($start_date . " +{$i} days"));
                            $dayName = date('l, M d', strtotime($currentDate));
                            $dayDuties = $rosterByDay[$currentDate] ?? [];
                        ?>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-day"></i> <?= $dayName ?>
                                        <span class="badge badge-primary float-right">
                                            <?= count($dayDuties) ?> Officers
                                        </span>
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($dayDuties)): ?>
                                        <p class="text-muted p-3 mb-0">No duties scheduled</p>
                                    <?php else: ?>
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Officer</th>
                                                    <th>Rank</th>
                                                    <th>Shift</th>
                                                    <th>Time</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dayDuties as $duty): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($duty['officer_name']) ?></td>
                                                        <td><?= htmlspecialchars($duty['rank']) ?></td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                <?= htmlspecialchars($duty['shift_name']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?= date('H:i', strtotime($duty['start_time'])) ?> - 
                                                            <?= date('H:i', strtotime($duty['end_time'])) ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-secondary">
                                                                <?= htmlspecialchars($duty['duty_type']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $statusClass = [
                                                                'Scheduled' => 'warning',
                                                                'On Duty' => 'success',
                                                                'Completed' => 'info',
                                                                'Cancelled' => 'danger'
                                                            ];
                                                            $class = $statusClass[$duty['status']] ?? 'secondary';
                                                            ?>
                                                            <span class="badge badge-<?= $class ?>">
                                                                <?= htmlspecialchars($duty['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
