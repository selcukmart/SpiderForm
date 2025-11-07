# SpiderForm V2 - Renderer Configuration Guide

This guide explains how to properly configure renderers in SpiderForm V2 to avoid common template loading errors.

## Table of Contents

1. [Common Issues](#common-issues)
2. [Renderer Setup](#renderer-setup)
3. [Available Renderers](#available-renderers)
4. [Troubleshooting](#troubleshooting)

---

## Common Issues

### Error: "Unable to load template 'file:bootstrap3/form.tpl'"

**Cause**: The renderer doesn't know where to find the template files, or is looking in the wrong directory.

**Solution**: Configure the correct template directory path when instantiating the renderer.

**Important**: As of v2.x, templates for different renderers are separated:
- Twig templates (`.twig`) are in: `src/V2/Theme/templates/twig/`
- Smarty templates (`.tpl`) are in: `src/V2/Theme/templates/smarty/`
- Blade templates (`.blade.php`) are in: `src/V2/Theme/templates/blade/`

```php
// ❌ WRONG - No template path specified
$renderer = new SmartyRenderer();

// ❌ WRONG - Old path (before template separation)
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/../../vendor/selcukmart/spider-form/src/V2/Theme/templates'
);

// ✅ CORRECT - Smarty template path (note: /smarty subdirectory)
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/../../vendor/selcukmart/spider-form/src/V2/Theme/templates/smarty'
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

// IMPORTANT: Point to the 'smarty' subdirectory for .tpl templates
$renderer = new SmartyRenderer(
    templateDir: '/path/to/spider-form/src/V2/Theme/templates/smarty',
    compileDir: sys_get_temp_dir() . '/smarty_compile',  // Optional
    cacheDir: sys_get_temp_dir() . '/smarty_cache'       // Optional
);
```

**Parameters**:
- `templateDir` (required): Path to the Smarty template directory (`.tpl` files)
- `compileDir` (optional): Directory for compiled templates (defaults to system temp)
- `cacheDir` (optional): Directory for cached templates (defaults to system temp)

**Example - From Project Root**:

```php
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/smarty'
);
```

**Example - From Package Root**:

```php
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/src/V2/Theme/templates/smarty'
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

// IMPORTANT: Point to the 'twig' subdirectory for .twig templates
$renderer = new TwigRenderer(
    templatePaths: '/path/to/spider-form/src/V2/Theme/templates/twig',
    cacheDir: sys_get_temp_dir() . '/form_generator_cache'  // Optional
);
```

**Parameters**:
- `templatePaths` (required): Path(s) to Twig template directories (`.twig` files, string or array)
- `cacheDir` (optional): Directory for cached templates
- `debug` (optional): Enable debug mode (default: false)
- `autoReload` (optional): Auto-reload templates on change (default: true)

**Example**:

```php
$renderer = new TwigRenderer(
    templatePaths: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/twig',
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

// IMPORTANT: Point to the 'blade' subdirectory for .blade.php templates
$renderer = new BladeRenderer(
    templatePaths: '/path/to/spider-form/src/V2/Theme/templates/blade',
    cachePath: sys_get_temp_dir() . '/blade_cache'
);
```

**Parameters**:
- `templatePaths` (required): Path(s) to Blade template directories (`.blade.php` files, string or array)
- `cachePath` (required): Directory for compiled views

**Example - From Project Root**:

```php
$renderer = new BladeRenderer(
    templatePaths: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/blade',
    cachePath: sys_get_temp_dir() . '/blade_cache'
);
```

**Example - From Package Root**:

```php
$renderer = new BladeRenderer(
    templatePaths: __DIR__ . '/src/V2/Theme/templates/blade',
    cachePath: sys_get_temp_dir() . '/blade_cache'
);
```

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
       templateDir: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/smarty'
   );

   // ❌ WRONG - Relative paths may not work
   $renderer = new SmartyRenderer(
       templateDir: '../vendor/selcukmart/spider-form/src/V2/Theme/templates/smarty'
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
    templateDir: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/smarty',
    compileDir: $compileDir,
    cacheDir: $cacheDir
);
```

---

### Issue: Wrong Renderer for Theme

**Symptom**: Templates render incorrectly or with errors

**Solution**: Ensure you're using the correct template directory for your renderer:

```php
// TwigRenderer uses .twig templates from the twig/ subdirectory
$renderer = new TwigRenderer(
    templatePaths: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/twig'
);

// SmartyRenderer uses .tpl templates from the smarty/ subdirectory
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/smarty'
);

// BladeRenderer uses .blade.php templates from the blade/ subdirectory
$renderer = new BladeRenderer(
    templatePaths: __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/blade',
    cachePath: sys_get_temp_dir() . '/blade_cache'
);
```

**Note**: Themes reference templates with `.twig` extensions, but SmartyRenderer and BladeRenderer automatically convert these to their respective formats (`.tpl` for Smarty, `.blade.php` for Blade) when looking for files. This allows the same theme configuration to work with different renderers.

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
$templateDir = __DIR__ . '/vendor/selcukmart/spider-form/src/V2/Theme/templates/smarty';
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
