<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Between Rule - Value must be between two values (inclusive)
 */
class Between implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (count($parameters) < 2) {
            return false;
        }

        $min = $parameters[0];
        $max = $parameters[1];

        // For numeric values
        if (is_numeric($value)) {
            return $value >= $min && $value <= $max;
        }

        // For strings, check length
        if (is_string($value)) {
            $length = mb_strlen($value);
            return $length >= $min && $length <= $max;
        }

        // For arrays, check count
        if (is_array($value)) {
            $count = count($value);
            return $count >= $min && $count <= $max;
        }

        return false;
    }

    public function message(): string
    {
        return 'The :attribute must be between the specified values.';
    }

    public function name(): string
    {
        return 'between';
    }
}
