# Laravel-Style Validation System

FormGenerator V2 includes a powerful Laravel-inspired validation system that provides comprehensive server-side validation with an intuitive, fluent API.

## Table of Contents

- [Quick Start](#quick-start)
- [Basic Usage](#basic-usage)
- [Available Validation Rules](#available-validation-rules)
- [Using with FormBuilder](#using-with-formbuilder)
- [Custom Error Messages](#custom-error-messages)
- [Database Validation](#database-validation)
- [ValidatorFactory](#validatorfactory)
- [Advanced Features](#advanced-features)

## Quick Start

### Basic Validation

```php
use FormGenerator\V2\Validation\{Validator, ValidationException};

$data = [
    'email' => 'user@example.com',
    'age' => 25,
    'password' => 'secret123',
];

$rules = [
    'email' => 'required|email',
    'age' => 'required|integer|min:18|max:100',
    'password' => 'required|string|min:8',
];

try {
    $validator = new Validator($data, $rules);
    $validated = $validator->validate();

    // Data is valid, use $validated
    echo "Validation passed!";
} catch (ValidationException $e) {
    // Validation failed
    $errors = $e->errors();
    print_r($errors);
}
```

### Using ValidatorFactory

```php
use FormGenerator\V2\Validation\ValidatorFactory;

// Quick validation with exception on failure
$validated = ValidatorFactory::validate($data, $rules);

// Or create a validator instance
$validator = ValidatorFactory::make($data, $rules);
if ($validator->fails()) {
    $errors = $validator->errors();
}
```

## Basic Usage

### Creating a Validator

```php
$validator = new Validator($data, $rules, $messages, $attributes);
```

Parameters:
- `$data` (array): Data to validate
- `$rules` (array): Validation rules
- `$messages` (array, optional): Custom error messages
- `$attributes` (array, optional): Custom attribute names for error messages

### Checking Results

```php
// Check if validation passes
if ($validator->passes()) {
    echo "Valid!";
}

// Check if validation fails
if ($validator->fails()) {
    $errors = $validator->errors();
}

// Get validated data only
$validated = $validator->validated();
```

### Getting Errors

```php
// Get all errors
$errors = $validator->errors();
// Returns: ['email' => ['The email must be a valid email address.']]

// Get errors via ValidationException
try {
    $validator->validate();
} catch (ValidationException $e) {
    $allErrors = $e->errors();           // All errors
    $firstError = $e->first('email');    // First error for field
    $json = $e->toJson();                // Errors as JSON
}
```

## Available Validation Rules

### Required & Type Validation

#### required
Field must be present and not empty.
```php
'name' => 'required'
```

#### string
Value must be a string.
```php
'name' => 'string'
```

#### integer
Value must be an integer.
```php
'age' => 'integer'
```

#### numeric
Value must be numeric (int or float).
```php
'amount' => 'numeric'
```

#### boolean
Value must be boolean or boolean-like (true, false, 1, 0, "1", "0", "true", "false").
```php
'is_active' => 'boolean'
```

#### array
Value must be an array.
```php
'items' => 'array'
```

### String & Pattern Validation

#### alpha
Value must contain only alphabetic characters.
```php
'name' => 'alpha'
```

#### alpha_numeric
Value must contain only alphanumeric characters.
```php
'username' => 'alpha_numeric'
```

#### digits[:length]
Value must be numeric digits, optionally with exact length.
```php
'pin' => 'digits:4'      // Must be exactly 4 digits
'code' => 'digits'       // Any length digits
```

#### regex:pattern
Value must match the given regular expression.
```php
'code' => 'regex:/^[A-Z]{3}-[0-9]{4}$/'
```

### Size & Length Validation

#### min:value
Minimum value for numbers, minimum length for strings, minimum count for arrays.
```php
'age' => 'min:18'        // Number >= 18
'name' => 'min:3'        // String length >= 3
'items' => 'min:2'       // Array count >= 2
```

#### max:value
Maximum value for numbers, maximum length for strings, maximum count for arrays.
```php
'age' => 'max:100'       // Number <= 100
'name' => 'max:255'      // String length <= 255
'items' => 'max:10'      // Array count <= 10
```

#### between:min,max
Value must be between min and max (inclusive).
```php
'age' => 'between:18,65'
'name' => 'between:3,50'
```

### Format Validation

#### email
Value must be a valid email address.
```php
'email' => 'email'
```

#### url
Value must be a valid URL.
```php
'website' => 'url'
```

#### ip[:version]
Value must be a valid IP address. Optionally specify 'ipv4' or 'ipv6'.
```php
'ip' => 'ip'            // IPv4 or IPv6
'ip' => 'ip:ipv4'       // IPv4 only
'ip' => 'ip:ipv6'       // IPv6 only
```

#### json
Value must be valid JSON.
```php
'config' => 'json'
```

### Date & Time Validation

#### date
Value must be a valid date.
```php
'birthday' => 'date'
```

#### date_format:format
Value must match the specified date format.
```php
'date' => 'date_format:Y-m-d'
'time' => 'date_format:H:i:s'
```

#### before:date
Value must be a date before the given date.
```php
'start_date' => 'before:2025-01-01'
```

#### after:date
Value must be a date after the given date.
```php
'end_date' => 'after:2024-01-01'
```

### Comparison Validation

#### confirmed[:field]
Value must match a confirmation field. By default looks for `{field}_confirmation`.
```php
'password' => 'confirmed'                    // Looks for password_confirmation
'password' => 'confirmed:password_verify'    // Custom confirmation field
```

#### in:value1,value2,...
Value must be in the list of allowed values.
```php
'role' => 'in:admin,user,moderator'
```

#### not_in:value1,value2,...
Value must not be in the list of disallowed values.
```php
'role' => 'not_in:admin,root'
```

### Database Validation

#### unique:table,column[,except,idColumn]
Value must be unique in the database table.
```php
// Basic usage
'email' => 'unique:users,email'

// With exception (for updates)
'email' => 'unique:users,email,5,id'
```

**Note:** Requires database connection to be set:
```php
$validator->setDatabaseConnection($pdo);
```

#### exists:table,column
Value must exist in the database table.
```php
'user_id' => 'exists:users,id'
'country_code' => 'exists:countries,code'
```

**Note:** Requires database connection to be set.

## Using with FormBuilder

### Fluent Validation API

FormBuilder provides chainable validation methods for easy form creation:

```php
use FormGenerator\V2\Builder\FormBuilder;

$form = FormBuilder::create('user_form')
    ->addText('username', 'Username')
        ->required()
        ->minLength(3)
        ->maxLength(20)
        ->alphaNumeric()
        ->unique('users', 'username')
        ->add()

    ->addEmail('email', 'Email Address')
        ->required()
        ->email()
        ->unique('users', 'email')
        ->add()

    ->addPassword('password', 'Password')
        ->required()
        ->minLength(8)
        ->confirmed()
        ->add()

    ->addPassword('password_confirmation', 'Confirm Password')
        ->required()
        ->add()

    ->addNumber('age', 'Age')
        ->required()
        ->integer()
        ->between(18, 100)
        ->add()

    ->addSelect('role', 'Role')
        ->required()
        ->in(['user', 'admin', 'moderator'])
        ->add()

    ->addSubmit('register', 'Register')
    ->build();
```

### Available Validation Methods

All validation methods are available on `InputBuilder`:

```php
// Type validation
->string()
->integer()
->numeric()
->boolean()
->array()

// Pattern validation
->alpha()
->alphaNumeric()
->digits(?int $length)
->regex(string $pattern, ?string $message)

// Size validation
->min(int|float $value)
->max(int|float $value)
->minLength(int $length)
->maxLength(int $length)
->between(int|float $min, int|float $max)

// Format validation
->email()
->url()
->ip(?string $version)
->json()

// Date validation
->date()
->dateFormat(string $format)
->before(string $date)
->after(string $date)

// Comparison validation
->confirmed(?string $field)
->in(array $values)
->notIn(array $values)

// Database validation
->unique(string $table, ?string $column, mixed $except, string $idColumn)
->exists(string $table, ?string $column)

// Laravel-style rule string
->rules(string $rules)
```

### Server-Side Validation

```php
$form = FormBuilder::create('user_form')
    ->addText('email', 'Email')
        ->required()
        ->email()
        ->add()
    ->build();

// Validate submitted data
try {
    $validated = $form->validateData($_POST);

    // Data is valid, process it
    echo "Validation passed!";
} catch (ValidationException $e) {
    // Display errors
    $errors = $e->errors();
}
```

### With Database Connection

```php
// For unique/exists rules
$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');

$validated = $form->validateData(
    $_POST,
    $customMessages = [],
    $customAttributes = [],
    $dbConnection = $pdo
);
```

## Custom Error Messages

### Field-Level Messages

```php
$messages = [
    'email.required' => 'Please enter your email address',
    'email.email' => 'Please enter a valid email address',
    'password.min' => 'Password must be at least 8 characters',
];

$validator = new Validator($data, $rules, $messages);
```

### Attribute-Level Messages

```php
$messages = [
    'email' => 'The email field is invalid',  // Used for all email errors
];
```

### Custom Attribute Names

```php
$attributes = [
    'email' => 'Email Address',
    'user_password' => 'Password',
];

$validator = new Validator($data, $rules, [], $attributes);
// Error: "The Email Address must be a valid email."
```

## Database Validation

### Setting Up Database Connection

#### Using Validator Directly

```php
$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');

$validator = new Validator($data, $rules);
$validator->setDatabaseConnection($pdo);
```

#### Using ValidatorFactory

```php
// Set default connection for all validators
ValidatorFactory::setDefaultConnection($pdo);

// All validators created by factory will use this connection
$validator = ValidatorFactory::make($data, $rules);
```

### Unique Rule Examples

```php
// Check if email is unique in users table
'email' => 'unique:users,email'

// Except current user (for update forms)
$userId = 5;
'email' => "unique:users,email,{$userId},id"

// Custom ID column
'email' => 'unique:users,email,5,user_id'
```

### Exists Rule Examples

```php
// Verify user_id exists in users table
'user_id' => 'exists:users,id'

// Verify country code exists
'country' => 'exists:countries,code'

// Multiple table checks
$rules = [
    'user_id' => 'exists:users,id',
    'category_id' => 'exists:categories,id',
];
```

## ValidatorFactory

The `ValidatorFactory` provides convenient methods for creating validators:

### make()

Create a validator instance.

```php
$validator = ValidatorFactory::make($data, $rules, $messages, $attributes);
```

### validate()

Validate data and return validated data or throw exception.

```php
try {
    $validated = ValidatorFactory::validate($data, $rules);
} catch (ValidationException $e) {
    $errors = $e->errors();
}
```

### makeBail()

Create validator with bail mode enabled (stops on first failure).

```php
$validator = ValidatorFactory::makeBail($data, $rules);
```

### setDefaultConnection()

Set default database connection for all validators.

```php
ValidatorFactory::setDefaultConnection($pdo);
```

## Advanced Features

### Bail Mode

Stop validation on first failure:

```php
$validator = new Validator($data, $rules);
$validator->bail();

// Or using factory
$validator = ValidatorFactory::makeBail($data, $rules);
```

### Multiple Rules

Combine multiple rules with pipe separator:

```php
$rules = [
    'email' => 'required|email|unique:users,email',
    'password' => 'required|string|min:8|confirmed',
    'age' => 'required|integer|min:18|max:100',
];
```

### Laravel-Style Rule Strings

Use the `rules()` method on InputBuilder for complex validation:

```php
$form->addText('username', 'Username')
    ->rules('required|alpha_numeric|min:3|max:20|unique:users,username')
    ->add();
```

### Validated Data Only

Get only the validated fields (excludes extra fields):

```php
$data = [
    'email' => 'test@example.com',
    'name' => 'John Doe',
    'extra_field' => 'This will not be in validated data',
];

$rules = [
    'email' => 'required|email',
    'name' => 'required',
];

$validated = $validator->validated();
// Returns only: ['email' => '...', 'name' => '...']
```

### Integration with Events

Validation triggers FormBuilder events:

```php
$form = FormBuilder::create('user_form')
    ->addSubscriber(new class implements EventSubscriberInterface {
        public function getSubscribedEvents(): array {
            return [
                FormEvents::PRE_SUBMIT => 'onPreSubmit',
                FormEvents::VALIDATION_SUCCESS => 'onValidationSuccess',
                FormEvents::VALIDATION_ERROR => 'onValidationError',
                FormEvents::POST_SUBMIT => 'onPostSubmit',
            ];
        }

        public function onPreSubmit(FormEvent $event): void {
            // Modify data before validation
        }

        public function onValidationSuccess(FormEvent $event): void {
            // Handle successful validation
            $validated = $event->getData();
        }

        public function onValidationError(FormEvent $event): void {
            // Handle validation errors
            $errors = $event->getData();
        }

        public function onPostSubmit(FormEvent $event): void {
            // After validation, regardless of result
        }
    });

$form->validateData($_POST);
```

## Examples

### User Registration Form

```php
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

    ->addPassword('password', 'Password')
        ->required()
        ->minLength(8)
        ->confirmed()
        ->add()

    ->addPassword('password_confirmation', 'Confirm Password')
        ->required()
        ->add()

    ->addNumber('age', 'Age')
        ->integer()
        ->between(18, 100)
        ->add()

    ->addSubmit('register', 'Create Account')
    ->build();

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');
        $validated = $form->validateData($_POST, [], [], $pdo);

        // Create user account
        echo "Registration successful!";
    } catch (ValidationException $e) {
        // Display errors
        foreach ($e->errors() as $field => $errors) {
            echo "<p>" . implode(', ', $errors) . "</p>";
        }
    }
}
```

### Profile Update Form

```php
$userId = $_SESSION['user_id'];

$form = FormBuilder::create('profile')
    ->addEmail('email', 'Email')
        ->required()
        ->email()
        ->unique('users', 'email', $userId, 'id')  // Except current user
        ->add()

    ->addText('phone', 'Phone')
        ->regex('/^\+?[0-9]{10,15}$/', 'Invalid phone format')
        ->add()

    ->addDate('birthday', 'Birthday')
        ->date()
        ->before('today')
        ->add()

    ->addSubmit('update', 'Update Profile')
    ->build();
```

### API Data Validation

```php
// Validate JSON API request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$rules = [
    'name' => 'required|string|min:3|max:255',
    'email' => 'required|email|unique:users,email',
    'role' => 'required|in:user,admin,moderator',
    'settings' => 'json',
];

try {
    $validated = ValidatorFactory::validate($data, $rules);

    // Return success response
    echo json_encode(['success' => true, 'data' => $validated]);
} catch (ValidationException $e) {
    // Return error response
    http_response_code(422);
    echo $e->toJson();
}
```

## Migration from Laravel

If you're familiar with Laravel validation, this system will feel very familiar:

```php
// Laravel
$validator = Validator::make($request->all(), [
    'email' => 'required|email|unique:users',
]);

// FormGenerator V2
$validator = ValidatorFactory::make($_POST, [
    'email' => 'required|email|unique:users',
]);
```

The main differences:
1. Import from `FormGenerator\V2\Validation` namespace
2. Database validation requires explicit PDO connection
3. Some advanced Laravel features (like Rule objects, conditional validation) are not yet implemented

## Best Practices

1. **Always validate on the server** - Client-side validation can be bypassed
2. **Use database connection for unique/exists** - Set via factory for convenience
3. **Customize error messages** - Provide user-friendly messages
4. **Use bail mode for expensive validations** - Stop early to save resources
5. **Validate only what you need** - Only validated fields are returned
6. **Combine with events** - Hook into validation lifecycle for complex logic
7. **Test your validations** - Write unit tests for custom validation logic

## See Also

- [Event System Documentation](./EVENTS.md)
- [FormBuilder API Documentation](./FORMBUILDER.md)
- [Laravel Integration](./LARAVEL_INTEGRATION.md)
