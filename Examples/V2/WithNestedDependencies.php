<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * Example: Nested Dependencies (A→B→C Chain) with Custom Animations
 * 
 * This example demonstrates:
 * 1. Multi-level dependency chains (A→B→C)
 * 2. Custom animation options (fade, slide, none)
 * 3. Different animation durations and easing
 */

// Example 1: Basic Nested Dependencies (A→B→C)
$form1 = FormBuilder::create('nested-dependencies-basic')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    // Level 1: Account Type (A)
    ->addSelect('account_type', 'Account Type')
    ->options([
        'personal' => 'Personal Account',
        'business' => 'Business Account',
        'enterprise' => 'Enterprise Account'
    ])
    ->required()
    ->isDependency()
    ->helpText('Select your account type to see relevant options')
    ->add()
    
    // Level 2: Business Details (B) - depends on account_type
    ->addText('company_name', 'Company Name')
    ->dependsOn('account_type', ['business', 'enterprise'])
    ->required()
    ->isDependency('company')
    ->placeholder('Enter company name')
    ->add()
    
    // Level 3: Business Type (C) - depends on company_name (Level 2)
    ->addSelect('company_size', 'Company Size')
    ->options([
        'small' => 'Small (1-50 employees)',
        'medium' => 'Medium (51-500 employees)',
        'large' => 'Large (500+ employees)'
    ])
    ->dependsOn('company_name', 'company')
    ->required()
    ->helpText('This appears when company name is filled')
    ->add()
    
    // Level 2: Enterprise Features (B2) - depends on account_type = enterprise
    ->addCheckbox('enterprise_support', 'Enable Enterprise Support')
    ->dependsOn('account_type', 'enterprise')
    ->isDependency('ent_support')
    ->add()
    
    // Level 3: Support Plan (C2) - depends on enterprise_support checkbox
    ->addRadio('support_plan', 'Support Plan')
    ->options([
        'basic' => 'Basic Support',
        'premium' => 'Premium Support',
        'dedicated' => 'Dedicated Support'
    ])
    ->dependsOn('enterprise_support', '1')
    ->add()
    
    ->addSubmit('Continue')
    ->build();

// Example 2: Complex Nested Chain with Custom Fade Animation (slower)
$form2 = FormBuilder::create('nested-fade-animation')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    ->setDependencyAnimation([
        'enabled' => true,
        'type' => 'fade',
        'duration' => 500,  // Slower fade (500ms)
        'easing' => 'ease-in-out'
    ])
    
    // Level 1
    ->addRadio('project_type', 'Project Type')
    ->options([
        'web' => 'Web Application',
        'mobile' => 'Mobile Application',
        'desktop' => 'Desktop Application'
    ])
    ->isDependency()
    ->add()
    
    // Level 2
    ->addSelect('web_framework', 'Web Framework')
    ->options([
        'laravel' => 'Laravel',
        'symfony' => 'Symfony',
        'custom' => 'Custom Framework'
    ])
    ->dependsOn('project_type', 'web')
    ->isDependency()
    ->add()
    
    // Level 3
    ->addText('custom_framework_name', 'Custom Framework Name')
    ->dependsOn('web_framework', 'custom')
    ->placeholder('e.g., My Custom Framework')
    ->add()
    
    ->addSubmit('Submit')
    ->build();

// Example 3: Slide Animation
$form3 = FormBuilder::create('nested-slide-animation')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    ->setDependencyAnimation([
        'enabled' => true,
        'type' => 'slide',      // Slide animation
        'duration' => 400,
        'easing' => 'ease-out'
    ])
    
    // Level 1
    ->addCheckbox('has_shipping_address', 'Different shipping address?')
    ->isDependency()
    ->add()
    
    // Level 2
    ->addText('shipping_street', 'Shipping Street')
    ->dependsOn('has_shipping_address', '1')
    ->required()
    ->isDependency('shipping')
    ->add()
    
    // Level 3
    ->addCheckbox('require_signature', 'Require signature on delivery?')
    ->dependsOn('shipping_street', 'shipping')
    ->add()
    
    ->addSubmit('Place Order')
    ->build();

