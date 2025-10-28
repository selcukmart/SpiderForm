# FormGenerator: Symfony Form Component Alternative - Complete Roadmap

**Current Version:** v2.3.1
**Target:** Full Symfony Form Component Alternative
**Timeline:** v2.4.0 → v3.0.0 (8 major releases)

---

## Executive Summary

This roadmap transforms FormGenerator into a **production-ready alternative** to Symfony Form Component by adding:
- **Nested Forms & Collections** (v2.4.0)
- **Type System & Extensions** (v2.5.0)
- **FormView & Stateful Objects** (v2.6.0)
- **Advanced Validation** (v2.7.0)
- **Dynamic Modification API** (v2.8.0)
- **Error Handling & Bubbling** (v2.9.0)
- **i18n & Auto CSRF** (v3.0.0)

---

## 🎯 Version 2.4.0: Nested Forms & Form Collections

**Priority:** CRITICAL
**Complexity:** High
**Estimated Effort:** 3-4 days

### Why This Feature?
Symfony's most powerful feature - ability to handle complex hierarchical data structures with embedded forms.

### Features to Implement

#### 1. **Nested Forms (Sub-forms)**
```php
// Example: User form with nested Address form
$form = FormBuilder::create('user')
    ->addText('name', 'Name')->add()

    // Embedded address form
    ->addNestedForm('address', 'Address', function(FormBuilder $subForm) {
        $subForm->addText('street', 'Street')->add()
                ->addText('city', 'City')->add()
                ->addText('zipcode', 'ZIP')->add();
    })

    ->addSubmit('save')
    ->build();

// Data structure: ['name' => 'John', 'address' => ['street' => '...', 'city' => '...', 'zipcode' => '...']]
```

#### 2. **Form Collections (Dynamic Multiple)**
```php
// Example: Invoice with multiple line items
$form = FormBuilder::create('invoice')
    ->addText('invoice_number')->add()

    // Collection of line items
    ->addCollection('items', 'Line Items', function(FormBuilder $itemForm) {
        $itemForm->addText('product_name', 'Product')->add()
                 ->addNumber('quantity', 'Qty')->add()
                 ->addNumber('price', 'Price')->add();
    })
    ->allowAdd()        // Allow adding new items
    ->allowDelete()     // Allow removing items
    ->min(1)            // Minimum 1 item required
    ->max(10)           // Maximum 10 items
    ->prototype(true)   // Generate JS prototype for new items
    ->add()

    ->build();

// Data: ['invoice_number' => 'INV-001', 'items' => [['product_name' => '...', 'quantity' => 5, 'price' => 100], ...]]
```

#### 3. **Deeply Nested Structures**
```php
// Example: Company → Departments → Employees
$form = FormBuilder::create('company')
    ->addText('company_name')->add()

    ->addCollection('departments', 'Departments', function($deptForm) {
        $deptForm->addText('dept_name')->add()

                 // Nested collection inside collection
                 ->addCollection('employees', 'Employees', function($empForm) {
                     $empForm->addText('name')->add()
                             ->addEmail('email')->add();
                 })
                 ->allowAdd()
                 ->add();
    })
    ->allowAdd()
    ->add()

    ->build();
```

### Technical Implementation

**New Classes:**
- `src/V2/Form/NestedFormBuilder.php` - Handle nested form structures
- `src/V2/Form/CollectionType.php` - Collection field type
- `src/V2/Form/FormHierarchy.php` - Manage parent-child relationships
- `src/V2/DataMapper/FormDataMapper.php` - Map nested data to forms

**Key Methods:**
- `FormBuilder::addNestedForm(string $name, string $label, callable $builder)`
- `FormBuilder::addCollection(string $name, string $label, callable $prototype)`
- `Form::getData()` - Return hierarchical array
- `Form::setData(array $data)` - Accept nested arrays

**Rendering:**
```twig
{# Nested form rendering #}
{{ form_row(form.name) }}

<fieldset>
    <legend>Address</legend>
    {{ form_row(form.address.street) }}
    {{ form_row(form.address.city) }}
    {{ form_row(form.address.zipcode) }}
</fieldset>

{# Collection rendering #}
<div data-prototype="{{ form_widget(form.items.vars.prototype)|e }}">
    {% for item in form.items %}
        {{ form_row(item) }}
    {% endfor %}
</div>
<button type="button" class="add-item">Add Item</button>
```

---

## 🎯 Version 2.5.0: Type System & Extension Mechanism

