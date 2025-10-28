# Blade Template Engine Examples

This directory contains example Blade templates demonstrating FormGenerator V2 integration with Laravel's Blade template engine.

## Installation

### 1. Install via Composer

```bash
composer require selcukmart/form-generator
```

### 2. Register Service Provider (Laravel 10 and below)

Add to `config/app.php`:

```php
'providers' => [
    // Other providers...
    \FormGenerator\V2\Integration\Blade\BladeServiceProvider::class,
],
```

**Note**: Laravel 11+ automatically discovers the service provider via `composer.json`.

### 3. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=form-generator-config
```

## Usage Methods

FormGenerator V2 provides two ways to use Blade integration:

### Method 1: Blade Directives

Use custom Blade directives for form generation:

```blade
@formStart('user-form', ['action' => '/submit', 'method' => 'POST'])

@formText('username', 'Username', ['required' => true, 'minLength' => 3])

@formEmail('email', 'Email', ['required' => true])

@formPassword('password', 'Password', ['required' => true, 'minLength' => 8])

@formSubmit('Register')

@formEnd
```

### Method 2: Blade Components

Use Blade components for a more modern, XML-like syntax:

```blade
<x-form name="user-form" action="/submit" method="POST">

    <x-form-text
        name="username"
        label="Username"
        required
        placeholder="Enter username"
    />

    <x-form-email
        name="email"
        label="Email"
        required
    />

    <x-form-password
        name="password"
        label="Password"
        required
    />

    <x-form-submit>Register</x-form-submit>

</x-form>
```

## Available Directives

- `@formStart(name, options)` - Start a form
- `@formEnd` - End and render the form
- `@formText(name, label, options)` - Text input
- `@formEmail(name, label, options)` - Email input
- `@formPassword(name, label, options)` - Password input
- `@formTextarea(name, label, options)` - Textarea
- `@formNumber(name, label, options)` - Number input
- `@formDate(name, label, options)` - Date input
- `@formSelect(name, label, selectOptions, options)` - Select dropdown
- `@formCheckbox(name, label, options)` - Checkbox
- `@formRadio(name, label, radioOptions, options)` - Radio buttons
- `@formSubmit(label, options)` - Submit button
- `@formButton(label, type, options)` - Button

## Available Components

- `<x-form>` - Form wrapper
- `<x-form-text>` - Text input
- `<x-form-email>` - Email input
- `<x-form-password>` - Password input
- `<x-form-textarea>` - Textarea
- `<x-form-number>` - Number input
- `<x-form-select>` - Select dropdown
- `<x-form-submit>` - Submit button

## Common Options

All input directives/components support these options:

- `required` (bool) - Mark field as required
- `placeholder` (string) - Placeholder text
- `help` (string) - Help text below the input
- `value` (mixed) - Default value
- `class` (string) - CSS classes
- `min` (int/float) - Minimum value (for numbers/dates)
- `max` (int/float) - Maximum value (for numbers/dates)
- `minLength` (int) - Minimum length (for text)
- `maxLength` (int) - Maximum length (for text)
- `pattern` (string) - Regex pattern
- `readonly` (bool) - Read-only field
- `disabled` (bool) - Disabled field

## Validation Integration

FormGenerator V2 includes Laravel-style validation that integrates seamlessly with Blade:

```php
// In your controller
public function store(Request $request)
{
    $form = FormBuilder::create('register')
        ->addText('username', 'Username')
            ->required()
            ->alphaNumeric()
            ->minLength(3)
            ->maxLength(20)
            ->unique('users', 'username')
            ->add()
        ->addEmail('email', 'Email')
            ->required()
            ->email()
            ->unique('users', 'email')
            ->add()
        ->build();

    try {
        $validated = $form->validateData($request->all(), [], [], DB::connection()->getPdo());

        // Create user with validated data
        User::create($validated);

        return redirect()->back()->with('success', 'Registration successful!');
    } catch (\FormGenerator\V2\Validation\ValidationException $e) {
        return redirect()->back()->withErrors($e->errors())->withInput();
    }
}
```

Then in your Blade template:

```blade
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
```

## Examples in This Directory

1. **user-registration.blade.php** - Complete user registration form using directives
2. **contact-form-components.blade.php** - Contact form using Blade components

## Advanced Usage

### Custom Themes

```php
use FormGenerator\V2\Theme\TailwindTheme;
use FormGenerator\V2\Integration\Blade\FormGeneratorBladeDirectives;

FormGeneratorBladeDirectives::setDefaultTheme(new TailwindTheme());
```

### Custom Renderer

```php
use FormGenerator\V2\Renderer\BladeRenderer;
use FormGenerator\V2\Integration\Blade\FormGeneratorBladeDirectives;

$renderer = new BladeRenderer(resource_path('views'), storage_path('framework/views'));
FormGeneratorBladeDirectives::setRenderer($renderer);
```

## More Information

- [FormGenerator V2 Documentation](../../docs/V2/)
- [Validation Documentation](../../docs/V2/VALIDATION.md)
- [Laravel Integration Guide](../../README_V2.md#laravel-integration)
