<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

use FormGenerator\V2\Contracts\ValidatorInterface;

/**
 * Validation Manager
 *
 * Generates client-side JavaScript validation code
 *
 * @author selcukmart
 * @since 2.0.0
 */
class ValidationManager
{
    private static array $renderedForms = [];

    /**
     * Generate validation JavaScript for a form
     *
     * @param string $formId Unique form identifier
     * @param array<string, array<string, mixed>> $fieldsRules Field validation rules
     * @param ValidatorInterface $validator Validator instance
     * @param bool $includeScript Whether to wrap in <script> tags
     */
    public static function generateScript(
        string $formId,
        array $fieldsRules,
        ValidatorInterface $validator,
        bool $includeScript = true
    ): string {
        // Check if already rendered for this form
        if (isset(self::$renderedForms[$formId])) {
            return ''; // Already rendered, return empty
        }

        // Mark as rendered
        self::$renderedForms[$formId] = true;

        $script = self::getJavaScriptCode($formId, $fieldsRules, $validator);

        if ($includeScript) {
            return sprintf('<script type="text/javascript">%s</script>', $script);
        }

        return $script;
    }

    /**
     * Check if script was already rendered for a form
     */
    public static function isRendered(string $formId): bool
    {
        return isset(self::$renderedForms[$formId]);
    }

    /**
     * Reset rendered forms tracker (useful for testing)
     */
    public static function reset(): void
    {
        self::$renderedForms = [];
    }

    /**
     * Get the pure JavaScript validation code
     */
    private static function getJavaScriptCode(
        string $formId,
        array $fieldsRules,
        ValidatorInterface $validator
    ): string {
        // Create a unique namespace for this form
        $namespace = 'FormValidator_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $formId);

        // Generate field validators
        $fieldValidators = self::generateFieldValidators($fieldsRules, $validator);

        return <<<JAVASCRIPT

(function() {
    'use strict';

    /**
     * FormGenerator V2 Validation Manager
     * Form ID: {$formId}
     */
    const {$namespace} = {
        formSelector: '#{$formId}',
        errors: {},

        /**
         * Initialize validation
         */
        init: function() {
            const form = document.querySelector(this.formSelector);
            if (!form) {
                console.warn('FormValidator: Form not found:', this.formSelector);
                return;
            }

            // Attach submit handler
            form.addEventListener('submit', (e) => this.handleSubmit(e));

            // Attach blur handlers for real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach((input) => {
                if (input.type !== 'submit' && input.type !== 'button') {
                    input.addEventListener('blur', () => this.validateField(input));
                    input.addEventListener('input', () => this.clearFieldError(input));
                }
            });
        },

        /**
         * Handle form submission
         */
        handleSubmit: function(event) {
            this.errors = {};
            const form = event.target;
            const formData = new FormData(form);
            let isValid = true;

            // Validate all fields
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach((input) => {
                if (input.type !== 'submit' && input.type !== 'button') {
                    if (!this.validateField(input)) {
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                event.preventDefault();
                this.showSummary();
                // Focus first error field
                const firstError = form.querySelector('.is-invalid, .has-error');
                if (firstError) {
                    firstError.focus();
                }
            }
        },

        /**
         * Validate a single field
         */
        validateField: function(input) {
            const name = input.name;
            const value = input.value;
            const errors = [];

            // Skip if disabled or hidden by dependency
            if (input.disabled || input.closest('[data-dependends]')?.style.display === 'none') {
                return true;
            }

            // Run validators
            {$fieldValidators}

            if (errors.length > 0) {
                this.errors[name] = errors;
                this.showFieldError(input, errors[0]);
                return false;
            } else {
                this.clearFieldError(input);
                return true;
            }
        },

        /**
         * Show field error
         */
        showFieldError: function(input, message) {
            // Remove existing errors
            this.clearFieldError(input);

            // Add error classes
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');

            // Find or create error container
            const wrapper = input.closest('.mb-3, .form-group');
            if (wrapper) {
                let errorDiv = wrapper.querySelector('.invalid-feedback, .error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback d-block';
                    input.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        },

        /**
         * Clear field error
         */
        clearFieldError: function(input) {
            input.classList.remove('is-invalid');
            const wrapper = input.closest('.mb-3, .form-group');
            if (wrapper) {
                const errorDiv = wrapper.querySelector('.invalid-feedback, .error-message');
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                    errorDiv.textContent = '';
                }
            }
            delete this.errors[input.name];
        },

        /**
         * Show error summary
         */
        showSummary: function() {
            const form = document.querySelector(this.formSelector);
            if (!form) return;

            // Remove existing summary
            const existingSummary = form.querySelector('.validation-summary');
            if (existingSummary) {
                existingSummary.remove();
            }

            // Create new summary
            const summary = document.createElement('div');
            summary.className = 'validation-summary alert alert-danger';
            summary.innerHTML = '<strong>Please correct the following errors:</strong><ul></ul>';

            const ul = summary.querySelector('ul');
            for (const [field, errors] of Object.entries(this.errors)) {
                errors.forEach((error) => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    ul.appendChild(li);
                });
            }

            form.insertBefore(summary, form.firstChild);

            // Scroll to summary
            summary.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {$namespace}.init());
    } else {
        {$namespace}.init();
    }
})();

JAVASCRIPT;
    }

    /**
     * Generate field validators JavaScript code
     */
    private static function generateFieldValidators(
        array $fieldsRules,
        ValidatorInterface $validator
    ): string {
        $validators = [];

        foreach ($fieldsRules as $fieldName => $rules) {
            $validatorCode = $validator->getJavaScriptCode($rules);
            if (!empty($validatorCode)) {
                $validators[] = "if (name === '{$fieldName}') {\n    {$validatorCode}\n}";
            }
        }

        return implode("\n", $validators);
    }
}
