<?php

/**
 * FormGenerator V2 - Basic Usage Example
 *
 * This example demonstrates the new Chain Pattern fluent interface
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Security\SecurityManager;

// Initialize components
$renderer = new TwigRenderer(
    templatePaths: __DIR__ . '/../../src/V2/Theme/templates',
    cacheDir: sys_get_temp_dir() . '/form_generator_cache'
);

$theme = new Bootstrap5Theme();
$security = new SecurityManager();

// Build form using Chain Pattern - Simple and intuitive!
$form = FormBuilder::create('user_registration')
    ->setAction('/users/register')
    ->setMethod('POST')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setSecurity($security)

    // Add inputs with fluent interface
    ->addText('name', 'Full Name')
        ->required()
        ->minLength(3)
        ->maxLength(100)
        ->placeholder('Enter your full name')
        ->helpText('Please provide your legal name')
        ->add()

    ->addEmail('email', 'Email Address')
        ->required()
        ->placeholder('you@example.com')
        ->add()

    ->addPassword('password', 'Password')
        ->required()
        ->minLength(8)
        ->helpText('Minimum 8 characters')
        ->add()

    ->addPassword('password_confirm', 'Confirm Password')
        ->required()
        ->minLength(8)
        ->add()

    ->addSelect('country', 'Country')
        ->required()
        ->options([
            'us' => 'United States',
            'uk' => 'United Kingdom',
            'ca' => 'Canada',
            'de' => 'Germany',
            'fr' => 'France',
            'tr' => 'Turkey',
        ])
        ->placeholder('Select your country')
        ->add()

    ->addCheckbox('newsletter', 'Subscribe to newsletter')
        ->defaultValue(true)
        ->add()

    ->addCheckbox('terms', 'I agree to Terms & Conditions')
        ->required()
        ->add()

    ->addSubmit('register', 'Create Account')

    ->build();

// Output the form
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration - FormGenerator V2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h2 class="mb-4">Create Account</h2>
                <?= $form ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
