<?php
/**
 * General utility helpers.
 * Contains session bootstrapping, response security headers, escaping, flash messages, validation helpers, pagination, and activity logging.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => !empty($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data:; base-uri 'self'; form-action 'self'; frame-ancestors 'self';");
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function isPostRequest(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function old(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function currentUserId(): ?int
{
    return isset($_SESSION['user']['uid']) ? (int) $_SESSION['user']['uid'] : null;
}

function currentUsername(): ?string
{
    return $_SESSION['user']['username'] ?? null;
}

function currentUserRole(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function isAdmin(): bool
{
    return currentUserRole() === 'admin';
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhase(string $phase): bool
{
    return in_array($phase, getAllowedPhases(), true);
}

function getAllowedPhases(): array
{
    return ['design', 'development', 'testing', 'deployment', 'complete'];
}

function validateDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function normalizeString(string $value): string
{
    return trim($value);
}

function currentPage(): string
{
    return basename($_SERVER['PHP_SELF'] ?? '');
}

function isActivePage(array $pages): bool
{
    return in_array(currentPage(), $pages, true);
}

function renderErrorList(array $errors): void
{
    if (!$errors) {
        return;
    }

    echo '<div class="error-list"><ul>';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul></div>';
}

function phaseLabel(string $phase): string
{
    return ucfirst($phase);
}

function paginate(int $totalItems, int $perPage = 5): array
{
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $page = $page && $page > 0 ? $page : 1;
    $totalPages = max(1, (int) ceil($totalItems / $perPage));

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    return [
        'page' => $page,
        'per_page' => $perPage,
        'offset' => ($page - 1) * $perPage,
        'total_pages' => $totalPages
    ];
}

function buildQueryString(array $params): string
{
    return http_build_query(array_filter(
        $params,
        static fn($value) => $value !== '' && $value !== null
    ));
}

function renderSummaryCard(string $title, string $value): string
{
    return '<div class="summary-card"><h3>' . e($title) . '</h3><p>' . e($value) . '</p></div>';
}

function logProjectAction(PDO $pdo, ?int $pid, int $uid, string $action, ?string $oldPhase = null, ?string $newPhase = null, ?string $details = null): void
{
    $stmt = $pdo->prepare("
        INSERT INTO project_logs (pid, uid, action, old_phase, new_phase, details)
        VALUES (:pid, :uid, :action, :old_phase, :new_phase, :details)
    ");

    $stmt->execute([
        'pid' => $pid,
        'uid' => $uid,
        'action' => $action,
        'old_phase' => $oldPhase,
        'new_phase' => $newPhase,
        'details' => $details
    ]);
}

function loginAttemptsKey(): string
{
    return 'login_attempts';
}

function recordFailedLogin(): void
{
    $key = loginAttemptsKey();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'locked_until' => null];
    }

    $_SESSION[$key]['count']++;

    if ($_SESSION[$key]['count'] >= LOGIN_MAX_ATTEMPTS) {
        $_SESSION[$key]['locked_until'] = time() + (LOGIN_LOCK_MINUTES * 60);
    }
}

function clearFailedLogins(): void
{
    unset($_SESSION[loginAttemptsKey()]);
}

function isLoginLocked(): bool
{
    $data = $_SESSION[loginAttemptsKey()] ?? null;

    if (!$data || empty($data['locked_until'])) {
        return false;
    }

    if (time() >= (int) $data['locked_until']) {
        clearFailedLogins();
        return false;
    }

    return true;
}

function loginLockRemainingMinutes(): int
{
    $data = $_SESSION[loginAttemptsKey()] ?? null;

    if (!$data || empty($data['locked_until'])) {
        return 0;
    }

    return max(1, (int) ceil(((int) $data['locked_until'] - time()) / 60));
}