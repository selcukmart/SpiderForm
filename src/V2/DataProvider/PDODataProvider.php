<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataProvider;

use FormGenerator\V2\Contracts\DataProviderInterface;
use PDO;
use PDOException;

/**
 * PDO Data Provider
 *
 * @author selcukmart
 * @since 2.0.0
 */
class PDODataProvider implements DataProviderInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $table,
        private readonly string $primaryKey = 'id'
    ) {
        // Set PDO attributes for better error handling
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Fetch single row by identifier
     */
    public function findById(mixed $id): ?array
    {
        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = :id LIMIT 1',
            $this->escapeIdentifier($this->table),
            $this->escapeIdentifier($this->primaryKey)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Fetch multiple rows with optional criteria
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = sprintf('SELECT * FROM %s', $this->escapeIdentifier($this->table));
        $params = [];

        // Build WHERE clause
        if (!empty($criteria)) {
            $conditions = [];
            foreach ($criteria as $field => $value) {
                $paramName = ':' . $field;
                $conditions[] = sprintf('%s = %s', $this->escapeIdentifier($field), $paramName);
                $params[$paramName] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // Build ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = sprintf('%s %s', $this->escapeIdentifier($field), $direction);
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        }

        // Build LIMIT/OFFSET clause
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        if ($offset !== null) {
            $sql .= ' OFFSET ' . (int) $offset;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Fetch all rows
     */
    public function findAll(): array
    {
        $sql = sprintf('SELECT * FROM %s', $this->escapeIdentifier($this->table));

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Execute custom query
     */
    public function query(string $query, array $parameters = []): array
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }

    /**
     * Get options for select/radio/checkbox
     */
    public function getOptions(
        string $keyColumn,
        string $labelColumn,
        array $criteria = []
    ): array {
        $sql = sprintf(
            'SELECT %s, %s FROM %s',
            $this->escapeIdentifier($keyColumn),
            $this->escapeIdentifier($labelColumn),
            $this->escapeIdentifier($this->table)
        );

        $params = [];

        // Build WHERE clause
        if (!empty($criteria)) {
            $conditions = [];
            foreach ($criteria as $field => $value) {
                $paramName = ':' . $field;
                $conditions[] = sprintf('%s = %s', $this->escapeIdentifier($field), $paramName);
                $params[$paramName] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $options = [];
        while ($row = $stmt->fetch()) {
            $options[$row[$keyColumn]] = $row[$labelColumn];
        }

        return $options;
    }

    /**
     * Check if data provider is connected/ready
     */
    public function isReady(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get provider type identifier
     */
    public function getType(): string
    {
        return 'pdo';
    }

    /**
     * Get PDO instance
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    /**
     * Get table name
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Escape identifier (table/column names) for SQL injection prevention
     */
    private function escapeIdentifier(string $identifier): string
    {
        // Remove any existing quotes
        $identifier = trim($identifier, '`"\']');

        // Determine the quote character based on driver
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'mysql' => "`{$identifier}`",
            'pgsql' => "\"{$identifier}\"",
            'sqlsrv' => "[{$identifier}]",
            default => "\"{$identifier}\"",
        };
    }

    /**
     * Insert new row
     */
    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->escapeIdentifier($this->table),
            implode(', ', array_map(fn($col) => $this->escapeIdentifier($col), $columns)),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Update existing row
     */
    public function update(mixed $id, array $data): bool
    {
        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $setClauses[] = sprintf('%s = :%s', $this->escapeIdentifier($column), $column);
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :id',
            $this->escapeIdentifier($this->table),
            implode(', ', $setClauses),
            $this->escapeIdentifier($this->primaryKey)
        );

        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Delete row
     */
    public function delete(mixed $id): bool
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE %s = :id',
            $this->escapeIdentifier($this->table),
            $this->escapeIdentifier($this->primaryKey)
        );

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
