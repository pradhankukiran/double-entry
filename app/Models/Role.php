<?php

declare(strict_types=1);

namespace DoubleE\Models;

class Role extends BaseModel
{
    protected string $table = 'roles';

    /**
     * Get all permissions assigned to a role.
     */
    public function getPermissions(int $roleId): array
    {
        $sql = "SELECT p.*
                FROM permissions p
                INNER JOIN role_permissions rp ON rp.permission_id = p.id
                WHERE rp.role_id = ?";

        return $this->db->query($sql, [$roleId]);
    }

    /**
     * Find a role by its name.
     */
    public function findByName(string $name): ?array
    {
        return $this->findBy('name', $name);
    }
}
