<?php

declare(strict_types=1);

/**
 * Examples: i18n & Auto CSRF Protection (v3.0.0)
 *
 * This file demonstrates:
 * - Multi-language support with FormTranslator
 * - Translation loaders (PHP arrays, YAML)
 * - Automatic CSRF protection
 * - CSRF token customization
 * - Real-world usage scenarios
 *
 * @author selcukmart
 * @since 3.0.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Translation\{FormTranslator, TranslatorInterface};
use FormGenerator\V2\Translation\Loader\PhpLoader;
use FormGenerator\V2\Security\{CsrfProtection, CsrfTokenManager};
use FormGenerator\V2\Form\Form;

echo "\n=== i18n & Auto CSRF Protection Examples (v3.0.0) ===\n\n";

// =============================================================================
// Example 1: Basic Translation Setup
// =============================================================================
echo "--- Example 1: Basic Translation Setup ---\n\n";

/**
 * Scenario: Set up translator with PHP array loader
 */

$translator = new FormTranslator(__DIR__ . '/translations');
$translator->addLoader('php', new PhpLoader());
$translator->setLocale('en_US');
$translator->setFallbackLocale('en_US');

// Set translator globally
FormBuilder::setTranslator($translator);

echo "Translator configured:\n";
echo "  Current locale: {$translator->getLocale()}\n";
echo "  Fallback locale: {$translator->getFallbackLocale()}\n";
echo "\n";

// Test translations
echo "Sample translations (en_US):\n";
echo "  form.label.name: " . $translator->trans('form.label.name') . "\n";
echo "  form.label.email: " . $translator->trans('form.label.email') . "\n";
echo "  form.error.required: " . $translator->trans('form.error.required') . "\n";
echo "\n";

// =============================================================================
// Example 2: Multi-Language Forms
// =============================================================================
echo "--- Example 2: Multi-Language Forms ---\n\n";

/**
 * Scenario: Same form in different languages
 */

// English form
$translator->setLocale('en_US');
echo "Form in English (en_US):\n";
echo "  Name label: " . $translator->trans('form.label.name') . "\n";
echo "  Email label: " . $translator->trans('form.label.email') . "\n";
echo "  Submit button: " . $translator->trans('form.button.submit') . "\n";
echo "\n";

// Turkish form
$translator->setLocale('tr_TR');
echo "Form in Turkish (tr_TR):\n";
echo "  Name label: " . $translator->trans('form.label.name') . "\n";
echo "  Email label: " . $translator->trans('form.label.email') . "\n";
echo "  Submit button: " . $translator->trans('form.button.submit') . "\n";
echo "\n";

// French form
$translator->setLocale('fr_FR');
echo "Form in French (fr_FR):\n";
echo "  Name label: " . $translator->trans('form.label.name') . "\n";
echo "  Email label: " . $translator->trans('form.label.email') . "\n";
echo "  Submit button: " . $translator->trans('form.button.submit') . "\n";
echo "\n";

// =============================================================================
// Example 3: Translation with Parameters
// =============================================================================
echo "--- Example 3: Translation with Parameters ---\n\n";

/**
 * Scenario: Parameterized error messages
 */

$translator->setLocale('en_US');

$error1 = $translator->trans('form.error.minLength', ['min' => 3]);
$error2 = $translator->trans('form.error.maxLength', ['max' => 50]);

echo "Parameterized translations (en_US):\n";
echo "  {$error1}\n";
echo "  {$error2}\n";
echo "\n";

$translator->setLocale('tr_TR');

$error1 = $translator->trans('form.error.minLength', ['min' => 3]);
$error2 = $translator->trans('form.error.maxLength', ['max' => 50]);

echo "Parameterized translations (tr_TR):\n";
echo "  {$error1}\n";
echo "  {$error2}\n";
echo "\n";

// =============================================================================
// Example 4: Auto CSRF Protection
// =============================================================================
echo "--- Example 4: Auto CSRF Protection ---\n\n";

