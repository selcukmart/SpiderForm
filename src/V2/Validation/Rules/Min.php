<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Min Rule - Minimum numeric value
 *
 * For numbers: validates the value is >= minimum
 * For strings: validates the length is >= minimum
 * For arrays: validates the count is >= minimum
 *
 * Usage: min:18
 */
class Min implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        $min = $parameters[0] ?? 0;

        if (is_numeric($value)) {
            return $value >= $min;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return false;
    }

    public function message(): string
    {
        return 'The :attribute must be at least :param.';
    }

    public function name(): string
    {
        return 'min';
    }
}
