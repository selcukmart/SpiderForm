<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Digits Rule - Value must be numeric and have an exact length
 */
class Digits implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $length = $parameters[0] ?? null;

        if ($length === null) {
            return ctype_digit((string) $value);
        }

        return ctype_digit((string) $value) && strlen((string) $value) === (int) $length;
    }

    public function message(): string
    {
        return 'The :attribute must be numeric digits.';
    }

    public function name(): string
    {
        return 'digits';
    }
}
