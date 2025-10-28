<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

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

        return strtotime($value) !== false;
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
