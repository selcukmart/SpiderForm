<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Email Rule - Validates email format
 *
 * Uses PHP's built-in FILTER_VALIDATE_EMAIL filter.
 */
class Email implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function message(): string
    {
        return 'The :attribute must be a valid email address.';
    }

    public function name(): string
    {
        return 'email';
    }
}
