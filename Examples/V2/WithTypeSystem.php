<?php

/**
 * FormGenerator V2.5.0 - Type System & Extensions Example
 *
 * This example demonstrates:
 * 1. Using built-in types with addField()
 * 2. Creating custom types
 * 3. Type inheritance
 * 4. Type extensions
 * 5. OptionsResolver for type configuration
 *
 * @since 2.5.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Type\{
    AbstractType,
    AbstractTypeExtension,
    OptionsResolver,
    TypeRegistry,
    TypeExtensionRegistry
};
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;

// ============================================================================
// EXAMPLE 1: Built-in Types with addField()
// ============================================================================

echo "<h2>Example 1: Built-in Types</h2>\n\n";

$form1 = FormBuilder::create('user_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    // Using type system instead of addText(), addEmail(), etc.
    ->addField('username', 'text', [
        'label' => 'Username',
        'required' => true,
        'minlength' => 3,
        'maxlength' => 20,
        'placeholder' => 'Enter username',
        'help' => 'Choose a unique username',
    ])
    ->add()

    ->addField('email', 'email', [
        'label' => 'Email Address',
        'required' => true,
        'placeholder' => 'you@example.com',
    ])
    ->add()

    ->addField('age', 'integer', [
        'label' => 'Age',
        'min' => 18,
        'max' => 100,
    ])
    ->add()

    ->addField('website', 'url', [
        'label' => 'Website',
        'placeholder' => 'https://example.com',
    ])
    ->add()

    ->addField('bio', 'textarea', [
        'label' => 'Biography',
        'rows' => 5,
        'placeholder' => 'Tell us about yourself...',
    ])
    ->add()

    ->buildForm();

echo "Built-in types available:\n";
print_r(FormBuilder::getRegisteredTypes());
echo "\n\n";

// ============================================================================
// EXAMPLE 2: Custom Type - PhoneType
// ============================================================================

echo "<h2>Example 2: Custom Type - PhoneType</h2>\n\n";

/**
 * Custom Phone Type
 */
class PhoneType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::TEL);

        // Apply country-specific pattern
        $pattern = match ($options['country']) {
            'US' => '^[0-9]{3}-[0-9]{3}-[0-9]{4}$',
            'UK' => '^[0-9]{4} [0-9]{6}$',
            'TR' => '^0[0-9]{3} [0-9]{3} [0-9]{4}$',
            default => null,
        };

        if ($pattern) {
            $builder->regex($pattern);
        }

        // Apply placeholder based on country
        $placeholder = match ($options['country']) {
            'US' => '555-123-4567',
            'UK' => '0207 123456',
            'TR' => '0555 123 4567',
            default => '',
        };

        if ($placeholder && !$options['placeholder']) {
            $builder->placeholder($placeholder);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'country' => 'US',
        ]);

        $resolver->setAllowedTypes('country', 'string');
        $resolver->setAllowedValues('country', ['US', 'UK', 'TR']);
    }

    public function getParent(): ?string
    {
        return 'tel'; // Inherits from TelType
    }
}

// Register the custom type
FormBuilder::registerType('phone', PhoneType::class);

