<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$pdo = getPDO();

if (isAdmin()) {
    $stmt = $pdo->query("
        SELECT p.pid, p.title, p.start_date, p.end_date, p.phase, u.username
        FROM projects p
        INNER JOIN users u ON p.uid = u.uid
        ORDER BY p.start_date DESC, p.pid DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT p.pid, p.title, p.start_date, p.end_date, p.phase, u.username
        FROM projects p
        INNER JOIN users u ON p.uid = u.uid
        WHERE p.uid = :uid
        ORDER BY p.start_date DESC, p.pid DESC
    ");
    $stmt->execute(['uid' => currentUserId()]);
}

$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=projects_export.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Project ID', 'Title', 'Start Date', 'End Date', 'Phase', 'Owner']);

foreach ($rows as $row) {
    fputcsv($output, [
        $row['pid'],
        $row['title'],
        $row['start_date'],
        $row['end_date'],
        $row['phase'],
        $row['username']
    ]);
}

fclose($output);
exit;