<?php

/**
 * FormGenerator V2 - Validation Example
 *
 * Demonstrates built-in validation with both PHP and JavaScript
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Validation\NativeValidator;

$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();
$validator = new NativeValidator();

$form = FormBuilder::create('registration_form')
    ->setAction('/register')
    ->setMethod('POST')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setValidator($validator) // Enable validation
    ->enableClientSideValidation() // Auto JavaScript validation

    // Username - required, alphanumeric, min/max length
    ->addText('username', 'Username')
        ->required()
        ->alphanumeric()
        ->minLength(3)
        ->maxLength(20)
        ->helpText('3-20 alphanumeric characters')
        ->add()

    // Email - required, valid email
    ->addEmail('email', 'Email Address')
        ->required()
        ->email() // Auto validates email format
        ->add()

    // Password - required, minimum length, pattern
    ->addPassword('password', 'Password')
        ->required()
        ->minLength(8)
        ->pattern(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'Password must contain uppercase, lowercase, and number'
        )
        ->helpText('At least 8 characters with uppercase, lowercase, and number')
        ->add()

    // Confirm Password - must match password
    ->addPassword('password_confirm', 'Confirm Password')
        ->required()
        ->add()

    // Age - numeric, min/max value
    ->addNumber('age', 'Age')
        ->required()
        ->min(18)
        ->max(120)
        ->helpText('Must be 18 or older')
        ->add()

    // Website - optional but must be valid URL if provided
    ->addUrl('website', 'Website')
        ->url()
        ->placeholder('https://example.com')
        ->add()

    // Terms - required checkbox
    ->addCheckbox('terms', 'I agree to Terms & Conditions')
        ->required()
        ->add()

    ->addSubmit('register', 'Register')
    ->build();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Validation Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h2>Registration Form</h2>
                <div class="alert alert-info">
                    <strong>Validation Features:</strong>
                    <ul class="mb-0">
                        <li>Real-time JavaScript validation</li>
                        <li>Validation on blur</li>
                        <li>Clear errors on input</li>
                        <li>Error summary on submit</li>
                        <li>PHP validation available on backend</li>
                    </ul>
                </div>

                <?= $form ?>

                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <div class="alert alert-success mt-3">
                        <strong>Server-Side Validation:</strong>
                        <pre><?php
                        // Example: Validate with PHP
                        $errors = $validator->validateForm($_POST, [
                            'username' => ['required' => true, 'minLength' => 3, 'maxLength' => 20],
                            'email' => ['required' => true, 'email' => true],
                            'password' => ['required' => true, 'minLength' => 8],
                            'age' => ['required' => true, 'min' => 18, 'max' => 120],
                        ]);

                        foreach ($errors as $field => $result) {
                            if ($result->isFailed()) {
                                echo "$field: " . implode(', ', $result->getErrors()) . "\n";
                            }
                        }
                        ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
