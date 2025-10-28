<?php

declare(strict_types=1);

/**
 * Examples: Advanced Error Handling & Error Bubbling (v2.9.0)
 *
 * This file demonstrates:
 * - Error bubbling from nested forms
 * - Error severity levels (ERROR, WARNING, INFO)
 * - Error presentation formats (nested, flat, filtered)
 * - Error bubbling strategies
 * - Real-world error handling scenarios
 *
 * @author selcukmart
 * @since 2.9.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Form\{Form, FormConfig};
use FormGenerator\V2\Error\{ErrorLevel, ErrorBubblingStrategy, FormError};

echo "\n=== Advanced Error Handling Examples (v2.9.0) ===\n\n";

// =============================================================================
// Example 1: Error Severity Levels
// =============================================================================
echo "--- Example 1: Error Severity Levels (ERROR, WARNING, INFO) ---\n\n";

/**
 * Scenario: Form with different error severity levels
 */

$form = new Form('registration');
$form->add('username', 'text', ['label' => 'Username', 'required' => true]);
$form->add('password', 'password', ['label' => 'Password', 'required' => true]);
$form->add('email', 'email', ['label' => 'Email', 'required' => true]);

// Add errors with different levels
$form->addError('Username is required', ErrorLevel::ERROR, 'username');
$form->addError('Password is weak - consider using special characters', ErrorLevel::WARNING, 'password');
$form->addError('Email verification will be sent after registration', ErrorLevel::INFO, 'email');

echo "Total errors: " . $form->getErrorList()->count() . "\n";
echo "Blocking errors (ERROR): " . $form->getErrorList()->blocking()->count() . "\n";
echo "Warnings: " . $form->getErrorList()->byLevel(ErrorLevel::WARNING)->count() . "\n";
echo "Info messages: " . $form->getErrorList()->byLevel(ErrorLevel::INFO)->count() . "\n";

echo "\nCritical errors only:\n";
foreach ($form->getErrorList()->blocking() as $error) {
    echo "  - [{$error->getPath()}] {$error->getMessage()}\n";
}

echo "\nWarnings only:\n";
foreach ($form->getErrorList()->byLevel(ErrorLevel::WARNING) as $error) {
    echo "  - [{$error->getPath()}] {$error->getMessage()}\n";
}

echo "\n";

// =============================================================================
// Example 2: Error Bubbling from Nested Forms
// =============================================================================
echo "--- Example 2: Error Bubbling from Nested Forms ---\n\n";

/**
 * Scenario: User form with nested address form
 * Errors from address fields bubble up to parent form
 */

$userForm = new Form('user');
$userForm->add('name', 'text', ['label' => 'Name', 'required' => true]);
$userForm->add('email', 'email', ['label' => 'Email', 'required' => true]);

// Add nested address form
$addressForm = new Form('address');
$addressForm->add('street', 'text', ['label' => 'Street', 'required' => true]);
$addressForm->add('city', 'text', ['label' => 'City', 'required' => true]);
$addressForm->add('zipcode', 'text', ['label' => 'ZIP Code', 'required' => true]);

$addressForm->setParent($userForm);
$userForm->add('address', $addressForm);

// Add errors to user form
$userForm->addError('Email is invalid', ErrorLevel::ERROR, 'email');

// Add errors to nested address form
$addressForm->addError('Street is required', ErrorLevel::ERROR, 'street');
$addressForm->addError('Invalid ZIP code format', ErrorLevel::ERROR, 'zipcode');

echo "User form errors (shallow):\n";
foreach ($userForm->getErrorList(deep: false) as $error) {
    echo "  - [{$error->getPath()}] {$error->getMessage()}\n";
}

echo "\nUser form errors (deep with bubbling):\n";
foreach ($userForm->getErrorList(deep: true) as $error) {
    echo "  - [{$error->getPath()}] {$error->getMessage()}\n";
}

echo "\n";

// =============================================================================
// Example 3: Error Presentation Formats
// =============================================================================
echo "--- Example 3: Error Presentation Formats ---\n\n";

/**
 * Scenario: Multiple errors in nested structure
 */

$checkoutForm = new Form('checkout');
$checkoutForm->add('customer_email', 'email');

// Shipping address
$shippingForm = new Form('shipping');
$shippingForm->add('name', 'text');
$shippingForm->add('address', 'text');
$shippingForm->add('zipcode', 'text');
$shippingForm->setParent($checkoutForm);
$checkoutForm->add('shipping', $shippingForm);

