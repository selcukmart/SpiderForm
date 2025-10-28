<?php

declare(strict_types=1);

/**
 * Example: Form with Data Transformation
 *
 * This example demonstrates how to use Data Transformers to automatically
 * convert data between model format and view format.
 *
 * Features demonstrated:
 * - DateTimeToStringTransformer: Convert DateTime <-> string
 * - StringToArrayTransformer: Convert array <-> comma-separated string
 * - NumberToLocalizedStringTransformer: Format numbers with locale
 * - BooleanToStringTransformer: Convert boolean <-> string
 * - CallbackTransformer: Custom transformation logic
 *
 * @since 2.3.1
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\DataTransformer\DateTimeToStringTransformer;
use FormGenerator\V2\DataTransformer\StringToArrayTransformer;
use FormGenerator\V2\DataTransformer\NumberToLocalizedStringTransformer;
use FormGenerator\V2\DataTransformer\BooleanToStringTransformer;
use FormGenerator\V2\DataTransformer\CallbackTransformer;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;

// ========== Setup ==========

$twig = new \Twig\Environment(
    new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates'),
    ['cache' => false]
);

$renderer = new TwigRenderer($twig);
$theme = new Bootstrap5Theme();

// ========== Sample Model Data ==========

// Simulate data from database/model with proper types
$userData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'birthday' => new \DateTime('1990-05-15'),  // DateTime object
    'tags' => ['php', 'symfony', 'laravel'],    // Array
    'salary' => 75000.50,                        // Float
    'is_active' => true,                         // Boolean
    'join_date' => new \DateTime('2020-01-10'),  // DateTime object
    'bio' => 'Software Developer',
];

// ========== Example 1: Basic Data Transformation ==========

echo "<h2>Example 1: Form with Data Transformers</h2>\n";

$form = FormBuilder::create('user_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/users/save')
    ->setData($userData);

// Text input (no transformation needed)
$form->addText('name', 'Full Name')
    ->required()
    ->minLength(3)
    ->add();

$form->addEmail('email', 'Email')
    ->required()
    ->add();

// DateTime -> String transformation
// Model: DateTime object -> View: 'Y-m-d' string
$form->addDate('birthday', 'Birthday')
    ->addTransformer(new DateTimeToStringTransformer('Y-m-d'))
    ->required()
    ->add();

// DateTime -> String with custom format
$form->addDate('join_date', 'Join Date')
    ->addTransformer(new DateTimeToStringTransformer('d/m/Y'))
    ->add();

// Array -> String transformation
// Model: ['php', 'symfony', 'laravel'] -> View: 'php, symfony, laravel'
$form->addText('tags', 'Skills (comma-separated)')
    ->addTransformer(new StringToArrayTransformer(', '))
    ->helpText('Enter skills separated by commas')
    ->add();

// Number -> Localized String
// Model: 75000.50 -> View: '75,000.50'
$form->addText('salary', 'Salary')
    ->addTransformer(new NumberToLocalizedStringTransformer(
        precision: 2,
        decimalSeparator: '.',
        thousandsSeparator: ','
    ))
    ->add();

// Boolean -> String transformation
// Model: true/false -> View: 'yes'/'no'
$form->addRadio('is_active', 'Account Status')
    ->options(['yes' => 'Active', 'no' => 'Inactive'])
    ->addTransformer(new BooleanToStringTransformer('yes', 'no'))
    ->add();

$form->addTextarea('bio', 'Biography')
    ->add();

$form->addSubmit('save', 'Save User');

echo $form->build();

echo "\n<hr>\n\n";

// ========== Example 2: Custom Transformation with CallbackTransformer ==========

echo "<h2>Example 2: Custom Transformation with Callbacks</h2>\n";

$productData = [
    'name' => 'Premium Package',
    'code' => 'PKG-001',
    'price' => 99.99,
];

$form2 = FormBuilder::create('product_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/products/save')
    ->setData($productData);

$form2->addText('name', 'Product Name')
    ->required()
    ->add();

// Transform: Uppercase for display, lowercase for storage
$form2->addText('code', 'Product Code')
    ->addTransformer(new CallbackTransformer(
        transform: fn($value) => strtoupper($value),
        reverseTransform: fn($value) => strtolower($value)
    ))
    ->helpText('Code will be stored in lowercase')
    ->required()
    ->add();

// Transform: Add currency symbol for display
$form2->addText('price', 'Price')
    ->addTransformer(new CallbackTransformer(
        transform: fn($value) => '$' . number_format($value, 2),
        reverseTransform: fn($value) => (float)str_replace(['$', ','], '', $value)
    ))
    ->add();

$form2->addSubmit('save', 'Save Product');

echo $form2->build();

echo "\n<hr>\n\n";

// ========== Example 3: Processing Form Submission ==========

echo "<h2>Example 3: Processing Submitted Data</h2>\n";

// Simulate form submission
$_POST = [
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'birthday' => '1995-08-20',           // String from form
    'join_date' => '15/03/2021',          // String from form
    'tags' => 'javascript, react, nodejs', // String from form
    'salary' => '85,000.00',               // String from form
    'is_active' => 'yes',                  // String from form
    'bio' => 'Frontend Developer',
];

echo "<h3>Submitted Data (View Format):</h3>\n";
echo "<pre>";
print_r($_POST);
echo "</pre>\n";

// Apply reverse transformation (view -> model)
$modelData = $form->applyReverseTransform($_POST);

echo "<h3>Transformed Data (Model Format):</h3>\n";
echo "<pre>";
print_r($modelData);
echo "</pre>\n";

echo "<p><strong>Notice:</strong> The data has been transformed back to model format:</p>\n";
echo "<ul>\n";
echo "  <li><code>birthday</code>: string '1995-08-20' → DateTime object</li>\n";
echo "  <li><code>join_date</code>: string '15/03/2021' → DateTime object</li>\n";
echo "  <li><code>tags</code>: string 'javascript, react, nodejs' → array ['javascript', 'react', 'nodejs']</li>\n";
echo "  <li><code>salary</code>: string '85,000.00' → float 85000.00</li>\n";
echo "  <li><code>is_active</code>: string 'yes' → boolean true</li>\n";
echo "</ul>\n";

// ========== Example 4: Chaining Multiple Transformers ==========

echo "\n<hr>\n";
echo "<h2>Example 4: Chaining Multiple Transformers</h2>\n";

$form3 = FormBuilder::create('advanced_form')
    ->setRenderer($renderer)
    ->setTheme($theme);

// Multiple transformers are applied in order
$form3->addText('special_field', 'Special Field')
    ->addTransformer(new CallbackTransformer(
        fn($value) => strtoupper($value),
        fn($value) => strtolower($value)
    ))
    ->addTransformer(new CallbackTransformer(
        fn($value) => "PREFIX_{$value}",
        fn($value) => str_replace('PREFIX_', '', $value)
    ))
    ->value('example')  // Will be displayed as "PREFIX_EXAMPLE"
    ->helpText('Demonstrates chaining transformers')
    ->add();

$form3->addSubmit('save');

echo $form3->build();

echo "\n<hr>\n\n";

// ========== Style ==========
?>

<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
        background: #f5f5f5;
    }

    h2 {
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-top: 30px;
    }

    h3 {
        color: #555;
        margin-top: 20px;
    }

    pre {
        background: #fff;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #ddd;
        overflow-x: auto;
    }

    form {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin: 20px 0;
    }

    .alert {
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }

    .alert-info {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
    }

    ul {
        background: #fff;
        padding: 20px 40px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    code {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        color: #e83e8c;
    }
</style>
