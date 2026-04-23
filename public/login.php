<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getPDO();
$errors = [];

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if (isPostRequest()) {
    $username = normalizeString($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? null;

    if (!verifyCsrfToken($csrfToken)) {
        $errors[] = 'Invalid CSRF token.';
    }

    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT uid, username, password, email FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid login details.';
        } else {
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE uid = :uid");
                $updateStmt->execute([
                    'password' => $newHash,
                    'uid' => $user['uid']
                ]);
            }

            loginUser($user);
            setFlash('success', 'Login successful.');
            redirect('dashboard.php');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card form-card">
    <h1>Login</h1>

    <?php renderErrorList($errors); ?>

    <form method="POST" action="login.php" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required value="<?= e(old('username')) ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>

        <button type="submit">Login</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>