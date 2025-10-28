<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

use Exception;

/**
 * Validation Exception
 *
 * Thrown when validation fails. Contains all validation errors.
 *
 * Example:
 * ```php
 * try {
 *     $validator->validate();
 * } catch (ValidationException $e) {
 *     $errors = $e->errors();
 *     // ['email' => ['The email field is required.']]
 * }
 * ```
 */
class ValidationException extends Exception
{
    /**
     * @param array<string, array<string>> $errors Validation errors
     * @param string $message Exception message
     */
    public function __construct(
        private array $errors = [],
        string $message = 'The given data was invalid.'
    ) {
        parent::__construct($message);
    }

    /**
     * Get all validation errors
     *
     * @return array<string, array<string>> Validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for a specific field
     *
     * @param string $field Field name
     * @return array<string> Field errors
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Check if a field has errors
     *
     * @param string $field Field name
     * @return bool True if field has errors
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Get the first error message for a field
     *
     * @param string $field Field name
     * @return string|null First error message or null
     */
    public function first(string $field): ?string
    {
        $errors = $this->getFieldErrors($field);
        return $errors[0] ?? null;
    }

    /**
     * Get all error messages as a flat array
     *
     * @return array<string> All error messages
     */
    public function all(): array
    {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return $messages;
    }

    /**
     * Check if there are any errors
     *
     * @return bool True if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get errors as JSON
     *
     * @param int $flags JSON encode flags
     * @return string JSON representation of errors
     */
    public function toJson(int $flags = JSON_PRETTY_PRINT): string
    {
        return json_encode($this->errors, $flags);
    }
}
