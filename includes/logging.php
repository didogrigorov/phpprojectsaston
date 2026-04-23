<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function logProjectAction(int $projectId, int $userId, string $action, ?string $oldPhase = null, ?string $newPhase = null): void
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        INSERT INTO project_logs (pid, uid, action, old_phase, new_phase)
        VALUES (:pid, :uid, :action, :old_phase, :new_phase)
    ");

    $stmt->execute([
        'pid' => $projectId,
        'uid' => $userId,
        'action' => $action,
        'old_phase' => $oldPhase,
        'new_phase' => $newPhase
    ]);
}