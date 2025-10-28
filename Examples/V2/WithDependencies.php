<?php

/**
 * FormGenerator V2 - Dependency Management Example
 *
 * This example demonstrates the native dependency management feature
 * where inputs show/hide based on other input values
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

// Build form with dependency management
$form = FormBuilder::create('invoice_form')
    ->setAction('/invoices/create')
    ->setMethod('POST')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setSecurity($security)

    // Invoice Type - This controls which fields are shown
    ->addRadio('invoice_type', 'Invoice Type')
        ->required()
        ->options([
            '1' => 'Individual',
            '2' => 'Corporate',
        ])
        ->defaultValue('1')
        ->controls('invoice_type') // Mark as dependency controller
        ->add()

    // Individual Fields (shown when invoice_type = 1)
    ->addText('first_name', 'First Name')
        ->required()
        ->dependsOn('invoice_type', '1') // Show only when invoice_type = 1
        ->add()

    ->addText('last_name', 'Last Name')
        ->required()
        ->dependsOn('invoice_type', '1')
        ->add()

    ->addText('id_number', 'ID Number')
        ->required()
        ->dependsOn('invoice_type', '1')
        ->placeholder('11 digits')
        ->add()

    // Corporate Fields (shown when invoice_type = 2)
    ->addText('company_name', 'Company Name')
        ->required()
        ->dependsOn('invoice_type', '2') // Show only when invoice_type = 2
        ->add()

    ->addText('tax_office', 'Tax Office')
        ->required()
        ->dependsOn('invoice_type', '2')
        ->add()

    ->addText('tax_number', 'Tax Number')
        ->required()
        ->dependsOn('invoice_type', '2')
        ->placeholder('10 digits')
        ->add()

    // Country - Another dependency controller
    ->addSelect('country', 'Country')
        ->required()
        ->options([
            'us' => 'United States',
            'uk' => 'United Kingdom',
            'tr' => 'Turkey',
            'de' => 'Germany',
        ])
        ->placeholder('Select country...')
        ->controls('country') // Mark as dependency controller
        ->add()

    // State (shown only for US)
    ->addSelect('state', 'State')
        ->required()
        ->dependsOn('country', 'us') // Show only when country = us
        ->options([
            'ca' => 'California',
            'ny' => 'New York',
            'tx' => 'Texas',
        ])
        ->placeholder('Select state...')
        ->add()

    // VAT Number (shown for UK, TR, DE - multiple values)
    ->addText('vat_number', 'VAT Number')
        ->dependsOn('country', ['uk', 'tr', 'de']) // Show for multiple country values
        ->placeholder('Enter VAT number')
        ->add()

    // E-Invoice checkbox
    ->addCheckbox('e_invoice', 'Use E-Invoice')
        ->controls('e_invoice') // Mark as dependency controller
        ->add()

    // E-Invoice email (shown only when e_invoice is checked)
    ->addEmail('e_invoice_email', 'E-Invoice Email')
        ->required()
        ->dependsOn('e_invoice', '1') // Checkbox value is '1' when checked
        ->placeholder('your@email.com')
        ->add()

    // Common fields
    ->addText('address', 'Address')
        ->required()
        ->add()

    ->addText('postal_code', 'Postal Code')
        ->required()
        ->add()

    ->addSubmit('create_invoice', 'Create Invoice')

    ->build();

// Output the form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Form - Dependency Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dependency-demo {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2 class="mb-4">Invoice Form with Dependencies</h2>

                <div class="dependency-demo">
                    <h5>How It Works:</h5>
                    <ul>
                        <li><strong>Invoice Type:</strong> Select "Individual" to see personal fields, "Corporate" for company fields</li>
                        <li><strong>Country:</strong> Select "United States" to see state selection</li>
                        <li><strong>Country (VAT):</strong> Select UK, Turkey, or Germany to see VAT number field</li>
                        <li><strong>E-Invoice:</strong> Check the box to see email field</li>
                    </ul>
                    <p class="mb-0"><em>All dependencies work with pure JavaScript - no jQuery required!</em></p>
                </div>

                <?= $form ?>

                <div class="alert alert-info mt-4">
                    <strong>Developer Note:</strong>
                    The dependency JavaScript is automatically generated and included only once,
                    even if you render the same form multiple times on the page.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
