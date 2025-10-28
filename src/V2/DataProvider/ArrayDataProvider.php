<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataProvider;

use FormGenerator\V2\Contracts\DataProviderInterface;

/**
 * Array Data Provider (for static data)
 *
 * @author selcukmart
 * @since 2.0.0
 */
class ArrayDataProvider implements DataProviderInterface
{
    private array $data;
    private string $primaryKey;

    public function __construct(array $data, string $primaryKey = 'id')
    {
        $this->data = $data;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Fetch single row by identifier
     */
    public function findById(mixed $id): ?array
    {
        foreach ($this->data as $row) {
            if (isset($row[$this->primaryKey]) && $row[$this->primaryKey] == $id) {
                return $row;
            }
        }

        return null;
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
        $results = $this->data;

        // Apply criteria
        if (!empty($criteria)) {
            $results = array_filter($results, function ($row) use ($criteria) {
                foreach ($criteria as $field => $value) {
                    if (!isset($row[$field]) || $row[$field] != $value) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Apply ordering
        if (!empty($orderBy)) {
            usort($results, function ($a, $b) use ($orderBy) {
                foreach ($orderBy as $field => $direction) {
                    $direction = strtoupper($direction);
                    $comparison = $a[$field] <=> $b[$field];

                    if ($comparison !== 0) {
                        return $direction === 'DESC' ? -$comparison : $comparison;
                    }
                }
                return 0;
            });
        }

        // Apply offset
        if ($offset !== null) {
            $results = array_slice($results, $offset);
        }

        // Apply limit
        if ($limit !== null) {
            $results = array_slice($results, 0, $limit);
        }

        return array_values($results);
    }

    /**
     * Fetch all rows
     */
    public function findAll(): array
    {
        return $this->data;
    }

    /**
     * Execute custom query (not applicable for array provider)
     */
    public function query(string $query, array $parameters = []): array
    {
        throw new \BadMethodCallException('Array data provider does not support custom queries');
    }

    /**
     * Get options for select/radio/checkbox
     */
    public function getOptions(
        string $keyColumn,
        string $labelColumn,
        array $criteria = []
    ): array {
        $results = $this->findBy($criteria);
        $options = [];

        foreach ($results as $row) {
            if (isset($row[$keyColumn], $row[$labelColumn])) {
                $options[$row[$keyColumn]] = $row[$labelColumn];
            }
        }

        return $options;
    }

    /**
     * Check if data provider is connected/ready
     */
    public function isReady(): bool
    {
        return true;
    }

    /**
     * Get provider type identifier
     */
    public function getType(): string
    {
        return 'array';
    }

    /**
     * Get all data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Add row
     */
    public function addRow(array $row): void
    {
        $this->data[] = $row;
    }
}
