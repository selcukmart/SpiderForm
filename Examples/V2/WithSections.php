<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * Example: Form Sections
 * 
 * This example demonstrates:
 * 1. Grouping inputs under sections with titles
 * 2. Section descriptions (HTML supported)
 * 3. Custom HTML content in sections
 * 4. Collapsible sections
 * 5. Custom section styling
 */

// Example 1: Basic Sections
$form1 = FormBuilder::create('form-with-sections')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    // Section 1: Personal Information
    ->addSection('Personal Information', 'Please provide your basic details')
    ->addText('first_name', 'First Name')
    ->required()
    ->add()
    
    ->addText('last_name', 'Last Name')
    ->required()
    ->add()
    
    ->addEmail('email', 'Email Address')
    ->required()
    ->add()
    
    ->addDate('birthdate', 'Date of Birth')
    ->add()
    ->endSection()
    
    // Section 2: Address Information
    ->addSection('Address', 'Where do you currently reside?')
    ->addText('street', 'Street Address')
    ->required()
    ->add()
    
    ->addText('city', 'City')
    ->required()
    ->add()
    
    ->addSelect('country', 'Country')
    ->options([
        'us' => 'United States',
        'uk' => 'United Kingdom',
        'ca' => 'Canada',
        'au' => 'Australia'
    ])
    ->required()
    ->add()
    
    ->addText('postal_code', 'Postal Code')
    ->add()
    ->endSection()
    
    // Section 3: Account Settings
    ->addSection('Account Settings', 'Configure your account preferences')
    ->addPassword('password', 'Password')
    ->required()
    ->minLength(8)
    ->add()
    
    ->addPassword('password_confirm', 'Confirm Password')
    ->required()
    ->add()
    
    ->addCheckbox('newsletter', 'Subscribe to newsletter')
    ->add()
    ->endSection()
    
    ->addSubmit('Create Account')
    ->build();

