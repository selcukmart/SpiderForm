# Query String Based Conditional Rendering

This document explains how to use the event-driven dependency system to conditionally render form fields based on URL query string parameters.

## Overview

The event-driven dependency system allows you to show/hide form fields based on query string parameters in the URL. This is useful for:

- **Feature Flags**: `?feature=beta` → Show beta features
- **Role-Based Forms**: `?role=admin` → Show admin controls
- **Pricing Plans**: `?plan=premium` → Show premium fields
- **A/B Testing**: `?variant=B` → Show variant B fields
- **Localization**: `?country=US` → Show country-specific fields
- **Debug Mode**: `?debug=true` → Show debug fields

## Implementation Methods

### Method 1: Hidden Field + dependsOn (Client-side + Server-side)

Add query parameter as a hidden field and use `dependsOn()`:

```php
$mode = $_GET['mode'] ?? '';

$form = FormBuilder::create('query_form')
    ->setRenderer($renderer)
    ->setTheme($theme);

// Add query string as hidden field
$form->addHidden('query_mode', $mode)
    ->isDependency('query_mode')
    ->add();

// This field shows when ?mode=advanced
$form->addText('advanced_settings', 'Advanced Settings')
    ->dependsOn('query_mode', 'advanced')
    ->add();

echo $form->build();
```

**URL**: `?mode=advanced`

### Method 2: Server-Side Evaluation (PHP Rendering)

Enable server-side dependency evaluation to prevent fields from being rendered in HTML:

```php
$role = $_GET['role'] ?? '';

$form = FormBuilder::create('query_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation() // Enable PHP-side evaluation
    ->setData(['role' => $role]);

$form->addHidden('role', $role)
    ->isDependency()
    ->add();

// This field is NOT rendered in HTML when ?role=admin is missing
$form->addTextarea('admin_notes', 'Admin Notes')
    ->dependsOn('role', 'admin')
    ->add();

echo $form->build();
```

**URL**: `?role=admin`

**Key Benefit**: Fields with unmet dependencies are completely excluded from HTML output (more secure).

### Method 3: onPreRender Event (Custom Logic)

Use events for flexible query string checking:

```php
$tier = $_GET['tier'] ?? '';

$form = FormBuilder::create('query_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation();

$form->addText('premium_feature', 'Premium Feature')
    ->onPreRender(function(FieldEvent $event) use ($tier) {
        $isPremium = ($tier === 'premium');

        if (!$isPremium) {
            $event->getField()->wrapperAttributes(['style' => 'display: none;']);
            $event->getField()->disabled(true);
        }
    })
    ->add();

echo $form->build();
```

**URL**: `?tier=premium`

### Method 4: Complex Conditions (Multiple Query Params)

Combine multiple query parameters with custom dependency logic:

```php
$country = $_GET['country'] ?? '';
$role = $_GET['role'] ?? '';

$form = FormBuilder::create('query_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation()
    ->setData([
        'country' => $country,
        'role' => $role,
    ]);

$form->addHidden('country', $country)->isDependency()->add();
$form->addHidden('role', $role)->isDependency()->add();

// Complex condition: BOTH country=US AND role=admin required
$form->addText('us_tax_settings', 'US Tax Settings')
    ->dependsOn('country', 'US')
    ->onDependencyCheck(function(FieldEvent $event) use ($country, $role) {
        // Must be BOTH US country AND admin role
        $visible = ($country === 'US' && $role === 'admin');
        $event->setVisible($visible);
    })
    ->add();

echo $form->build();
```

**URL**: `?country=US&role=admin`

### Method 5: Nested Dependencies

Query string triggers first level, then nested dependencies:

```php
$feature = $_GET['feature'] ?? '';

$form = FormBuilder::create('query_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setData(['feature' => $feature]);

$form->addHidden('feature', $feature)->isDependency()->add();

// Level 1: ?feature=new_ui
$form->addSelect('ui_theme', 'UI Theme')
    ->dependsOn('feature', 'new_ui')
    ->options(['dark' => 'Dark', 'light' => 'Light'])
    ->isDependency('ui_theme') // Controls next level
    ->add();

// Level 2: ui_theme = dark
$form->addCheckbox('dark_options', 'Dark Theme Options')
    ->dependsOn('ui_theme', 'dark')
    ->options(['high_contrast' => 'High Contrast'])
    ->add();

echo $form->build();
```

**URL**: `?feature=new_ui`

## Symfony Controller Example

