<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Validator Interface
 *
 * Provides abstraction for validation rules
 *
 * @author selcukmart
 * @since 2.0.0
 */
interface ValidatorInterface
{
    /**
     * Validate a value against rules
     *
     * @param mixed $value Value to validate
     * @param array<string, mixed> $rules Validation rules
     * @param array<string, mixed> $context Additional context (other field values)
     * @return ValidationResult
     */
    public function validate(mixed $value, array $rules, array $context = []): ValidationResult;

    /**
     * Add custom validation rule
     *
     * @param string $name Rule name
     * @param callable $callback Validation callback (value, params, context) => bool
     * @param string $message Error message template
     */
    public function addRule(string $name, callable $callback, string $message): void;

    /**
     * Check if rule exists
     */
    public function hasRule(string $name): bool;

    /**
     * Get JavaScript validation code for rules
     *
     * @param array<string, mixed> $rules
     * @return string JavaScript validation code
     */
    public function getJavaScriptCode(array $rules): string;

    /**
     * Validate entire form data
     *
     * @param array<string, mixed> $data Form data
     * @param array<string, array<string, mixed>> $fieldsRules Fields and their rules
     * @return array<string, ValidationResult>
     */
    public function validateForm(array $data, array $fieldsRules): array;
}
