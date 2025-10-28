# Upgrade Guide from V1 to V2

## Overview

FormGenerator V2 is a complete rewrite with modern PHP 8.1+ features, chain pattern fluent interface, and better framework integration. This guide will help you migrate from V1 to V2.

## Breaking Changes

### 1. PHP Version Requirement
- **V1**: PHP 5.2+
- **V2**: PHP 8.1+

### 2. Array Configuration → Chain Pattern

**V1 (Array-based):**
```php
$form_generator_array = [
    'data' => [...],
    'build' => [...],
    'inputs' => [
        [
            'type' => 'text',
            'attributes' => ['name' => 'username'],
        ],
    ],
];
$form = new FormGeneratorDirector($form_generator_array, 'edit');
$form->buildHtmlOutput();
echo $form->getHtmlOutput();
```

**V2 (Chain Pattern):**
```php
use FormGenerator\V2\Builder\FormBuilder;

$form = FormBuilder::create('user_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->addText('username', 'Username')
        ->required()
        ->add()
    ->addSubmit('save')
    ->build();

echo $form;
```

### 3. Namespace Changes

**V1:**
```php
use FormGenerator\FormGeneratorDirector;
```

**V2:**
```php
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
```

### 4. Data Provider Interface

**V1:**
```php
'data' => [
    'from' => 'row',
    'row' => $row,
]
```

**V2:**
```php
use FormGenerator\V2\DataProvider\ArrayDataProvider;

$dataProvider = new ArrayDataProvider([$row]);
$form->setDataProvider($dataProvider)->loadData(1);
```

### 5. Renderer Configuration

**V1:**
```php
'render' => [
    'by' => 'smarty',
    'smarty' => $smarty,
],
```

**V2:**
```php
use FormGenerator\V2\Renderer\SmartyRenderer;

$renderer = new SmartyRenderer($smarty);
$form->setRenderer($renderer);
```

## Migration Steps

### Step 1: Update Dependencies

Update your `composer.json`:

```bash
composer require selcukmart/form-generator:^2.0
```

### Step 2: Update PHP Version

Ensure your project runs on PHP 8.1 or higher:

```json
{
    "require": {
        "php": ">=8.1"
    }
}
```

### Step 3: Refactor Form Definitions

Convert array-based forms to chain pattern:

**Before (V1):**
```php
$inputs = [
    'inputs' => [
        [
            'type' => 'text',
            'attributes' => ['name' => 'email'],
            'help_block' => 'Enter your email',
        ],
        [
            'type' => 'password',
            'attributes' => ['name' => 'password'],
        ],
    ],
];
```

**After (V2):**
```php
$form->addEmail('email', 'Email')
        ->helpText('Enter your email')
        ->required()
        ->add()
    ->addPassword('password', 'Password')
        ->required()
        ->add();
```

### Step 4: Update Data Providers

Choose appropriate data provider:

**Doctrine:**
```php
use FormGenerator\V2\DataProvider\DoctrineDataProvider;

$provider = new DoctrineDataProvider($entityManager, User::class);
$form->setDataProvider($provider)->loadData($userId);
```

**Laravel Eloquent:**
```php
use FormGenerator\V2\DataProvider\EloquentDataProvider;

$provider = new EloquentDataProvider(User::class);
$form->setDataProvider($provider)->loadData($userId);
```

**PDO:**
```php
use FormGenerator\V2\DataProvider\PDODataProvider;

$provider = new PDODataProvider($pdo, 'users', 'id');
$form->setDataProvider($provider)->loadData($userId);
```

### Step 5: Update Templates

V2 uses Twig by default with Bootstrap 5 theme:

```php
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;

$renderer = new TwigRenderer(__DIR__ . '/templates');
$theme = new Bootstrap5Theme();

$form->setRenderer($renderer)->setTheme($theme);
```

### Step 6: Enable Security Features

V2 includes built-in CSRF protection:

```php
use FormGenerator\V2\Security\SecurityManager;

$security = new SecurityManager();
$form->setSecurity($security)->enableCsrf();
```

### Step 7: Framework Integration

**Symfony:**
```php
// In bundles.php
FormGenerator\V2\Integration\Symfony\FormGeneratorBundle::class => ['all' => true],

// In config/packages/form_generator.yaml
form_generator:
    default_theme: 'bootstrap5'
    default_renderer: 'twig'
```

**Laravel:**
```php
// In config/app.php 'providers'
FormGenerator\V2\Integration\Laravel\FormGeneratorServiceProvider::class,

// Publish config
php artisan vendor:publish --tag=form-generator-config
```

## Backward Compatibility

V1 code is still available under `FormGenerator\V1` namespace:

```php
// V1 still works
use FormGenerator\V1\FormGeneratorDirector;

$form = new FormGeneratorDirector($array, 'edit');
```

However, we recommend migrating to V2 for better performance and features.

## New Features in V2

### 1. Type Safety
- Enums for input types and scopes
- Typed properties and parameters
- PHP 8.1+ features

### 2. Fluent Interface
- Chain pattern for intuitive form building
- Method chaining for all operations
- IDE auto-completion support

### 3. Modern Data Providers
- Doctrine ORM integration
- Laravel Eloquent support
- PDO with SQL injection protection
- Array data provider

### 4. Enhanced Security
- Built-in CSRF protection
- XSS prevention with auto-escaping
- Input sanitization
- File upload validation

### 5. Theme System
- Bootstrap 5 theme included
- Easy theme customization
- Runtime theme switching
- Asset management

### 6. Framework Integration
- Symfony Bundle
- Laravel ServiceProvider
- Standalone usage support

## Getting Help

- **Documentation**: Check `/docs` directory
- **Examples**: See `/Examples/V2` directory
- **Issues**: https://github.com/selcukmart/FormGenerator/issues

## Checklist

- [ ] Updated PHP to 8.1+
- [ ] Updated composer dependencies
- [ ] Converted array configs to chain pattern
- [ ] Updated data providers
- [ ] Configured renderer and theme
- [ ] Enabled security features
- [ ] Tested forms in development
- [ ] Updated tests
- [ ] Deployed to production
