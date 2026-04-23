<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT username, email, created_at
    FROM users
    WHERE uid = :uid
    LIMIT 1
");
$stmt->execute(['uid' => currentUserId()]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User profile could not be loaded.');
    redirect('dashboard.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card hero-card">
    <h1>My Profile</h1>
    <p class="small-text">View your account information and membership details.</p>
</div>

<div class="card">
    <div class="detail-grid">
        <div>
            <strong>Username:</strong><br>
            <?= e($user['username']) ?>
        </div>

        <div>
            <strong>Email:</strong><br>
            <?= e($user['email']) ?>
        </div>

        <div>
            <strong>Member Since:</strong><br>
            <?= e($user['created_at']) ?>
        </div>
    </div>

    <hr class="section-divider">

    <div class="actions">
        <a class="btn btn-secondary" href="dashboard.php">Back to Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>