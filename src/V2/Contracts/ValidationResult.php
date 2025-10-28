<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Validation Result
 *
 * Represents the result of a validation operation
 *
 * @author selcukmart
 * @since 2.0.0
 */
class ValidationResult
{
    public function __construct(
        private readonly bool $valid,
        private readonly array $errors = [],
        private readonly array $warnings = []
    ) {
    }

    /**
     * Check if validation passed
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Check if validation failed
     */
    public function isFailed(): bool
    {
        return !$this->valid;
    }

    /**
     * Get error messages
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Check if has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get warning messages
     *
     * @return array<string, string>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Check if has warnings
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Get all messages (errors + warnings)
     *
     * @return array{errors: array<string, string>, warnings: array<string, string>}
     */
    public function getAllMessages(): array
    {
        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Create successful validation result
     */
    public static function success(array $warnings = []): self
    {
        return new self(true, [], $warnings);
    }

    /**
     * Create failed validation result
     */
    public static function failure(array $errors, array $warnings = []): self
    {
        return new self(false, $errors, $warnings);
    }
}
