<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Numeric Rule - Value must be numeric
 */
class Numeric implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        return is_numeric($value);
    }

    public function message(): string
    {
        return 'The :attribute must be a number.';
    }

    public function name(): string
    {
        return 'numeric';
    }
}