// Billing address
$billingForm = new Form('billing');
$billingForm->add('card_number', 'text');
$billingForm->add('cvv', 'text');
$billingForm->setParent($checkoutForm);
$checkoutForm->add('billing', $billingForm);

// Add various errors
$checkoutForm->addError('Email is required', ErrorLevel::ERROR, 'customer_email');
$shippingForm->addError('Name is required', ErrorLevel::ERROR, 'name');
$shippingForm->addError('Invalid ZIP code', ErrorLevel::ERROR, 'zipcode');
$billingForm->addError('Invalid card number', ErrorLevel::ERROR, 'card_number');
$billingForm->addError('CVV is required', ErrorLevel::ERROR, 'cvv');

echo "Format 1: Nested Array\n";
print_r($checkoutForm->getErrorsAsArray(deep: true));

echo "\nFormat 2: Flat Array (dot notation)\n";
print_r($checkoutForm->getErrorsFlattened(deep: true));

echo "\nFormat 3: ErrorList iteration\n";
foreach ($checkoutForm->getErrorList(deep: true) as $error) {
    $level = $error->getLevel()->getIcon();
    echo "  {$level} [{$error->getPath()}] {$error->getMessage()}\n";
}

echo "\n";

// =============================================================================
// Example 4: Error Bubbling Strategies
// =============================================================================
echo "--- Example 4: Error Bubbling Strategies ---\n\n";

/**
 * Scenario: Testing different bubbling strategies
 */

// Strategy 1: Full bubbling (default)
$form1 = new Form('form1');
$childForm1 = new Form('child');
$childForm1->addError('Child error 1', ErrorLevel::ERROR, 'field1');
$childForm1->addError('Child error 2', ErrorLevel::ERROR, 'field2');
$childForm1->setParent($form1);
$form1->add('child', $childForm1);

echo "Strategy 1: Full Bubbling (default)\n";
echo "  Parent errors (deep): " . $form1->getErrorList(deep: true)->count() . "\n";

// Strategy 2: Bubbling disabled
$form2 = new Form('form2');
$form2->setErrorBubblingStrategy(ErrorBubblingStrategy::disabled());
$childForm2 = new Form('child');
$childForm2->addError('Child error 1', ErrorLevel::ERROR, 'field1');
$childForm2->addError('Child error 2', ErrorLevel::ERROR, 'field2');
$childForm2->setParent($form2);
$form2->add('child', $childForm2);

echo "\nStrategy 2: Bubbling Disabled\n";
echo "  Parent errors (deep): " . $form2->getErrorList(deep: true)->count() . "\n";

// Strategy 3: Stop on blocking error
$form3 = new Form('form3');
$form3->setErrorBubblingStrategy(ErrorBubblingStrategy::stopOnBlocking());
$childForm3 = new Form('child');
$childForm3->addError('Blocking error', ErrorLevel::ERROR, 'field1');
$childForm3->addError('This should not bubble', ErrorLevel::WARNING, 'field2');
$childForm3->setParent($form3);
$form3->add('child', $childForm3);

echo "\nStrategy 3: Stop on Blocking Error\n";
echo "  Parent errors (deep): " . $form3->getErrorList(deep: true)->count() . "\n";
foreach ($form3->getErrorList(deep: true) as $error) {
    echo "    - {$error->getMessage()} (Level: {$error->getLevel()->value})\n";
}

echo "\n";

// =============================================================================
// Example 5: Error Message Interpolation
// =============================================================================
echo "--- Example 5: Error Message Interpolation ---\n\n";

/**
 * Scenario: Parameterized error messages
 */

$form = new Form('validation');
$form->add('username', 'text');
$form->add('age', 'number');

// Add errors with parameters
$form->addError(
    'Field {{ field }} must be at least {{ min }} characters',
    ErrorLevel::ERROR,
    'username',
    ['field' => 'username', 'min' => 3]
);

$form->addError(
    'Age must be between {{ min }} and {{ max }}',
    ErrorLevel::ERROR,
    'age',
    ['min' => 18, 'max' => 100]
);

echo "Errors with parameter interpolation:\n";
foreach ($form->getErrorList() as $error) {
    echo "  - [{$error->getPath()}] {$error->getMessage()}\n";
    echo "    Parameters: " . json_encode($error->getParameters()) . "\n";
}

echo "\n";

// =============================================================================
// Example 6: Real-World Scenario - Multi-Step Form Validation
// =============================================================================
echo "--- Example 6: Real-World - Multi-Step Form Validation ---\n\n";

/**
 * Scenario: Job application with multiple sections
 * - Personal info
 * - Work experience
 * - Education
 * Different sections can have different error levels
 */

$applicationForm = new Form('job_application');