// Use the custom type
$form2 = FormBuilder::create('contact_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addField('name', 'text', ['label' => 'Name', 'required' => true])
    ->add()

    ->addField('phone_us', 'phone', [
        'label' => 'US Phone',
        'country' => 'US',
        'required' => true,
    ])
    ->add()

    ->addField('phone_uk', 'phone', [
        'label' => 'UK Phone',
        'country' => 'UK',
    ])
    ->add()

    ->addField('phone_tr', 'phone', [
        'label' => 'Turkish Phone',
        'country' => 'TR',
    ])
    ->add()

    ->buildForm();

echo "Custom PhoneType registered and used!\n";
echo "Phone fields have country-specific validation patterns.\n\n";

// ============================================================================
// EXAMPLE 3: Advanced Custom Type - MoneyType
// ============================================================================

echo "<h2>Example 3: Advanced Custom Type - MoneyType</h2>\n\n";

/**
 * Money Type with Currency Support
 */
class MoneyType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::NUMBER);

        // Set step for decimal places
        $step = 1 / (10 ** $options['decimal_places']);
        $builder->addAttribute('step', (string) $step);

        // Set min to 0 for money
        $builder->min(0);

        // Add currency symbol as prefix/suffix
        if ($options['currency_symbol_position'] === 'before') {
            // Add as prefix in wrapper or use JS
            $builder->addWrapperAttribute('data-prefix', $options['currency_symbol']);
        } else {
            $builder->addWrapperAttribute('data-suffix', $options['currency_symbol']);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'currency' => 'USD',
            'currency_symbol' => '$',
            'currency_symbol_position' => 'before',
            'decimal_places' => 2,
        ]);

        $resolver->setAllowedTypes('currency', 'string');
        $resolver->setAllowedTypes('currency_symbol', 'string');
        $resolver->setAllowedTypes('currency_symbol_position', 'string');
        $resolver->setAllowedTypes('decimal_places', 'int');

        $resolver->setAllowedValues('currency_symbol_position', ['before', 'after']);

        // Normalizer: Set currency symbol based on currency code
        $resolver->setNormalizer('currency_symbol', function ($options, $value) {
            if ($value !== '$') {
                return $value; // Custom symbol provided
            }

            // Auto-detect symbol from currency
            return match ($options['currency'] ?? 'USD') {
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                'TRY' => '₺',
                'JPY' => '¥',
                default => $value,
            };
        });
    }

    public function getParent(): ?string
    {
        return 'number';
    }
}

// Register MoneyType
FormBuilder::registerType('money', MoneyType::class);

