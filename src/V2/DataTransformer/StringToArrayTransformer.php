<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataTransformer;

/**
 * String To Array Transformer
 *
 * Transforms between arrays and delimited strings (e.g., comma-separated values).
 *
 * Usage Example:
 * ```php
 * // Transform ['apple', 'banana', 'orange'] <-> 'apple, banana, orange'
 * $form->addText('tags', 'Tags')
 *     ->addTransformer(new StringToArrayTransformer(', '))
 *     ->add();
 * ```
 *
 * @author selcukmart
 * @since 2.3.1
 */
class StringToArrayTransformer extends AbstractDataTransformer
{
    /**
     * @param string $delimiter The delimiter to use for splitting/joining (default: ',')
     * @param bool $trimValues Whether to trim whitespace from values (default: true)
     * @param bool $removeEmpty Whether to remove empty values (default: true)
     */
    public function __construct(
        private readonly string $delimiter = ',',
        private readonly bool $trimValues = true,
        private readonly bool $removeEmpty = true
    ) {
    }

    /**
     * Transforms an array into a delimited string.
     *
     * @param mixed $value An array or null
     * @return string|null The delimited string or null
     * @throws \InvalidArgumentException If the value is not an array or null
     */
    public function transform(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $this->validateType($value, 'array', 'transform');

        // Filter empty values if needed
        if ($this->removeEmpty) {
            $value = array_filter($value, fn($item) => $item !== '' && $item !== null);
        }

        // Trim values if needed
        if ($this->trimValues) {
            $value = array_map('trim', array_map('strval', $value));
        } else {
            $value = array_map('strval', $value);
        }

        return implode($this->delimiter, $value);
    }

    /**
     * Transforms a delimited string into an array.
     *
     * @param mixed $value A delimited string or null
     * @return array|null The array or null
     * @throws \InvalidArgumentException If the value is not a string or null
     */
    public function reverseTransform(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected string, got "%s" instead.',
                get_debug_type($value)
            ));
        }

        // Split by delimiter
        $array = explode($this->delimiter, $value);

        // Trim values if needed
        if ($this->trimValues) {
            $array = array_map('trim', $array);
        }

        // Remove empty values if needed
        if ($this->removeEmpty) {
            $array = array_filter($array, fn($item) => $item !== '');
        }

        // Re-index array to ensure sequential keys
        return array_values($array);
    }

    /**
     * Get the configured delimiter
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }
}
