<?php

declare(strict_types=1);

namespace SpiderForm\V2\Validation\Rules;

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

        // Check with strict comparison first
        if (in_array($value, $parameters, true)) {
            return true;
        }

        // Also check with loose comparison to handle string/int variations
        // This allows '1' to match 1 and vice versa
        return in_array($value, $parameters, false);
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
