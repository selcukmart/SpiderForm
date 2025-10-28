<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

use FormGenerator\V2\Builder\FormBuilder;

/**
 * Interface for Form Types (similar to Symfony's FormTypeInterface)
 *
 * Form types allow you to create reusable, class-based form definitions.
 *
 * Example:
 * ```php
 * class UserFormType implements FormTypeInterface
 * {
 *     public function buildForm(FormBuilder $builder, array $options): void
 *     {
 *         $builder
 *             ->addText('username', 'Username')->required()->add()
 *             ->addEmail('email', 'Email')->required()->add()
 *             ->addPassword('password', 'Password')->required()->add();
 *     }
 *
 *     public function configureOptions(): array
 *     {
 *         return [
 *             'csrf_protection' => true,
 *             'validation' => true,
 *         ];
 *     }
 * }
 * ```
 */
interface FormTypeInterface
{
    /**
     * Build the form
     *
     * @param FormBuilder $builder Form builder instance
     * @param array $options Form options
     */
    public function buildForm(FormBuilder $builder, array $options): void;

    /**
     * Configure default options for this form type
     *
     * @return array Default options
     */
    public function configureOptions(): array;

    /**
     * Get the form name/identifier
     *
     * @return string Form name
     */
    public function getName(): string;
}
