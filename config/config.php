<?php
/**
 * Project-wide configuration values.
 * Keeps app identity, database credentials, session settings, and login throttling values in one place.
 */
declare(strict_types=1);

define('APP_NAME', 'Software Project Manager');
define('APP_URL', 'https://webdevtests.site/');

define('DB_HOST', 'localhost');
define('DB_NAME', 'aproject');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SESSION_NAME', 'spm_session');
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCK_MINUTES', 5);