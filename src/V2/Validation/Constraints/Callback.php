<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Constraints;

use FormGenerator\V2\Validation\ExecutionContext;

/**
 * Callback Constraint - Custom Validation Logic
 *
 * Allows custom validation logic using callbacks.
 * Perfect for cross-field validation.
 *
 * Usage:
 * ```php
 * // Password confirmation
 * $form->addConstraint(new Callback(function($data, $context) {
 *     if ($data['password'] !== $data['password_confirm']) {
 *         $context->buildViolation('Passwords do not match')
 *                 ->atPath('password_confirm')
 *                 ->addViolation();
 *     }
 * }));
 *
 * // Date range validation
 * $form->addConstraint(new Callback(function($data, $context) {
 *     if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
 *         $context->buildViolation('End date must be after start date')
 *                 ->atPath('end_date')
 *                 ->addViolation();
 *     }
 * }));
 * ```
 *
 * @author selcukmart
 * @since 2.7.0
 */
class Callback
{
    /**
     * Callback function for validation
     *
     * Signature: function(array $data, ExecutionContext $context): void
     */
    private $callback;

    /**
     * Validation groups this constraint belongs to
     */
    private array $groups;

    /**
     * @param callable $callback Validation callback
     * @param array $groups Validation groups
     */
    public function __construct(callable $callback, array $groups = ['Default'])
    {
        $this->callback = $callback;
        $this->groups = $groups;
    }

    /**
     * Validate data using callback
     *
     * @param array $data Form data
     * @param ExecutionContext $context Execution context
     */
    public function validate(array $data, ExecutionContext $context): void
    {
        ($this->callback)($data, $context);
    }

    /**
     * Get validation groups
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Check if constraint applies to group
     */
    public function appliesToGroup(string $group): bool
    {
        return in_array($group, $this->groups, true);
    }
}
