<?php

/**
 * FormGenerator V2 - Symfony DTO Example
 *
 * Demonstrates DTO support with automatic validation from Symfony constraints
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Validation\SymfonyValidator;
use FormGenerator\V2\Validation\NativeValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

/**
 * Example DTO/Entity with Symfony validation constraints
 */
class UserDTO
{
    #[Assert\NotBlank(message: 'Username is required')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Username must be at least {{ limit }} characters',
        maxMessage: 'Username cannot be longer than {{ limit }} characters'
    )]
    public ?string $username = null;

    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Please enter a valid email address')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(min: 8, minMessage: 'Password must be at least {{ limit }} characters')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        message: 'Password must contain uppercase, lowercase, and number'
    )]
    public ?string $password = null;

    #[Assert\NotBlank]
    #[Assert\Range(min: 18, max: 120, notInRangeMessage: 'Age must be between {{ min }} and {{ max }}')]
    public ?int $age = null;

    #[Assert\Url(message: 'Please enter a valid URL')]
    public ?string $website = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['individual', 'business'], message: 'Invalid account type')]
    public ?string $accountType = null;
}

// Setup
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

// Create Symfony validator
$symfonyValidator = Validation::createValidatorBuilder()
    ->enableAnnotationMapping()
    ->getValidator();

$validator = new SymfonyValidator($symfonyValidator, new NativeValidator());

// Create DTO instance (for edit mode, or null for add mode)
$userDto = new UserDTO();
// $userDto->username = 'johndoe'; // Uncomment for edit mode

$form = FormBuilder::create('user_form')
    ->setAction('/users/save')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setValidator($validator)
    ->setDto($userDto) // Automatically extracts validation rules!
    ->enableClientSideValidation()

    // Validation rules are automatically extracted from DTO!
    ->addText('username', 'Username')
        ->required() // These can override DTO rules
        ->add()

    ->addEmail('email', 'Email Address')
        ->required()
        ->add()

    ->addPassword('password', 'Password')
        ->required()
        ->add()

    ->addNumber('age', 'Age')
        ->required()
        ->add()

    ->addUrl('website', 'Website')
        ->placeholder('https://example.com')
        ->add()

    ->addSelect('accountType', 'Account Type')
        ->required()
        ->options([
            'individual' => 'Individual',
            'business' => 'Business',
        ])
        ->add()

    ->addSubmit('save', 'Save User')
    ->build();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Symfony DTO Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2>User Form with Symfony DTO</h2>

                <div class="alert alert-info">
                    <strong>DTO Features:</strong>
                    <ul class="mb-0">
                        <li>Validation rules extracted from DTO attributes</li>
                        <li>Auto data hydration from DTO properties</li>
                        <li>Symfony Validator integration</li>
                        <li>Type-safe with PHP 8+ attributes</li>
                    </ul>
                </div>

                <?= $form ?>

                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <div class="alert alert-success mt-3">
                        <strong>DTO Validation Result:</strong>
                        <pre><?php
                        // Validate against DTO
                        $formBuilder = FormBuilder::create('user_form')
                            ->setValidator($validator)
                            ->setDto($userDto);

                        $errors = $formBuilder->validateDto($_POST);

                        if (empty($errors)) {
                            echo "✓ All validations passed!\n";
                            echo "DTO is ready to be persisted to database.";
                        } else {
                            echo "✗ Validation errors:\n";
                            foreach ($errors as $field => $error) {
                                echo "  - $field: $error\n";
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
