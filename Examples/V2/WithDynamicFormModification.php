<?php

declare(strict_types=1);

/**
 * Examples: Dynamic Form Modification API (v2.8.0)
 *
 * This file demonstrates:
 * - Event-based form modification (PRE_SET_DATA, POST_SET_DATA, PRE_SUBMIT, POST_SUBMIT)
 * - Dynamic field addition/removal at runtime
 * - Conditional field building
 * - Real-world scenarios
 *
 * @author selcukmart
 * @since 2.8.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Event\{FormEvents, FormEvent};
use FormGenerator\V2\Form\Form;

echo "\n=== Dynamic Form Modification API Examples (v2.8.0) ===\n\n";

// =============================================================================
// Example 1: Event-Based Form Modification (PRE_SET_DATA)
// =============================================================================
echo "--- Example 1: PRE_SET_DATA - Modify Form Based on Loaded Data ---\n\n";

/**
 * Scenario: Product form that shows different fields based on product category
 * - Physical products: weight, dimensions
 * - Digital products: download link, file size
 */

$productForm = FormBuilder::create('product')
    ->addField('name', 'text', ['label' => 'Product Name', 'required' => true])
    ->addField('category', 'select', [
        'label' => 'Category',
        'choices' => [
            'physical' => 'Physical Product',
            'digital' => 'Digital Product',
            'service' => 'Service',
        ],
        'required' => true,
    ])
    ->addField('price', 'number', ['label' => 'Price', 'required' => true])
    ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Add category-specific fields based on existing data
        if ($data && isset($data['category'])) {
            if ($data['category'] === 'physical') {
                // Add physical product fields
                $form->addField('weight', 'number', [
                    'label' => 'Weight (kg)',
                    'required' => true,
                ]);
                $form->addField('dimensions', 'text', [
                    'label' => 'Dimensions (LxWxH cm)',
                ]);
            } elseif ($data['category'] === 'digital') {
                // Add digital product fields
                $form->addField('download_link', 'url', [
                    'label' => 'Download URL',
                    'required' => true,
                ]);
                $form->addField('file_size', 'number', [
                    'label' => 'File Size (MB)',
                ]);
            } elseif ($data['category'] === 'service') {
                // Add service fields
                $form->addField('duration', 'number', [
                    'label' => 'Duration (hours)',
                    'required' => true,
                ]);
                $form->addField('location', 'text', [
                    'label' => 'Service Location',
                ]);
            }
        }
    })
    ->buildForm();

// Simulate loading a physical product from database
$existingProductData = [
    'name' => 'Laptop Stand',
    'category' => 'physical',
    'price' => 49.99,
];

$productForm->setData($existingProductData);

echo "Product form loaded with physical product data:\n";
echo "- Form has weight field: " . ($productForm->has('weight') ? 'Yes' : 'No') . "\n";
echo "- Form has download_link field: " . ($productForm->has('download_link') ? 'No' : 'Yes') . "\n";
echo "- Form has duration field: " . ($productForm->has('duration') ? 'Yes' : 'No') . "\n";
echo "\n";

// =============================================================================
// Example 2: Event-Based Form Modification (PRE_SUBMIT)
// =============================================================================
echo "--- Example 2: PRE_SUBMIT - Modify Form Based on Submitted Data ---\n\n";

/**
 * Scenario: User registration form that dynamically adds fields based on user type
 */

$registrationForm = FormBuilder::create('registration')
    ->addField('username', 'text', ['label' => 'Username', 'required' => true])
    ->addField('email', 'email', ['label' => 'Email', 'required' => true])
    ->addField('user_type', 'select', [
        'label' => 'User Type',
        'choices' => [
            'individual' => 'Individual',
            'business' => 'Business',
            'nonprofit' => 'Non-Profit Organization',
        ],
        'required' => true,
    ])
    ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Add type-specific fields based on submitted data
        if (isset($data['user_type'])) {
            if ($data['user_type'] === 'business') {
                $form->addField('company_name', 'text', [
                    'label' => 'Company Name',
                    'required' => true,
                ]);
                $form->addField('tax_id', 'text', [
                    'label' => 'Tax ID',
                    'required' => true,
                ]);
            } elseif ($data['user_type'] === 'nonprofit') {
                $form->addField('organization_name', 'text', [
                    'label' => 'Organization Name',
                    'required' => true,
                ]);
                $form->addField('registration_number', 'text', [
                    'label' => 'Registration Number',
                    'required' => true,
                ]);
            } elseif ($data['user_type'] === 'individual') {
                $form->addField('first_name', 'text', [
                    'label' => 'First Name',
                    'required' => true,
                ]);
                $form->addField('last_name', 'text', [
                    'label' => 'Last Name',
                    'required' => true,
                ]);
            }
        }
    })
    ->buildForm();

