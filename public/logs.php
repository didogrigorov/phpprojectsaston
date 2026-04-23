<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$pdo = getPDO();

if (isAdmin()) {
    $count = (int) $pdo->query("SELECT COUNT(*) FROM project_logs")->fetchColumn();
    $pagination = paginate($count, 10);

    $stmt = $pdo->prepare("
        SELECT l.log_id, l.action, l.old_phase, l.new_phase, l.details, l.created_at, u.username, p.title
        FROM project_logs l
        INNER JOIN users u ON l.uid = u.uid
        LEFT JOIN projects p ON l.pid = p.pid
        ORDER BY l.created_at DESC, l.log_id DESC
        LIMIT :limit OFFSET :offset
    ");
} else {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM project_logs WHERE uid = :uid");
    $countStmt->execute(['uid' => currentUserId()]);
    $count = (int) $countStmt->fetchColumn();
    $pagination = paginate($count, 10);

    $stmt = $pdo->prepare("
        SELECT l.log_id, l.action, l.old_phase, l.new_phase, l.details, l.created_at, u.username, p.title
        FROM project_logs l
        INNER JOIN users u ON l.uid = u.uid
        LEFT JOIN projects p ON l.pid = p.pid
        WHERE l.uid = :uid
        ORDER BY l.created_at DESC, l.log_id DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':uid', currentUserId(), PDO::PARAM_INT);
}

$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card hero-card">
    <h1>Activity Logs</h1>
    <p class="small-text">Review project activity history.</p>
</div>

<div class="card">
    <?php if (!$logs): ?>
        <p>No log entries found.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Project</th>
                        <th>Action</th>
                        <th>Old Phase</th>
                        <th>New Phase</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= e($log['created_at']) ?></td>
                            <td><?= e($log['username']) ?></td>
                            <td><?= e($log['title'] ?? 'Deleted project') ?></td>
                            <td><?= e(ucfirst($log['action'])) ?></td>
                            <td><?= e($log['old_phase'] ?? '-') ?></td>
                            <td><?= e($log['new_phase'] ?? '-') ?></td>
                            <td><?= e($log['details'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="pagination" aria-label="Log pages">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <a class="page-link <?= $i === $pagination['page'] ? 'current' : '' ?>"
                       href="?<?= e(buildQueryString(['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>