<?php

declare(strict_types=1);

namespace SpiderForm\V2\Validation\Rules;

/**
 * Date Rule - Value must be a valid date
 */
class Date implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        if (is_numeric($value)) {
            return true; // Unix timestamp
        }

        // Try strtotime first
        if (strtotime($value) !== false) {
            return true;
        }

        // Try common date formats that strtotime might not handle
        $commonFormats = [
            'd/m/Y',  // 15/01/2024
            'm/d/Y',  // 01/15/2024
            'd-m-Y',  // 15-01-2024
            'm-d-Y',  // 01-15-2024
            'Y-m-d',  // 2024-01-15
            'd.m.Y',  // 15.01.2024
        ];

        foreach ($commonFormats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return true;
            }
        }

        return false;
    }

    public function message(): string
    {
        return 'The :attribute is not a valid date.';
    }

    public function name(): string
    {
        return 'date';
    }
}
