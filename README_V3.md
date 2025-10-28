# FormGenerator V3 - Complete Documentation

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-3.2.1-blue.svg)](CHANGELOG.md)
[![Tests](https://img.shields.io/badge/tests-500%2B-success.svg)](tests/)

**Production-Ready PHP Form Generator**

Modern form builder library with nested forms, type system, cross-field validation, dynamic modification, internationalization, and automatic CSRF protection.

---

## Version 3.0.0 - Production Ready

FormGenerator V3.0.0 is a comprehensive form building library with an intuitive fluent API.

### Key Features

**Enterprise-Grade Form Builder**
- Comprehensive form building capabilities
- Fluent chain pattern API
- Framework-agnostic design (standalone or integrate with Symfony/Laravel)
- 500+ comprehensive unit tests

🌍 **Internationalization (v3.0.0)**
- Multi-language form labels and messages
- Built-in translator with PHP and YAML loaders
- Parameter interpolation (`{{ param }}` syntax)
- Locale fallback chains

🔒 **Automatic CSRF Protection (v3.0.0)**
- Session-based token management
- Automatic token generation and validation
- Configurable token lifetime (default: 2 hours)
- Zero configuration required

⚠️ **Advanced Error Handling (v2.9.0)**
- Three error levels: ERROR, WARNING, INFO
- Error bubbling from nested forms
- Rich error metadata and causes
- Error filtering and grouping

🎭 **Dynamic Form Modification (v2.8.0)**
- Event-driven form building
- Modify forms based on data
- Add/remove fields dynamically
- Full event lifecycle control

✅ **Cross-Field Validation (v2.7.0)**
- Validate relationships between fields
- Validation groups for conditional rules
- Custom callback constraints
- Execution context with violation builder

🏗️ **Type System & Extensions (v2.5.0)**
- Custom field types
- Type inheritance
- Type extensions
- Options resolver

🌳 **Nested Forms & Collections (v2.4.0)**
- Unlimited nesting levels
- Form collections (repeatable forms)
- Parent-child relationships
- Data mapping

---

## 📦 Installation

```bash
composer require selcukmart/form-generator
```

**Requirements:**
- PHP 8.1 or higher
- No external dependencies required (standalone)
- Optional: Symfony 6/7, Laravel 10/11

---

## 🚀 Quick Start

### Simple Contact Form with i18n & CSRF

```php
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Translation\FormTranslator;
use FormGenerator\V2\Translation\Loader\PhpLoader;

// Setup translator (optional)
$translator = new FormTranslator('en_US');
$translator->addLoader('php', new PhpLoader());
$translator->loadTranslationFile(__DIR__ . '/translations/forms.en_US.php', 'en_US', 'php');

FormBuilder::setTranslator($translator);

// Build form with CSRF protection (automatic!)
$form = FormBuilder::create('contact_form')
    ->setAction('/contact/send')
    ->setMethod('POST')
    ->setCsrfTokenId('contact_form') // Automatic CSRF protection

    ->addText('name', 'form.label.name') // Translated automatically
        ->required()
        ->minLength(3)
        ->add()

    ->addEmail('email', 'form.label.email')
        ->required()
        ->add()

    ->addTextarea('message', 'form.label.message')
        ->required()
        ->minLength(20)
        ->add()

    ->addSubmit('send', 'form.button.send')
    ->build();

// Render with automatic CSRF token
echo $form;

// Process submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form->submit($_POST);

    if ($form->isValid()) {
        $data = $form->getData();
        // Process data...
    } else {
        $errors = $form->getErrorList(deep: true);
    }
}
```

### Nested Forms with Cross-Field Validation

```php
use FormGenerator\V2\Form\Form;
use FormGenerator\V2\Validation\Constraints\Callback;

// Create address sub-form
$addressForm = new Form('address');
$addressForm->add('street', FormBuilder::text('street', 'Street')->required());
$addressForm->add('city', FormBuilder::text('city', 'City')->required());
$addressForm->add('zipcode', FormBuilder::text('zipcode', 'ZIP Code')->required());

// Create user form with nested address
$form = FormBuilder::create('user_registration')
    ->setCsrfTokenId('register')
    ->setLocale('en_US') // Set locale for all fields

    ->addText('username', 'Username')
        ->required()
        ->minLength(3)
        ->maxLength(20)
        ->add()

    ->addEmail('email', 'Email')
        ->required()
        ->add()

    ->addPassword('password', 'Password')
        ->required()
        ->minLength(8)
        ->add()

    ->addPassword('password_confirm', 'Confirm Password')
        ->required()
        ->add()

    ->add('address', $addressForm) // Nested form!

    ->addCheckbox('terms', 'I agree to terms and conditions')
        ->required()
        ->add()

    ->addSubmit('register', 'Create Account')
    ->build();

// Add cross-field validation
$form->addConstraint(new Callback(function($data, $context) {
    if ($data['password'] !== $data['password_confirm']) {
        $context->buildViolation('Passwords do not match')
                ->atPath('password_confirm')
                ->addViolation();
    }
}));

// Process
$form->submit($_POST);
if ($form->isValid()) {
    $data = $form->getData();
    // Data structure:
    // [
    //     'username' => 'john',
    //     'email' => 'john@example.com',
    //     'password' => '...',
    //     'address' => [
    //         'street' => '123 Main St',
    //         'city' => 'New York',
    //         'zipcode' => '10001'
    //     ],
    //     'terms' => true
    // ]
} else {
    // Get all errors with enhanced error handling
    $errors = $form->getErrorList(deep: true);

    // Get only blocking errors
    $critical = $errors->blocking();

    // Get warnings
    $warnings = $errors->byLevel(ErrorLevel::WARNING);

    // Get errors for specific field
    $emailErrors = $errors->byPath('email');

    // Format errors
    $errorsArray = $errors->toArray();    // Nested structure
    $errorsFlat = $errors->toFlat();      // Flat dot notation
}
```

### Dynamic Form Modification with Events

```php
use FormGenerator\V2\Event\FormEvents;

$form = FormBuilder::create('product_form')
    ->setCsrfTokenId('product')

    ->addSelect('product_type', 'Product Type')
        ->options([
            'physical' => 'Physical Product',
            'digital' => 'Digital Product',
        ])
        ->required()
        ->add()

    ->addText('name', 'Product Name')
        ->required()
        ->add()

    ->addNumber('price', 'Price')
        ->required()
        ->min(0.01)
        ->add()

    ->build();

// Add fields dynamically based on product type
$form->addEventListener(FormEvents::PRE_SET_DATA, function($event) {
    $data = $event->getData();
    $form = $event->getForm();

    if (!empty($data['product_type'])) {
        if ($data['product_type'] === 'physical') {
            // Add shipping fields for physical products
            $form->add('weight', FormBuilder::number('weight', 'Weight (kg)')
                ->required()
                ->min(0.01));
            $form->add('dimensions', FormBuilder::text('dimensions', 'Dimensions')
                ->placeholder('L x W x H'));
            $form->add('shipping_class', FormBuilder::select('shipping_class', 'Shipping Class')
                ->options([
                    'standard' => 'Standard',
                    'express' => 'Express',
                    'overnight' => 'Overnight'
                ]));
        } else {
            // Add download fields for digital products
            $form->add('file_size', FormBuilder::text('file_size', 'File Size')
                ->placeholder('e.g., 50MB'));
            $form->add('download_limit', FormBuilder::number('download_limit', 'Download Limit')
                ->min(1)
                ->value(3));
            $form->add('license_type', FormBuilder::select('license_type', 'License')
                ->options([
                    'single' => 'Single User',
                    'multi' => 'Multi User',
                    'unlimited' => 'Unlimited'
                ]));
        }
    }
});

// You can also modify on PRE_SUBMIT
$form->addEventListener(FormEvents::PRE_SUBMIT, function($event) {
    $data = $event->getData();
    $form = $event->getForm();

    // Modify form based on submitted data
    if ($data['product_type'] === 'physical' && $data['weight'] > 100) {
        $form->add('freight_notice', FormBuilder::text('freight_notice', 'Freight Notice')
            ->readonly()
            ->value('This product requires freight shipping'));
    }
});
```

### Form Collections (Repeatable Sub-forms)

```php
use FormGenerator\V2\Form\FormCollection;

// Create phone number sub-form
$phoneForm = new Form('phone');
$phoneForm->add('type', FormBuilder::select('type', 'Type')
    ->options(['mobile' => 'Mobile', 'home' => 'Home', 'work' => 'Work']));
$phoneForm->add('number', FormBuilder::tel('number', 'Number')->required());

// Create contact form with phone collection
$form = FormBuilder::create('contact')
    ->addText('name', 'Name')->required()->add()
    ->addEmail('email', 'Email')->required()->add()

    // Add collection of phone numbers (allow multiple)
    ->addCollection('phones', $phoneForm, 'Phone Numbers')
        ->allowAdd(true)    // Can add new entries
        ->allowDelete(true) // Can remove entries
        ->min(1)           // Require at least 1 phone
        ->max(5)           // Allow maximum 5 phones
        ->add()

    ->addSubmit('save')
    ->build();

// Data structure after submission:
// [
//     'name' => 'John Doe',
//     'email' => 'john@example.com',
//     'phones' => [
//         ['type' => 'mobile', 'number' => '555-1234'],
//         ['type' => 'work', 'number' => '555-5678'],
//     ]
// ]
```

### Validation Groups

```php
use FormGenerator\V2\Validation\GroupedValidation;

$form = FormBuilder::create('user_profile')
    ->setCsrfTokenId('profile')

    // These fields validated in 'registration' group
    ->addText('username', 'Username')
        ->required(['groups' => ['registration']])
        ->minLength(3, ['groups' => ['registration']])
        ->add()

    ->addEmail('email', 'Email')
        ->required(['groups' => ['registration', 'profile']])
        ->add()

    ->addPassword('password', 'Password')
        ->required(['groups' => ['registration']])
        ->minLength(8, ['groups' => ['registration']])
        ->add()

    // These fields validated in 'profile' group
    ->addText('bio', 'Bio')
        ->maxLength(500, ['groups' => ['profile']])
        ->add()

    ->addDate('birthday', 'Birthday')
        ->required(['groups' => ['profile']])
        ->add()

    ->addSubmit('save')
    ->build();

// Validate specific group
$form->submit($_POST);
$form->validate(['groups' => ['registration']]); // Only validates registration fields

// Or validate multiple groups
$form->validate(['groups' => ['registration', 'profile']]);
```

### Custom Field Types

```php
use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Type\TypeRegistry;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;

// Create custom phone type
class PhoneType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::TEL)
                ->setPlaceholder($options['placeholder'])
                ->addAttribute('pattern', $options['pattern']);

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'placeholder' => '(555) 123-4567',
            'pattern' => '^\(\d{3}\) \d{3}-\d{4}$',
            'country' => 'US',
        ]);

        $resolver->setAllowedTypes('country', 'string');
        $resolver->setAllowedValues('country', ['US', 'UK', 'CA']);
    }

    public function getParent(): ?string
    {
        return 'text'; // Inherits from TextType
    }
}

// Register custom type
TypeRegistry::register('phone', PhoneType::class);

// Use in forms
$form = FormBuilder::create('contact')
    ->addField('mobile', 'phone', ['label' => 'Mobile Phone', 'country' => 'US'])
    ->addField('work', 'phone', ['label' => 'Work Phone'])
    ->build();
```

### Type Extensions

```php
use FormGenerator\V2\Type\AbstractTypeExtension;
use FormGenerator\V2\Type\TypeExtensionRegistry;

// Create extension to add help text to all text inputs
class HelpTextExtension extends AbstractTypeExtension
{
    public function extendType(): string|array
    {
        return ['text', 'email', 'number']; // Apply to these types
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('help');
        $resolver->setAllowedTypes('help', ['null', 'string']);
    }

    public function buildField(InputBuilder $builder, array $options): void
    {
        if (!empty($options['help'])) {
            $builder->helpText($options['help']);
        }
    }
}

// Register extension
TypeExtensionRegistry::register(new HelpTextExtension());

// Now all text, email, and number fields support 'help' option
$form = FormBuilder::create('user')
    ->addText('username', 'Username', ['help' => 'Choose a unique username'])
    ->addEmail('email', 'Email', ['help' => 'We\'ll never share your email'])
    ->build();
```

---

## 🎯 Complete Feature Set

### Version 3.0.0 - Internationalization & CSRF

**Translation System**
```php
use FormGenerator\V2\Translation\FormTranslator;
use FormGenerator\V2\Translation\Loader\PhpLoader;
use FormGenerator\V2\Translation\Loader\YamlLoader;

$translator = new FormTranslator('en_US');

// Add loaders
$translator->addLoader('php', new PhpLoader());
$translator->addLoader('yaml', new YamlLoader());

// Load translation files
$translator->loadTranslationFile(__DIR__ . '/translations/forms.en_US.php', 'en_US', 'php');
$translator->loadTranslationFile(__DIR__ . '/translations/forms.fr_FR.yml', 'fr_FR', 'yaml');

// Set globally
FormBuilder::setTranslator($translator);

// Change locale
$translator->setLocale('fr_FR');

// Translate with parameters
$message = $translator->trans('form.error.minLength', ['min' => 5]);
// Result: "Must be at least 5 characters"
```

**CSRF Protection**
```php
use FormGenerator\V2\Security\CsrfTokenManager;
use FormGenerator\V2\Security\CsrfProtection;

// Automatic protection (recommended)
$form = FormBuilder::create('user_form')
    ->setCsrfTokenId('user_form') // Token auto-generated and validated
    ->build();

// Manual protection (advanced)
$csrfManager = new CsrfTokenManager();
$token = $csrfManager->generateToken('my_form');

$csrfProtection = new CsrfProtection($csrfManager);
$isValid = $csrfProtection->validateToken('my_form', $_POST);

// CSRF token is automatically included in form HTML
echo $form; // Includes hidden _csrf_token field

// Token is automatically validated on submit
$form->submit($_POST); // Throws exception if CSRF invalid
```

### Version 2.9.0 - Advanced Error Handling

**Error Levels**
```php
use FormGenerator\V2\Error\ErrorLevel;
use FormGenerator\V2\Error\FormError;
use FormGenerator\V2\Error\ErrorList;

$form->submit($_POST);

if (!$form->isValid()) {
    $errors = $form->getErrorList(deep: true);

    // Three error levels
    $critical = $errors->byLevel(ErrorLevel::ERROR);    // Must fix
    $warnings = $errors->byLevel(ErrorLevel::WARNING);  // Should fix
    $info = $errors->byLevel(ErrorLevel::INFO);        // Nice to fix

    // Check for blocking errors
    if ($errors->hasBlocking()) {
        // Cannot proceed
    }

    // Filter by path
    $emailErrors = $errors->byPath('email');
    $addressErrors = $errors->byPath('address', deep: true); // Include nested

    // Get first error
    $firstError = $errors->first();
    $firstEmailError = $errors->first('email');

    // Format errors
    foreach ($errors as $error) {
        echo $error->getLevel()->getIcon() . ' ';  // ❌ ⚠️ ℹ️
        echo $error->getLevel()->getLabel() . ': '; // Error, Warning, Info
        echo $error->getMessage();
        echo ' (at: ' . $error->getPath() . ')';
    }

    // Export errors
    $array = $errors->toArray();    // Nested structure
    $flat = $errors->toFlat();      // Flat with dot notation
}
```

**Error Bubbling**
```php
use FormGenerator\V2\Error\ErrorBubblingStrategy;

// Configure bubbling
$strategy = ErrorBubblingStrategy::enabled();
// or
$strategy = ErrorBubblingStrategy::stopOnBlocking();
// or
$strategy = ErrorBubblingStrategy::withDepthLimit(3);

// Errors from nested forms bubble up to parent automatically
$parentForm->add('address', $addressForm);
$parentForm->submit($_POST);

// Parent form receives all errors including from nested forms
$allErrors = $parentForm->getErrorList(deep: true);
// Includes errors like: 'address.street', 'address.city', etc.
```

### Version 2.8.0 - Dynamic Form Modification

**Form Events**
```php
use FormGenerator\V2\Event\FormEvents;
use FormGenerator\V2\Event\FormEvent;

$form = FormBuilder::create('dynamic_form')->build();

// PRE_SET_DATA - Before data is set
$form->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
    $data = $event->getData();
    $form = $event->getForm();

    // Modify data before setting
    $data['processed'] = true;
    $event->setData($data);

    // Add fields based on data
    if ($data['type'] === 'premium') {
        $form->add('premium_features', FormBuilder::textarea('premium_features'));
    }
});

// POST_SET_DATA - After data is set
$form->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
    // Log, validate, transform after data set
});

// PRE_SUBMIT - Before submission processing
$form->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
    $data = $event->getData();

    // Sanitize input
    $data['username'] = trim(strtolower($data['username']));
    $event->setData($data);
});

// POST_SUBMIT - After submission
$form->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
    if ($event->getForm()->isValid()) {
        // Send notifications, log, etc.
    }
});

// Stop propagation
$form->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
    if (someCondition) {
        $event->stopPropagation(); // Stops other listeners
    }
});
```

**Event Dispatcher**
```php
use FormGenerator\V2\Event\EventDispatcher;
use FormGenerator\V2\Event\EventSubscriberInterface;

$dispatcher = new EventDispatcher();

// Add listener with priority
$dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $myListener, priority: 10);

// Add subscriber
class FormSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::POST_SUBMIT => ['onPostSubmit', 5], // With priority
        ];
    }

    public function onPreSetData(FormEvent $event): void { /* ... */ }
    public function onPostSubmit(FormEvent $event): void { /* ... */ }
}

$dispatcher->addSubscriber(new FormSubscriber());
```

### Version 2.7.0 - Cross-Field Validation

**Callback Constraints**
```php
use FormGenerator\V2\Validation\Constraints\Callback;
use FormGenerator\V2\Validation\ExecutionContext;

$form->addConstraint(new Callback(function($data, ExecutionContext $context) {
    // Password confirmation
    if ($data['password'] !== $data['password_confirm']) {
        $context->buildViolation('Passwords do not match')
                ->atPath('password_confirm')
                ->addViolation();
    }

    // Age verification
    if ($data['adult_content'] && $data['age'] < 18) {
        $context->buildViolation('Must be {{ min }} or older')
                ->atPath('age')
                ->setParameter('min', 18)
                ->addViolation();
    }

    // Date range
    if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
        $context->buildViolation('End date must be after start date')
                ->atPath('end_date')
                ->addViolation();
    }

    // Conditional requirements
    if (empty($data['email']) && empty($data['phone'])) {
        $context->buildViolation('Either email or phone is required')
                ->atPath('email')
                ->addViolation();
    }
}));
```

**Execution Context**
```php
use FormGenerator\V2\Validation\ExecutionContext;
use FormGenerator\V2\Validation\ViolationBuilder;

$context = new ExecutionContext($formData);

// Build violations with fluent API
$context->buildViolation('Value is invalid')
        ->atPath('fieldName')
        ->setParameter('value', $actualValue)
        ->addViolation();

// Access form data
$email = $context->getValue('email');
$allData = $context->getData();

// Set validation context
$context->setCurrentPath('address.street');
$context->setCurrentGroup('registration');

// Check violations
if ($context->hasViolations()) {
    $violations = $context->getViolations();
}
```

### Version 2.5.0 - Type System & Extensions

**Options Resolver**
```php
use FormGenerator\V2\Type\OptionsResolver;

$resolver = new OptionsResolver();

// Set defaults
$resolver->setDefaults([
    'required' => false,
    'label' => null,
    'placeholder' => '',
    'min' => 0,
    'max' => 100,
]);

// Mark as required
$resolver->setRequired(['name', 'email']);

// Define allowed types
$resolver->setAllowedTypes('required', 'bool');
$resolver->setAllowedTypes('label', ['null', 'string']);
$resolver->setAllowedTypes('min', 'int');

// Define allowed values
$resolver->setAllowedValues('size', ['small', 'medium', 'large']);
$resolver->setAllowedValues('count', fn($value) => $value > 0 && $value <= 10);

// Add normalizers
$resolver->setNormalizer('label', fn($value) => ucfirst($value));
$resolver->setNormalizer('email', fn($value) => strtolower(trim($value)));

// Resolve options
$options = $resolver->resolve([
    'name' => 'username',
    'required' => true,
    'min' => 3,
]);
```

**Type Registry**
```php
use FormGenerator\V2\Type\TypeRegistry;

// Register custom type
TypeRegistry::register('phone', PhoneType::class);
TypeRegistry::register('wysiwyg', WysiwygType::class);

// Register multiple
TypeRegistry::registerMany([
    'color' => ColorPickerType::class,
    'rich_text' => RichTextType::class,
]);

// Create alias
TypeRegistry::alias('mobile', 'phone');

// Check if registered
if (TypeRegistry::has('phone')) {
    $type = TypeRegistry::get('phone');
}

// Get type hierarchy
$hierarchy = TypeRegistry::getTypeHierarchy('email');
// Returns: [EmailType, TextType]

// Check type inheritance
TypeRegistry::isTypeOf('email', 'text'); // true
```

### Version 2.4.0 - Nested Forms & Collections

**Basic Nested Form**
```php
// Method 1: Using Form object
$addressForm = new Form('address');
$addressForm->add('street', FormBuilder::text('street', 'Street'));
$addressForm->add('city', FormBuilder::text('city', 'City'));
$addressForm->add('zipcode', FormBuilder::text('zipcode', 'ZIP'));

$form = FormBuilder::create('user')
    ->addText('name', 'Name')->add()
    ->add('address', $addressForm) // Nested!
    ->build();

// Method 2: Using closure
$form = FormBuilder::create('user')
    ->addText('name', 'Name')->add()
    ->addNestedForm('address', function(FormBuilder $subForm) {
        $subForm->addText('street', 'Street')->add();
        $subForm->addText('city', 'City')->add();
        $subForm->addText('zipcode', 'ZIP')->add();
    })
    ->build();
```

**Deep Nesting**
```php
// Company -> Departments -> Employees
$employeeForm = new Form('employee');
$employeeForm->add('name', FormBuilder::text('name', 'Name'));
$employeeForm->add('position', FormBuilder::text('position', 'Position'));

$departmentForm = new Form('department');
$departmentForm->add('name', FormBuilder::text('name', 'Department Name'));
$departmentForm->addCollection('employees', $employeeForm, 'Employees');

$form = FormBuilder::create('company')
    ->addText('name', 'Company Name')->add()
    ->addCollection('departments', $departmentForm, 'Departments')
    ->build();

// Data structure:
// [
//     'name' => 'Acme Corp',
//     'departments' => [
//         [
//             'name' => 'Engineering',
//             'employees' => [
//                 ['name' => 'John', 'position' => 'Developer'],
//                 ['name' => 'Jane', 'position' => 'Manager'],
//             ]
//         ],
//         [
//             'name' => 'Sales',
//             'employees' => [...]
//         ]
//     ]
// ]
```

---

## 🔧 Framework Integration

### Symfony Integration

```php
// config/bundles.php
return [
    // ...
    FormGenerator\Symfony\FormGeneratorBundle::class => ['all' => true],
];

// In controller
use FormGenerator\V2\Builder\FormBuilder;

class UserController extends AbstractController
{
    #[Route('/register', name: 'user_register')]
    public function register(Request $request): Response
    {
        $form = FormBuilder::create('registration')
            ->setCsrfTokenId('registration')
            ->setLocale($request->getLocale())

            ->addText('username', 'form.label.username')
                ->required()
                ->minLength(3)
                ->add()

            ->addEmail('email', 'form.label.email')
                ->required()
                ->add()

            ->addPassword('password', 'form.label.password')
                ->required()
                ->minLength(8)
                ->add()

            ->addSubmit('register', 'form.button.register')
            ->build();

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $data = $form->getData();
            // Process registration...

            return $this->redirectToRoute('user_success');
        }

        return $this->render('user/register.html.twig', [
            'form' => $form,
            'errors' => $form->getErrorList()->toArray(),
        ]);
    }
}
```

### Laravel Integration

```php
// config/app.php
'providers' => [
    // ...
    FormGenerator\Laravel\FormGeneratorServiceProvider::class,
],

// In controller
use FormGenerator\V2\Builder\FormBuilder;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function create()
    {
        $form = FormBuilder::create('contact_form')
            ->setCsrfTokenId('contact')
            ->setAction(route('contact.store'))

            ->addText('name', 'Name')
                ->required()
                ->add()

            ->addEmail('email', 'Email')
                ->required()
                ->add()

            ->addTextarea('message', 'Message')
                ->required()
                ->minLength(20)
                ->add()

            ->addSubmit('send', 'Send Message')
            ->build();

        return view('contact.create', ['form' => $form]);
    }

    public function store(Request $request)
    {
        $form = FormBuilder::create('contact_form')
            ->setCsrfTokenId('contact')
            // ... (same as above)
            ->build();

        $form->submit($request->all());

        if ($form->isValid()) {
            $data = $form->getData();
            // Process contact form...

            return redirect()->route('contact.success')
                           ->with('message', 'Thank you for contacting us!');
        }

        return back()
            ->withErrors($form->getErrorList()->toFlat())
            ->withInput();
    }
}
```

---

## 📚 Complete API Reference

### FormBuilder Core Methods

```php
// Creation
FormBuilder::create(string $name): FormBuilder
FormBuilder::setTranslator(TranslatorInterface $translator): void
FormBuilder::getTranslator(): ?TranslatorInterface

// Configuration
->setAction(string $action): self
->setMethod(string $method): self
->setEnctype(string $enctype): self
->setLocale(string $locale): self
->setDirection(TextDirection $direction): self
->setCsrfTokenId(string $tokenId): self
->setCsrfFieldName(string $fieldName): self

// Field Addition
->addText(string $name, string $label, array $options = []): InputBuilder
->addEmail(string $name, string $label, array $options = []): InputBuilder
->addPassword(string $name, string $label, array $options = []): InputBuilder
->addNumber(string $name, string $label, array $options = []): InputBuilder
->addTextarea(string $name, string $label, array $options = []): InputBuilder
->addSelect(string $name, string $label, array $options = []): InputBuilder
->addCheckbox(string $name, string $label, array $options = []): InputBuilder
->addRadio(string $name, string $label, array $options = []): InputBuilder
->addDate(string $name, string $label, array $options = []): InputBuilder
->addTime(string $name, string $label, array $options = []): InputBuilder
->addDateTime(string $name, string $label, array $options = []): InputBuilder
->addFile(string $name, string $label, array $options = []): InputBuilder
->addHidden(string $name, mixed $value = null): InputBuilder
->addSubmit(string $name, string $label = 'Submit'): InputBuilder

// Advanced Fields
->addField(string $name, string $type, array $options = []): InputBuilder
->add(string $name, Form|FormInterface $form): self
->addCollection(string $name, Form $prototype, string $label = ''): CollectionBuilder
->addNestedForm(string $name, string $label, callable $callback): self

// Building
->build(): Form
```

### Form Methods

```php
// Data Handling
->setData(array $data): self
->getData(): array
->submit(array $data): self

// Validation
->isValid(): bool
->validate(array $options = []): bool
->addConstraint(Callback $constraint): self
->getErrors(): array
->getErrorList(bool $deep = false): ErrorList
->addError(string $message, ErrorLevel $level, ?string $path): self

// Events
->addEventListener(string $eventName, callable $listener): self
->getEventDispatcher(): EventDispatcher

// Hierarchy
->setParent(?FormInterface $parent): self
->getParent(): ?FormInterface
->getRoot(): FormInterface
->isRoot(): bool

// Fields
->has(string $name): bool
->get(string $name): FormInterface
->remove(string $name): self
->all(): array

// State
->isSubmitted(): bool
->getState(): FormState

// CSRF
->getCsrfProtection(): CsrfProtection
```

### InputBuilder Chain Methods

```php
// Validation
->required(bool|array $options = true): self
->minLength(int $length, array $options = []): self
->maxLength(int $length, array $options = []): self
->min(float $value): self
->max(float $value): self
->email(): self
->url(): self
->regex(string $pattern): self
->pattern(string $pattern): self

// Attributes
->value(mixed $value): self
->placeholder(string $placeholder): self
->readonly(bool $readonly = true): self
->disabled(bool $disabled = true): self
->autofocus(bool $autofocus = true): self
->addAttribute(string $key, mixed $value): self

// Options (for select, radio, checkbox)
->options(array $options): self
->multiple(bool $multiple = true): self

// Styling
->addClass(string $class): self
->id(string $id): self
->label(string $label): self
->helpText(string $text): self

// Finalize
->add(): FormBuilder
```

---

## 🧪 Testing

FormGenerator includes **500+ comprehensive unit tests** covering all features:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific version tests
vendor/bin/phpunit tests/V2/Form/            # v2.4.0 - Nested Forms
vendor/bin/phpunit tests/V2/Type/            # v2.5.0 - Type System
vendor/bin/phpunit tests/V2/Validation/      # v2.7.0 - Validation
vendor/bin/phpunit tests/V2/Event/           # v2.8.0 - Events
vendor/bin/phpunit tests/V2/Error/           # v2.9.0 - Error Handling
vendor/bin/phpunit tests/V2/Translation/     # v3.0.0 - i18n
vendor/bin/phpunit tests/V2/Security/        # v3.0.0 - CSRF

# With coverage report
vendor/bin/phpunit --coverage-html coverage/
```

**Test Coverage:**
- ✅ 400+ unit tests
- ✅ 100+ integration tests
- ✅ Full API coverage
- ✅ Edge case handling
- ✅ Real-world scenarios
- ✅ PHPUnit 10+ compatible

---

## 📖 Examples

All examples are available in the [`/Examples/V2/`](/Examples/V2/) directory:

**Core Features**
- [Basic Usage](/Examples/V2/BasicUsage.php)
- [With Validation](/Examples/V2/WithValidation.php)
- [With Dependencies](/Examples/V2/WithDependencies.php)
- [With Sections](/Examples/V2/WithSections.php)
- [With Stepper](/Examples/V2/WithStepper.php)

**v2.4.0+ Features**
- [Nested Forms](/Examples/V2/WithNestedForms.php)
- [Form Collections](/Examples/V2/WithFormCollections.php)

**v2.5.0+ Features**
- [Custom Types](/Examples/V2/WithCustomTypes.php)
- [Type Extensions](/Examples/V2/WithTypeExtensions.php)

**v2.7.0+ Features**
- [Callback Validation](/Examples/V2/WithCallbackValidation.php)
- [Validation Groups](/Examples/V2/WithValidationGroups.php)

**v2.8.0+ Features**
- [Dynamic Form Modification](/Examples/V2/WithDynamicFormModification.php)

**v2.9.0+ Features**
- [Advanced Error Handling](/Examples/V2/WithAdvancedErrorHandling.php)

**v3.0.0+ Features**
- [Internationalization & CSRF](/Examples/V2/WithI18nAndCsrf.php)

**Framework Integration**
- [Doctrine Integration](/Examples/V2/WithDoctrine.php)
- [Laravel Integration](/Examples/V2/WithLaravel.php)
- [Symfony Integration](/Examples/Symfony/)

---

## 🔄 Version History

- **v3.0.0** (2025) - Internationalization & Auto CSRF ⭐ **Current**
- **v2.9.0** (2025) - Advanced Error Handling & Bubbling
- **v2.8.0** (2025) - Dynamic Form Modification
- **v2.7.0** (2024) - Cross-Field Validation & Groups
- **v2.5.0** (2024) - Type System & Extensions
- **v2.4.0** (2024) - Nested Forms & Collections
- **v2.3.1** (2024) - Data Transformation
- **v2.3.0** (2024) - Event-Driven Dependencies
- **v2.2.0** (2024) - Blade Template Engine
- **v2.1.0** (2024) - Laravel-Style Validation
- **v2.0.0** (2024) - Complete rewrite with chain pattern

See [CHANGELOG.md](CHANGELOG.md) for detailed changes.

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

**Areas for contribution:**
- Additional field types
- More translation loaders
- Framework integrations
- Theme development
- Documentation improvements
- Bug fixes and optimizations

---

## 📄 License

MIT License - see [LICENSE](LICENSE) for details.

---

## 👨‍💻 Author

**selcukmart**
- Email: admin@hostingdevi.com
- GitHub: [@selcukmart](https://github.com/selcukmart)

---

## 🌟 Support

If this project helped you, please:
- ⭐ Star it on GitHub
- 📢 Share it with others
- 🐛 Report issues
- 💡 Suggest features

---

**Made with ❤️ using modern PHP 8.1+**

**Production-ready • Secure by Default • Internationalized • Framework-agnostic**