```php
use Symfony\Component\HttpFoundation\Request;
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Event\FieldEvent;

#[Route('/form/query-based')]
public function queryBasedForm(Request $request): Response
{
    $mode = $request->query->get('mode', '');
    $role = $request->query->get('role', '');

    $form = FormBuilder::create('query_form')
        ->setRenderer($renderer)
        ->setTheme($theme)
        ->enableServerSideDependencyEvaluation()
        ->setData([
            'mode' => $mode,
            'role' => $role,
        ]);

    $form->addHidden('mode', $mode)->isDependency()->add();
    $form->addHidden('role', $role)->isDependency()->add();

    $form->addText('username', 'Username')->required()->add();

    // Only with ?mode=advanced
    $form->addText('advanced_settings', 'Advanced Settings')
        ->dependsOn('mode', 'advanced')
        ->add();

    // Only with ?role=admin
    $form->addTextarea('admin_notes', 'Admin Notes')
        ->dependsOn('role', 'admin')
        ->add();

    return $this->render('form.html.twig', [
        'form' => $form->build(),
    ]);
}
```

## Real-World Example: Dynamic Pricing Form

```php
$plan = $_GET['plan'] ?? 'basic';
$promoCode = $_GET['promo'] ?? '';

$form = FormBuilder::create('pricing_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->enableServerSideDependencyEvaluation()
    ->setData([
        'plan' => $plan,
        'promo' => $promoCode,
    ]);

$form->addHidden('plan', $plan)->isDependency()->add();
$form->addHidden('promo', $promoCode)->isDependency()->add();

$form->addText('company_name', 'Company Name')->required()->add();

// Basic plan (?plan=basic)
$form->addNumber('users_basic', 'Users (max 10)')
    ->dependsOn('plan', 'basic')
    ->min(1)->max(10)
    ->helpText('$10/month')
    ->add();

// Pro plan (?plan=pro)
$form->addNumber('users_pro', 'Users (max 100)')
    ->dependsOn('plan', 'pro')
    ->min(1)->max(100)
    ->helpText('$50/month')
    ->add();

// Enterprise (?plan=enterprise)
$form->addText('dedicated_manager', 'Account Manager')
    ->dependsOn('plan', 'enterprise')
    ->add();

// Promo code (?promo=SAVE20)
$form->addText('promo_display', 'Promo Code Active!')
    ->dependsOn('promo', 'SAVE20')
    ->value('20% discount applied!')
    ->readonly()
    ->add();

echo $form->build();
```

**URLs**:
- `?plan=basic` - Show basic plan fields
- `?plan=pro` - Show pro plan fields
- `?plan=enterprise` - Show enterprise fields
- `?plan=basic&promo=SAVE20` - Show basic + promo

## Use Cases

### 1. Feature Flags
```php
// ?feature=beta
$form->addText('beta_feature', 'Beta Feature')
    ->dependsOn('feature_flag', 'beta')
    ->add();
```

### 2. Role-Based Access
```php
// ?role=admin
$form->addTextarea('admin_panel', 'Admin Panel')
    ->dependsOn('user_role', 'admin')
    ->add();
```

### 3. A/B Testing
```php
// ?variant=B
$form->addText('variant_b_field', 'Variant B Feature')
    ->dependsOn('ab_variant', 'B')
    ->add();
```

### 4. Localization
```php
// ?country=US
$form->addText('state', 'State')
    ->dependsOn('country', 'US')
    ->add();
```

### 5. Debug Mode
```php
// ?debug=true
$form->addTextarea('debug_info', 'Debug Information')
    ->dependsOn('debug_mode', 'true')
    ->add();
```

## Best Practices

1. **Security**: Use `enableServerSideDependencyEvaluation()` for sensitive fields
2. **Validation**: Always validate and sanitize query string values
3. **Logging**: Use `onShow()` events to log when premium/admin features are accessed
4. **Testing**: Test with various query string combinations
5. **Documentation**: Document required query parameters for each form

## Available Events

- `onShow()` - Field becomes visible
- `onHide()` - Field becomes hidden
- `onPreRender()` - Before field renders
- `onDependencyCheck()` - Custom dependency logic
- `onDependencyMet()` - Dependency condition met
- `onDependencyNotMet()` - Dependency condition not met

## Examples

See complete working examples:
- `Examples/V2/WithQueryStringDependencies.php` - Standalone examples
- `Examples/Symfony/QueryStringFormController.php` - Symfony integration

## Summary

Query string based conditional rendering enables:
- ✅ Dynamic form fields based on URL parameters
- ✅ Both client-side (JavaScript) and server-side (PHP) control
- ✅ Security through server-side evaluation
- ✅ Complex multi-parameter conditions
- ✅ Nested dependency chains
- ✅ Event-driven architecture
