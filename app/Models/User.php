<?php

declare(strict_types=1);

namespace DoubleE\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Verify a plain-text password against a stored hash.
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Record a successful login: update timestamp, IP, and reset failure count.
     */
    public function updateLastLogin(int $id, string $ip): void
    {
        $sql = "UPDATE {$this->table}
                SET last_login_at = NOW(),
                    last_login_ip = ?,
                    failed_login_count = 0,
                    locked_until = NULL
                WHERE {$this->primaryKey} = ?";

        $this->db->exec($sql, [$ip, $id]);
    }

    /**
     * Increment the failed login counter and lock the account after 5 failures.
     */
    public function incrementFailedLogin(int $id): void
    {
        $sql = "UPDATE {$this->table}
                SET failed_login_count = failed_login_count + 1,
                    locked_until = CASE
                        WHEN failed_login_count + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        ELSE locked_until
                    END
                WHERE {$this->primaryKey} = ?";

        $this->db->exec($sql, [$id]);
    }

    /**
     * Check whether a user account is currently locked.
     */
    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        return strtotime($user['locked_until']) > time();
    }

    /**
     * Get all roles assigned to a user.
     */
    public function getRoles(int $userId): array
    {
        $sql = "SELECT r.*
                FROM roles r
                INNER JOIN user_roles ur ON ur.role_id = r.id
                WHERE ur.user_id = ?";

        return $this->db->query($sql, [$userId]);
    }
}
