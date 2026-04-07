<?php

declare(strict_types=1);

namespace DoubleE\Models;

use DoubleE\Core\Database;
use DoubleE\Core\Session;

class AuditLog extends BaseModel
{
    protected string $table = 'audit_log';

    /**
     * Write an audit log entry for the current user and request.
     */
    public static function log(
        string $action,
        string $entityType = '',
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        $db = Database::getInstance();
        $userId = Session::get('user_id');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $sql = "INSERT INTO audit_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $db->exec($sql, [
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues !== null ? json_encode($oldValues, JSON_THROW_ON_ERROR) : null,
            $newValues !== null ? json_encode($newValues, JSON_THROW_ON_ERROR) : null,
            $ip,
        ]);
    }

    /**
     * Get the most recent audit log entries with user details.
     */
    public function getRecent(int $limit = 50): array
    {
        $sql = "SELECT al.*, u.first_name, u.last_name, u.email
                FROM {$this->table} al
                LEFT JOIN users u ON u.id = al.user_id
                ORDER BY al.created_at DESC
                LIMIT ?";

        return $this->db->query($sql, [$limit]);
    }
}
