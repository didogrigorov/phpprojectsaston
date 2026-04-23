<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();

$projectId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$projectId) {
    setFlash('error', 'Invalid project ID.');
    redirect('index.php');
}

$sql = "SELECT 
            p.pid,
            p.title,
            p.start_date,
            p.end_date,
            p.short_description,
            p.phase,
            u.username
        FROM projects p
        INNER JOIN users u ON p.uid = u.uid
        WHERE p.pid = :pid
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute(['pid' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    setFlash('error', 'Project not found.');
    redirect('index.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="project-top">
        <h1><?= e($project['title']) ?></h1>
        <span class="badge badge-<?= e($project['phase']) ?>">
            <?= e(phaseLabel($project['phase'])) ?>
        </span>
    </div>

    <div class="detail-grid">
        <div><strong>Start Date:</strong> <?= e($project['start_date']) ?></div>
        <div><strong>End Date:</strong> <?= e($project['end_date'] ?? 'Not set') ?></div>
        <div><strong>Phase:</strong> <?= e(phaseLabel($project['phase'])) ?></div>
        <div><strong>Project Owner:</strong> <?= e($project['username']) ?></div>
    </div>

    <hr class="section-divider">

    <p><strong>Description:</strong></p>
    <p><?= nl2br(e($project['short_description'])) ?></p>

    <div class="actions">
        <a class="btn btn-secondary" href="index.php">Back to Projects</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>