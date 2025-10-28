<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * DateFormat Rule - Value must match a specific date format
 */
class DateFormat implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        if (empty($parameters[0])) {
            return false;
        }

        $format = $parameters[0];
        $date = \DateTime::createFromFormat($format, $value);

        return $date && $date->format($format) === $value;
    }

    public function message(): string
    {
        return 'The :attribute does not match the required format.';
    }

    public function name(): string
    {
        return 'date_format';
    }
}
