<?php

declare(strict_types=1);

namespace DoubleE\Models;

class AccountSubType extends BaseModel
{
    protected string $table = 'account_subtypes';

    /**
     * Find a subtype by its code within a specific account type.
     */
    public function findByCode(string $code, int $typeId): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE code = ? AND account_type_id = ?
                LIMIT 1";

        return $this->db->queryOne($sql, [$code, $typeId]);
    }
}