// Use MoneyType
$form3 = FormBuilder::create('product_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addField('product_name', 'text', ['label' => 'Product', 'required' => true])
    ->add()

    ->addField('price_usd', 'money', [
        'label' => 'Price (USD)',
        'currency' => 'USD',
        'required' => true,
    ])
    ->add()

    ->addField('price_eur', 'money', [
        'label' => 'Price (EUR)',
        'currency' => 'EUR',
    ])
    ->add()

    ->addField('price_try', 'money', [
        'label' => 'Price (TRY)',
        'currency' => 'TRY',
        'decimal_places' => 2,
    ])
    ->add()

    ->buildForm();

echo "MoneyType with auto currency symbol detection!\n";
echo "  USD → $ | EUR → € | GBP → £ | TRY → ₺\n\n";

// ============================================================================
// EXAMPLE 4: Type Extensions
// ============================================================================

echo "<h2>Example 4: Type Extensions</h2>\n\n";

/**
 * Icon Extension - Adds icon support to text-based fields
 */
class IconExtension extends AbstractTypeExtension
{
    public function extendType(): string|array
    {
        return ['text', 'email', 'password', 'tel', 'url']; // Apply to these types
    }

    public function buildField(InputBuilder $builder, array $options): void
    {
        if (isset($options['icon'])) {
            $builder->addAttribute('data-icon', $options['icon']);
            $builder->addWrapperAttribute('class', 'input-with-icon');
        }

        if (isset($options['icon_position'])) {
            $builder->addWrapperAttribute('data-icon-position', $options['icon_position']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['icon', 'icon_position']);
        $resolver->setAllowedTypes('icon', 'string');
        $resolver->setAllowedTypes('icon_position', 'string');
        $resolver->setDefaults(['icon_position' => 'left']);
        $resolver->setAllowedValues('icon_position', ['left', 'right']);
    }

    public function finishView(InputBuilder $builder, array $options): void
    {
        // Could modify the view here after all building is done
    }
}

/**
 * Tooltip Extension - Adds tooltip support to all fields
 */
class TooltipExtension extends AbstractTypeExtension
{
    public function extendType(): string|array
    {
        return ['text', 'email', 'number', 'select', 'textarea']; // Common types
    }

    public function buildField(InputBuilder $builder, array $options): void
    {
        if (isset($options['tooltip'])) {
            $builder->addAttribute('data-tooltip', $options['tooltip']);
            $builder->addAttribute('data-toggle', 'tooltip');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('tooltip');
        $resolver->setAllowedTypes('tooltip', 'string');
    }
}

// Register extensions
FormBuilder::registerTypeExtension(new IconExtension());
FormBuilder::registerTypeExtension(new TooltipExtension());

// Use extended types
$form4 = FormBuilder::create('enhanced_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addField('username', 'text', [
        'label' => 'Username',
        'icon' => 'user', // Added by IconExtension
        'icon_position' => 'left',
        'tooltip' => 'Enter your unique username', // Added by TooltipExtension
        'required' => true,
    ])
    ->add()

    ->addField('email', 'email', [
        'label' => 'Email',
        'icon' => 'envelope',
        'tooltip' => 'We will never share your email',
        'required' => true,
    ])
    ->add()

    ->addField('password', 'password', [
        'label' => 'Password',
        'icon' => 'lock',
        'icon_position' => 'right',
        'tooltip' => 'Minimum 8 characters',
        'required' => true,
    ])
    ->add()

    ->buildForm();

echo "Type Extensions applied!\n";
echo "  - IconExtension adds icon support to text fields\n";
echo "  - TooltipExtension adds tooltip support to all fields\n\n";

// ============================================================================
// EXAMPLE 5: Type Inheritance Hierarchy
// ============================================================================

echo "<h2>Example 5: Type Inheritance</h2>\n\n";

echo "Type Inheritance Hierarchy:\n";
echo "  text (base)\n";
echo "    ├─ email (extends text)\n";
echo "    ├─ password (extends text)\n";
echo "    ├─ tel (extends text)\n";
echo "    │   └─ phone (extends tel) ← Custom!\n";
echo "    └─ url (extends text)\n";
echo "\n";
echo "  number (base)\n";
echo "    ├─ integer (extends number)\n";
echo "    └─ money (extends number) ← Custom!\n";
echo "\n";

// Check type hierarchy
$phoneHierarchy = TypeRegistry::getTypeHierarchy('phone');
echo "PhoneType hierarchy:\n";
foreach ($phoneHierarchy as $type) {
    echo "  - " . $type->getName() . "\n";
}
echo "\n";

// ============================================================================
// EXAMPLE 6: OptionsResolver in Action
// ============================================================================

echo "<h2>Example 6: OptionsResolver Validation</h2>\n\n";

try {
    // This will throw an exception - invalid option
    $form5 = FormBuilder::create('test')
        ->addField('test', 'text', [
            'label' => 'Test',
            'invalid_option' => 'value', // Not defined!
        ]);
} catch (\InvalidArgumentException $e) {
    echo "❌ OptionsResolver caught invalid option:\n";
    echo "   " . $e->getMessage() . "\n\n";
}

try {
    // This will throw - wrong type
    $form6 = FormBuilder::create('test2')
        ->addField('test', 'text', [
            'required' => 'yes', // Should be bool!
        ]);
} catch (\InvalidArgumentException $e) {
    echo "❌ OptionsResolver caught type mismatch:\n";
    echo "   " . $e->getMessage() . "\n\n";
}

try {
    // This will throw - invalid enum value
    $form7 = FormBuilder::create('test3')
        ->addField('phone', 'phone', [
            'country' => 'FR', // Not in allowed values!
        ]);
} catch (\InvalidArgumentException $e) {
    echo "❌ OptionsResolver caught invalid value:\n";
    echo "   " . $e->getMessage() . "\n\n";
}

// ============================================================================
// OUTPUT
// ============================================================================

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ FormGenerator V2.5.0 - Type System & Extensions\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";
echo "New Features:\n";
echo "  • Type Registry for custom types\n";
echo "  • 18 built-in types (text, email, number, select, etc.)\n";
echo "  • Type inheritance (extend existing types)\n";
echo "  • Type extensions (enhance types without modifying)\n";
echo "  • OptionsResolver (Symfony-like option validation)\n";
echo "  • addField() method for type-based field creation\n";
echo "\n";
echo "Custom Types Created:\n";
echo "  • PhoneType (with country-specific validation)\n";
echo "  • MoneyType (with currency support)\n";
echo "\n";
echo "Extensions Created:\n";
echo "  • IconExtension (adds icon support)\n";
echo "  • TooltipExtension (adds tooltip support)\n";
echo "\n";
echo "This brings FormGenerator to Symfony Form Component parity!\n";
echo "\n";