**Priority:** CRITICAL
**Complexity:** High
**Estimated Effort:** 3-4 days

### Why This Feature?
Symfony's type system allows custom field types with inheritance and reusable configurations.

### Features to Implement

#### 1. **Form Type Registry**
```php
// Register custom types
TypeRegistry::register('phone', PhoneType::class);
TypeRegistry::register('color_picker', ColorPickerType::class);
TypeRegistry::register('wysiwyg', WysiwygType::class);

// Use in forms
$form->add('phone', 'phone', ['country' => 'US']);
$form->add('bio', 'wysiwyg', ['toolbar' => 'full']);
```

#### 2. **Type Inheritance**
```php
// Custom type extending base type
class PhoneType extends AbstractFormType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::TEL)
                ->addAttribute('pattern', '^[0-9]{3}-[0-9]{3}-[0-9]{4}$')
                ->setPlaceholder('555-123-4567');
    }

    public function getParent(): ?string
    {
        return TextType::class; // Inherits from text
    }
}

// Advanced: Money type with currency selection
class MoneyType extends AbstractFormType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::NUMBER)
                ->addAttribute('step', '0.01')
                ->setPrefix($options['currency_symbol']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'currency' => 'USD',
            'currency_symbol' => '$',
            'decimal_places' => 2,
        ]);
    }
}
```

#### 3. **Type Extensions**
```php
// Extend existing types without modifying them
class HelpTextExtension extends AbstractTypeExtension
{
    public function extendType(string $type): array
    {
        return [TextType::class, EmailType::class]; // Apply to these types
    }

    public function buildField(InputBuilder $builder, array $options): void
    {
        if (isset($options['help'])) {
            $builder->setHelpText($options['help']);
        }
    }
}

// Usage
TypeExtensionRegistry::register(HelpTextExtension::class);

$form->addEmail('email')
     ->setOptions(['help' => 'We will never share your email'])
     ->add();
```

### Technical Implementation

**New Classes:**
- `src/V2/Type/TypeRegistry.php` - Central type registry
- `src/V2/Type/AbstractFormType.php` - Base for custom types ✅ (already exists)
- `src/V2/Type/TypeExtensionInterface.php` - Type extension interface
- `src/V2/Type/TypeExtensionRegistry.php` - Extension registry
- `src/V2/Type/OptionsResolver.php` - Symfony-like option resolver
- `src/V2/Type/Built-in types/`:
  - `TextType.php`
  - `EmailType.php`
  - `NumberType.php`
  - `DateType.php`
  - etc. (wrap existing InputType enum)

**Integration:**
- Modify `FormBuilder::add()` to accept type names
- Add type resolution logic
- Apply extensions during field building

---

## 🎯 Version 2.6.0: FormView & Stateful Form Object

**Priority:** HIGH
**Complexity:** Medium-High
**Estimated Effort:** 2-3 days

### Why This Feature?
Symfony's FormView separates data from presentation and provides a stateful form object for better control.

### Features to Implement

#### 1. **FormView Data Structure**
```php
// Symfony-style FormView
$formView = $form->createView();

// Access form structure
foreach ($formView->children as $child) {
    echo $child->vars['name'];        // Field name
    echo $child->vars['value'];       // Current value
    echo $child->vars['label'];       // Label
    echo $child->vars['errors'];      // Errors
    echo $child->vars['required'];    // Is required?
    echo $child->vars['attr'];        // HTML attributes
}

// Template rendering
echo $renderer->render($formView, 'form.twig');
```

#### 2. **Stateful Form Object**
```php
// Create form
$form = FormBuilder::create('user')
    ->addText('name')->add()
    ->addEmail('email')->add()
    ->build();

// Bind request data
$form->handleRequest($_POST);

// Check form state
if ($form->isSubmitted() && $form->isValid()) {
    $data = $form->getData();
    // Save to database
}

// Form states
$form->isSubmitted();  // Was form submitted?
$form->isValid();      // Passed validation?
$form->isEmpty();      // Has no data?
$form->isRoot();       // Is root form (not nested)?
$form->getErrors();    // Get all errors
$form->getRoot();      // Get root form
$form->getParent();    // Get parent form (for nested)
```

#### 3. **Form State Management**
```php
class Form
{
    private FormState $state;

    public function submit(array $data): void
    {
        $this->state = FormState::SUBMITTED;
        $this->data = $data;
        $this->validate();
    }

    public function isSubmitted(): bool
    {
        return $this->state === FormState::SUBMITTED;
    }

    public function isValid(): bool
    {
        return $this->isSubmitted() && empty($this->errors);
    }
}
```

