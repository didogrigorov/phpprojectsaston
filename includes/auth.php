<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('error', 'You must be logged in to access that page.');
        redirect('login.php');
    }
}

function loginUser(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'uid' => (int) $user['uid'],
        'username' => $user['username'],
        'email' => $user['email']
    ];
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}