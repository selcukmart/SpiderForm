<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Exists Rule - Value must exist in a database table
 *
 * Usage: exists:table,column
 * Example: exists:users,id
 * Example with custom column: exists:users,email
 *
 * This rule requires a database connection to be injected.
 */
class Exists implements RuleInterface
{
    private ?\PDO $connection = null;

    public function setConnection(\PDO $connection): void
    {
        $this->connection = $connection;
    }

    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if ($this->connection === null) {
            throw new \RuntimeException('Database connection not set for Exists rule');
        }

        if (empty($parameters[0])) {
            throw new \InvalidArgumentException('Table name is required for Exists rule');
        }

        $table = $parameters[0];
        $column = $parameters[1] ?? $attribute;

        // Build query
        $query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $params = ['value' => $value];

        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    public function message(): string
    {
        return 'The selected :attribute is invalid.';
    }

    public function name(): string
    {
        return 'exists';
    }
}