// Simulate form submission with business user type
$submittedData = [
    'username' => 'acmecorp',
    'email' => 'info@acme.com',
    'user_type' => 'business',
    'company_name' => 'ACME Corporation',
    'tax_id' => '12-3456789',
];

$registrationForm->submit($submittedData);

echo "Registration form submitted with business user type:\n";
echo "- Form has company_name field: " . ($registrationForm->has('company_name') ? 'Yes' : 'No') . "\n";
echo "- Form has tax_id field: " . ($registrationForm->has('tax_id') ? 'Yes' : 'No') . "\n";
echo "- Form has first_name field: " . ($registrationForm->has('first_name') ? 'Yes' : 'No') . "\n";
echo "- Form is valid: " . ($registrationForm->isValid() ? 'Yes' : 'No') . "\n";
echo "\n";

// =============================================================================
// Example 3: Dynamic Field Addition/Removal
// =============================================================================
echo "--- Example 3: Runtime Field Addition/Removal ---\n\n";

/**
 * Scenario: Form that dynamically adds/removes fields based on business logic
 */

$form = new Form('dynamic_form');

// Start with basic fields
$form->add('email', 'email', ['label' => 'Email', 'required' => true]);
$form->add('password', 'password', ['label' => 'Password', 'required' => true]);

echo "Initial form fields: " . implode(', ', array_keys($form->all())) . "\n";

// Add fields dynamically
$form->add('remember_me', 'checkbox', ['label' => 'Remember Me']);
$form->add('newsletter', 'checkbox', ['label' => 'Subscribe to Newsletter']);

echo "After adding fields: " . implode(', ', array_keys($form->all())) . "\n";

// Remove a field
$form->remove('newsletter');

echo "After removing newsletter: " . implode(', ', array_keys($form->all())) . "\n";

// Check field existence
echo "Has 'remember_me': " . ($form->has('remember_me') ? 'Yes' : 'No') . "\n";
echo "Has 'newsletter': " . ($form->has('newsletter') ? 'Yes' : 'No') . "\n";

// Get specific field
try {
    $emailField = $form->get('email');
    echo "Email field type: " . $emailField->getConfig()->getType() . "\n";
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// =============================================================================
// Example 4: Conditional Field Building
// =============================================================================
echo "--- Example 4: Conditional Field Building ---\n\n";

/**
 * Scenario: Job application form with conditional fields based on job type
 */

$jobType = 'remote'; // Can be: 'remote', 'onsite', 'hybrid'
$requiresCoverLetter = true;
$requiresPortfolio = false;

$applicationFormBuilder = FormBuilder::create('job_application')
    ->addField('full_name', 'text', ['label' => 'Full Name', 'required' => true])
    ->addField('email', 'email', ['label' => 'Email', 'required' => true])
    ->addField('phone', 'tel', ['label' => 'Phone', 'required' => true]);

// Conditionally add location field
if ($jobType === 'onsite' || $jobType === 'hybrid') {
    $applicationFormBuilder->addField('current_location', 'text', [
        'label' => 'Current Location',
        'required' => true,
    ]);
}

// Conditionally add remote-specific fields
if ($jobType === 'remote') {
    $applicationFormBuilder->addField('timezone', 'select', [
        'label' => 'Timezone',
        'choices' => [
            'UTC-8' => 'Pacific Time (UTC-8)',
            'UTC-5' => 'Eastern Time (UTC-5)',
            'UTC+0' => 'UTC',
            'UTC+1' => 'Central European Time (UTC+1)',
        ],
        'required' => true,
    ]);
    $applicationFormBuilder->addField('home_office_setup', 'textarea', [
        'label' => 'Describe Your Home Office Setup',
    ]);
}

// Conditionally add cover letter
if ($requiresCoverLetter) {
    $applicationFormBuilder->addField('cover_letter', 'textarea', [
        'label' => 'Cover Letter',
        'required' => true,
    ]);
}

// Conditionally add portfolio
if ($requiresPortfolio) {
    $applicationFormBuilder->addField('portfolio_url', 'url', [
        'label' => 'Portfolio URL',
        'required' => true,
    ]);
}

$applicationForm = $applicationFormBuilder->buildForm();

echo "Job application form for {$jobType} position:\n";
$fields = array_keys($applicationForm->all());
echo "- Fields: " . implode(', ', $fields) . "\n";
echo "- Has timezone: " . ($applicationForm->has('timezone') ? 'Yes' : 'No') . "\n";
echo "- Has current_location: " . ($applicationForm->has('current_location') ? 'Yes' : 'No') . "\n";
echo "- Has cover_letter: " . ($applicationForm->has('cover_letter') ? 'Yes' : 'No') . "\n";
echo "- Has portfolio_url: " . ($applicationForm->has('portfolio_url') ? 'Yes' : 'No') . "\n";
echo "\n";

// =============================================================================
// Example 5: POST_SET_DATA and POST_SUBMIT Events
// =============================================================================
echo "--- Example 5: POST_SET_DATA and POST_SUBMIT Events ---\n\n";

/**
 * Scenario: Form with data transformation and logging
 */

$userForm = FormBuilder::create('user')
    ->addField('email', 'email', ['label' => 'Email', 'required' => true])
    ->addField('age', 'number', ['label' => 'Age', 'required' => true])

    // POST_SET_DATA: Transform/normalize data after it's set
    ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
        $data = $event->getData();

        echo "[POST_SET_DATA] Data loaded into form:\n";
        echo "  - Email: " . ($data['email'] ?? 'N/A') . "\n";
        echo "  - Age: " . ($data['age'] ?? 'N/A') . "\n";

        // You could transform data here if needed
        // $event->setData($transformedData);
    })

    // POST_SUBMIT: Process form after submission and validation
    ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        echo "[POST_SUBMIT] Form submitted:\n";
        echo "  - Is Valid: " . ($form->isValid() ? 'Yes' : 'No') . "\n";

        if ($form->isValid()) {
            echo "  - Would save to database: Email={$data['email']}, Age={$data['age']}\n";
            // Here you would typically:
            // - Save to database
            // - Send notifications
            // - Redirect user
        } else {
            echo "  - Validation failed\n";
        }
    })
    ->buildForm();

