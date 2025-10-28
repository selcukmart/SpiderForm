<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Regex Rule - Value must match a regular expression pattern
 */
class Regex implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        if (empty($parameters[0])) {
            return false;
        }

        $pattern = $parameters[0];

        return preg_match($pattern, $value) === 1;
    }

    public function message(): string
    {
        return 'The :attribute format is invalid.';
    }

    public function name(): string
    {
        return 'regex';
    }
}
