<?php
/**
 * Registration page controller.
 * Validates the signup form, hashes the password, creates a new user account, and redirects to login on success.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

$pdo = getPDO();
$errors = [];

if (isPostRequest()) {
    $username = normalizeString($_POST['username'] ?? '');
    $email = normalizeString($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
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

    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include one uppercase letter and one number.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $checkStmt = $pdo->prepare("SELECT uid FROM users WHERE username = :username OR email = :email LIMIT 1");
        $checkStmt->execute(['username' => $username, 'email' => $email]);

        if ($checkStmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, email, role)
            VALUES (:username, :password, :email, 'user')
        ");
        $stmt->execute([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email
        ]);

        setFlash('success', 'Registration successful. You can now log in.');
        redirect('login.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card form-card">
    <h1>Register</h1>
    <?php renderErrorList($errors); ?>

    <form id="register-form" method="POST" action="register.php" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required maxlength="50" autocomplete="username" value="<?= e(old('username')) ?>">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required maxlength="100" autocomplete="email" value="<?= e(old('email')) ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
            <p class="small-text">At least 8 characters, one uppercase letter, and one number.</p>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">
        </div>

        <div class="actions">
            <button type="submit">Create Account</button>
            <a class="btn btn-secondary" href="login.php">Back to Login</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>