/**
 * Scenario: Automatic CSRF token generation and validation
 */

$csrfProtection = new CsrfProtection();

// Generate token for form
$formToken = $csrfProtection->generateToken('contact_form');

echo "CSRF Protection:\n";
echo "  Token generated for 'contact_form'\n";
echo "  Token value: " . substr($formToken, 0, 20) . "...\n";
echo "  Token length: " . strlen($formToken) . " characters\n";
echo "\n";

// Simulate form submission
$submittedData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    '_csrf_token' => $formToken,
];

$isValid = $csrfProtection->validateToken('contact_form', $submittedData);

echo "Token validation:\n";
echo "  Submitted data includes CSRF token: Yes\n";
echo "  Token is valid: " . ($isValid ? 'Yes' : 'No') . "\n";
echo "\n";

// Test with invalid token
$invalidData = [
    'name' => 'John Doe',
    '_csrf_token' => 'invalid_token_12345',
];

$isValid = $csrfProtection->validateToken('contact_form', $invalidData);
echo "Invalid token test:\n";
echo "  Token is valid: " . ($isValid ? 'Yes' : 'No') . "\n";
echo "\n";

// =============================================================================
// Example 5: CSRF with Custom Configuration
// =============================================================================
echo "--- Example 5: CSRF with Custom Configuration ---\n\n";

/**
 * Scenario: Custom CSRF field name and token ID
 */

$customCsrf = new CsrfProtection();

// Generate token with custom ID
$token = $customCsrf->generateToken('user_registration_form');

// Get CSRF field HTML with custom field name
$csrfHtml = $customCsrf->getCsrfFieldHtml('user_registration_form', '_form_token');

echo "Custom CSRF configuration:\n";
echo "  Token ID: user_registration_form\n";
echo "  Field name: _form_token\n";
echo "  Generated HTML:\n";
echo "    {$csrfHtml}\n";
echo "\n";

// Validate with custom field name
$formData = [
    'username' => 'johndoe',
    '_form_token' => $token,
];

$isValid = $customCsrf->validateToken('user_registration_form', $formData, '_form_token');
echo "Validation result: " . ($isValid ? 'Valid' : 'Invalid') . "\n";
echo "\n";

// =============================================================================
// Example 6: CSRF Meta Tags for AJAX
// =============================================================================
echo "--- Example 6: CSRF Meta Tags for AJAX ---\n\n";

/**
 * Scenario: CSRF protection for AJAX requests
 */

$ajaxCsrf = new CsrfProtection();
$metaTags = $ajaxCsrf->getCsrfMetaTags('ajax_form');

echo "CSRF meta tags for AJAX:\n";
echo $metaTags;
echo "\n\n";

echo "JavaScript usage example:\n";
echo <<<'JS'
// Get CSRF token from meta tag
const token = document.querySelector('meta[name="csrf-token"]').content;

// Include in AJAX request
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify(data)
});
JS;
echo "\n\n";

// =============================================================================
// Example 7: Combined i18n and CSRF in FormBuilder
// =============================================================================
echo "--- Example 7: Combined i18n and CSRF in FormBuilder ---\n\n";

/**
 * Scenario: Real-world form with both i18n and CSRF
 */

// Setup translator
$translator->setLocale('en_US');
FormBuilder::setTranslator($translator);

// Create form with translations
$builder = FormBuilder::create('contact_form')
    ->setLocale('en_US')
    ->setCsrfTokenId('contact_form')
    ->setCsrfFieldName('_csrf_token')
    ->enableCsrf(true);

echo "Form configuration:\n";
echo "  Form name: contact_form\n";
echo "  Locale: " . $builder->getFormLocale() . "\n";
echo "  CSRF enabled: Yes\n";
echo "  CSRF token ID: contact_form\n";
echo "  CSRF field name: _csrf_token\n";
echo "\n";