### Technical Implementation

**New Classes:**
- `src/V2/Form/Form.php` - Stateful form object
- `src/V2/Form/FormView.php` - View representation
- `src/V2/Form/FormState.php` (enum) - Form states
- `src/V2/Form/FormInterface.php` - Form contract

**Key Features:**
- Separation of concerns (data vs presentation)
- Form lifecycle management
- Parent-child form relationships
- Error aggregation

---

## 🎯 Version 2.7.0: Cross-Field Validation & Validation Groups

**Priority:** HIGH
**Complexity:** Medium
**Estimated Effort:** 2-3 days

### Why This Feature?
Complex validation scenarios require field interdependencies and conditional validation.

### Features to Implement

#### 1. **Cross-Field Validation**
```php
// Password confirmation
$form->addPassword('password')->add()
     ->addPassword('password_confirm')->add()

     // Cross-field validation
     ->addConstraint(new Callback(function($data, ExecutionContext $context) {
         if ($data['password'] !== $data['password_confirm']) {
             $context->buildViolation('Passwords do not match')
                     ->atPath('password_confirm')
                     ->addViolation();
         }
     }))
     ->build();

// Date range validation
$form->addDate('start_date')->add()
     ->addDate('end_date')->add()

     ->addConstraint(new Callback(function($data, ExecutionContext $context) {
         if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
             $context->buildViolation('End date must be after start date')
                     ->atPath('end_date')
                     ->addViolation();
         }
     }))
     ->build();
```

#### 2. **Validation Groups**
```php
// Define validation groups
$form = FormBuilder::create('user')
    ->addText('username')
        ->required(['groups' => ['registration', 'profile']])
        ->minLength(3, ['groups' => ['registration']])
        ->add()

    ->addEmail('email')
        ->required(['groups' => ['registration']])
        ->add()

    ->addPassword('password')
        ->required(['groups' => ['registration', 'password_change']])
        ->minLength(8, ['groups' => ['registration', 'password_change']])
        ->add()

    ->build();

// Validate only registration group
$form->handleRequest($_POST);
$errors = $form->validate(['groups' => ['registration']]);

// Validate multiple groups
$errors = $form->validate(['groups' => ['registration', 'profile']]);
```

#### 3. **Sequential Validation Groups**
```php
// Stop validation if earlier group fails
$form->setValidationGroups(['First', 'Second', 'Third']);

// First group fails → skip Second and Third
```

### Technical Implementation

**New Classes:**
- `src/V2/Validation/Constraints/Callback.php` - Custom validation logic
- `src/V2/Validation/ExecutionContext.php` - Validation context
- `src/V2/Validation/GroupedValidation.php` - Group management
- `src/V2/Validation/SequentialGroupValidation.php` - Sequential groups

**Enhancements:**
- Modify validation rules to accept `groups` parameter
- Add form-level constraint support
- Cross-field validation context

---

## 🎯 Version 2.8.0: Dynamic Form Modification API

**Priority:** HIGH
**Complexity:** Medium-High
**Estimated Effort:** 2-3 days

### Why This Feature?
Forms need to change based on user input, loaded data, or business logic at runtime.

### Features to Implement

#### 1. **Event-Based Form Modification**
```php
use FormGenerator\V2\Event\FormEvents;

$form = FormBuilder::create('product')
    ->addSelect('category', 'Category')
        ->options(['physical' => 'Physical', 'digital' => 'Digital'])
        ->add()

    ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Modify form based on existing data
        if ($data && $data['category'] === 'physical') {
            $form->add('weight', 'number', ['label' => 'Weight (kg)']);
            $form->add('dimensions', 'text', ['label' => 'Dimensions']);
        }
    })

    ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Modify form based on submitted data
        if ($data['category'] === 'digital') {
            $form->add('download_link', 'url', ['label' => 'Download URL']);
            $form->add('file_size', 'number', ['label' => 'File Size (MB)']);
        }
    })

    ->build();
```

#### 2. **Dynamic Field Addition/Removal**
```php
$form = FormBuilder::create('user')
    ->addText('name')->add()
    ->build();

// Add fields dynamically
$form->add('email', 'email', ['label' => 'Email Address']);
$form->add('phone', 'tel', ['label' => 'Phone']);

// Remove fields
$form->remove('phone');

// Check field existence
if ($form->has('email')) {
    $emailField = $form->get('email');
}

// Get all fields
$fields = $form->all();
```

