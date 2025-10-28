<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

use FormGenerator\V2\Validation\Rules\RuleInterface;

/**
 * Validator Class - Laravel-inspired validation system
 *
 * Usage:
 * ```php
 * $validator = new Validator($data, [
 *     'email' => 'required|email',
 *     'age' => 'required|numeric|min:18',
 *     'password' => 'required|min:8|confirmed',
 * ]);
 *
 * if ($validator->fails()) {
 *     $errors = $validator->errors();
 * }
 *
 * $validated = $validator->validate(); // Throws ValidationException on failure
 * ```
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $messages;
    private array $customAttributes;
    private array $errors = [];
    private ?\PDO $dbConnection = null;
    private bool $bail = false;
    private array $ruleInstances = [];

    /**
     * Available validation rules
     */
    private const RULE_MAP = [
        'required' => Rules\Required::class,
        'email' => Rules\Email::class,
        'min' => Rules\Min::class,
        'max' => Rules\Max::class,
        'numeric' => Rules\Numeric::class,
        'integer' => Rules\Integer::class,
        'string' => Rules\String::class,
        'boolean' => Rules\Boolean::class,
        'array' => Rules\ArrayRule::class,
        'url' => Rules\Url::class,
        'ip' => Rules\Ip::class,
        'json' => Rules\Json::class,
        'regex' => Rules\Regex::class,
        'alpha' => Rules\Alpha::class,
        'alpha_numeric' => Rules\AlphaNumeric::class,
        'digits' => Rules\Digits::class,
        'date' => Rules\Date::class,
        'date_format' => Rules\DateFormat::class,
        'before' => Rules\Before::class,
        'after' => Rules\After::class,
        'between' => Rules\Between::class,
        'confirmed' => Rules\Confirmed::class,
        'in' => Rules\In::class,
        'not_in' => Rules\NotIn::class,
        'unique' => Rules\Unique::class,
        'exists' => Rules\Exists::class,
    ];

    public function __construct(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ) {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
        $this->customAttributes = $customAttributes;
    }

    /**
     * Set database connection for database rules (unique, exists)
     */
    public function setDatabaseConnection(\PDO $connection): self
    {
        $this->dbConnection = $connection;
        return $this;
    }

    /**
     * Enable bail mode (stop on first failure)
     */
    public function bail(): self
    {
        $this->bail = true;
        return $this;
    }

    /**
     * Validate the data and throw exception on failure
     *
     * @throws ValidationException
     */
    public function validate(): array
    {
        $this->performValidation();

        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }

        return $this->validated();
    }

    /**
     * Perform the validation
     */
    private function performValidation(): void
    {
        foreach ($this->rules as $attribute => $rules) {
            $this->validateAttribute($attribute, $rules);

            if ($this->bail && !empty($this->errors)) {
                break;
            }
        }
    }

    /**
     * Validate a single attribute
     */
    private function validateAttribute(string $attribute, string|array $rules): void
    {
        $value = $this->getValue($attribute);

        // Parse rules
        $parsedRules = $this->parseRules($rules);

        foreach ($parsedRules as $rule => $parameters) {
            $passed = $this->validateRule($attribute, $value, $rule, $parameters);

            if (!$passed) {
                $this->addError($attribute, $rule, $parameters);

                if ($this->bail) {
                    break;
                }
            }
        }
    }

    /**
     * Validate a single rule
     */
    private function validateRule(string $attribute, mixed $value, string $rule, array $parameters): bool
    {
        // Get rule instance
        $ruleInstance = $this->getRuleInstance($rule);

        if ($ruleInstance === null) {
            throw new \InvalidArgumentException("Validation rule '{$rule}' not found");
        }

        // Set database connection for database rules
        if (method_exists($ruleInstance, 'setConnection') && $this->dbConnection !== null) {
            $ruleInstance->setConnection($this->dbConnection);
        }

        // Set all data for rules that need it (e.g., Confirmed)
        if (method_exists($ruleInstance, 'setAllData')) {
            $ruleInstance->setAllData($this->data);
        }

        return $ruleInstance->passes($attribute, $value, $parameters);
    }

    /**
     * Get or create rule instance
     */
    private function getRuleInstance(string $rule): ?RuleInterface
    {
        // Check if we already have an instance
        if (isset($this->ruleInstances[$rule])) {
            return $this->ruleInstances[$rule];
        }

        // Check if it's a built-in rule
        if (!isset(self::RULE_MAP[$rule])) {
            return null;
        }

        $ruleClass = self::RULE_MAP[$rule];
        $instance = new $ruleClass();

        $this->ruleInstances[$rule] = $instance;

        return $instance;
    }

    /**
     * Parse rules into an array
     */
    private function parseRules(string|array $rules): array
    {
        if (is_array($rules)) {
            return $this->parseArrayRules($rules);
        }

        return $this->parseStringRules($rules);
    }

    /**
     * Parse string rules (e.g., "required|email|min:3")
     */
    private function parseStringRules(string $rules): array
    {
        $parsed = [];
        $ruleParts = explode('|', $rules);

        foreach ($ruleParts as $rule) {
            if (str_contains($rule, ':')) {
                [$ruleName, $params] = explode(':', $rule, 2);
                $parsed[$ruleName] = explode(',', $params);
            } else {
                $parsed[$rule] = [];
            }
        }

        return $parsed;
    }

    /**
     * Parse array rules
     */
    private function parseArrayRules(array $rules): array
    {
        $parsed = [];

        foreach ($rules as $rule) {
            if ($rule instanceof RuleInterface) {
                // Custom rule object
                $this->ruleInstances[$rule->name()] = $rule;
                $parsed[$rule->name()] = [];
            } elseif (is_string($rule)) {
                // String rule
                $stringParsed = $this->parseStringRules($rule);
                $parsed = array_merge($parsed, $stringParsed);
            }
        }

        return $parsed;
    }

    /**
     * Add an error message
     */
    private function addError(string $attribute, string $rule, array $parameters): void
    {
        $message = $this->getMessage($attribute, $rule, $parameters);

        if (!isset($this->errors[$attribute])) {
            $this->errors[$attribute] = [];
        }

        $this->errors[$attribute][] = $message;
    }

    /**
     * Get error message for a rule
     */
    private function getMessage(string $attribute, string $rule, array $parameters): string
    {
        // Check for custom message
        $key = "{$attribute}.{$rule}";
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }

        // Check for attribute-level message
        if (isset($this->messages[$attribute])) {
            return $this->messages[$attribute];
        }

        // Get default message from rule
        $ruleInstance = $this->getRuleInstance($rule);
        $message = $ruleInstance ? $ruleInstance->message() : 'The :attribute is invalid.';

        // Replace placeholders
        $message = $this->replacePlaceholders($message, $attribute, $parameters);

        return $message;
    }

    /**
     * Replace placeholders in message
     */
    private function replacePlaceholders(string $message, string $attribute, array $parameters): string
    {
        // Replace :attribute
        $attributeName = $this->customAttributes[$attribute] ?? $this->formatAttribute($attribute);
        $message = str_replace(':attribute', $attributeName, $message);

        // Replace :value, :min, :max, etc.
        foreach ($parameters as $index => $value) {
            $message = str_replace(":{$index}", (string) $value, $message);
        }

        return $message;
    }

    /**
     * Format attribute name for display
     */
    private function formatAttribute(string $attribute): string
    {
        return str_replace('_', ' ', $attribute);
    }

    /**
     * Get value from data
     */
    private function getValue(string $attribute): mixed
    {
        return $this->data[$attribute] ?? null;
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        if (empty($this->errors)) {
            $this->performValidation();
        }

        return !empty($this->errors);
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get validated data (only fields that have rules)
     */
    public function validated(): array
    {
        $validated = [];

        foreach (array_keys($this->rules) as $key) {
            if (array_key_exists($key, $this->data)) {
                $validated[$key] = $this->data[$key];
            }
        }

        return $validated;
    }

    /**
     * Add a custom validation rule
     */
    public function addRule(string $name, RuleInterface $rule): self
    {
        $this->ruleInstances[$name] = $rule;
        return $this;
    }
}
