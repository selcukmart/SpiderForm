<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

use FormGenerator\V2\Builder\FormBuilder;

/**
 * Abstract Form Type - Base class for all form types
 *
 * Provides default implementations and helper methods for form types.
 *
 * Example:
 * ```php
 * class UserRegistrationForm extends AbstractFormType
 * {
 *     public function buildForm(FormBuilder $builder, array $options): void
 *     {
 *         $builder
 *             ->addText('username', 'Username')
 *                 ->required()
 *                 ->minLength(3)
 *                 ->maxLength(20)
 *                 ->add()
 *
 *             ->addEmail('email', 'Email')
 *                 ->required()
 *                 ->add()
 *
 *             ->addPassword('password', 'Password')
 *                 ->required()
 *                 ->minLength(8)
 *                 ->add()
 *
 *             ->addPassword('password_confirm', 'Confirm Password')
 *                 ->required()
 *                 ->add()
 *
 *             ->addCheckbox('terms', 'I agree to terms and conditions')
 *                 ->required()
 *                 ->add();
 *     }
 *
 *     public function configureOptions(): array
 *     {
 *         return [
 *             'csrf_protection' => true,
 *             'validation' => true,
 *             'method' => 'POST',
 *             'action' => '/register',
 *         ];
 *     }
 * }
 * ```
 */
abstract class AbstractFormType implements FormTypeInterface
{
    /**
     * Build the form - must be implemented by child classes
     *
     * @param FormBuilder $builder Form builder instance
     * @param array $options Form options
     */
    abstract public function buildForm(FormBuilder $builder, array $options): void;

    /**
     * Configure default options for this form type
     *
     * Default options:
     * - csrf_protection: Enable CSRF protection (default: true)
     * - validation: Enable validation (default: true)
     * - method: HTTP method (default: POST)
     * - action: Form action URL (default: '')
     * - attr: Additional HTML attributes (default: [])
     *
     * @return array Default options
     */
    public function configureOptions(): array
    {
        return [
            'csrf_protection' => true,
            'validation' => true,
            'method' => 'POST',
            'action' => '',
            'attr' => [],
        ];
    }

    /**
     * Get the form name/identifier
     *
     * By default, returns the short class name in snake_case
     * Override this method to customize the form name
     *
     * @return string Form name
     */
    public function getName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();

        // Convert from PascalCase to snake_case
        // UserRegistrationForm -> user_registration_form
        $name = preg_replace('/(?<!^)[A-Z]/', '_$0', $className);
        $name = strtolower($name ?? '');

        // Remove common suffixes
        $name = preg_replace('/(form|_type)$/', '', $name);

        return $name;
    }

    /**
     * Get option value with default fallback
     *
     * @param array $options Options array
     * @param string $key Option key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Option value or default
     */
    protected function getOption(array $options, string $key, mixed $default = null): mixed
    {
        return $options[$key] ?? $default;
    }

    /**
     * Check if option exists and is true
     *
     * @param array $options Options array
     * @param string $key Option key
     * @return bool True if option exists and is truthy
     */
    protected function isOptionEnabled(array $options, string $key): bool
    {
        return !empty($options[$key]);
    }

    /**
     * Merge custom options with default options
     *
     * @param array $customOptions Custom options to merge
     * @return array Merged options
     */
    protected function mergeOptions(array $customOptions): array
    {
        return array_merge($this->configureOptions(), $customOptions);
    }
}
