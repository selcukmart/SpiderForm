<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Required Rule - Field must have a value
 *
 * Validates that a field is present and not empty.
 * Empty values: null, empty string, empty array
 */
class Required implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'The :attribute field is required.';
    }

    public function name(): string
    {
        return 'required';
    }
}
