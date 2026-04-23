<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();

$totalStmt = $pdo->query("SELECT COUNT(*) FROM projects");
$totalProjects = (int) $totalStmt->fetchColumn();

$pagination = paginate($totalProjects, 5);

$stmt = $pdo->prepare("
    SELECT p.pid, p.title, p.start_date, p.short_description, p.phase
    FROM projects p
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
    <h1>All Projects</h1>
    <p class="small-text">Browse all software projects, view project details, and search by title, date, or phase.</p>
</div>

<?php if (!$projects): ?>
    <div class="card">
        <p>No projects found.</p>
    </div>
<?php else: ?>
    <?php foreach ($projects as $project): ?>
        <article class="card project-card">
            <div class="project-top">
                <h2><?= e($project['title']) ?></h2>
                <span class="badge badge-<?= e($project['phase']) ?>"><?= e(phaseLabel($project['phase'])) ?></span>
            </div>

            <p class="project-meta">Start Date: <?= e($project['start_date']) ?></p>
            <p><?= e($project['short_description']) ?></p>

            <div class="actions">
                <a class="btn" href="project.php?id=<?= (int) $project['pid'] ?>">View Details</a>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="pagination" aria-label="Project pages">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a class="page-link <?= $i === $pagination['page'] ? 'current' : '' ?>" href="?page=<?= $i ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>