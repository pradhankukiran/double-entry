<?php

declare(strict_types=1);

namespace DoubleE\Models;

use DoubleE\Core\Database;

class Setting extends BaseModel
{
    protected string $table = 'settings';

    /**
     * Get a single setting value by key.
     *
     * @param string $key     The setting key to look up
     * @param mixed  $default Value to return if the key does not exist
     *
     * @return mixed The setting value, or $default if not found
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $db = Database::getInstance();

        $value = $db->queryScalar(
            "SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1",
            [$key]
        );

        if ($value === false || $value === null) {
            return $default;
        }

        return $value;
    }

    /**
     * Set a setting value, creating or updating as needed.
     *
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for an atomic upsert.
     *
     * @param string $key   The setting key
     * @param mixed  $value The value to store (will be cast to string)
     * @param string $group The setting group (default: 'general')
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        $db = Database::getInstance();

        $sql = "INSERT INTO settings (setting_key, setting_value, setting_group, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    setting_group = VALUES(setting_group),
                    updated_at = NOW()";

        $db->exec($sql, [$key, (string) $value, $group]);
    }

    /**
     * Get all settings belonging to a specific group.
     *
     * @param string $group The setting group name
     *
     * @return array Associative array of key => value pairs
     */
    public static function getByGroup(string $group): array
    {
        $db = Database::getInstance();

        $rows = $db->query(
            "SELECT setting_key, setting_value FROM settings WHERE setting_group = ? ORDER BY setting_key ASC",
            [$group]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }

        return $result;
    }

    /**
     * Get all settings as a flat associative array.
     *
     * @return array Associative array of key => value pairs
     */
    public static function getAll(): array
    {
        $db = Database::getInstance();

        $rows = $db->query(
            "SELECT setting_key, setting_value FROM settings ORDER BY setting_group ASC, setting_key ASC"
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }

        return $result;
    }
}
