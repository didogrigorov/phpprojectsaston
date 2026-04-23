<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireLogin();

$pdo = getPDO();
$userId = currentUserId();

$stmt = $pdo->prepare("
    SELECT pid, title, start_date, end_date, phase
    FROM projects
    WHERE uid = :uid
    ORDER BY start_date DESC, pid DESC
");
$stmt->execute(['uid' => $userId]);
$projects = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card hero-card">
    <h1>Dashboard</h1>
    <p>Welcome, <strong><?= e((string) currentUsername()) ?></strong>.</p>
    <p class="small-text">You can only manage your own projects.</p>
</div>

<div class="card">
    <div class="actions">
        <a class="btn" href="add_project.php">Add New Project</a>
    </div>
</div>

<div class="card">
    <h2>My Projects</h2>

    <?php if (!$projects): ?>
        <p>You have not added any projects yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>