// Translate labels using builder
echo "Translated labels via FormBuilder:\n";
echo "  Name: " . $builder->trans('form.label.name') . "\n";
echo "  Email: " . $builder->trans('form.label.email') . "\n";
echo "  Submit: " . $builder->trans('form.button.submit') . "\n";
echo "\n";

// Get CSRF protection instance
$csrf = $builder->getCsrfProtection();
$csrfToken = $csrf->getTokenValue('contact_form');

echo "CSRF token for form: " . substr($csrfToken, 0, 20) . "...\n";
echo "\n";

// =============================================================================
// Example 8: Multi-Language Form with CSRF
// =============================================================================
echo "--- Example 8: Multi-Language Contact Form ---\n\n";

/**
 * Scenario: Complete contact form in multiple languages
 */

function displayFormInfo(string $locale): void
{
    $translator = FormBuilder::getTranslator();
    $translator->setLocale($locale);

    $builder = FormBuilder::create('contact')
        ->setLocale($locale)
        ->enableCsrf(true);

    echo "Contact Form ({$locale}):\n";
    echo "  " . $builder->trans('form.label.name') . "\n";
    echo "  " . $builder->trans('form.label.email') . "\n";
    echo "  " . $builder->trans('form.label.message') . "\n";
    echo "  [" . $builder->trans('form.button.submit') . "]\n";
    echo "\n";
}

displayFormInfo('en_US');
displayFormInfo('tr_TR');
displayFormInfo('fr_FR');

// =============================================================================
// Example 9: CSRF Token Manager Direct Usage
// =============================================================================
echo "--- Example 9: CSRF Token Manager Direct Usage ---\n\n";

/**
 * Scenario: Low-level token management
 */

$tokenManager = new CsrfTokenManager();

// Generate multiple tokens
$token1 = $tokenManager->generateToken('form1');
$token2 = $tokenManager->generateToken('form2');
$token3 = $tokenManager->generateToken('form3');

echo "Multiple form tokens:\n";
echo "  form1: " . substr($token1, 0, 15) . "...\n";
echo "  form2: " . substr($token2, 0, 15) . "...\n";
echo "  form3: " . substr($token3, 0, 15) . "...\n";
echo "\n";

// Check token existence
echo "Token checks:\n";
echo "  Has 'form1': " . ($tokenManager->hasToken('form1') ? 'Yes' : 'No') . "\n";
echo "  Has 'form99': " . ($tokenManager->hasToken('form99') ? 'Yes' : 'No') . "\n";
echo "\n";

// Refresh token
$newToken = $tokenManager->refreshToken('form1');
echo "Token refresh:\n";
echo "  Old token: " . substr($token1, 0, 15) . "...\n";
echo "  New token: " . substr($newToken, 0, 15) . "...\n";
echo "  Tokens are different: " . ($token1 !== $newToken ? 'Yes' : 'No') . "\n";
echo "\n";

// =============================================================================
// Example 10: Translation Fallback
// =============================================================================
echo "--- Example 10: Translation Fallback ---\n\n";

/**
 * Scenario: Missing translations fall back to default locale
 */

$translator->setLocale('es_ES'); // Spanish (not available)
$translator->setFallbackLocale('en_US');

echo "Fallback test (es_ES → en_US):\n";
echo "  Requested locale: es_ES (not available)\n";
echo "  Fallback locale: en_US\n";
echo "\n";

// Try to translate
$label = $translator->trans('form.label.name');
echo "Translation result:\n";
echo "  form.label.name: {$label}\n";
echo "  (Fell back to English)\n";
echo "\n";

// Missing key returns key itself
$missing = $translator->trans('form.label.nonexistent');
echo "Missing key test:\n";
echo "  form.label.nonexistent: {$missing}\n";
echo "  (Returns key as-is when not found)\n";
echo "\n";

echo "=== All Examples Completed Successfully! ===\n";
