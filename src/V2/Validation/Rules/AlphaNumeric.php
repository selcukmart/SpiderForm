<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * AlphaNumeric Rule - Value must contain only letters and numbers
 */
class AlphaNumeric implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return ctype_alnum($value);
    }

    public function message(): string
    {
        return 'The :attribute must contain only letters and numbers.';
    }

    public function name(): string
    {
        return 'alpha_numeric';
    }
}
