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
$sort = $_GET['sort'] ?? 'start_desc';

$sortOptions = [
    'start_desc' => 'start_date DESC, pid DESC',
    'start_asc'  => 'start_date ASC, pid ASC',
    'title_asc'  => 'title ASC, pid ASC',
    'title_desc' => 'title DESC, pid DESC'
];
$orderBy = $sortOptions[$sort] ?? $sortOptions['start_desc'];

$summaryStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_projects,
        SUM(phase = 'design') AS design_count,
        SUM(phase = 'development') AS development_count,
        SUM(phase = 'complete') AS complete_count
    FROM projects
    WHERE uid = :uid
");
$summaryStmt->execute(['uid' => $userId]);
$summary = $summaryStmt->fetch();

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE uid = :uid");
$countStmt->execute(['uid' => $userId]);
$totalProjects = (int) $countStmt->fetchColumn();

$pagination = paginate($totalProjects, 5);

$stmt = $pdo->prepare("
    SELECT pid, title, start_date, end_date, phase
    FROM projects
    WHERE uid = :uid
    ORDER BY {$orderBy}
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$projects = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card hero-card">
    <h1>Dashboard</h1>
    <p>Welcome, <strong><?= e((string) currentUsername()) ?></strong>.</p>
    <p class="small-text">Manage your projects and review your summary below.</p>
</div>

<div class="summary-grid">
    <?= renderSummaryCard('Total Projects', (string) ($summary['total_projects'] ?? 0)) ?>
    <?= renderSummaryCard('Design', (string) ($summary['design_count'] ?? 0)) ?>
    <?= renderSummaryCard('Development', (string) ($summary['development_count'] ?? 0)) ?>
    <?= renderSummaryCard('Complete', (string) ($summary['complete_count'] ?? 0)) ?>
</div>

<div class="card">
    <div class="actions">
        <a class="btn" href="add_project.php">Add New Project</a>
        <a class="btn btn-secondary" href="profile.php">View Profile</a>
    </div>
</div>

<div class="card">
    <form method="GET" action="dashboard.php" class="search-bar">
        <div class="form-group">
            <label for="sort">Sort My Projects</label>
            <select name="sort" id="sort">
                <option value="start_desc" <?= $sort === 'start_desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="start_asc" <?= $sort === 'start_asc' ? 'selected' : '' ?>>Oldest First</option>
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A-Z</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z-A</option>
            </select>
        </div>

        <div class="form-group sort-button-wrap">
            <label class="visually-hidden" for="dashboard-sort-submit">Apply sort</label>
            <button id="dashboard-sort-submit" type="submit">Apply Sorting</button>
        </div>
    </form>
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

        <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="pagination" aria-label="Dashboard project pages">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <a class="page-link <?= $i === $pagination['page'] ? 'current' : '' ?>"
                       href="?<?= e(buildQueryString(['page' => $i, 'sort' => $sort])) ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>