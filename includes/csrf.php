<?php
/**
 * CSRF protection helpers.
 * Generates, prints, and verifies one session-based anti-forgery token used by POST forms.
 */
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}

function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token'], $token)
        && hash_equals($_SESSION['csrf_token'], $token);
}