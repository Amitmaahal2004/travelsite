<?php
declare(strict_types=1);

namespace App\Services;

use Core\Auth;
use Core\Database;

/**
 * Activity & Audit Logging Service
 */
class AuditService
{
    public static function log(string $module, string $action, string $description, ?string $entityType = null, ?int $entityId = null, ?int $userId = null): void
    {
        try {
            $db = Database::getInstance();
            $uid = $userId ?? ($_SESSION['auth_user_id'] ?? null);
            $db->insert('activity_logs', [
                'user_id' => $uid ? (int) $uid : null,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('AuditService failure: ' . $e->getMessage());
        }
    }
}
