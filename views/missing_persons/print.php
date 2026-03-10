<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Missing Person Report - <?= htmlspecialchars($person['report_number']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h3 {
            background: #f0f0f0;
            padding: 8px;
            margin: 10px 0 5px 0;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-missing { background: #ffc107; color: #000; }
        .status-found-alive { background: #28a745; color: #fff; }
        .status-found-deceased { background: #dc3545; color: #fff; }
        .status-closed { background: #6c757d; color: #fff; }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 12px;
            color: #666;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>GHANA POLICE SERVICE</h1>
        <h2>MISSING PERSON REPORT</h2>
        <p><strong>Report Number:</strong> <?= htmlspecialchars($person['report_number']) ?></p>
    </div>

    <div class="section">
        <h3>Personal Information</h3>
        <table>
            <tr>
                <td>Full Name:</td>
                <td><?= htmlspecialchars($person['first_name'] . ' ' . ($person['middle_name'] ? $person['middle_name'] . ' ' : '') . $person['last_name']) ?></td>
            </tr>
            <tr>
                <td>Gender:</td>
                <td><?= htmlspecialchars($person['gender'] ?? 'Not specified') ?></td>
            </tr>
            <tr>
                <td>Date of Birth:</td>
                <td><?= $person['date_of_birth'] ? date('F j, Y', strtotime($person['date_of_birth'])) : 'Not specified' ?></td>
            </tr>
            <?php if ($person['date_of_birth']): ?>
            <tr>
                <td>Age:</td>
                <td><?= date_diff(date_create($person['date_of_birth']), date_create('now'))->y ?> years</td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Height:</td>
                <td><?= htmlspecialchars($person['height'] ?? 'Not specified') ?></td>
            </tr>
            <tr>
                <td>Weight:</td>
                <td><?= htmlspecialchars($person['weight'] ?? 'Not specified') ?></td>
            </tr>
            <tr>
                <td>Complexion:</td>
                <td><?= htmlspecialchars($person['complexion'] ?? 'Not specified') ?></td>
            </tr>
            <?php if ($person['distinguishing_marks']): ?>
            <tr>
                <td>Distinguishing Marks:</td>
                <td><?= nl2br(htmlspecialchars($person['distinguishing_marks'])) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="section">
        <h3>Last Seen Information</h3>
        <table>
            <tr>
                <td>Date & Time:</td>
                <td><?= date('F j, Y g:i A', strtotime($person['last_seen_date'])) ?></td>
            </tr>
            <tr>
                <td>Location:</td>
                <td><?= htmlspecialchars($person['last_seen_location']) ?></td>
            </tr>
            <?php if ($person['last_seen_wearing']): ?>
            <tr>
                <td>Last Seen Wearing:</td>
                <td><?= nl2br(htmlspecialchars($person['last_seen_wearing'])) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Circumstances:</td>
                <td><?= nl2br(htmlspecialchars($person['circumstances'])) ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Reporter Information</h3>
        <table>
            <tr>
                <td>Reported By:</td>
                <td><?= htmlspecialchars($person['reported_by_name']) ?></td>
            </tr>
            <tr>
                <td>Contact:</td>
                <td><?= htmlspecialchars($person['reported_by_contact'] ?? 'Not provided') ?></td>
            </tr>
            <tr>
                <td>Relationship:</td>
                <td><?= htmlspecialchars($person['relationship_to_missing'] ?? 'Not specified') ?></td>
            </tr>
            <tr>
                <td>Report Date:</td>
                <td><?= date('F j, Y g:i A', strtotime($person['created_at'])) ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Status Information</h3>
        <table>
            <tr>
                <td>Current Status:</td>
                <td>
                    <?php
                    $statusClass = match($person['status']) {
                        'Missing' => 'status-missing',
                        'Found Alive' => 'status-found-alive',
                        'Found Deceased' => 'status-found-deceased',
                        'Closed' => 'status-closed',
                        default => 'status-closed'
                    };
                    ?>
                    <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($person['status']) ?></span>
                </td>
            </tr>
            <?php if ($person['found_date']): ?>
            <tr>
                <td>Found Date:</td>
                <td><?= date('F j, Y', strtotime($person['found_date'])) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($person['found_location']): ?>
            <tr>
                <td>Found Location:</td>
                <td><?= htmlspecialchars($person['found_location']) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="footer">
        <p>This is an official document of the Ghana Police Service.</p>
        <p>Printed on: <?= date('F j, Y g:i A') ?></p>
    </div>
</body>
</html>
