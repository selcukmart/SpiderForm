<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

/**
 * Grouped Validation - Validation Group Management
 *
 * Manages validation groups for conditional validation scenarios.
 *
 * Usage:
 * ```php
 * // Define field with groups
 * $builder->addText('username')
 *         ->required(['groups' => ['registration', 'profile']])
 *         ->minLength(3, ['groups' => ['registration']])
 *         ->add();
 *
 * // Validate specific group
 * $errors = $validator->validate($data, $rules, ['groups' => ['registration']]);
 * ```
 *
 * @author selcukmart
 * @since 2.7.0
 */
class GroupedValidation
{
    /**
     * Default validation group
     */
    public const DEFAULT_GROUP = 'Default';

    /**
     * Validation groups configuration
     */
    private array $groups = [];

    /**
     * Field rules with group information
     */
    private array $fieldRules = [];

    /**
     * Add a validation rule to a field with groups
     *
     * @param string $field Field name
     * @param string $rule Rule name
     * @param array $options Rule options including 'groups'
     */
    public function addRule(string $field, string $rule, array $options = []): void
    {
        $groups = $options['groups'] ?? [self::DEFAULT_GROUP];

        if (!isset($this->fieldRules[$field])) {
            $this->fieldRules[$field] = [];
        }

        $this->fieldRules[$field][] = [
            'rule' => $rule,
            'options' => $options,
            'groups' => $groups,
        ];

        // Register groups
        foreach ($groups as $group) {
            $this->groups[$group] = true;
        }
    }

    /**
     * Get rules for a field filtered by groups
     *
     * @param string $field Field name
     * @param array $validateGroups Groups to validate
     * @return array Rules applicable to the groups
     */
    public function getRulesForField(string $field, array $validateGroups = [self::DEFAULT_GROUP]): array
    {
        if (!isset($this->fieldRules[$field])) {
            return [];
        }

        $applicableRules = [];

        foreach ($this->fieldRules[$field] as $ruleConfig) {
            $ruleGroups = $ruleConfig['groups'];

            // Check if rule applies to any of the validate groups
            if (array_intersect($validateGroups, $ruleGroups)) {
                $applicableRules[] = $ruleConfig['rule'];
            }
        }

        return $applicableRules;
    }

    /**
     * Get all rules filtered by groups
     *
     * @param array $validateGroups Groups to validate
     * @return array Field => Rules mapping
     */
    public function getAllRules(array $validateGroups = [self::DEFAULT_GROUP]): array
    {
        $filteredRules = [];

        foreach ($this->fieldRules as $field => $rules) {
            $fieldRules = $this->getRulesForField($field, $validateGroups);

            if (!empty($fieldRules)) {
                $filteredRules[$field] = implode('|', $fieldRules);
            }
        }

        return $filteredRules;
    }

    /**
     * Get all registered groups
     */
    public function getGroups(): array
    {
        return array_keys($this->groups);
    }

    /**
     * Check if a group exists
     */
    public function hasGroup(string $group): bool
    {
        return isset($this->groups[$group]);
    }

    /**
     * Clear all rules and groups
     */
    public function clear(): void
    {
        $this->groups = [];
        $this->fieldRules = [];
    }
}
