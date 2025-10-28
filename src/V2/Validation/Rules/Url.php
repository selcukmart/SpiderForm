<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Url Rule - Validates URL format
 */
class Url implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public function message(): string
    {
        return 'The :attribute must be a valid URL.';
    }

    public function name(): string
    {
        return 'url';
    }
}
