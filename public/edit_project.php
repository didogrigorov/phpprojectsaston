<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireLogin();

$pdo = getPDO();
$errors = [];
$projectId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$projectId) {
    setFlash('error', 'Invalid project ID.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM projects
    WHERE pid = :pid AND uid = :uid
    LIMIT 1
");
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
    $title = normalizeString($_POST['title'] ?? '');
    $startDate = normalizeString($_POST['start_date'] ?? '');
    $endDate = normalizeString($_POST['end_date'] ?? '');
    $description = normalizeString($_POST['short_description'] ?? '');
    $phase = normalizeString($_POST['phase'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? null;

    if (!verifyCsrfToken($csrfToken)) {
        $errors[] = 'Invalid CSRF token.';
    }

    if ($title === '' || strlen($title) < 3 || strlen($title) > 150) {
        $errors[] = 'Title must be between 3 and 150 characters.';
    }

    if (!validateDate($startDate)) {
        $errors[] = 'A valid start date is required.';
    }

    if ($endDate !== '' && !validateDate($endDate)) {
        $errors[] = 'End date must be valid or left empty.';
    }

    if ($endDate !== '' && $endDate < $startDate) {
        $errors[] = 'End date cannot be earlier than start date.';
    }

    if ($description === '' || strlen($description) < 10) {
        $errors[] = 'Description must be at least 10 characters.';
    }

    if (!validatePhase($phase)) {
        $errors[] = 'Please select a valid phase.';
    }

    if (!$errors) {
        $updateStmt = $pdo->prepare("
            UPDATE projects
            SET title = :title,
                start_date = :start_date,
                end_date = :end_date,
                short_description = :short_description,
                phase = :phase
            WHERE pid = :pid AND uid = :uid
        ");

        $updateStmt->execute([
            'title' => $title,
            'start_date' => $startDate,
            'end_date' => $endDate !== '' ? $endDate : null,
            'short_description' => $description,
            'phase' => $phase,
            'pid' => $projectId,
            'uid' => currentUserId()
        ]);

        setFlash('success', 'Project updated successfully.');
        redirect('dashboard.php');
    } else {
        $project = [
            'pid' => $projectId,
            'title' => $title,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'short_description' => $description,
            'phase' => $phase
        ];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card form-card">
    <h1>Edit Project</h1>

    <?php renderErrorList($errors); ?>

    <form id="project-form" method="POST" action="edit_project.php?id=<?= (int) $projectId ?>" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="title">Project Title</label>
            <input
                id="title"
                name="title"
                type="text"
                required
                maxlength="150"
                value="<?= e($project['title']) ?>"
            >
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input
                    id="start_date"
                    name="start_date"
                    type="date"
                    required
                    value="<?= e($project['start_date']) ?>"
                >
            </div>

            <div class="form-group">
                <label for="end_date">End Date</label>
                <input
                    id="end_date"
                    name="end_date"
                    type="date"
                    value="<?= e($project['end_date'] ?? '') ?>"
                >
            </div>
        </div>

        <div class="form-group">
            <label for="phase">Phase</label>
            <select id="phase" name="phase" required>
                <option value="">Select phase</option>
                <?php foreach (getAllowedPhases() as $allowedPhase): ?>
                    <option value="<?= e($allowedPhase) ?>" <?= $project['phase'] === $allowedPhase ? 'selected' : '' ?>>
                        <?= e(phaseLabel($allowedPhase)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="short_description">Short Description</label>
            <textarea
                id="short_description"
                name="short_description"
                required
            ><?= e($project['short_description']) ?></textarea>
        </div>

        <div class="actions">
            <button type="submit">Update Project</button>
            <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>