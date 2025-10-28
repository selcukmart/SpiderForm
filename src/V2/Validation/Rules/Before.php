<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Before Rule - Value must be a date before another date
 */
class Before implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (empty($parameters[0])) {
            return false;
        }

        $compareDate = $parameters[0];

        // Convert to timestamps
        $valueTime = is_numeric($value) ? (int) $value : strtotime((string) $value);
        $compareTime = is_numeric($compareDate) ? (int) $compareDate : strtotime($compareDate);

        if ($valueTime === false || $compareTime === false) {
            return false;
        }

        return $valueTime < $compareTime;
    }

    public function message(): string
    {
        return 'The :attribute must be a date before the specified date.';
    }

    public function name(): string
    {
        return 'before';
    }
}
