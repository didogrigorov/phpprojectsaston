<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!isPostRequest()) {
    redirect('index.php');
}

$csrfToken = $_POST['csrf_token'] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    setFlash('error', 'Invalid logout request.');
    redirect('index.php');
}

logoutUser();
setFlash('success', 'You have been logged out.');
redirect('login.php');