<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

/**
 * Form Configuration Interface
 *
 * Holds immutable form configuration data
 *
 * @author selcukmart
 * @since 2.4.0
 */
interface FormConfigInterface
{
    /**
     * Get form name
     */
    public function getName(): string;

    /**
     * Get form type
     */
    public function getType(): string;

    /**
     * Get form method (GET/POST)
     */
    public function getMethod(): string;

    /**
     * Get form action URL
     */
    public function getAction(): string;

    /**
     * Get form options
     */
    public function getOptions(): array;

    /**
     * Get specific option
     */
    public function getOption(string $key, mixed $default = null): mixed;

    /**
     * Check if option exists
     */
    public function hasOption(string $key): bool;

    /**
     * Get form attributes
     */
    public function getAttributes(): array;

    /**
     * Check if form has CSRF protection enabled
     */
    public function hasCsrfProtection(): bool;

    /**
     * Check if form has validation enabled
     */
    public function hasValidation(): bool;

    /**
     * Check if error bubbling is enabled
     */
    public function hasErrorBubbling(): bool;

    /**
     * Get compound status (has children)
     */
    public function isCompound(): bool;
}
