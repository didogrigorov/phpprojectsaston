<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/validation.js"></script>
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="logo" href="index.php"><?= e(APP_NAME) ?></a>

        <nav class="nav" aria-label="Main navigation">
            <a class="<?= isActivePage(['index.php', 'project.php']) ? 'active' : '' ?>" href="index.php">All Projects</a>
            <a class="<?= isActivePage(['search.php']) ? 'active' : '' ?>" href="search.php">Search</a>

            <?php if (isLoggedIn()): ?>
                <a class="<?= isActivePage(['dashboard.php']) ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
                <a class="<?= isActivePage(['profile.php', 'edit_profile.php']) ? 'active' : '' ?>" href="profile.php">Profile</a>
                <a class="<?= isActivePage(['add_project.php']) ? 'active' : '' ?>" href="add_project.php">Add Project</a>
                <a class="<?= isActivePage(['logs.php']) ? 'active' : '' ?>" href="logs.php">Logs</a>
                <a class="<?= isActivePage(['export_projects.php']) ? 'active' : '' ?>" href="export_projects.php">Export CSV</a>

                <?php if (isAdmin()): ?>
                    <a class="<?= isActivePage(['admin.php']) ? 'active' : '' ?>" href="admin.php">Admin</a>
                <?php endif; ?>

                <form class="logout-form" method="POST" action="logout.php">
                    <?= csrfField() ?>
                    <button type="submit" class="nav-button">Logout</button>
                </form>
            <?php else: ?>
                <a class="<?= isActivePage(['register.php']) ? 'active' : '' ?>" href="register.php">Register</a>
                <a class="<?= isActivePage(['login.php']) ? 'active' : '' ?>" href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>