<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Event\FieldEvent;

/**
 * Query String Based Conditional Rendering
 *
 * Demonstrates how to conditionally render form fields based on
 * URL query string parameters using the event-driven dependency system.
 *
 * Examples:
 * - ?mode=advanced         -> Show advanced settings
 * - ?role=admin            -> Show admin panel
 * - ?tier=premium          -> Show premium features
 * - ?country=US&role=admin -> Show US tax settings (complex condition)
 *
 * @author selcukmart
 * @since 2.3.0
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

// Get query string parameters
$mode = $_GET['mode'] ?? '';
$role = $_GET['role'] ?? '';
$tier = $_GET['tier'] ?? '';
$country = $_GET['country'] ?? '';
$feature = $_GET['feature'] ?? '';

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

echo "<h1 class='mb-4'>Query String Based Conditional Rendering</h1>";
echo "<p class='lead'>Conditionally render form fields based on URL query parameters</p>";

// Show current query params
echo "<div class='alert alert-info mb-4'>";
echo "<strong>Current Query Parameters:</strong><br>";
echo "mode = " . ($mode ?: '<em>not set</em>') . "<br>";
echo "role = " . ($role ?: '<em>not set</em>') . "<br>";
echo "tier = " . ($tier ?: '<em>not set</em>') . "<br>";
echo "country = " . ($country ?: '<em>not set</em>') . "<br>";
echo "feature = " . ($feature ?: '<em>not set</em>') . "<br>";
echo "</div>";

// Show test links
echo "<div class='card mb-4'>";
echo "<div class='card-header'><strong>Try These URLs:</strong></div>";
echo "<div class='card-body'>";
echo "<ul class='mb-0'>";
echo "<li><a href='?mode=advanced'>?mode=advanced</a> - Show advanced settings</li>";
echo "<li><a href='?role=admin'>?role=admin</a> - Show admin panel</li>";
echo "<li><a href='?tier=premium'>?tier=premium</a> - Show premium features</li>";
echo "<li><a href='?country=US&role=admin'>?country=US&role=admin</a> - Show US tax settings (complex condition)</li>";
echo "<li><a href='?feature=new_ui'>?feature=new_ui</a> - Show new UI options</li>";
echo "<li><a href='?'>Clear all parameters</a></li>";
echo "</ul>";
echo "</div>";
echo "</div>";

// =============================================================================
// Example 1: Basic Query String Dependency
// =============================================================================
echo "<h2 class='mb-4'>Example 1: Basic Query String Dependency</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Hidden field + dependsOn approach:</p>";

$form1 = FormBuilder::create('query_form1')
    ->setRenderer($renderer)
    ->setTheme($theme);

// Add query string as hidden field and mark as dependency controller
$form1->addHidden('query_mode', $mode)
    ->isDependency('query_mode')
    ->add();

$form1->addText('username', 'Username')
    ->required()
    ->add();

// This field only shows when ?mode=advanced
$form1->addText('advanced_settings', 'Advanced Settings')
    ->dependsOn('query_mode', 'advanced')
    ->placeholder('Only visible with ?mode=advanced')
    ->helpText('Try adding ?mode=advanced to URL')
    ->add();

echo $form1->build();
echo "</div></div>";

// =============================================================================
// Example 2: Server-Side Evaluation
// =============================================================================
echo "<h2 class='mb-4'>Example 2: Server-Side Evaluation (PHP Rendering)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Fields with unmet dependencies are NOT rendered in HTML:</p>";

$form2 = FormBuilder::create('query_form2')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation() // Enable PHP-side evaluation
    ->setData([
        'role' => $role,
    ]);

$form2->addHidden('role', $role)
    ->isDependency()
    ->add();

$form2->addEmail('email', 'Email')
    ->required()
    ->add();

// This field is only rendered in HTML when ?role=admin exists
$form2->addTextarea('admin_notes', 'Admin Notes')
    ->dependsOn('role', 'admin')
    ->rows(3)
    ->helpText('This field is only in HTML when ?role=admin')
    ->add();

if ($role === 'admin') {
    echo "<div class='alert alert-success'>✓ Admin field IS rendered in HTML (check page source)</div>";
} else {
    echo "<div class='alert alert-warning'>✗ Admin field is NOT rendered in HTML (check page source)</div>";
}

echo $form2->build();
echo "</div></div>";

// =============================================================================
// Example 3: Custom Logic with onPreRender
// =============================================================================
echo "<h2 class='mb-4'>Example 3: Custom Logic with onPreRender Event</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Use events for flexible query string checking:</p>";

$form3 = FormBuilder::create('query_form3')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation();

$form3->addText('product_name', 'Product Name')
    ->required()
    ->add();

$form3->addText('premium_feature', 'Premium Feature')
    ->onPreRender(function(FieldEvent $event) use ($tier) {
        // Custom query string check
        $isPremium = ($tier === 'premium');

        if (!$isPremium) {
            // Don't render if not premium
            $event->getField()->wrapperAttributes(['style' => 'display: none;']);
            $event->getField()->disabled(true);
        } else {
            $event->getField()->helpText('✓ Premium tier detected!');
        }
    })
    ->placeholder('Premium users only')
    ->add();

if ($tier === 'premium') {
    echo "<div class='alert alert-success'>✓ Premium tier active - field visible</div>";
} else {
    echo "<div class='alert alert-warning'>Try <a href='?tier=premium'>?tier=premium</a> to see premium field</div>";
}

echo $form3->build();
echo "</div></div>";

// =============================================================================
// Example 4: Complex Conditions (Multiple Query Params)
// =============================================================================
echo "<h2 class='mb-4'>Example 4: Complex Conditions</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Combine multiple query parameters with custom logic:</p>";

$form4 = FormBuilder::create('query_form4')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation()
    ->setData([
        'country' => $country,
        'role' => $role,
    ]);

$form4->addHidden('country', $country)
    ->isDependency()
    ->add();

$form4->addHidden('role', $role)
    ->isDependency()
    ->add();

$form4->addText('company_name', 'Company Name')
    ->required()
    ->add();

// Complex condition: BOTH country=US AND role=admin required
$form4->addText('us_tax_settings', 'US Tax Settings')
    ->dependsOn('country', 'US')
    ->onDependencyCheck(function(FieldEvent $event) use ($country, $role) {
        // Must be BOTH US country AND admin role
        $visible = ($country === 'US' && $role === 'admin');
        $event->setVisible($visible);

        if ($visible) {
            $event->getField()->required(true);
        }
    })
    ->placeholder('US Admin only')
    ->add();

if ($country === 'US' && $role === 'admin') {
    echo "<div class='alert alert-success'>✓ Both conditions met: country=US AND role=admin</div>";
} else {
    echo "<div class='alert alert-warning'>Try <a href='?country=US&role=admin'>?country=US&role=admin</a> to see tax settings</div>";
}

echo $form4->build();
echo "</div></div>";

// =============================================================================
// Example 5: Nested Dependencies with Query Strings
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Nested Dependencies</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Query string triggers first level, then nested dependencies:</p>";

$form5 = FormBuilder::create('query_form5')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setData([
        'feature' => $feature,
    ]);

$form5->addHidden('feature', $feature)
    ->isDependency()
    ->add();

$form5->addText('email', 'Email')
    ->required()
    ->email()
    ->add();

// Level 1: ?feature=new_ui
$form5->addSelect('ui_theme', 'UI Theme')
    ->dependsOn('feature', 'new_ui')
    ->options([
        '' => '-- Select Theme --',
        'dark' => 'Dark Theme',
        'light' => 'Light Theme',
    ])
    ->isDependency('ui_theme') // Controls next level
    ->helpText('Available with ?feature=new_ui')
    ->add();

// Level 2: ui_theme = dark
$form5->addCheckbox('dark_options', 'Dark Theme Options')
    ->dependsOn('ui_theme', 'dark')
    ->options([
        'high_contrast' => 'High Contrast',
        'reduce_blue' => 'Reduce Blue Light',
    ])
    ->onShow(function(FieldEvent $event) {
        echo "<div class='alert alert-info'>Event: Dark theme options shown!</div>";
    })
    ->add();

if ($feature === 'new_ui') {
    echo "<div class='alert alert-success'>✓ Feature flag active - UI theme selector visible</div>";
} else {
    echo "<div class='alert alert-warning'>Try <a href='?feature=new_ui'>?feature=new_ui</a> to see UI options</div>";
}

echo $form5->build();
echo "</div></div>";

// =============================================================================
// Example 6: Real-World Use Case - Dynamic Pricing Form
// =============================================================================
echo "<h2 class='mb-4'>Example 6: Real-World - Dynamic Pricing Form</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Show different fields based on pricing plan:</p>";

$plan = $_GET['plan'] ?? 'basic';
$promoCode = $_GET['promo'] ?? '';

echo "<div class='btn-group mb-3' role='group'>";
echo "<a href='?plan=basic' class='btn btn-sm btn-outline-primary'>Basic Plan</a>";
echo "<a href='?plan=pro' class='btn btn-sm btn-outline-primary'>Pro Plan</a>";
echo "<a href='?plan=enterprise' class='btn btn-sm btn-outline-primary'>Enterprise</a>";
echo "<a href='?plan={$plan}&promo=SAVE20' class='btn btn-sm btn-outline-success'>+ Promo Code</a>";
echo "</div>";

$form6 = FormBuilder::create('pricing_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation()
    ->setData([
        'plan' => $plan,
        'promo' => $promoCode,
    ]);

$form6->addHidden('plan', $plan)
    ->isDependency()
    ->add();

$form6->addHidden('promo', $promoCode)
    ->isDependency()
    ->add();

$form6->addText('company_name', 'Company Name')
    ->required()
    ->add();

// Basic plan
$form6->addNumber('users_basic', 'Users (max 10)')
    ->dependsOn('plan', 'basic')
    ->min(1)
    ->max(10)
    ->value(5)
    ->helpText('Basic plan: $10/month')
    ->add();

// Pro plan
$form6->addNumber('users_pro', 'Users (max 100)')
    ->dependsOn('plan', 'pro')
    ->min(1)
    ->max(100)
    ->value(25)
    ->helpText('Pro plan: $50/month')
    ->add();

// Enterprise plan
$form6->addText('dedicated_manager', 'Account Manager')
    ->dependsOn('plan', 'enterprise')
    ->placeholder('Your dedicated manager')
    ->helpText('Enterprise: Custom pricing')
    ->add();

// Promo code (any plan)
$form6->addText('promo_display', 'Promo Code Active!')
    ->dependsOn('promo', 'SAVE20')
    ->value('SAVE20 - 20% discount applied!')
    ->readonly()
    ->addClass('text-success fw-bold')
    ->onShow(function(FieldEvent $event) {
        echo "<div class='alert alert-success'>🎉 Promo code applied: 20% discount!</div>";
    })
    ->add();

$form6->addSubmit('subscribe', 'Subscribe Now');

echo $form6->build();
echo "</div></div>";

// =============================================================================
// Summary
// =============================================================================
echo "<div class='alert alert-success'>";
echo "<h4>Summary: Query String Dependencies</h4>";
echo "<p><strong>Use Cases:</strong></p>";
echo "<ul>";
echo "<li><strong>Feature Flags</strong>: ?feature=beta → Show beta features</li>";
echo "<li><strong>Role-Based Forms</strong>: ?role=admin → Show admin controls</li>";
echo "<li><strong>Pricing Plans</strong>: ?plan=premium → Show premium fields</li>";
echo "<li><strong>Localization</strong>: ?country=US → Show US-specific fields</li>";
echo "<li><strong>A/B Testing</strong>: ?variant=B → Show variant B fields</li>";
echo "<li><strong>Debug Mode</strong>: ?debug=true → Show debug fields</li>";
echo "</ul>";

echo "<p class='mt-3'><strong>Implementation Methods:</strong></p>";
echo "<ol>";
echo "<li><strong>Hidden Field + dependsOn</strong>: Simple and works with JS</li>";
echo "<li><strong>Server-Side Evaluation</strong>: Fields not in HTML if condition not met</li>";
echo "<li><strong>onPreRender Event</strong>: Custom logic before rendering</li>";
echo "<li><strong>onDependencyCheck Event</strong>: Complex multi-parameter conditions</li>";
echo "</ol>";

echo "<p class='mt-3'><strong>Best Practices:</strong></p>";
echo "<ul>";
echo "<li>Use <code>enableServerSideDependencyEvaluation()</code> for security-sensitive fields</li>";
echo "<li>Combine multiple query params with <code>onDependencyCheck()</code></li>";
echo "<li>Use events to log when premium/admin features are accessed</li>";
echo "<li>Validate and sanitize query string values</li>";
echo "</ul>";
echo "</div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";
