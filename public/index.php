<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();
$sort = $_GET['sort'] ?? 'start_desc';

$sortOptions = [
    'start_desc' => 'p.start_date DESC, p.pid DESC',
    'start_asc'  => 'p.start_date ASC, p.pid ASC',
    'title_asc'  => 'p.title ASC, p.pid ASC',
    'title_desc' => 'p.title DESC, p.pid DESC'
];

$orderBy = $sortOptions[$sort] ?? $sortOptions['start_desc'];

$totalStmt = $pdo->query("SELECT COUNT(*) FROM projects");
$totalProjects = (int) $totalStmt->fetchColumn();
$pagination = paginate($totalProjects, 5);

$stmt = $pdo->prepare("
    SELECT p.pid, p.title, p.start_date, p.short_description, p.phase, u.username
    FROM projects p
    INNER JOIN users u ON p.uid = u.uid
    ORDER BY $orderBy
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
    <p class="small-text">Browse software projects, sort them, and view project owners.</p>
</div>

<div class="card">
    <form method="GET" action="index.php" class="search-bar">
        <div class="form-group">
            <label for="sort">Sort Projects</label>
            <select name="sort" id="sort">
                <option value="start_desc" <?= $sort === 'start_desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="start_asc" <?= $sort === 'start_asc' ? 'selected' : '' ?>>Oldest First</option>
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A-Z</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z-A</option>
            </select>
        </div>

        <div class="form-group sort-button-wrap">
            <label class="visually-hidden" for="sort-submit">Apply sort</label>
            <button id="sort-submit" type="submit">Apply Sorting</button>
        </div>
    </form>
</div>

<?php if (!$projects): ?>
    <div class="card"><p>No projects found.</p></div>
<?php else: ?>
    <?php foreach ($projects as $project): ?>
        <article class="card project-card">
            <div class="project-top">
                <h2><?= e($project['title']) ?></h2>
                <span class="badge badge-<?= e($project['phase']) ?>"><?= e(phaseLabel($project['phase'])) ?></span>
            </div>
            <p class="project-meta">Start Date: <?= e($project['start_date']) ?> | Owner: <?= e($project['username']) ?></p>
            <p><?= e($project['short_description']) ?></p>
            <div class="actions">
                <a class="btn" href="project.php?id=<?= (int) $project['pid'] ?>">View Details</a>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="pagination" aria-label="Project pages">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a class="page-link <?= $i === $pagination['page'] ? 'current' : '' ?>"
                   href="?<?= e(buildQueryString(['page' => $i, 'sort' => $sort])) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>