#### 3. **Conditional Field Building**
```php
$formBuilder = FormBuilder::create('user')
    ->addText('name')->add()
    ->addEmail('email')->add();

// Add fields conditionally
if ($user->isAdmin()) {
    $formBuilder->addCheckbox('is_super_admin', 'Super Admin')->add();
}

if ($enableNotifications) {
    $formBuilder->addCheckbox('email_notifications', 'Email Notifications')->add()
                ->addCheckbox('sms_notifications', 'SMS Notifications')->add();
}

$form = $formBuilder->build();
```

### Technical Implementation

**New Events:**
- `FormEvents::PRE_SET_DATA` - Before data is bound to form
- `FormEvents::POST_SET_DATA` - After data is bound
- `FormEvents::PRE_SUBMIT` - Before form submission processed
- `FormEvents::POST_SUBMIT` - After form submission processed

**New Methods:**
- `Form::add(string $name, string $type, array $options)` - Runtime field addition
- `Form::remove(string $name)` - Runtime field removal
- `Form::has(string $name)` - Check field existence
- `Form::get(string $name)` - Get field instance
- `Form::all()` - Get all fields

---

## 🎯 Version 2.9.0: Advanced Error Handling & Error Bubbling

**Priority:** MEDIUM-HIGH
**Complexity:** Medium
**Estimated Effort:** 2 days

### Why This Feature?
Professional error presentation and propagation for nested forms and collections.

### Features to Implement

#### 1. **Error Bubbling**
```php
// Error on nested field bubbles to parent
$form = FormBuilder::create('user')
    ->addNestedForm('address', function($addressForm) {
        $addressForm->addText('zipcode')
                    ->required()
                    ->regex('/^[0-9]{5}$/')
                    ->add();
    })
    ->setErrorBubbling(true) // Errors bubble to parent
    ->build();

// Validation error on address.zipcode also appears on user form
$errors = $form->getErrors(true); // Include child errors
```

#### 2. **Error Mapping**
```php
// Map error to different field
$form->addConstraint(new Callback(function($data, $context) {
    if ($data['password'] !== $data['password_confirm']) {
        // Error on form, but map to specific field
        $context->buildViolation('Passwords do not match')
                ->atPath('password_confirm')
                ->addViolation();
    }
}));
```

#### 3. **Error Presentation**
```php
// Get errors by severity
$form->getErrors(ErrorLevel::ERROR);
$form->getErrors(ErrorLevel::WARNING);

// Get errors deeply (including nested)
$form->getErrors(true); // recursive

// Format errors
$errors = $form->getErrorsAsArray();
/* [
    'name' => ['Name is required'],
    'address' => [
        'street' => ['Street is required'],
        'zipcode' => ['Invalid ZIP format']
    ]
] */

$errorsFlat = $form->getErrorsFlattened();
/* [
    'name' => 'Name is required',
    'address.street' => 'Street is required',
    'address.zipcode' => 'Invalid ZIP format'
] */
```

### Technical Implementation

**New Classes:**
- `src/V2/Error/FormError.php` - Error representation
- `src/V2/Error/ErrorList.php` - Error collection
- `src/V2/Error/ErrorLevel.php` (enum) - Error/Warning/Info
- `src/V2/Error/ErrorBubblingStrategy.php` - Bubble logic

---

## 🎯 Version 3.0.0: Complete i18n & Auto CSRF Protection

**Priority:** MEDIUM
**Complexity:** Medium-High
**Estimated Effort:** 3 days

### Why This Feature?
Production applications require multi-language support and automatic security.

### Features to Implement

#### 1. **Internationalization (i18n)**
```php
// Set form locale
$form = FormBuilder::create('user')
    ->setLocale('tr_TR')

    ->addText('name', 'form.label.name') // Translated label
        ->required()
        ->setErrorMessage('required', 'form.error.required') // Translated error
        ->add()

    ->addEmail('email', 'form.label.email')
        ->setErrorMessage('email', 'form.error.invalid_email')
        ->add()

    ->build();

// Translation files: translations/forms.tr_TR.yaml
// form:
//   label:
//     name: "İsim"
//     email: "E-posta"
//   error:
//     required: "Bu alan zorunludur"
//     invalid_email: "Geçersiz e-posta adresi"
```

#### 2. **Translation Component**
```php
// Register translator
$translator = new FormTranslator('translations/');
$translator->setLocale('tr_TR');
FormBuilder::setTranslator($translator);

// Support multiple translation backends
$translator = new SymfonyTranslatorAdapter($symfonyTranslator);
$translator = new LaravelTranslatorAdapter(app('translator'));
```

