<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();

$keyword = normalizeString($_GET['keyword'] ?? '');
$startDate = normalizeString($_GET['start_date'] ?? '');
$phase = normalizeString($_GET['phase'] ?? '');
$sort = $_GET['sort'] ?? 'start_desc';

$sortOptions = [
    'start_desc' => 'start_date DESC, pid DESC',
    'start_asc'  => 'start_date ASC, pid ASC',
    'title_asc'  => 'title ASC, pid ASC',
    'title_desc' => 'title DESC, pid DESC'
];

$orderBy = $sortOptions[$sort] ?? $sortOptions['start_desc'];

$where = [];
$params = [];

if ($keyword !== '') {
    $where[] = "title LIKE :keyword";
    $params['keyword'] = '%' . $keyword . '%';
}

if ($startDate !== '' && validateDate($startDate)) {
    $where[] = "start_date = :start_date";
    $params['start_date'] = $startDate;
}

if ($phase !== '' && validatePhase($phase)) {
    $where[] = "phase = :phase";
    $params['phase'] = $phase;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) FROM projects {$whereSql}";
$countStmt = $pdo->prepare($countSql);

foreach ($params as $key => $value) {
    $countStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}

$countStmt->execute();
$totalProjects = (int) $countStmt->fetchColumn();

$pagination = paginate($totalProjects, 5);

$sql = "SELECT pid, title, start_date, short_description, phase
        FROM projects
        {$whereSql}
        ORDER BY {$orderBy}
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();

$projects = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card hero-card">
    <h1>Search Projects</h1>
    <p class="small-text">
        Search projects by title, start date, phase, and sort the results for easier navigation.
    </p>
</div>

<div class="card">
    <form method="GET" action="search.php">
        <div class="search-bar">
            <div class="form-group">
                <label for="keyword">Project Title</label>
                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    placeholder="Search by title"
                    value="<?= e($keyword) ?>"
                >
            </div>

            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="<?= e($startDate) ?>"
                >
            </div>

            <div class="form-group">
                <label for="phase">Phase</label>
                <select name="phase" id="phase">
                    <option value="">All phases</option>
                    <?php foreach (getAllowedPhases() as $allowedPhase): ?>
                        <option value="<?= e($allowedPhase) ?>" <?= $phase === $allowedPhase ? 'selected' : '' ?>>
                            <?= e(phaseLabel($allowedPhase)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sort">Sort Results</label>
                <select name="sort" id="sort">
                    <option value="start_desc" <?= $sort === 'start_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="start_asc" <?= $sort === 'start_asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A-Z</option>
                    <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z-A</option>
                </select>
            </div>

            <div class="form-group sort-button-wrap">
                <label class="visually-hidden" for="search-submit">Search</label>
                <button id="search-submit" type="submit">Search</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <h2>Search Results</h2>

    <?php if (!$projects): ?>
        <p>No matching projects found.</p>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <article class="card project-card">
                <div class="project-top">
                    <h3><?= e($project['title']) ?></h3>
                    <span class="badge badge-<?= e($project['phase']) ?>">
                        <?= e(phaseLabel($project['phase'])) ?>
                    </span>
                </div>

                <p class="project-meta">Start Date: <?= e($project['start_date']) ?></p>
                <p><?= e($project['short_description']) ?></p>

                <div class="actions">
                    <a class="btn" href="project.php?id=<?= (int) $project['pid'] ?>">View Details</a>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="pagination" aria-label="Search result pages">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php
                    $queryString = buildQueryString([
                        'keyword' => $keyword,
                        'start_date' => $startDate,
                        'phase' => $phase,
                        'sort' => $sort,
                        'page' => $i
                    ]);
                    ?>
                    <a
                        class="page-link <?= $i === $pagination['page'] ? 'current' : '' ?>"
                        href="?<?= e($queryString) ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>