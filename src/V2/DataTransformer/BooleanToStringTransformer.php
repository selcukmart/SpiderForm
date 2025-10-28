<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataTransformer;

/**
 * Boolean To String Transformer
 *
 * Transforms between boolean values and string representations.
 * Useful for radio buttons, select dropdowns, or hidden inputs that need to represent boolean values.
 *
 * Usage Example:
 * ```php
 * // Transform true/false <-> '1'/'0'
 * $form->addRadio('is_active', 'Active?')
 *     ->options(['1' => 'Yes', '0' => 'No'])
 *     ->addTransformer(new BooleanToStringTransformer('1', '0'))
 *     ->add();
 *
 * // Transform true/false <-> 'yes'/'no'
 * $form->addSelect('newsletter', 'Newsletter')
 *     ->options(['yes' => 'Subscribe', 'no' => 'Unsubscribe'])
 *     ->addTransformer(new BooleanToStringTransformer('yes', 'no'))
 *     ->add();
 * ```
 *
 * @author selcukmart
 * @since 2.3.1
 */
class BooleanToStringTransformer extends AbstractDataTransformer
{
    /**
     * @param string $trueValue The string representation of true (default: '1')
     * @param string $falseValue The string representation of false (default: '0')
     */
    public function __construct(
        private readonly string $trueValue = '1',
        private readonly string $falseValue = '0'
    ) {
    }

    /**
     * Transforms a boolean into a string.
     *
     * @param mixed $value A boolean or null
     * @return string|null The string representation or null
     * @throws \InvalidArgumentException If the value is not a boolean or null
     */
    public function transform(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_bool($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected boolean, got "%s" instead.',
                get_debug_type($value)
            ));
        }

        return $value ? $this->trueValue : $this->falseValue;
    }

    /**
     * Transforms a string into a boolean.
     *
     * @param mixed $value A string or null
     * @return bool|null The boolean value or null
     */
    public function reverseTransform(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle various truthy/falsy representations
        if ($value === $this->trueValue || $value === true || $value === 1 || $value === '1') {
            return true;
        }

        if ($value === $this->falseValue || $value === false || $value === 0 || $value === '0') {
            return false;
        }

        // Try to parse as boolean
        $lowerValue = is_string($value) ? strtolower(trim($value)) : $value;

        return match ($lowerValue) {
            'true', 'yes', 'on', '1', 1, true => true,
            'false', 'no', 'off', '0', 0, false => false,
            default => throw new \InvalidArgumentException(sprintf(
                'The value "%s" cannot be transformed to a boolean.',
                is_scalar($value) ? $value : get_debug_type($value)
            ))
        };
    }

    /**
     * Get the configured true value string
     */
    public function getTrueValue(): string
    {
        return $this->trueValue;
    }

    /**
     * Get the configured false value string
     */
    public function getFalseValue(): string
    {
        return $this->falseValue;
    }
}
