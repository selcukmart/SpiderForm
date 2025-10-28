<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

/**
 * Execution Context - Context for Validation
 *
 * Provides context during validation for building violations,
 * accessing form data, and specifying error paths.
 *
 * Usage:
 * ```php
 * $context->buildViolation('Passwords do not match')
 *         ->atPath('password_confirm')
 *         ->addViolation();
 * ```
 *
 * @author selcukmart
 * @since 2.7.0
 */
class ExecutionContext
{
    /**
     * Violations collected during validation
     */
    private array $violations = [];

    /**
     * Form data being validated
     */
    private array $data;

    /**
     * Current validation path
     */
    private string $currentPath = '';

    /**
     * Current validation group
     */
    private ?string $currentGroup = null;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build a violation
     *
     * Returns a ViolationBuilder for fluent configuration
     */
    public function buildViolation(string $message): ViolationBuilder
    {
        return new ViolationBuilder($this, $message);
    }

    /**
     * Add a violation to the context
     *
     * @internal Called by ViolationBuilder
     */
    public function addViolation(string $message, string $path, array $parameters = []): void
    {
        if (!isset($this->violations[$path])) {
            $this->violations[$path] = [];
        }

        // Replace parameters in message
        $finalMessage = $message;
        foreach ($parameters as $key => $value) {
            $finalMessage = str_replace('{{ ' . $key . ' }}', (string) $value, $finalMessage);
        }

        $this->violations[$path][] = $finalMessage;
    }

    /**
     * Get all violations
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Check if context has violations
     */
    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }

    /**
     * Get form data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get specific field value
     */
    public function getValue(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /**
     * Set current validation path
     */
    public function setCurrentPath(string $path): self
    {
        $this->currentPath = $path;
        return $this;
    }

    /**
     * Get current validation path
     */
    public function getCurrentPath(): string
    {
        return $this->currentPath;
    }

    /**
     * Set current validation group
     */
    public function setCurrentGroup(?string $group): self
    {
        $this->currentGroup = $group;
        return $this;
    }

    /**
     * Get current validation group
     */
    public function getCurrentGroup(): ?string
    {
        return $this->currentGroup;
    }

    /**
     * Clear all violations
     */
    public function clear(): void
    {
        $this->violations = [];
    }
}
