<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Rule Interface - Contract for all validation rules
 *
 * All validation rules must implement this interface to work with
 * the validation system.
 *
 * Example:
 * ```php
 * class Uppercase implements RuleInterface
 * {
 *     public function passes(string $attribute, mixed $value, array $parameters = []): bool
 *     {
 *         return is_string($value) && strtoupper($value) === $value;
 *     }
 *
 *     public function message(): string
 *     {
 *         return 'The :attribute must be uppercase.';
 *     }
 * }
 * ```
 */
interface RuleInterface
{
    /**
     * Determine if the validation rule passes
     *
     * @param string $attribute Attribute name being validated
     * @param mixed $value Value to validate
     * @param array $parameters Rule parameters (e.g., ['min' => 3])
     * @return bool True if validation passes
     */
    public function passes(string $attribute, mixed $value, array $parameters = []): bool;

    /**
     * Get the validation error message
     *
     * Supports placeholders:
     * - :attribute - The field name
     * - :value - The field value
     * - :param - First parameter value
     *
     * @return string Error message
     */
    public function message(): string;

    /**
     * Get the rule name
     *
     * Used for identifying the rule in validation strings
     *
     * @return string Rule name (e.g., 'required', 'email')
     */
    public function name(): string;
}
