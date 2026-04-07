<?php

declare(strict_types=1);

namespace DoubleE\Models;

class AccountType extends BaseModel
{
    protected string $table = 'account_types';

    /**
     * Find an account type by its unique code.
     */
    public function findByCode(string $code): ?array
    {
        return $this->findBy('code', $code);
    }

    /**
     * Get all account types ordered by display_order.
     */
    public function getAllOrdered(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY display_order";
        return $this->db->query($sql);
    }

    /**
     * Get all subtypes belonging to a given account type.
     */
    public function getSubtypes(int $typeId): array
    {
        $sql = "SELECT * FROM account_subtypes WHERE account_type_id = ? ORDER BY name";
        return $this->db->query($sql, [$typeId]);
    }
}
