<?php

declare(strict_types=1);

namespace SpiderForm\V2\Validation\Rules;

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

        // Allow letters, numbers, and underscores (common for usernames)
        return preg_match('/^[A-Za-z0-9_]+$/', $value) === 1;
    }

    public function message(): string
    {
        return 'The :attribute must contain only letters, numbers, and underscores.';
    }

    public function name(): string
    {
        return 'alpha_numeric';
    }
}