// Personal Info Section
$personalForm = new Form('personal');
$personalForm->add('first_name', 'text');
$personalForm->add('last_name', 'text');
$personalForm->add('email', 'email');
$personalForm->add('phone', 'tel');
$personalForm->setParent($applicationForm);
$applicationForm->add('personal', $personalForm);

// Work Experience Section
$experienceForm = new Form('experience');
$experienceForm->add('company', 'text');
$experienceForm->add('position', 'text');
$experienceForm->add('years', 'number');
$experienceForm->setParent($applicationForm);
$applicationForm->add('experience', $experienceForm);

// Education Section
$educationForm = new Form('education');
$educationForm->add('degree', 'text');
$educationForm->add('institution', 'text');
$educationForm->add('year', 'number');
$educationForm->setParent($applicationForm);
$applicationForm->add('education', $educationForm);

// Validation: Personal info (critical errors)
$personalForm->addError('First name is required', ErrorLevel::ERROR, 'first_name');
$personalForm->addError('Email is required', ErrorLevel::ERROR, 'email');

// Validation: Work experience (warnings for optional fields)
$experienceForm->addError('Consider adding your company name for better results', ErrorLevel::WARNING, 'company');
$experienceForm->addError('Years of experience helps our evaluation', ErrorLevel::INFO, 'years');

// Validation: Education (errors)
$educationForm->addError('Degree is required', ErrorLevel::ERROR, 'degree');

echo "Application Form Validation Results:\n";
echo "=====================================\n\n";

$allErrors = $applicationForm->getErrorList(deep: true);

echo "Summary:\n";
echo "  Total errors: {$allErrors->count()}\n";
echo "  Critical errors: {$allErrors->blocking()->count()}\n";
echo "  Warnings: {$allErrors->byLevel(ErrorLevel::WARNING)->count()}\n";
echo "  Info messages: {$allErrors->byLevel(ErrorLevel::INFO)->count()}\n";
echo "\n";

// Group by section
echo "Errors by Section:\n";
foreach (['personal', 'experience', 'education'] as $section) {
    $sectionErrors = $allErrors->byPath($section, deep: true);
    if ($sectionErrors->count() > 0) {
        echo "\n  {$section}:\n";
        foreach ($sectionErrors as $error) {
            $icon = $error->getLevel()->getIcon();
            $path = str_replace($section . '.', '', $error->getPath());
            echo "    {$icon} [{$path}] {$error->getMessage()}\n";
        }
    }
}

echo "\n";

// Check if form can be submitted
$canSubmit = !$applicationForm->getErrorList(deep: true)->hasBlocking();
echo "Can submit form: " . ($canSubmit ? 'Yes' : 'No (has blocking errors)') . "\n";

echo "\n";

// =============================================================================
// Example 7: Error Filtering and Querying
// =============================================================================
echo "--- Example 7: Error Filtering and Querying ---\n\n";

/**
 * Scenario: Complex form with many errors, need to filter/query them
 */

$form = new Form('complex');
$form->add('field1', 'text');
$form->add('field2', 'text');
$form->add('field3', 'text');

// Add various errors
$form->addError('Field 1 error', ErrorLevel::ERROR, 'field1');
$form->addError('Field 2 warning', ErrorLevel::WARNING, 'field2');
$form->addError('Field 3 info', ErrorLevel::INFO, 'field3');
$form->addError('Another field 1 error', ErrorLevel::ERROR, 'field1');

$errors = $form->getErrorList();

echo "Query 1: Get all errors for 'field1'\n";
$field1Errors = $errors->byPath('field1');
echo "  Count: {$field1Errors->count()}\n";
foreach ($field1Errors as $error) {
    echo "    - {$error->getMessage()}\n";
}

echo "\nQuery 2: Get first error for 'field1'\n";
$firstError = $errors->first('field1');
echo "  {$firstError->getMessage()}\n";

echo "\nQuery 3: Check if has any errors\n";
echo "  Has errors: " . ($errors->isEmpty() ? 'No' : 'Yes') . "\n";
echo "  Has blocking: " . ($errors->hasBlocking() ? 'Yes' : 'No') . "\n";

echo "\nQuery 4: Get error counts by level\n";
echo "  Errors: " . $errors->byLevel(ErrorLevel::ERROR)->count() . "\n";
echo "  Warnings: " . $errors->byLevel(ErrorLevel::WARNING)->count() . "\n";
echo "  Info: " . $errors->byLevel(ErrorLevel::INFO)->count() . "\n";

echo "\n";

echo "=== All Examples Completed Successfully! ===\n";
