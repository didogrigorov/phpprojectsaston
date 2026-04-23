<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireLogin();

$pdo = getPDO();
$projectId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$projectId) {
    setFlash('error', 'Invalid project ID.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("SELECT pid, title FROM projects WHERE pid = :pid AND uid = :uid LIMIT 1");
$stmt->execute([
    'pid' => $projectId,
    'uid' => currentUserId()
]);
$project = $stmt->fetch();

if (!$project) {
    setFlash('error', 'Project not found or access denied.');
    redirect('dashboard.php');
}

if (isPostRequest()) {
    $csrfToken = $_POST['csrf_token'] ?? null;

    if (!verifyCsrfToken($csrfToken)) {
        setFlash('error', 'Invalid CSRF token.');
        redirect('dashboard.php');
    }

    $deleteStmt = $pdo->prepare("DELETE FROM projects WHERE pid = :pid AND uid = :uid");
    $deleteStmt->execute([
        'pid' => $projectId,
        'uid' => currentUserId()
    ]);

    setFlash('success', 'Project deleted successfully.');
    redirect('dashboard.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card form-card">
    <h1>Delete Project</h1>
    <p>Are you sure you want to delete <strong><?= e($project['title']) ?></strong>?</p>

    <form method="POST" action="delete_project.php?id=<?= (int) $projectId ?>">
        <?= csrfField() ?>
        <div class="actions">
            <button type="submit" class="btn btn-danger">Yes, Delete</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>