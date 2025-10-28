<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Contracts\TextDirection;
use FormGenerator\Examples\V2\Forms\UserRegistrationForm;

/**
 * FormGenerator V2 - Class-Based Form Types Examples
 *
 * Demonstrates how to use class-based, OOP approach for forms
 * similar to Symfony's FormType system.
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

echo "<h1 class='mb-4'>Class-Based Form Types</h1>";
echo "<p class='lead'>Reusable, OOP approach for building forms</p>";

// =============================================================================
// Example 1: Basic Form Type Usage
// =============================================================================
echo "<h2 class='mb-4'>Example 1: Basic Form Type Usage</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

// Create form from FormType class
$form = FormBuilder::createFromType(new UserRegistrationForm())
    ->setRenderer($renderer)
    ->setTheme($theme);

echo $form->build();

echo "</div></div>";

// =============================================================================
// Example 2: Form Type with Custom Options
// =============================================================================
echo "<h2 class='mb-4'>Example 2: Form Type with Custom Options</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Override default options when creating from type:</p>";

$form2 = FormBuilder::createFromType(new UserRegistrationForm(), [
    'action' => '/custom-register',
    'method' => 'POST',
    'csrf_protection' => false, // Override default
    'attr' => [
        'class' => 'custom-form',
        'data-ajax' => 'true',
    ],
])
    ->setRenderer($renderer)
    ->setTheme($theme);

echo $form2->build();

echo "</div></div>";

// =============================================================================
// Example 3: Inline Form Type Definition
// =============================================================================
echo "<h2 class='mb-4'>Example 3: Inline Form Type Definition</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Create a form type on-the-fly using anonymous class:</p>";

$inlineForm = FormBuilder::createFromType(
    new class extends \FormGenerator\V2\Form\AbstractFormType {
        public function buildForm(FormBuilder $builder, array $options): void
        {
            $builder
                ->addText('search', 'Search')
                    ->placeholder('Enter search term...')
                    ->add()
                ->addSelect('category', 'Category')
                    ->options([
                        '' => '-- All Categories --',
                        'products' => 'Products',
                        'users' => 'Users',
                        'orders' => 'Orders',
                    ])
                    ->add()
                ->addSubmit('search', 'Search');
        }

        public function configureOptions(): array
        {
            return [
                'method' => 'GET',
                'csrf_protection' => false,
            ];
        }
    }
)
    ->setRenderer($renderer)
    ->setTheme($theme);

echo $inlineForm->build();

echo "</div></div>";

// =============================================================================
// Example 4: Form Type with RTL Support
// =============================================================================
echo "<h2 class='mb-4'>Example 4: Form Type with RTL Support</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Form type with Arabic/RTL support:</p>";

$rtlForm = FormBuilder::createFromType(
    new class extends \FormGenerator\V2\Form\AbstractFormType {
        public function buildForm(FormBuilder $builder, array $options): void
        {
            $builder
                ->addText('username', 'اسم المستخدم')
                    ->required()
                    ->add()
                ->addEmail('email', 'البريد الإلكتروني')
                    ->required()
                    ->add()
                ->addPassword('password', 'كلمة المرور')
                    ->required()
                    ->add()
                ->addSubmit('submit', 'تسجيل');
        }

        public function configureOptions(): array
        {
            return [
                'action' => '/register',
                'direction' => TextDirection::RTL,
            ];
        }
    },
    [
        'direction' => TextDirection::RTL,
        'locale' => \FormGenerator\V2\Builder\DatePickerManager::LOCALE_AR,
    ]
)
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setDirection(TextDirection::RTL);

echo $rtlForm->build();

echo "</div></div>";

// =============================================================================
// Example 5: Comparison - Old vs New Approach
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Old vs New Approach</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

echo "<h5 class='text-danger'>❌ Old Approach (Inline form building):</h5>";
echo "<pre style='direction: ltr; text-align: left;'>" . htmlspecialchars(
<<<'PHP'
// Every time you need this form, you repeat the entire definition
$form = FormBuilder::create('user_registration')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/register')
    ->addText('username', 'Username')->required()->add()
    ->addEmail('email', 'Email')->required()->add()
    ->addPassword('password', 'Password')->required()->add()
    ->addSubmit('register', 'Register')
    ->build();

// Not reusable, not maintainable, not testable
PHP
) . "</pre>";

echo "<h5 class='text-success mt-4'>✅ New Approach (Class-based FormType):</h5>";
echo "<pre style='direction: ltr; text-align: left;'>" . htmlspecialchars(
<<<'PHP'
// Define once in UserRegistrationForm class
class UserRegistrationForm extends AbstractFormType {
    public function buildForm(FormBuilder $builder, array $options): void {
        $builder
            ->addText('username', 'Username')->required()->add()
            ->addEmail('email', 'Email')->required()->add()
            ->addPassword('password', 'Password')->required()->add()
            ->addSubmit('register', 'Register');
    }
}

// Use anywhere with one line
$form = FormBuilder::createFromType(new UserRegistrationForm())
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->build();

// Reusable, maintainable, testable, follows OOP principles
PHP
) . "</pre>";

echo "<div class='alert alert-info mt-3'>";
echo "<strong>Benefits of Class-Based Approach:</strong>";
echo "<ul>";
echo "<li><strong>Reusability</strong>: Define once, use many times</li>";
echo "<li><strong>Maintainability</strong>: Single source of truth</li>";
echo "<li><strong>Testability</strong>: Easy to unit test</li>";
echo "<li><strong>OOP Principles</strong>: Encapsulation, inheritance</li>";
echo "<li><strong>Type Safety</strong>: IDE autocomplete and type checking</li>";
echo "<li><strong>Configurability</strong>: Override options when needed</li>";
echo "</ul>";
echo "</div>";

echo "</div></div>";

// =============================================================================
// Summary
// =============================================================================
echo "<div class='alert alert-success'>";
echo "<h4>Summary: Class-Based Form Types</h4>";
echo "<p><strong>Key Features:</strong></p>";
echo "<ul>";
echo "<li><strong>AbstractFormType</strong>: Base class for all form types</li>";
echo "<li><strong>FormTypeInterface</strong>: Contract for form types</li>";
echo "<li><strong>buildForm()</strong>: Define form fields</li>";
echo "<li><strong>configureOptions()</strong>: Set default options</li>";
echo "<li><strong>createFromType()</strong>: Create form from type</li>";
echo "<li><strong>Option Override</strong>: Customize per usage</li>";
echo "<li><strong>Inheritance</strong>: Extend and reuse form types</li>";
echo "<li><strong>Composition</strong>: Combine multiple form types</li>";
echo "</ul>";
echo "</div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";
