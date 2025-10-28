<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * String Rule - Value must be a string
 */
class String implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        return is_string($value);
    }

    public function message(): string
    {
        return 'The :attribute must be a string.';
    }

    public function name(): string
    {
        return 'string';
    }
}
