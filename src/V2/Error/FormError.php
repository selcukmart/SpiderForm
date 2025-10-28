<?php

declare(strict_types=1);

namespace FormGenerator\V2\Error;

use FormGenerator\V2\Form\FormInterface;

/**
 * Form Error - Structured Error Representation
 *
 * Represents a single form error with message, level, path, and cause.
 * Provides rich error information for professional error handling.
 *
 * Usage:
 * ```php
 * $error = new FormError(
 *     message: 'Email is required',
 *     level: ErrorLevel::ERROR,
 *     path: 'email',
 *     cause: $validationException
 * );
 *
 * echo $error->getMessage();     // "Email is required"
 * echo $error->getPath();        // "email"
 * echo $error->getLevel()->value; // "error"
 * ```
 *
 * @author selcukmart
 * @since 2.9.0
 */
class FormError
{
    /**
     * Error message
     */
    private readonly string $message;

    /**
     * Error severity level
     */
    private readonly ErrorLevel $level;

    /**
     * Field path (e.g., 'address.zipcode' for nested)
     */
    private readonly ?string $path;

    /**
     * Message parameters for interpolation
     */
    private readonly array $parameters;

    /**
     * Original cause (exception, etc.)
     */
    private readonly mixed $cause;

    /**
     * Form that owns this error
     */
    private readonly ?FormInterface $origin;

    /**
     * @param string $message Error message (can include {{ parameter }} placeholders)
     * @param ErrorLevel $level Error severity level
     * @param string|null $path Field path (null for form-level errors)
     * @param array $parameters Parameters for message interpolation
     * @param mixed $cause Original cause (exception, validation result, etc.)
     * @param FormInterface|null $origin Form that generated this error
     */
    public function __construct(
        string $message,
        ErrorLevel $level = ErrorLevel::ERROR,
        ?string $path = null,
        array $parameters = [],
        mixed $cause = null,
        ?FormInterface $origin = null
    ) {
        $this->message = $message;
        $this->level = $level;
        $this->path = $path;
        $this->parameters = $parameters;
        $this->cause = $cause;
        $this->origin = $origin;
    }

    /**
     * Get error message
     */
    public function getMessage(): string
    {
        return $this->interpolateMessage($this->message, $this->parameters);
    }

    /**
     * Get raw message (without interpolation)
     */
    public function getRawMessage(): string
    {
        return $this->message;
    }

    /**
     * Get error level
     */
    public function getLevel(): ErrorLevel
    {
        return $this->level;
    }

    /**
     * Get field path
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Get message parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get cause
     */
    public function getCause(): mixed
    {
        return $this->cause;
    }

    /**
     * Get origin form
     */
    public function getOrigin(): ?FormInterface
    {
        return $this->origin;
    }

    /**
     * Check if this is a blocking error
     */
    public function isBlocking(): bool
    {
        return $this->level->isBlocking();
    }

    /**
     * Interpolate message parameters
     *
     * Replaces {{ parameter }} with values from $parameters array
     *
     * Example:
     * - Message: "Field {{ field }} must be at least {{ min }} characters"
     * - Parameters: ['field' => 'username', 'min' => 3]
     * - Result: "Field username must be at least 3 characters"
     */
    private function interpolateMessage(string $message, array $parameters): string
    {
        if (empty($parameters)) {
            return $message;
        }

        return preg_replace_callback(
            '/\{\{\s*(\w+)\s*\}\}/',
            function ($matches) use ($parameters) {
                $key = $matches[1];
                return $parameters[$key] ?? $matches[0];
            },
            $message
        );
    }

    /**
     * Convert to array representation
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'level' => $this->level->value,
            'path' => $this->path,
            'parameters' => $this->parameters,
        ];
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        $prefix = $this->path ? "{$this->path}: " : '';
        return $prefix . $this->getMessage();
    }
}
