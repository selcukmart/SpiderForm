# SpiderForm V2 - Renderer Configuration Guide

This guide explains how to properly configure renderers in SpiderForm V2 to avoid common template loading errors.

## Table of Contents

1. [Common Issues](#common-issues)
2. [Renderer Setup](#renderer-setup)
3. [Available Renderers](#available-renderers)
4. [Troubleshooting](#troubleshooting)

---

## Common Issues

### Error: "Unable to load template 'file:bootstrap3/form.twig'"

**Cause**: The renderer doesn't know where to find the template files.

**Solution**: Configure the template directory path when instantiating the renderer.

```php
// ❌ WRONG - No template path specified
$renderer = new SmartyRenderer();

// ✅ CORRECT - Template path specified
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/../../vendor/selcukmart/spider-form/src/V2/Theme/templates'
);
```

---

## Renderer Setup

### 1. SmartyRenderer

**Installation**: Requires Smarty 4.x

```bash
composer require smarty/smarty
```

**Configuration**:

```php
use SpiderForm\V2\Renderer\SmartyRenderer;

$renderer = new SmartyRenderer(
    templateDir: '/path/to/spider-form/src/V2/Theme/templates',
    compileDir: sys_get_temp_dir() . '/smarty_compile',  // Optional
    cacheDir: sys_get_temp_dir() . '/smarty_cache'       // Optional
);
```

**Parameters**:
- `templateDir` (required): Path to the template directory
- `compileDir` (optional): Directory for compiled templates (defaults to system temp)
- `cacheDir` (optional): Directory for cached templates (defaults to system temp)

**Example - From Project Root**:

```php
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates'
);
```

**Example - From Package Root**:

```php
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/src/V2/Theme/templates'
);
```

---

### 2. TwigRenderer (Recommended)

**Installation**: Requires Twig 3.x

```bash
composer require twig/twig
```

**Configuration**:

```php
use SpiderForm\V2\Renderer\TwigRenderer;

$renderer = new TwigRenderer(
    templatePaths: '/path/to/spider-form/src/V2/Theme/templates',
    cacheDir: sys_get_temp_dir() . '/form_generator_cache'  // Optional
);
```

**Parameters**:
- `templatePaths` (required): Path(s) to template directories (string or array)
- `cacheDir` (optional): Directory for cached templates
- `debug` (optional): Enable debug mode (default: false)
- `autoReload` (optional): Auto-reload templates on change (default: true)

**Example**:

```php
$renderer = new TwigRenderer(
    templatePaths: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates',
    cacheDir: sys_get_temp_dir() . '/form_cache'
);
```

---

### 3. BladeRenderer

**Installation**: Works with Laravel's Blade engine

```bash
composer require illuminate/view
```

**Configuration**:

```php
use SpiderForm\V2\Renderer\BladeRenderer;

$renderer = new BladeRenderer(
    viewPaths: '/path/to/spider-form/src/V2/Theme/templates',
    cachePath: sys_get_temp_dir() . '/blade_cache'
);
```

**Parameters**:
- `viewPaths` (required): Path(s) to view directories (string or array)
- `cachePath` (required): Directory for compiled views

---

## Available Renderers

| Renderer | Template Engine | Recommended Use Case |
|----------|----------------|---------------------|
| **TwigRenderer** | Twig 3.x | General purpose, best performance |
| **SmartyRenderer** | Smarty 4.x | Legacy projects using Smarty |
| **BladeRenderer** | Laravel Blade | Laravel applications |

---

## Troubleshooting

### Issue: Templates Not Found

**Symptom**:
```
SmartyException: Unable to load template 'file:bootstrap3/form.twig'
```

**Solution**: Check the following:

1. **Verify the template path exists**:
   ```php
   $templatePath = __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates';
   if (!is_dir($templatePath)) {
       throw new \RuntimeException("Template directory not found: $templatePath");
   }
   ```

2. **Use absolute paths**: Always use `__DIR__` or `realpath()` for paths:
   ```php
   // ✅ CORRECT
   $renderer = new SmartyRenderer(
       templateDir: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates'
   );

   // ❌ WRONG - Relative paths may not work
   $renderer = new SmartyRenderer(
       templateDir: '../vendor/selcukmart/spider-form/src/V2/Theme/templates'
   );
   ```

3. **Check file permissions**: Ensure the web server can read the template directory:
   ```bash
   chmod -R 755 vendor/selcukmart/spider-form/src/V2/Theme/templates
   ```

---

### Issue: Permission Denied on Cache/Compile Directories

**Symptom**:
```
Permission denied: /tmp/smarty_compile/...
```

**Solution**: Ensure cache directories are writable:

```php
$compileDir = sys_get_temp_dir() . '/smarty_compile';
$cacheDir = sys_get_temp_dir() . '/smarty_cache';

// Create directories if they don't exist
if (!is_dir($compileDir)) {
    mkdir($compileDir, 0755, true);
}
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates',
    compileDir: $compileDir,
    cacheDir: $cacheDir
);
```

---

### Issue: Wrong Renderer for Theme

**Symptom**: Templates render incorrectly or with errors

**Solution**: Ensure all renderers use `.twig` templates. All themes (Bootstrap3, Bootstrap5, Tailwind) use Twig template syntax, even when using SmartyRenderer or BladeRenderer.

```php
// All these work with the same .twig templates
$renderer = new TwigRenderer(...);      // Native Twig
$renderer = new SmartyRenderer(...);    // Smarty with .twig templates
$renderer = new BladeRenderer(...);     // Blade with .twig templates
```

---

## Best Practices

1. **Use TwigRenderer** for new projects (better performance and native template support)
2. **Always specify template directory** when instantiating renderers
3. **Use absolute paths** with `__DIR__` for reliability
4. **Set proper cache directories** for production performance
5. **Disable caching in development** for faster development cycles:
   ```php
   $renderer->setCaching(false);
   ```

---

## Complete Example

Here's a complete working example:

```php
<?php

use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Theme\Bootstrap3Theme;

// Configure paths
$templateDir = __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates';
$compileDir = sys_get_temp_dir() . '/smarty_compile';
$cacheDir = sys_get_temp_dir() . '/smarty_cache';

// Ensure cache directories exist
if (!is_dir($compileDir)) mkdir($compileDir, 0755, true);
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// Create renderer
$renderer = new SmartyRenderer(
    templateDir: $templateDir,
    compileDir: $compileDir,
    cacheDir: $cacheDir
);

// Create theme
$theme = new Bootstrap3Theme();

// Build form
$form = FormBuilder::create('my_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->addText('name', 'Name')
        ->required()
        ->add()
    ->addSubmit('save', 'Submit')
    ->build();

echo $form;
```

---

## See Also

- [Basic Usage Example](../../Examples/V2/BasicUsage.php)
- [SmartyRenderer Example](../../Examples/V2/WithSmartyRenderer.php)
- [Validation Guide](./VALIDATION.md)
