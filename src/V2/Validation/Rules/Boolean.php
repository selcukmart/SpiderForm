<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Boolean Rule - Value must be a boolean
 */
class Boolean implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        // Accept boolean values and common boolean representations
        return in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true);
    }

    public function message(): string
    {
        return 'The :attribute must be true or false.';
    }

    public function name(): string
    {
        return 'boolean';
    }
}
