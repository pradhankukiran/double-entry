<?php

declare(strict_types=1);

namespace DoubleE\Models;

class ContactAddress extends BaseModel
{
    protected string $table = 'contact_addresses';

    /**
     * Get all addresses belonging to a contact.
     */
    public function getByContact(int $contactId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE contact_id = ?
                ORDER BY is_default DESC, type";

        return $this->db->query($sql, [$contactId]);
    }
}
