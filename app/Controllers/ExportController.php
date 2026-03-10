<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class ExportController extends BaseController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Export cases to CSV
     */
    public function exportCases(): void
    {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'status' => $_GET['status'] ?? null,
            'station_id' => $_GET['station_id'] ?? null
        ];
        
        $cases = $this->getCasesForExport($filters);
        
        $filename = 'cases_export_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'Case Number',
            'Description',
            'Status',
            'Priority',
            'Case Type',
            'Station',
            'Created Date',
            'Investigating Officer'
        ]);
        
        // Data
        foreach ($cases as $case) {
            fputcsv($output, [
                $case['case_number'],
                $case['description'],
                $case['status'],
                $case['case_priority'],
                $case['case_type'],
                $case['station_name'] ?? 'N/A',
                date('Y-m-d', strtotime($case['created_at'])),
                $case['officer_name'] ?? 'Unassigned'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export persons to CSV
     */
    public function exportPersons(): void
    {
        $persons = $this->getPersonsForExport();
        
        $filename = 'persons_export_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'Full Name',
            'Ghana Card',
            'Date of Birth',
            'Gender',
            'Contact',
            'Address',
            'Has Criminal Record',
            'Is Wanted',
            'Registration Date'
        ]);
        
        // Data
        foreach ($persons as $person) {
            fputcsv($output, [
                $person['first_name'] . ' ' . $person['middle_name'] . ' ' . $person['last_name'],
                $person['ghana_card_number'] ?? 'N/A',
                $person['date_of_birth'] ?? 'N/A',
                $person['gender'] ?? 'N/A',
                $person['contact_number'] ?? 'N/A',
                $person['address'] ?? 'N/A',
                $person['has_criminal_record'] ? 'Yes' : 'No',
                $person['is_wanted'] ? 'Yes' : 'No',
                date('Y-m-d', strtotime($person['created_at']))
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export officers to CSV
     */
    public function exportOfficers(): void
    {
        $officers = $this->getOfficersForExport();
        
        $filename = 'officers_export_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'Service Number',
            'Full Name',
            'Rank',
            'Current Station',
            'Employment Status',
            'Date of Enlistment',
            'Contact'
        ]);
        
        // Data
        foreach ($officers as $officer) {
            fputcsv($output, [
                $officer['service_number'],
                $officer['first_name'] . ' ' . $officer['middle_name'] . ' ' . $officer['last_name'],
                $officer['rank'],
                $officer['station_name'] ?? 'Unassigned',
                $officer['employment_status'],
                $officer['date_of_enlistment'] ?? 'N/A',
                $officer['contact_number'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export report to PDF (basic HTML to PDF)
     */
    public function exportReportPDF(): void
    {
        $type = $_GET['type'] ?? 'cases';
        
        // Get data based on type
        $data = [];
        $title = '';
        
        switch ($type) {
            case 'cases':
                $data = $this->getCasesForExport([]);
                $title = 'Cases Report';
                break;
            case 'persons':
                $data = $this->getPersonsForExport();
                $title = 'Persons Report';
                break;
            case 'officers':
                $data = $this->getOfficersForExport();
                $title = 'Officers Report';
                break;
        }
        
        // Simple HTML output (can be enhanced with PDF library)
        header('Content-Type: text/html; charset=utf-8');
        
        echo '<!DOCTYPE html>
<html>
<head>
    <title>' . $title . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ghana Police Service</h1>
        <h2>' . $title . '</h2>
        <p>Generated: ' . date('Y-m-d H:i:s') . '</p>
    </div>';
        
        if ($type === 'cases') {
            echo '<table>
                <thead>
                    <tr>
                        <th>Case Number</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($data as $row) {
                echo '<tr>
                    <td>' . htmlspecialchars($row['case_number']) . '</td>
                    <td>' . htmlspecialchars(substr($row['description'], 0, 50)) . '...</td>
                    <td>' . htmlspecialchars($row['status']) . '</td>
                    <td>' . htmlspecialchars($row['case_priority']) . '</td>
                    <td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>
                </tr>';
            }
            echo '</tbody></table>';
        }
        
        echo '<div class="footer">
            <p>Ghana Police Integrated Management System (GHPIMS)</p>
            <p>This is a confidential document</p>
        </div>
        <script>window.print();</script>
</body>
</html>';
        exit;
    }
    
    private function getCasesForExport(array $filters): array
    {
        $sql = "
            SELECT c.*, s.station_name,
                   CONCAT_WS(' ', o.first_name, o.last_name) as officer_name
            FROM cases c
            LEFT JOIN stations s ON c.station_id = s.id
            LEFT JOIN case_assignments ca ON c.id = ca.case_id
            LEFT JOIN officers o ON ca.officer_id = o.id
            WHERE c.created_at BETWEEN ? AND ?
        ";
        
        $params = [
            $filters['start_date'] ?? date('Y-m-01'),
            ($filters['end_date'] ?? date('Y-m-d')) . ' 23:59:59'
        ];
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['station_id'])) {
            $sql .= " AND c.station_id = ?";
            $params[] = $filters['station_id'];
        }
        
        $sql .= " ORDER BY c.created_at DESC LIMIT 1000";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getPersonsForExport(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM persons
            ORDER BY created_at DESC
            LIMIT 1000
        ");
        return $stmt->fetchAll();
    }
    
    private function getOfficersForExport(): array
    {
        $stmt = $this->db->query("
            SELECT o.*, s.station_name
            FROM officers o
            LEFT JOIN stations s ON o.current_station_id = s.id
            ORDER BY o.service_number
            LIMIT 1000
        ");
        return $stmt->fetchAll();
    }
}
