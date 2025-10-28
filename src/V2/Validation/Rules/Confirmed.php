<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Confirmed Rule - Value must match another field (e.g., password confirmation)
 *
 * This rule expects the confirmation field to be named {field}_confirmation
 * or to be passed as a parameter.
 */
class Confirmed implements RuleInterface
{
    private array $allData = [];

    public function setAllData(array $data): void
    {
        $this->allData = $data;
    }

    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        // Get confirmation field name
        $confirmationField = $parameters[0] ?? $attribute . '_confirmation';

        // Check if confirmation field exists
        if (!isset($this->allData[$confirmationField])) {
            return false;
        }

        // Compare values
        return $value === $this->allData[$confirmationField];
    }

    public function message(): string
    {
        return 'The :attribute confirmation does not match.';
    }

    public function name(): string
    {
        return 'confirmed';
    }
}
