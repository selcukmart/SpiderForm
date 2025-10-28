<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

use FormGenerator\V2\Contracts\{ValidatorInterface, ValidationResult};

/**
 * Native Validator
 *
 * Built-in validation engine with PHP and JavaScript support
 *
 * @author selcukmart
 * @since 2.0.0
 */
class NativeValidator implements ValidatorInterface
{
    private array $rules = [];
    private array $jsRules = [];
    private array $messages = [];

    public function __construct()
    {
        $this->registerBuiltInRules();
    }

    /**
     * Validate a value against rules
     */
    public function validate(mixed $value, array $rules, array $context = []): ValidationResult
    {
        $errors = [];

        foreach ($rules as $ruleName => $params) {
            if (!$this->hasRule($ruleName)) {
                continue;
            }

            $callback = $this->rules[$ruleName];
            $isValid = $callback($value, $params, $context);

            if (!$isValid) {
                $message = $this->getMessage($ruleName, $params);
                $errors[$ruleName] = $message;
            }
        }

        return empty($errors)
            ? ValidationResult::success()
            : ValidationResult::failure($errors);
    }

    /**
     * Add custom validation rule
     */
    public function addRule(string $name, callable $callback, string $message): void
    {
        $this->rules[$name] = $callback;
        $this->messages[$name] = $message;
    }

    /**
     * Check if rule exists
     */
    public function hasRule(string $name): bool
    {
        return isset($this->rules[$name]);
    }

    /**
     * Get JavaScript validation code for rules
     */
    public function getJavaScriptCode(array $rules): string
    {
        $jsCode = [];

        foreach ($rules as $ruleName => $params) {
            if (isset($this->jsRules[$ruleName])) {
                $jsCode[] = $this->jsRules[$ruleName]($params);
            }
        }

        return implode("\n", $jsCode);
    }

    /**
     * Validate entire form data
     */
    public function validateForm(array $data, array $fieldsRules): array
    {
        $results = [];

        foreach ($fieldsRules as $fieldName => $rules) {
            $value = $data[$fieldName] ?? null;
            $results[$fieldName] = $this->validate($value, $rules, $data);
        }

        return $results;
    }

