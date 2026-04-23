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

$stmt = $pdo->prepare("SELECT username, email, password FROM users WHERE uid = :uid LIMIT 1");
$stmt->execute(['uid' => currentUserId()]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User profile could not be loaded.');
    redirect('profile.php');
}

if (isPostRequest()) {
    $username = normalizeString($_POST['username'] ?? '');
    $email = normalizeString($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? null;

    if (!verifyCsrfToken($csrfToken)) {
        $errors[] = 'Invalid CSRF token.';
    }

    if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters.';
    }

    if (!validateEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    }

    $checkStmt = $pdo->prepare("
        SELECT uid FROM users
        WHERE (username = :username OR email = :email) AND uid != :uid
        LIMIT 1
    ");
    $checkStmt->execute([
        'username' => $username,
        'email' => $email,
        'uid' => currentUserId()
    ]);

    if ($checkStmt->fetch()) {
        $errors[] = 'Username or email already exists.';
    }

    if (($newPassword !== '' || $confirmNewPassword !== '') && !password_verify($currentPassword, $user['password'])) {
        $errors[] = 'Current password is required to change password.';
    }

    if ($newPassword !== '' || $confirmNewPassword !== '') {
        if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $errors[] = 'New password must be at least 8 characters and include one uppercase letter and one number.';
        }

        if ($newPassword !== $confirmNewPassword) {
            $errors[] = 'New passwords do not match.';
        }
    }

    if (!$errors) {
        $sql = "UPDATE users SET username = :username, email = :email";
        $params = [
            'username' => $username,
            'email' => $email,
            'uid' => currentUserId()
        ];

        if ($newPassword !== '') {
            $sql .= ", password = :password";
            $params['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE uid = :uid";

        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute($params);

        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;

        setFlash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card form-card">
    <h1>Edit Profile</h1>
    <?php renderErrorList($errors); ?>

    <form method="POST" action="edit_profile.php" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required value="<?= e(old('username', $user['username'])) ?>">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?= e(old('email', $user['email'])) ?>">
        </div>

        <hr class="section-divider">

        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input id="current_password" name="current_password" type="password">
        </div>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input id="new_password" name="new_password" type="password">
        </div>

        <div class="form-group">
            <label for="confirm_new_password">Confirm New Password</label>
            <input id="confirm_new_password" name="confirm_new_password" type="password">
        </div>

        <div class="actions">
            <button type="submit">Save Changes</button>
            <a class="btn btn-secondary" href="profile.php">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>