// Example 2: Section with HTML Content
$form2 = FormBuilder::create('form-with-html-content')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Terms and Conditions', 'Please review and accept our terms')
    ->setSectionHtml('
        <div class="alert alert-info">
            <strong>Important:</strong> By creating an account, you agree to our 
            <a href="/terms" target="_blank">Terms of Service</a> and 
            <a href="/privacy" target="_blank">Privacy Policy</a>.
        </div>
    ')
    
    ->addCheckbox('accept_terms', 'I accept the terms and conditions')
    ->required()
    ->add()
    
    ->addCheckbox('accept_privacy', 'I accept the privacy policy')
    ->required()
    ->add()
    ->endSection()
    
    ->addSubmit('Continue')
    ->build();

// Example 3: Collapsible Sections
$form3 = FormBuilder::create('form-with-collapsible-sections')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    // Expanded section
    ->addSection('Required Information', 'This information is required')
    ->collapsibleSection(false) // false = expanded by default
    
    ->addText('company_name', 'Company Name')
    ->required()
    ->add()
    
    ->addEmail('company_email', 'Company Email')
    ->required()
    ->add()
    ->endSection()
    
    // Collapsed section
    ->addSection('Optional Information', 'Additional details (optional)')
    ->collapsibleSection(true) // true = collapsed by default
    
    ->addText('tax_id', 'Tax ID')
    ->add()
    
    ->addText('vat_number', 'VAT Number')
    ->add()
    
    ->addText('website', 'Website')
    ->add()
    ->endSection()
    
    // Another collapsed section
    ->addSection('Advanced Settings', 'Expert configuration options')
    ->collapsibleSection(true)
    ->setSectionHtml('<p class="text-warning"><strong>Warning:</strong> Only modify these settings if you know what you\'re doing.</p>')
    
    ->addSelect('api_version', 'API Version')
    ->options(['v1' => 'Version 1.0', 'v2' => 'Version 2.0'])
    ->add()
    
    ->addCheckbox('enable_webhooks', 'Enable Webhooks')
    ->add()
    ->endSection()
    
    ->addSubmit('Save Settings')
    ->build();

// Example 4: Custom Section Styling
$form4 = FormBuilder::create('form-custom-section-styling')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Premium Features', '<strong>Unlock</strong> premium features with your subscription')
    ->setSectionClasses(['border', 'border-primary', 'p-3', 'rounded', 'bg-light'])
    ->setSectionAttributes(['data-premium' => 'true'])
    
    ->addCheckbox('feature_1', 'Advanced Analytics')
    ->add()
    
    ->addCheckbox('feature_2', 'Priority Support')
    ->add()
    
    ->addCheckbox('feature_3', 'API Access')
    ->add()
    ->endSection()
    
    ->addSection('Billing Information')
    ->setSectionClasses(['border', 'border-success', 'p-3', 'rounded'])
    
    ->addText('card_number', 'Card Number')
    ->required()
    ->pattern('[0-9]{16}')
    ->add()
    
    ->addText('card_expiry', 'Expiry (MM/YY)')
    ->required()
    ->pattern('[0-9]{2}/[0-9]{2}')
    ->add()
    ->endSection()
    
    ->addSubmit('Subscribe Now')
    ->build();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Sections Examples</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 40px 0;
            background: #f8f9fa;
        }
        .example-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .example-title {
            color: #495057;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .feature-badge {
            display: inline-block;
            background: #e7f3ff;
            color: #0056b3;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4">Form Sections</h1>
            <p class="lead text-muted">Organize your forms with titled sections, descriptions, and HTML content</p>
        </div>

        <!-- Example 1 -->
        <div class="example-section">
            <h2 class="example-title">Example 1: Basic Sections</h2>
            <div class="feature-badge">Section Titles</div>
            <div class="feature-badge">Descriptions</div>
            <div class="feature-badge">Grouped Inputs</div>
            
            <p class="text-muted mb-4">
                Form is divided into logical sections: Personal Information, Address, and Account Settings.
            </p>
            
            <?= $form1 ?>
        </div>

        <!-- Example 2 -->
        <div class="example-section">
            <h2 class="example-title">Example 2: HTML Content in Sections</h2>
            <div class="feature-badge">HTML Support</div>
            <div class="feature-badge">Rich Content</div>
            <div class="feature-badge">Custom Markup</div>
            
            <p class="text-muted mb-4">
                Sections can contain custom HTML content like alerts, links, and formatted text.
            </p>
            
            <?= $form2 ?>
        </div>

        <!-- Example 3 -->
        <div class="example-section">
            <h2 class="example-title">Example 3: Collapsible Sections</h2>
            <div class="feature-badge">Collapsible</div>
            <div class="feature-badge">Expand/Collapse</div>
            <div class="feature-badge">Default State</div>
            
            <p class="text-muted mb-4">
                Sections can be collapsible with Bootstrap's collapse component. Set initial state as expanded or collapsed.
            </p>
            
            <?= $form3 ?>
        </div>

        <!-- Example 4 -->
        <div class="example-section">
            <h2 class="example-title">Example 4: Custom Section Styling</h2>
            <div class="feature-badge">Custom Classes</div>
            <div class="feature-badge">Custom Attributes</div>
            <div class="feature-badge">Styled Sections</div>
            
            <p class="text-muted mb-4">
                Apply custom CSS classes and HTML attributes to sections for complete styling control.
            </p>
            
            <?= $form4 ?>
        </div>

        <div class="alert alert-info">
            <h5>Section API Reference</h5>
            <pre class="mb-0"><code>// Start a section
$form->addSection('Title', 'Optional description')
    ->setSectionHtml('<div>Custom HTML content</div>')
    ->setSectionClasses(['class1', 'class2'])
    ->setSectionAttributes(['data-key' => 'value'])
    ->collapsibleSection(false) // false = expanded, true = collapsed
    
    // Add inputs here
    ->addText('field1', 'Field 1')->add()
    ->addText('field2', 'Field 2')->add()
    
    ->endSection(); // End the section</code></pre>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