    /**
     * Get error message for rule
     */
    private function getMessage(string $ruleName, mixed $params): string
    {
        $template = $this->messages[$ruleName] ?? 'Validation failed';

        // Replace placeholders
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                $template = str_replace('{' . $key . '}', (string) $val, $template);
            }
        } else {
            $template = str_replace('{value}', (string) $params, $template);
        }

        return $template;
    }

    /**
     * Register built-in validation rules
     */
    private function registerBuiltInRules(): void
    {
        // Required
        $this->addRule('required', function ($value) {
            if (is_string($value)) {
                return trim($value) !== '';
            }
            return $value !== null && $value !== '';
        }, 'This field is required');

        $this->jsRules['required'] = fn() => "if (!value || value.trim() === '') errors.push('This field is required');";

        // Email
        $this->addRule('email', function ($value) {
            if (empty($value)) return true; // Optional unless required
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }, 'Please enter a valid email address');

        $this->jsRules['email'] = fn() => "if (value && !/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(value)) errors.push('Please enter a valid email address');";

        // Min length
        $this->addRule('minLength', function ($value, $min) {
            if (empty($value)) return true;
            return mb_strlen((string) $value) >= $min;
        }, 'Minimum {value} characters required');

        $this->jsRules['minLength'] = fn($min) => "if (value && value.length < {$min}) errors.push('Minimum {$min} characters required');";

        // Max length
        $this->addRule('maxLength', function ($value, $max) {
            if (empty($value)) return true;
            return mb_strlen((string) $value) <= $max;
        }, 'Maximum {value} characters allowed');

        $this->jsRules['maxLength'] = fn($max) => "if (value && value.length > {$max}) errors.push('Maximum {$max} characters allowed');";

        // Min value
        $this->addRule('min', function ($value, $min) {
            if (empty($value)) return true;
            return (float) $value >= $min;
        }, 'Minimum value is {value}');

        $this->jsRules['min'] = fn($min) => "if (value && parseFloat(value) < {$min}) errors.push('Minimum value is {$min}');";

        // Max value
        $this->addRule('max', function ($value, $max) {
            if (empty($value)) return true;
            return (float) $value <= $max;
        }, 'Maximum value is {value}');

        $this->jsRules['max'] = fn($max) => "if (value && parseFloat(value) > {$max}) errors.push('Maximum value is {$max}');";

        // Pattern
        $this->addRule('pattern', function ($value, $pattern) {
            if (empty($value)) return true;
            return preg_match($pattern['regex'] ?? $pattern, (string) $value) === 1;
        }, 'Invalid format');

        $this->jsRules['pattern'] = function ($pattern) {
            $regex = $pattern['regex'] ?? $pattern;
            $message = $pattern['message'] ?? 'Invalid format';
            return "if (value && !new RegExp('{$regex}').test(value)) errors.push('{$message}');";
        };

        // URL
        $this->addRule('url', function ($value) {
            if (empty($value)) return true;
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        }, 'Please enter a valid URL');

        $this->jsRules['url'] = fn() => "if (value && !/^https?:\\/\\/.+/.test(value)) errors.push('Please enter a valid URL');";

        // Numeric
        $this->addRule('numeric', function ($value) {
            if (empty($value)) return true;
            return is_numeric($value);
        }, 'Please enter a valid number');

        $this->jsRules['numeric'] = fn() => "if (value && isNaN(value)) errors.push('Please enter a valid number');";

        // Integer
        $this->addRule('integer', function ($value) {
            if (empty($value)) return true;
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        }, 'Please enter a valid integer');

        $this->jsRules['integer'] = fn() => "if (value && !Number.isInteger(parseFloat(value))) errors.push('Please enter a valid integer');";

        // Alpha
        $this->addRule('alpha', function ($value) {
            if (empty($value)) return true;
            return preg_match('/^[a-zA-Z]+$/', (string) $value) === 1;
        }, 'Only letters are allowed');

        $this->jsRules['alpha'] = fn() => "if (value && !/^[a-zA-Z]+$/.test(value)) errors.push('Only letters are allowed');";

        // Alphanumeric
        $this->addRule('alphanumeric', function ($value) {
            if (empty($value)) return true;
            return preg_match('/^[a-zA-Z0-9]+$/', (string) $value) === 1;
        }, 'Only letters and numbers are allowed');

        $this->jsRules['alphanumeric'] = fn() => "if (value && !/^[a-zA-Z0-9]+$/.test(value)) errors.push('Only letters and numbers are allowed');";

        // Date
        $this->addRule('date', function ($value) {
            if (empty($value)) return true;
            return strtotime((string) $value) !== false;
        }, 'Please enter a valid date');

        $this->jsRules['date'] = fn() => "if (value && isNaN(Date.parse(value))) errors.push('Please enter a valid date');";

        // Match (compare with another field)
        $this->addRule('match', function ($value, $fieldName, $context) {
            if (empty($value)) return true;
            return $value === ($context[$fieldName] ?? null);
        }, 'Fields do not match');

        $this->jsRules['match'] = fn($fieldName) => "if (value && value !== document.querySelector('[name=\"{$fieldName}\"]')?.value) errors.push('Fields do not match');";

        // In (must be one of the values)
        $this->addRule('in', function ($value, $allowed) {
            if (empty($value)) return true;
            return in_array($value, $allowed, true);
        }, 'Invalid selection');

        $this->jsRules['in'] = function ($allowed) {
            $values = json_encode($allowed);
            return "if (value && !{$values}.includes(value)) errors.push('Invalid selection');";
        };

        // Not in
        $this->addRule('notIn', function ($value, $disallowed) {
            if (empty($value)) return true;
            return !in_array($value, $disallowed, true);
        }, 'This value is not allowed');

        $this->jsRules['notIn'] = function ($disallowed) {
            $values = json_encode($disallowed);
            return "if (value && {$values}.includes(value)) errors.push('This value is not allowed');";
        };

        // File size (in bytes)
        $this->addRule('fileSize', function ($value, $maxSize) {
            if (empty($value) || !isset($value['size'])) return true;
            return $value['size'] <= $maxSize;
        }, 'File size must not exceed {value} bytes');

        // File type
        $this->addRule('fileType', function ($value, $allowedTypes) {
            if (empty($value) || !isset($value['type'])) return true;
            return in_array($value['type'], $allowedTypes, true);
        }, 'Invalid file type');
    }
}
