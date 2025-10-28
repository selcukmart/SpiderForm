<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataTransformer;

/**
 * Number To Localized String Transformer
 *
 * Transforms between numbers and localized string representations.
 * Handles decimal separators, thousand separators, and precision.
 *
 * Usage Example:
 * ```php
 * // Transform 1234.56 <-> '1,234.56' (US format)
 * $form->addText('price', 'Price')
 *     ->addTransformer(new NumberToLocalizedStringTransformer(2, '.', ','))
 *     ->add();
 *
 * // Transform 1234.56 <-> '1.234,56' (European format)
 * $form->addText('price', 'Price')
 *     ->addTransformer(new NumberToLocalizedStringTransformer(2, ',', '.'))
 *     ->add();
 * ```
 *
 * @author selcukmart
 * @since 2.3.1
 */
class NumberToLocalizedStringTransformer extends AbstractDataTransformer
{
    /**
     * @param int $precision Number of decimal places (default: 2)
     * @param string $decimalSeparator Decimal separator (default: '.')
     * @param string $thousandsSeparator Thousands separator (default: ',')
     * @param bool $roundMode Whether to round or truncate (default: true)
     */
    public function __construct(
        private readonly int $precision = 2,
        private readonly string $decimalSeparator = '.',
        private readonly string $thousandsSeparator = ',',
        private readonly bool $roundMode = true
    ) {
    }

    /**
     * Transforms a number into a localized string.
     *
     * @param mixed $value A number (int/float) or null
     * @return string|null The formatted number string or null
     * @throws \InvalidArgumentException If the value is not a number or null
     */
    public function transform(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected numeric value, got "%s" instead.',
                get_debug_type($value)
            ));
        }

        $value = (float)$value;

        return number_format(
            $value,
            $this->precision,
            $this->decimalSeparator,
            $this->thousandsSeparator
        );
    }

    /**
     * Transforms a localized string into a number.
     *
     * @param mixed $value A formatted number string or null
     * @return float|null The number or null
     * @throws \InvalidArgumentException If the string cannot be parsed
     */
    public function reverseTransform(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected string or numeric value, got "%s" instead.',
                get_debug_type($value)
            ));
        }

        // Convert to string
        $value = (string)$value;

        // Remove thousands separators
        if ($this->thousandsSeparator !== '') {
            $value = str_replace($this->thousandsSeparator, '', $value);
        }

        // Replace decimal separator with standard '.'
        if ($this->decimalSeparator !== '.') {
            $value = str_replace($this->decimalSeparator, '.', $value);
        }

        // Validate that result is numeric
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException(sprintf(
                'The value "%s" is not a valid number.',
                $value
            ));
        }

        $number = (float)$value;

        // Apply rounding if enabled
        if ($this->roundMode && $this->precision >= 0) {
            $number = round($number, $this->precision);
        }

        return $number;
    }

    /**
     * Get the configured precision
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }
}