#### 3. **Auto CSRF Protection**
```php
// Enable automatic CSRF
$form = FormBuilder::create('user')
    ->enableCsrf() // Automatically add CSRF token
    ->setCsrfTokenId('user_form') // Custom token ID
    ->setCsrfFieldName('_token') // Custom field name

    ->addText('name')->add()
    ->build();

// CSRF token automatically:
// - Generated on form build
// - Rendered as hidden field
// - Validated on submission

// Manual CSRF handling still available
$form->handleRequest($_POST);
if ($form->isSubmitted()) {
    if (!$form->isCsrfTokenValid()) {
        throw new InvalidCsrfTokenException();
    }
}
```

### Technical Implementation

**New Classes:**
- `src/V2/Translation/FormTranslator.php` - Translation engine
- `src/V2/Translation/TranslatorInterface.php` - Translator contract
- `src/V2/Translation/Loader/YamlLoader.php` - YAML translation loader
- `src/V2/Translation/Loader/PhpLoader.php` - PHP array loader
- `src/V2/Security/CsrfProtection.php` - Auto CSRF management
- `src/V2/Security/CsrfTokenManager.php` - Token generation/validation

**Translation Format:**
```yaml
# translations/forms.en_US.yaml
forms:
  labels:
    name: "Name"
    email: "Email Address"
  errors:
    required: "This field is required"
    email: "Please enter a valid email address"
  buttons:
    submit: "Submit"
    cancel: "Cancel"
```

---

## 📊 Version Increment Strategy

| Version | Features | Breaking Changes | Migration Effort |
|---------|----------|------------------|------------------|
| **v2.4.0** | Nested Forms, Collections | None | Easy - New features only |
| **v2.5.0** | Type System | Minor - FormBuilder::add() enhanced | Easy - Backward compatible |
| **v2.6.0** | FormView, Stateful Forms | Medium - Rendering changed | Medium - Update templates |
| **v2.7.0** | Cross-field Validation | None | Easy - New validation only |
| **v2.8.0** | Dynamic Modification | None | Easy - New API methods |
| **v2.9.0** | Error Handling | None | Easy - Enhanced errors |
| **v3.0.0** | i18n, Auto CSRF | Major - Translation system | High - Add translations |

---

## 🎯 Success Metrics

Each version will be considered complete when:

1. **Core Implementation:** All features coded and tested
2. **Unit Tests:** 90%+ code coverage
3. **Documentation:** README_V2.md updated with examples
4. **Examples:** Working examples in `/Examples/V2/`
5. **Symfony Integration:** Compatible with Symfony Bundle
6. **Laravel Integration:** Compatible with Laravel ServiceProvider
7. **Backward Compatibility:** V2.3.1 code still works (except v3.0.0)
8. **Performance:** No significant performance regression

---

## 🚀 Development Workflow

For each version:

```bash
# 1. Create feature branch (already done)
git checkout -b claude/symfony-alternative-complete-011CUXv2h6nBNdGjJMXxRmow

# 2. Implement features
# - Write code
# - Write tests
# - Update documentation

# 3. Version bump
# - Update composer.json version
# - Update README.md version badge
# - Create CHANGELOG entry

# 4. Commit & push
git add .
git commit -m "feat: Implement v2.X.0 - Feature Name"
git push -u origin claude/symfony-alternative-complete-011CUXv2h6nBNdGjJMXxRmow

# 5. Create tag
git tag -a v2.X.0 -m "Version 2.X.0: Feature Name"
git push origin v2.X.0
```

---

## 📦 Final Deliverable (v3.0.0)

By v3.0.0, FormGenerator will be a **complete Symfony Form Component alternative** with:

✅ **All Core Features:**
- Nested forms & collections
- Type system & extensions
- FormView & stateful forms
- Cross-field validation
- Dynamic form modification
- Error bubbling & handling
- i18n & auto CSRF

✅ **Better Than Symfony In:**
- Lighter weight (no Symfony dependencies)
- Better field-level events (10+ events)
- Built-in form wizard/stepper
- Built-in dependency management
- Modern PHP 8.1+ codebase
- Easier learning curve

✅ **Equal To Symfony In:**
- Nested form handling
- Type system flexibility
- Validation capabilities
- Security features
- Framework integration

---

## 🎉 Let's Start!

**Ready to begin with v2.4.0: Nested Forms & Form Collections**

This is the most critical feature that will unlock the rest of the roadmap.

