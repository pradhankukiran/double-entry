<?php

declare(strict_types=1);

namespace DoubleE\Models;

use DoubleE\Core\Database;

abstract class BaseModel
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a single record by its primary key.
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Find all records matching the given conditions.
     *
     * @param array  $conditions Associative array of column => value pairs for WHERE clause
     * @param string $orderBy    ORDER BY clause (e.g. "created_at DESC")
     * @param int    $limit      Maximum number of rows to return (0 = unlimited)
     * @param int    $offset     Number of rows to skip
     */
    public function findAll(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $column => $value) {
                if ($value === null) {
                    $clauses[] = "{$column} IS NULL";
                } else {
                    $clauses[] = "{$column} = ?";
                    $params[] = $value;
                }
            }
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Find a single record by a specific column value.
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1";
        return $this->db->queryOne($sql, [$value]);
    }

    /**
     * Insert a new record and return the auto-increment ID.
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->exec($sql, array_values($data));

        return $this->db->lastInsertId();
    }

    /**
     * Update a record by its primary key.
     */
    public function update(int $id, array $data): bool
    {
        $setClauses = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $params[] = $value;
        }

        $params[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE {$this->primaryKey} = ?";

        return $this->db->exec($sql, $params) >= 0;
    }

    /**
     * Delete a record by primary key.
     * Uses soft delete (is_active = 0) if the table supports it, otherwise hard delete.
     */
    public function delete(int $id): bool
    {
        // Check if table has is_active column by attempting a soft delete
        $row = $this->find($id);
        if ($row === null) {
            return false;
        }

        if (array_key_exists('is_active', $row)) {
            $sql = "UPDATE {$this->table} SET is_active = 0 WHERE {$this->primaryKey} = ?";
        } else {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        }

        return $this->db->exec($sql, [$id]) > 0;
    }

    /**
     * Count records matching the given conditions.
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $column => $value) {
                if ($value === null) {
                    $clauses[] = "{$column} IS NULL";
                } else {
                    $clauses[] = "{$column} = ?";
                    $params[] = $value;
                }
            }
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        return (int) $this->db->queryScalar($sql, $params);
    }
}
