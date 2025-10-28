<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataProvider;

use FormGenerator\V2\Contracts\DataProviderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laravel Eloquent Data Provider
 *
 * @author selcukmart
 * @since 2.0.0
 */
class EloquentDataProvider implements DataProviderInterface
{
    private Builder $query;

    public function __construct(
        private readonly Model|string $model
    ) {
        if (is_string($this->model)) {
            if (!class_exists($this->model)) {
                throw new \InvalidArgumentException("Model class {$this->model} does not exist");
            }
            $modelInstance = new $this->model();
        } else {
            $modelInstance = $this->model;
        }

        $this->query = $modelInstance->newQuery();
    }

    /**
     * Fetch single row by identifier
     */
    public function findById(mixed $id): ?array
    {
        $model = $this->query->find($id);

        if ($model === null) {
            return null;
        }

        return $model->toArray();
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
        $query = clone $this->query;

        // Apply criteria (where clauses)
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        // Apply ordering
        foreach ($orderBy as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        // Apply limit and offset
        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        $results = $query->get();

        return $results->map(fn($model) => $model->toArray())->toArray();
    }

    /**
     * Fetch all rows
     */
    public function findAll(): array
    {
        $results = $this->query->get();

        return $results->map(fn($model) => $model->toArray())->toArray();
    }

    /**
     * Execute custom query
     */
    public function query(string $query, array $parameters = []): array
    {
        $connection = $this->query->getConnection();
        $results = $connection->select($query, $parameters);

        return array_map(function ($result) {
            if (is_object($result)) {
                return (array) $result;
            }
            return $result;
        }, $results);
    }

    /**
     * Get options for select/radio/checkbox
     */
    public function getOptions(
        string $keyColumn,
        string $labelColumn,
        array $criteria = []
    ): array {
        $query = clone $this->query;

        // Apply criteria
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        $results = $query->get([$keyColumn, $labelColumn]);

        $options = [];
        foreach ($results as $result) {
            $options[$result->{$keyColumn}] = $result->{$labelColumn};
        }

        return $options;
    }

    /**
     * Check if data provider is connected/ready
     */
    public function isReady(): bool
    {
        try {
            $this->query->getConnection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get provider type identifier
     */
    public function getType(): string
    {
        return 'eloquent';
    }

    /**
     * Get underlying query builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Apply custom query modifications
     */
    public function applyQuery(callable $callback): self
    {
        $callback($this->query);
        return $this;
    }

    /**
     * Get model instance
     */
    public function getModel(): Model
    {
        return $this->query->getModel();
    }
}
