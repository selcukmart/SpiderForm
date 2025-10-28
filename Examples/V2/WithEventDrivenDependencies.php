<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Event\{FieldEvent, FieldEvents};

/**
 * FormGenerator V2 - Event-Driven Dependency System
 *
 * Demonstrates the native event-driven architecture for field dependencies
 * that works on both PHP (server-side) and JavaScript (client-side).
 *
 * Features:
 * - Field-level events (onShow, onHide, onValueChange, etc.)
 * - Server-side dependency evaluation
 * - PHP conditional rendering
 * - JavaScript real-time dependency handling
 * - Custom event logic
 *
 * @author selcukmart
 * @since 2.3.0
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

echo "<h1 class='mb-4'>Event-Driven Dependency System</h1>";
echo "<p class='lead'>Native event-driven architecture for both PHP and JavaScript</p>";

// =============================================================================
// Example 1: Basic Field Events with Dependencies
// =============================================================================
echo "<h2 class='mb-4'>Example 1: Field Events with Dependencies</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Field events are triggered when dependencies are met/unmet:</p>";

$form1 = FormBuilder::create('event_dependency_form')
    ->setRenderer($renderer)
    ->setTheme($theme);

$form1->addSelect('account_type', 'Account Type')
    ->options([
        '' => '-- Select Type --',
        'personal' => 'Personal',
        'business' => 'Business',
    ])
    ->isDependency() // Mark as dependency controller
    ->add();

$form1->addText('company_name', 'Company Name')
    ->dependsOn('account_type', 'business')
    ->onShow(function(FieldEvent $event) {
        // Called when field becomes visible
        echo "<div class='alert alert-info'>Event: company_name shown!</div>";
        // Make field required when shown
        $event->getField()->required(true);
    })
    ->onHide(function(FieldEvent $event) {
        // Called when field becomes hidden
        echo "<div class='alert alert-warning'>Event: company_name hidden!</div>";
        // Make field optional when hidden
        $event->getField()->required(false);
    })
    ->add();

echo $form1->build();
echo "</div></div>";

// =============================================================================
// Example 2: Server-Side Dependency Evaluation
// =============================================================================
echo "<h2 class='mb-4'>Example 2: Server-Side Dependency Evaluation</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>PHP-side conditional rendering based on form data:</p>";

$form2 = FormBuilder::create('server_side_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation() // Enable PHP-side evaluation
    ->setData([
        'user_type' => 'business', // Pre-set value
        'company_name' => 'Acme Corp'
    ]);

$form2->addSelect('user_type', 'User Type')
    ->options([
        'personal' => 'Personal',
        'business' => 'Business',
    ])
    ->isDependency()
    ->add();

// This field will be rendered in HTML because dependency is met
$form2->addText('company_name', 'Company Name')
    ->dependsOn('user_type', 'business')
    ->add();

// This field will NOT be rendered in HTML because dependency is not met
$form2->addText('personal_id', 'Personal ID')
    ->dependsOn('user_type', 'personal')
    ->add();

echo "<div class='alert alert-success'>";
echo "✓ Server-side evaluation enabled: Only fields with met dependencies are rendered in HTML";
echo "</div>";

echo $form2->build();
echo "</div></div>";

// =============================================================================
// Example 3: Custom Dependency Logic with Events
// =============================================================================
echo "<h2 class='mb-4'>Example 3: Custom Dependency Logic</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Use events to implement custom dependency logic:</p>";

$form3 = FormBuilder::create('custom_logic_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setData([
        'country' => 'US',
        'user_role' => 'admin'
    ]);

$form3->addSelect('country', 'Country')
    ->options([
        'US' => 'United States',
        'UK' => 'United Kingdom',
        'DE' => 'Germany',
    ])
    ->isDependency()
    ->add();

$form3->addHidden('user_role', 'admin')
    ->add();

$form3->addText('state', 'State')
    ->dependsOn('country', 'US')
    ->onDependencyCheck(function(FieldEvent $event) {
        // Custom logic: Only show for US country AND admin role
        $country = $event->getFieldValue('country');
        $role = $event->getFieldValue('user_role');

        $visible = ($country === 'US' && $role === 'admin');

        echo "<div class='alert alert-info'>";
        echo "Custom dependency check: country={$country}, role={$role} → visible=" . ($visible ? 'true' : 'false');
        echo "</div>";

        $event->setVisible($visible);
    })
    ->add();

echo $form3->build();
echo "</div></div>";

// =============================================================================
// Example 4: Field Value Change Events
// =============================================================================
echo "<h2 class='mb-4'>Example 4: Field Value Change Events</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Trigger cascading changes when field values change:</p>";

$form4 = FormBuilder::create('value_change_form')
    ->setRenderer($renderer)
    ->setTheme($theme);

$form4->addSelect('product_category', 'Product Category')
    ->options([
        '' => '-- Select Category --',
        'electronics' => 'Electronics',
        'clothing' => 'Clothing',
        'books' => 'Books',
    ])
    ->isDependency()
    ->onValueChange(function(FieldEvent $event) {
        // This would be triggered programmatically
        echo "<div class='alert alert-info'>Category changed to: " . $event->getValue() . "</div>";
    })
    ->add();

$form4->addSelect('product_subcategory', 'Subcategory')
    ->dependsOn('product_category', 'electronics')
    ->options([
        'phones' => 'Phones',
        'laptops' => 'Laptops',
        'tablets' => 'Tablets',
    ])
    ->onShow(function(FieldEvent $event) {
        echo "<div class='alert alert-success'>Subcategory field shown for electronics</div>";
    })
    ->add();

echo $form4->build();

// Demonstrate programmatic value change
echo "<div class='mt-3'>";
echo "<h5>Programmatic Value Change:</h5>";
echo "<p>Trigger field value change programmatically (PHP side):</p>";
echo "<code>\$form->triggerFieldValueChange('product_category', 'electronics', null);</code>";
echo "</div>";

echo "</div></div>";

// =============================================================================
// Example 5: Multiple Field Events
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Multiple Field Events</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Attach multiple event listeners to a single field:</p>";

$form5 = FormBuilder::create('multiple_events_form')
    ->setRenderer($renderer)
    ->setTheme($theme);

$form5->addSelect('subscription_plan', 'Subscription Plan')
    ->options([
        '' => '-- Select Plan --',
        'free' => 'Free',
        'premium' => 'Premium',
        'enterprise' => 'Enterprise',
    ])
    ->isDependency()
    ->add();

$form5->addText('enterprise_code', 'Enterprise Code')
    ->dependsOn('subscription_plan', 'enterprise')
    ->onPreRender(function(FieldEvent $event) {
        echo "<div class='badge bg-primary'>Event: PRE_RENDER</div> ";
    })
    ->onShow(function(FieldEvent $event) {
        echo "<div class='badge bg-success'>Event: SHOW</div> ";
        $event->getField()->required(true)->placeholder('Enter enterprise code');
    })
    ->onHide(function(FieldEvent $event) {
        echo "<div class='badge bg-warning'>Event: HIDE</div> ";
        $event->getField()->required(false);
    })
    ->onDependencyMet(function(FieldEvent $event) {
        echo "<div class='badge bg-info'>Event: DEPENDENCY_MET</div> ";
    })
    ->onPostRender(function(FieldEvent $event) {
        echo "<div class='badge bg-secondary'>Event: POST_RENDER</div> ";
    })
    ->add();

echo $form5->build();
echo "</div></div>";

// =============================================================================
// Example 6: Complex Nested Dependencies
// =============================================================================
echo "<h2 class='mb-4'>Example 6: Complex Nested Dependencies with Events</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Event-driven nested dependency chains (A→B→C):</p>";

$form6 = FormBuilder::create('nested_deps_form')
    ->setRenderer($renderer)
    ->setTheme($theme);

$form6->addSelect('service_type', 'Service Type')
    ->options([
        '' => '-- Select Service --',
        'consulting' => 'Consulting',
        'development' => 'Development',
        'support' => 'Support',
    ])
    ->isDependency()
    ->add();

$form6->addSelect('development_type', 'Development Type')
    ->dependsOn('service_type', 'development')
    ->options([
        '' => '-- Select Type --',
        'web' => 'Web Development',
        'mobile' => 'Mobile Development',
        'desktop' => 'Desktop Development',
    ])
    ->isDependency('development_type') // This becomes a controller for next level
    ->onShow(function(FieldEvent $event) {
        echo "<div class='alert alert-success'>Level 2: Development Type shown</div>";
    })
    ->add();

$form6->addCheckbox('web_technologies', 'Web Technologies')
    ->dependsOn('development_type', 'web')
    ->options([
        'react' => 'React',
        'vue' => 'Vue.js',
        'angular' => 'Angular',
    ])
    ->onShow(function(FieldEvent $event) {
        echo "<div class='alert alert-info'>Level 3: Web Technologies shown (nested dependency)</div>";
    })
    ->add();

echo $form6->build();
echo "</div></div>";

// =============================================================================
// Summary
// =============================================================================
echo "<div class='alert alert-success'>";
echo "<h4>Summary: Event-Driven Dependency System</h4>";
echo "<p><strong>Key Features:</strong></p>";
echo "<ul>";
echo "<li><strong>Field-Level Events</strong>: onShow(), onHide(), onValueChange(), etc.</li>";
echo "<li><strong>Server-Side Evaluation</strong>: PHP conditional rendering with enableServerSideDependencyEvaluation()</li>";
echo "<li><strong>Client-Side Events</strong>: Real-time JavaScript dependency handling</li>";
echo "<li><strong>Custom Logic</strong>: onDependencyCheck() for complex conditions</li>";
echo "<li><strong>Event Chaining</strong>: Multiple events on single field</li>";
echo "<li><strong>Nested Dependencies</strong>: Full support for A→B→C chains</li>";
echo "<li><strong>Native & Built-in</strong>: No framework dependencies</li>";
echo "</ul>";

echo "<p class='mt-3'><strong>Available Field Events:</strong></p>";
echo "<ul>";
echo "<li><code>FIELD_VALUE_CHANGE</code> - Field value changes</li>";
echo "<li><code>FIELD_SHOW</code> - Field becomes visible</li>";
echo "<li><code>FIELD_HIDE</code> - Field becomes hidden</li>";
echo "<li><code>FIELD_ENABLE</code> - Field is enabled</li>";
echo "<li><code>FIELD_DISABLE</code> - Field is disabled</li>";
echo "<li><code>FIELD_PRE_RENDER</code> - Before field renders</li>";
echo "<li><code>FIELD_POST_RENDER</code> - After field renders</li>";
echo "<li><code>FIELD_DEPENDENCY_CHECK</code> - Dependency evaluation</li>";
echo "<li><code>FIELD_DEPENDENCY_MET</code> - Dependency condition met</li>";
echo "<li><code>FIELD_DEPENDENCY_NOT_MET</code> - Dependency condition not met</li>";
echo "</ul>";

echo "<p class='mt-3'><strong>Usage Example:</strong></p>";
echo "<pre><code class='language-php'>";
echo htmlspecialchars('$form->addText(\'company_name\', \'Company Name\')
    ->dependsOn(\'user_type\', \'business\')
    ->onShow(function(FieldEvent $event) {
        $event->getField()->required(true);
    })
    ->onHide(function(FieldEvent $event) {
        $event->getField()->required(false);
    })
    ->add();');
echo "</code></pre>";
echo "</div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";
