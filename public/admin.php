<?php
/**
 * Admin dashboard page controller.
 * Lists users and projects for administrators and typically centralizes privileged management actions.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pdo = getPDO();

$userCount = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$projectCount = (int) $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$logCount = (int) $pdo->query("SELECT COUNT(*) FROM project_logs")->fetchColumn();

$pagination = paginate($projectCount, 10);

$stmt = $pdo->prepare("
    SELECT p.pid, p.title, p.start_date, p.end_date, p.phase, u.username
    FROM projects p
    INNER JOIN users u ON p.uid = u.uid
    ORDER BY p.start_date DESC, p.pid DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$projects = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card hero-card">
    <h1>Admin Panel</h1>
    <p class="small-text">Manage all projects and review system totals.</p>
</div>

<div class="summary-grid">
    <?= renderSummaryCard('Total Users', (string) $userCount) ?>
    <?= renderSummaryCard('Total Projects', (string) $projectCount) ?>
    <?= renderSummaryCard('Total Logs', (string) $logCount) ?>
</div>

<div class="card">
    <h2>All Projects</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Owner</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Phase</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= e($project['title']) ?></td>
                        <td><?= e($project['username']) ?></td>
                        <td><?= e($project['start_date']) ?></td>
                        <td><?= e($project['end_date'] ?? 'Not set') ?></td>
                        <td><span class="badge badge-<?= e($project['phase']) ?>"><?= e(phaseLabel($project['phase'])) ?></span></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-secondary btn-sm" href="edit_project.php?id=<?= (int) $project['pid'] ?>">Edit</a>
                                <form method="GET" action="delete_project.php" class="inline-form">
                                    <input type="hidden" name="id" value="<?= (int) $project['pid'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="pagination" aria-label="Admin project pages">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a class="page-link <?= $i === $pagination['page'] ? 'current' : '' ?>"
                   href="?<?= e(buildQueryString(['page' => $i])) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>