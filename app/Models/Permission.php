<?php

declare(strict_types=1);

namespace DoubleE\Models;

class Permission extends BaseModel
{
    protected string $table = 'permissions';

    /**
     * Find a permission by its unique code.
     */
    public function findByCode(string $code): ?array
    {
        return $this->findBy('code', $code);
    }

    /**
     * Get all permissions belonging to a specific module.
     */
    public function getByModule(string $module): array
    {
        return $this->findAll(['module' => $module], 'code ASC');
    }
}