// Load data
echo "Loading user data...\n";
$userForm->setData(['email' => 'john@example.com', 'age' => 30]);
echo "\n";

// Submit form
echo "Submitting form...\n";
$userForm->submit(['email' => 'john.doe@example.com', 'age' => 31]);
echo "\n";

// =============================================================================
// Example 6: Complex Real-World Scenario - Shipping Form
// =============================================================================
echo "--- Example 6: Complex Shipping Form with Dynamic Modification ---\n\n";

/**
 * Scenario: Shipping form that adapts based on:
 * - Shipping country (domestic vs international)
 * - Delivery speed (standard vs express)
 * - Package insurance selection
 */

$shippingForm = FormBuilder::create('shipping')
    ->addField('recipient_name', 'text', ['label' => 'Recipient Name', 'required' => true])
    ->addField('country', 'select', [
        'label' => 'Country',
        'choices' => [
            'US' => 'United States',
            'CA' => 'Canada',
            'UK' => 'United Kingdom',
            'DE' => 'Germany',
            'JP' => 'Japan',
        ],
        'required' => true,
    ])
    ->addField('delivery_speed', 'select', [
        'label' => 'Delivery Speed',
        'choices' => [
            'standard' => 'Standard (5-7 days)',
            'express' => 'Express (2-3 days)',
            'overnight' => 'Overnight',
        ],
        'required' => true,
    ])

    // Modify form based on submitted data
    ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Add international shipping fields
        if (isset($data['country']) && !in_array($data['country'], ['US', 'CA'])) {
            $form->addField('customs_value', 'number', [
                'label' => 'Customs Value (USD)',
                'required' => true,
            ]);
            $form->addField('customs_description', 'textarea', [
                'label' => 'Customs Description',
                'required' => true,
            ]);
        }

        // Add express delivery phone requirement
        if (isset($data['delivery_speed']) && $data['delivery_speed'] !== 'standard') {
            $form->addField('phone', 'tel', [
                'label' => 'Contact Phone (for delivery notifications)',
                'required' => true,
            ]);
        }

        // Add signature requirement for overnight
        if (isset($data['delivery_speed']) && $data['delivery_speed'] === 'overnight') {
            $form->addField('signature_required', 'checkbox', [
                'label' => 'Signature Required',
            ]);
        }
    })
    ->buildForm();

// Submit international express shipping
$shippingData = [
    'recipient_name' => 'Yuki Tanaka',
    'country' => 'JP',
    'delivery_speed' => 'express',
    'customs_value' => 250.00,
    'customs_description' => 'Electronics - Laptop Accessories',
    'phone' => '+81-90-1234-5678',
];

$shippingForm->submit($shippingData);

echo "Shipping form submitted (International + Express):\n";
echo "- Has customs_value: " . ($shippingForm->has('customs_value') ? 'Yes' : 'No') . "\n";
echo "- Has phone: " . ($shippingForm->has('phone') ? 'Yes' : 'No') . "\n";
echo "- Has signature_required: " . ($shippingForm->has('signature_required') ? 'Yes' : 'No') . "\n";
echo "- Form is valid: " . ($shippingForm->isValid() ? 'Yes' : 'No') . "\n";
echo "\n";

echo "=== All Examples Completed Successfully! ===\n";
