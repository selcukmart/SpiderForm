<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * In Rule - Value must be in a list of allowed values
 */
class In implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (empty($parameters)) {
            return false;
        }

        return in_array($value, $parameters, true);
    }

    public function message(): string
    {
        return 'The selected :attribute is invalid.';
    }

    public function name(): string
    {
        return 'in';
    }
}
