<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * NotIn Rule - Value must not be in a list of disallowed values
 */
class NotIn implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (empty($parameters)) {
            return true;
        }

        return !in_array($value, $parameters, true);
    }

    public function message(): string
    {
        return 'The selected :attribute is invalid.';
    }

    public function name(): string
    {
        return 'not_in';
    }
}
