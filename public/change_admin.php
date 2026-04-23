<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = getPDO();

// 🔑 CHANGE THIS PASSWORD
$newPassword = 'Admin1234';

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
$stmt->execute(['password' => $hash]);

echo "Admin password updated successfully!";