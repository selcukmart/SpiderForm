<?php

declare(strict_types=1);

namespace SpiderForm\V2\Validation\Rules;

/**
 * Alpha Rule - Value must contain only letters
 */
class Alpha implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return ctype_alpha($value);
    }

    public function message(): string
    {
        return 'The :attribute must contain only letters.';
    }

    public function name(): string
    {
        return 'alpha';
    }
}
