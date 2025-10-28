<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataTransformer;

use FormGenerator\V2\Contracts\DataTransformerInterface;

/**
 * Abstract Data Transformer
 *
 * Base class for data transformers providing common functionality.
 *
 * @author selcukmart
 * @since 2.3.1
 */
abstract class AbstractDataTransformer implements DataTransformerInterface
{
    /**
     * Handle null values gracefully
     *
     * @param mixed $value The value to check
     * @param mixed $default The default value to return if null
     * @return mixed The value or default
     */
    protected function handleNull(mixed $value, mixed $default = null): mixed
    {
        return $value === null ? $default : $value;
    }

    /**
     * Handle empty string values
     *
     * @param mixed $value The value to check
     * @param mixed $default The default value to return if empty
     * @return mixed The value or default
     */
    protected function handleEmpty(mixed $value, mixed $default = null): mixed
    {
        return $value === '' ? $default : $value;
    }

    /**
     * Validate that a value is of expected type
     *
     * @param mixed $value The value to validate
     * @param string $expectedType The expected type (class name or built-in type)
     * @param string $direction Direction of transformation ('transform' or 'reverseTransform')
     * @throws \InvalidArgumentException When type validation fails
     */
    protected function validateType(mixed $value, string $expectedType, string $direction): void
    {
        if ($value === null) {
            return; // Null is always valid
        }

        $isValid = match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            default => $value instanceof $expectedType,
        };

        if (!$isValid) {
            $actualType = get_debug_type($value);
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected %s to receive "%s", got "%s" instead during %s.',
                    static::class,
                    $expectedType,
                    $actualType,
                    $direction
                )
            );
        }
    }
}
