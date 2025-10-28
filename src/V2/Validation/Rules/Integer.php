<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

class Integer implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public function message(): string
    {
        return 'The :attribute must be an integer.';
    }

    public function name(): string
    {
        return 'integer';
    }
}
