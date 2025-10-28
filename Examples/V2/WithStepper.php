<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Builder\StepperManager;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * FormGenerator V2 - Stepper/Wizard Examples
 *
 * Demonstrates multi-step form wizards with different configurations
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

// =============================================================================
// Example 1: Linear Multi-Step Registration Form (Horizontal Layout)
// =============================================================================
echo "<h2 class='mb-4'>Example 1: Linear Multi-Step Registration (Horizontal)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form1 = FormBuilder::create('registration_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/register')
    ->setMethod('POST')
    ->enableStepper([
        'layout' => StepperManager::LAYOUT_HORIZONTAL,
        'mode' => StepperManager::MODE_LINEAR,
        'validateOnNext' => true,
        'animation' => true,
        'animationDuration' => 300,
    ]);

// Step 1: Personal Information
$form1->addSection('Personal Information', 'Please provide your basic information')
    ->addText('first_name', 'First Name')->required()->minLength(2)->add()
    ->addText('last_name', 'Last Name')->required()->minLength(2)->add()
    ->addEmail('email', 'Email Address')->required()->email()->add()
    ->addDate('birth_date', 'Date of Birth')->required()->add()
    ->endSection();

// Step 2: Account Details
$form1->addSection('Account Details', 'Set up your account credentials')
    ->addText('username', 'Username')->required()->minLength(4)->add()
    ->addPassword('password', 'Password')->required()->minLength(8)->add()
    ->addPassword('password_confirm', 'Confirm Password')->required()->add()
    ->endSection();

// Step 3: Contact Information
$form1->addSection('Contact Information', 'How can we reach you?')
    ->addText('phone', 'Phone Number')->required()->pattern('^[0-9]{10}$', 'Enter 10 digit phone number')->add()
    ->addText('address', 'Street Address')->required()->add()
    ->addText('city', 'City')->required()->add()
    ->addSelect('country', 'Country')->required()->options([
        '' => 'Select Country',
        'US' => 'United States',
        'UK' => 'United Kingdom',
        'CA' => 'Canada',
        'TR' => 'Turkey',
    ])->add()
    ->addText('postal_code', 'Postal Code')->required()->add()
    ->endSection();

// Step 4: Preferences
$form1->addSection('Preferences', 'Customize your experience')
    ->addCheckbox('newsletter', 'Newsletter')->options([
        'yes' => 'Subscribe to our newsletter'
    ])->add()
    ->addCheckbox('notifications', 'Notifications')->options([
        'email' => 'Email notifications',
        'sms' => 'SMS notifications',
        'push' => 'Push notifications',
    ])->add()
    ->addTextarea('bio', 'Tell us about yourself')->add()
    ->endSection();

echo $form1->build();

echo "</div></div>";

// =============================================================================
// Example 2: Non-Linear Wizard (Vertical Layout)
// =============================================================================
echo "<h2 class='mb-4'>Example 2: Non-Linear Project Setup (Vertical)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form2 = FormBuilder::create('project_wizard')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/projects/create')
    ->enableStepper([
        'layout' => StepperManager::LAYOUT_VERTICAL,
        'mode' => StepperManager::MODE_NON_LINEAR, // Allow jumping between steps
        'validateOnNext' => false, // Don't validate when navigating
    ]);

// Step 1: Basic Info
$form2->addSection('Basic Information', 'Project name and description')
    ->addText('project_name', 'Project Name')->required()->add()
    ->addTextarea('description', 'Description')->add()
    ->endSection();

// Step 2: Team
$form2->addSection('Team Setup', 'Add team members')
    ->addText('team_lead', 'Team Lead')->required()->add()
    ->addTextarea('team_members', 'Team Members (one per line)')->add()
    ->endSection();

// Step 3: Timeline
$form2->addSection('Timeline', 'Project dates')
    ->addDate('start_date', 'Start Date')->required()->add()
    ->addDate('end_date', 'End Date')->required()->add()
    ->addNumber('estimated_hours', 'Estimated Hours')->min(1)->add()
    ->endSection();

// Step 4: Budget
$form2->addSection('Budget', 'Financial planning')
    ->addNumber('budget', 'Total Budget ($)')->required()->min(0)->add()
    ->addSelect('currency', 'Currency')->options([
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'TRY' => 'Turkish Lira',
    ])->add()
    ->endSection();

echo $form2->build();

echo "</div></div>";

// =============================================================================
// Example 3: E-Commerce Checkout Wizard
// =============================================================================
echo "<h2 class='mb-4'>Example 3: E-Commerce Checkout Wizard</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form3 = FormBuilder::create('checkout_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/checkout')
    ->enableStepper([
        'layout' => StepperManager::LAYOUT_HORIZONTAL,
        'mode' => StepperManager::MODE_LINEAR,
        'startIndex' => 0,
    ]);

// Step 1: Shipping Information
$form3->addSection('Shipping Address', 'Where should we deliver your order?')
    ->setSectionHtml('<div class="alert alert-info"><i class="bi bi-truck"></i> Free shipping on orders over $50</div>')
    ->addText('ship_full_name', 'Full Name')->required()->add()
    ->addText('ship_address', 'Address')->required()->add()
    ->addText('ship_city', 'City')->required()->add()
    ->addText('ship_zip', 'ZIP Code')->required()->add()
    ->endSection();

