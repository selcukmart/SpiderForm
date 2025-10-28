<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Data Provider Interface
 *
 * Provides abstraction for different data sources:
 * - Doctrine ORM Entities
 * - Laravel Eloquent Models
 * - PDO Direct Queries
 * - Array Data
 *
 * @author selcukmart
 * @since 2.0.0
 */
interface DataProviderInterface
{
    /**
     * Fetch single row by identifier
     *
     * @param mixed $id Primary key value
     * @return array<string, mixed>|null
     */
    public function findById(mixed $id): ?array;

    /**
     * Fetch multiple rows with optional criteria
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<int, array<string, mixed>>
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Fetch all rows
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array;

    /**
     * Execute custom query
     *
     * @param string $query SQL query or DQL
     * @param array<string, mixed> $parameters
     * @return array<int, array<string, mixed>>
     */
    public function query(string $query, array $parameters = []): array;

    /**
     * Get options for select/radio/checkbox
     * Format: ['key' => 'label', ...]
     *
     * @param string $keyColumn Column name for option value
     * @param string $labelColumn Column name for option label
     * @param array<string, mixed> $criteria
     * @return array<string|int, string>
     */
    public function getOptions(
        string $keyColumn,
        string $labelColumn,
        array $criteria = []
    ): array;

    /**
     * Check if data provider is connected/ready
     */
    public function isReady(): bool;

    /**
     * Get provider type identifier
     */
    public function getType(): string;
}
