<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Array Rule - Value must be an array
 *
 * Note: Named ArrayRule to avoid conflict with PHP's array keyword
 */
class ArrayRule implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        return is_array($value);
    }

    public function message(): string
    {
        return 'The :attribute must be an array.';
    }

    public function name(): string
    {
        return 'array';
    }
}
