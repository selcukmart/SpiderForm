<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Json Rule - Value must be valid JSON
 */
class Json implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function message(): string
    {
        return 'The :attribute must be a valid JSON string.';
    }

    public function name(): string
    {
        return 'json';
    }
}
