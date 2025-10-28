<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Unique Rule - Value must be unique in a database table
 *
 * Usage: unique:table,column,except,idColumn
 * Example: unique:users,email
 * Example with exception: unique:users,email,5,id
 *
 * This rule requires a database connection to be injected.
 */
class Unique implements RuleInterface
{
    private ?\PDO $connection = null;
    private ?string $exceptId = null;
    private ?string $idColumn = 'id';

    public function setConnection(\PDO $connection): void
    {
        $this->connection = $connection;
    }

    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if ($this->connection === null) {
            throw new \RuntimeException('Database connection not set for Unique rule');
        }

        if (empty($parameters[0])) {
            throw new \InvalidArgumentException('Table name is required for Unique rule');
        }

        $table = $parameters[0];
        $column = $parameters[1] ?? $attribute;
        $except = $parameters[2] ?? null;
        $idColumn = $parameters[3] ?? 'id';

        // Build query
        $query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $params = ['value' => $value];

        // Add exception clause if needed
        if ($except !== null) {
            $query .= " AND {$idColumn} != :except";
            $params['except'] = $except;
        }

        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchColumn() === 0;
    }

    public function message(): string
    {
        return 'The :attribute has already been taken.';
    }

    public function name(): string
    {
        return 'unique';
    }
}
