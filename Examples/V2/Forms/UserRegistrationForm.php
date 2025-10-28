<?php

declare(strict_types=1);

namespace FormGenerator\Examples\V2\Forms;

use FormGenerator\V2\Form\AbstractFormType;
use FormGenerator\V2\Builder\FormBuilder;

/**
 * Example: User Registration Form Type
 *
 * This demonstrates how to create reusable, class-based forms
 * similar to Symfony's FormType approach.
 */
class UserRegistrationForm extends AbstractFormType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->addText('username', 'Username')
                ->required()
                ->minLength(3)
                ->maxLength(20)
                ->placeholder('Enter username')
                ->helpText('Username must be 3-20 characters')
                ->add()

            ->addEmail('email', 'Email Address')
                ->required()
                ->placeholder('your@email.com')
                ->add()

            ->addPassword('password', 'Password')
                ->required()
                ->minLength(8)
                ->placeholder('Minimum 8 characters')
                ->helpText('Use a strong password')
                ->add()

            ->addPassword('password_confirm', 'Confirm Password')
                ->required()
                ->minLength(8)
                ->placeholder('Re-enter password')
                ->add()

            ->addCheckbox('terms', 'I agree to the Terms and Conditions')
                ->required()
                ->add()

            ->addSubmit('register', 'Create Account');
    }

    public function configureOptions(): array
    {
        return [
            'csrf_protection' => true,
            'validation' => true,
            'method' => 'POST',
            'action' => '/register',
        ];
    }
}
