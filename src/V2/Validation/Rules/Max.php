<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Max Rule - Maximum numeric value
 *
 * For numbers: validates the value is <= maximum
 * For strings: validates the length is <= maximum
 * For arrays: validates the count is <= maximum
 *
 * Usage: max:100
 */
class Max implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        $max = $parameters[0] ?? PHP_INT_MAX;

        if (is_numeric($value)) {
            return $value <= $max;
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        if (is_array($value)) {
            return count($value) <= $max;
        }

        return false;
    }

    public function message(): string
    {
        return 'The :attribute may not be greater than :param.';
    }

    public function name(): string
    {
        return 'max';
    }
}
