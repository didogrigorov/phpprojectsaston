<?php
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
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
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

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhase(string $phase): bool
{
    $allowed = ['design', 'development', 'testing', 'deployment', 'complete'];
    return in_array($phase, $allowed, true);
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

    $offset = ($page - 1) * $perPage;

    return [
        'page' => $page,
        'per_page' => $perPage,
        'offset' => $offset,
        'total_pages' => $totalPages
    ];
}

function buildQueryString(array $params): string
{
    return http_build_query(array_filter($params, static fn($value) => $value !== '' && $value !== null));
}