// Example 4: No Animation (instant show/hide)
$form4 = FormBuilder::create('nested-no-animation')
    ->setAction('/submit')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    ->disableDependencyAnimation()  // No animations
    
    // Level 1
    ->addSelect('delivery_method', 'Delivery Method')
    ->options([
        'pickup' => 'Store Pickup',
        'standard' => 'Standard Shipping',
        'express' => 'Express Shipping'
    ])
    ->isDependency()
    ->add()
    
    // Level 2
    ->addText('pickup_location', 'Pickup Store')
    ->dependsOn('delivery_method', 'pickup')
    ->add()
    
    ->addDate('delivery_date', 'Delivery Date')
    ->dependsOn('delivery_method', ['standard', 'express'])
    ->isDependency()
    ->add()
    
    // Level 3
    ->addTime('delivery_time', 'Preferred Time')
    ->dependsOn('delivery_date', 'delivery_date')
    ->add()
    
    ->addSubmit('Confirm')
    ->build();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nested Dependencies & Custom Animations</title>
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
        .description {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4">Nested Dependencies & Animations</h1>
            <p class="lead text-muted">Demonstrating A→B→C dependency chains with customizable animations</p>
        </div>

        <!-- Example 1 -->
        <div class="example-section">
            <h2 class="example-title">Example 1: Basic Nested Dependencies (A→B→C)</h2>
            <div class="feature-badge">Multi-Level Chain</div>
            <div class="feature-badge">Default Fade Animation</div>
            <div class="feature-badge">3 Dependency Levels</div>
            
            <div class="description">
                <strong>How it works:</strong>
                <ul class="mb-0">
                    <li>Select <strong>Business</strong> or <strong>Enterprise</strong> → Shows Company Name field (Level 2)</li>
                    <li>Start typing in Company Name → Shows Company Size field (Level 3)</li>
                    <li>Select <strong>Enterprise</strong> → Shows Enterprise Support checkbox (Level 2)</li>
                    <li>Check Enterprise Support → Shows Support Plan options (Level 3)</li>
                </ul>
            </div>
            
            <?= $form1 ?>
        </div>

        <!-- Example 2 -->
        <div class="example-section">
            <h2 class="example-title">Example 2: Custom Fade Animation (500ms)</h2>
            <div class="feature-badge">Slow Fade</div>
            <div class="feature-badge">500ms Duration</div>
            <div class="feature-badge">Ease-In-Out</div>
            
            <div class="description">
                <strong>Animation Config:</strong> Fade effect with 500ms duration (slower than default)
                <br><strong>Try:</strong> Select <strong>Web Application</strong> → Choose <strong>Custom Framework</strong> → See custom field appear
            </div>
            
            <?= $form2 ?>
        </div>

        <!-- Example 3 -->
        <div class="example-section">
            <h2 class="example-title">Example 3: Slide Animation (400ms)</h2>
            <div class="feature-badge">Slide Effect</div>
            <div class="feature-badge">Height Animation</div>
            <div class="feature-badge">400ms Duration</div>
            
            <div class="description">
                <strong>Animation Config:</strong> Slide effect with expanding height
                <br><strong>Try:</strong> Check "Different shipping address?" → Fill shipping street → See signature option slide in
            </div>
            
            <?= $form3 ?>
        </div>

        <!-- Example 4 -->
        <div class="example-section">
            <h2 class="example-title">Example 4: No Animation (Instant)</h2>
            <div class="feature-badge">No Animation</div>
            <div class="feature-badge">Instant Show/Hide</div>
            <div class="feature-badge">Better Performance</div>
            
            <div class="description">
                <strong>Animation Config:</strong> Disabled for instant show/hide (best for performance)
                <br><strong>Try:</strong> Change delivery method → Select a date → See time picker appear instantly
            </div>
            
            <?= $form4 ?>
        </div>

        <div class="alert alert-info">
            <h5>Animation Options Reference</h5>
            <pre class="mb-0"><code>$form->setDependencyAnimation([
    'enabled' => true,           // Enable/disable animations
    'type' => 'fade',            // 'fade', 'slide', or 'none'
    'duration' => 300,           // Animation duration in milliseconds
    'easing' => 'ease-in-out'    // CSS easing function
]);

// Or simply disable all animations:
$form->disableDependencyAnimation();</code></pre>
        </div>
    </div>
</body>
</html>