// Step 2: Payment Method
$form3->addSection('Payment Information', 'How would you like to pay?')
    ->addRadio('payment_method', 'Payment Method')->required()->options([
        'credit_card' => 'Credit Card',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Bank Transfer',
    ])->add()
    ->addText('card_number', 'Card Number')->add()
    ->addText('card_expiry', 'Expiry (MM/YY)')->add()
    ->addText('card_cvv', 'CVV')->add()
    ->endSection();

// Step 3: Review & Confirm
$form3->addSection('Review Order', 'Please review your order before placing it')
    ->setSectionHtml('
        <div class="alert alert-light">
            <h5>Order Summary</h5>
            <hr>
            <div class="d-flex justify-content-between">
                <span>Subtotal:</span>
                <span>$99.99</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Shipping:</span>
                <span>$5.99</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Tax:</span>
                <span>$8.50</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total:</span>
                <span>$114.48</span>
            </div>
        </div>
    ')
    ->addCheckbox('terms', 'Terms')->required()->options([
        'agree' => 'I agree to the terms and conditions'
    ])->add()
    ->endSection();

echo $form3->build();

echo "</div></div>";

// =============================================================================
// Example 4: Custom Stepper with Events
// =============================================================================
echo "<h2 class='mb-4'>Example 4: Custom Stepper with JavaScript Events</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form4 = FormBuilder::create('custom_stepper')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save')
    ->enableStepper([
        'layout' => StepperManager::LAYOUT_HORIZONTAL,
        'mode' => StepperManager::MODE_LINEAR,
    ]);

$form4->addSection('Step 1', 'First step')
    ->addText('field1', 'Field 1')->required()->add()
    ->endSection();

$form4->addSection('Step 2', 'Second step')
    ->addText('field2', 'Field 2')->required()->add()
    ->endSection();

$form4->addSection('Step 3', 'Final step')
    ->addText('field3', 'Field 3')->required()->add()
    ->endSection();

echo $form4->build();

// Custom event handlers
echo <<<'JAVASCRIPT'
<script>
// Get stepper container
const stepper = document.querySelector('[data-stepper="custom_stepper"]');

if (stepper) {
    // Listen to stepper events
    stepper.addEventListener('stepper:init', (e) => {
        console.log('Stepper initialized with', e.detail.totalSteps, 'steps');
    });

    stepper.addEventListener('stepper:change', (e) => {
        console.log('Step changed from', e.detail.from, 'to', e.detail.to);

        // You can add custom logic here
        // For example, save progress to localStorage
        localStorage.setItem('currentStep', e.detail.to);
    });

    stepper.addEventListener('stepper:next', (e) => {
        console.log('Moving to next step:', e.detail.step);
    });

    stepper.addEventListener('stepper:previous', (e) => {
        console.log('Moving to previous step:', e.detail.step);
    });

    stepper.addEventListener('stepper:validation-failed', (e) => {
        console.log('Validation failed at step:', e.detail.step);
        alert('Please fill in all required fields before continuing.');
    });

    stepper.addEventListener('stepper:complete', (e) => {
        console.log('Wizard completed! Total steps:', e.detail.totalSteps);
        alert('Form submitted successfully!');
    });
}
</script>
JAVASCRIPT;

echo "</div></div>";

// =============================================================================
// Example 5: Horizontal Stepper with Progress Bar
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Stepper with Progress Indicator</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form5 = FormBuilder::create('progress_stepper')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->enableStepper([
        'layout' => StepperManager::LAYOUT_HORIZONTAL,
        'mode' => StepperManager::MODE_LINEAR,
    ]);

$form5->addSection('Getting Started', 'Welcome!')
    ->addText('name', 'Your Name')->required()->add()
    ->endSection();

$form5->addSection('Details', 'More info')
    ->addEmail('email', 'Email')->required()->add()
    ->endSection();

$form5->addSection('Finish', 'Almost done')
    ->addTextarea('comments', 'Comments')->add()
    ->endSection();

echo $form5->build();

// Add progress indicator
echo <<<'HTML'
<div class="mt-3">
    <div class="d-flex justify-content-between mb-2">
        <span id="progress-text">Step 1 of 3</span>
        <span id="progress-percent">0%</span>
    </div>
    <div class="progress">
        <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
    </div>
</div>

<script>
const progressStepper = window.Stepper_progress_stepper;
if (progressStepper) {
    const updateProgress = () => {
        const current = progressStepper.getCurrentStep() + 1;
        const total = progressStepper.getTotalSteps();
        const percent = progressStepper.getProgress();

        document.getElementById('progress-text').textContent = `Step ${current} of ${total}`;
        document.getElementById('progress-percent').textContent = `${percent}%`;
        document.getElementById('progress-bar').style.width = `${percent}%`;
    };

    document.querySelector('[data-stepper="progress_stepper"]').addEventListener('stepper:change', updateProgress);
    updateProgress(); // Initial update
}
</script>
HTML;

echo "</div></div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";
