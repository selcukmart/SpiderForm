<?php

/**
 * FormGenerator V2.4.0 - Nested Forms & Collections Example
 *
 * This example demonstrates:
 * 1. Nested forms (sub-forms for hierarchical data)
 * 2. Form collections (dynamic lists of forms)
 * 3. Stateful Form objects
 * 4. Form validation
 * 5. Data mapping
 *
 * @since 2.4.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\DataMapper\FormDataMapper;

// ============================================================================
// EXAMPLE 1: Simple Nested Form
// ============================================================================

echo "<h2>Example 1: User with Nested Address</h2>\n\n";

// Sample data with nested structure
$userData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'zipcode' => '10001',
    ],
];

// Build form with nested address form
$userForm = FormBuilder::create('user_form')
    ->setAction('/users/save')
    ->setMethod('POST')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())
    ->setData($userData)

    // Main user fields
    ->addText('name', 'Full Name')
        ->required()
        ->add()

    ->addEmail('email', 'Email Address')
        ->required()
        ->add()

    // Nested address form
    ->addNestedForm('address', 'Address', function(FormBuilder $addressForm) {
        $addressForm
            ->addText('street', 'Street Address')
                ->required()
                ->add()

            ->addText('city', 'City')
                ->required()
                ->add()

            ->addSelect('state', 'State')
                ->options([
                    'NY' => 'New York',
                    'CA' => 'California',
                    'TX' => 'Texas',
                ])
                ->add()

            ->addText('zipcode', 'ZIP Code')
                ->required()
                ->regex('/^[0-9]{5}$/')
                ->add();
    })

    ->addSubmit('save', 'Save User')
    ->buildForm();

// Simulate form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userForm->handleRequest($_POST);

    if ($userForm->isSubmitted() && $userForm->isValid()) {
        $savedData = $userForm->getData();
        echo "<div class='alert alert-success'>User saved successfully!</div>\n";
        echo "<pre>" . print_r($savedData, true) . "</pre>\n";
    } else {
        echo "<div class='alert alert-danger'>Form has errors:</div>\n";
        echo "<pre>" . print_r($userForm->getErrors(true), true) . "</pre>\n";
    }
}

// Render form
// echo $userForm->render();

echo "\n\n";

// ============================================================================
// EXAMPLE 2: Form with Collection
// ============================================================================

echo "<h2>Example 2: Invoice with Line Items Collection</h2>\n\n";

$invoiceData = [
    'invoice_number' => 'INV-001',
    'customer_name' => 'Acme Corp',
    'items' => [
        ['product' => 'Widget A', 'quantity' => 5, 'price' => 19.99],
        ['product' => 'Widget B', 'quantity' => 3, 'price' => 29.99],
        ['product' => 'Widget C', 'quantity' => 10, 'price' => 9.99],
    ],
];

$invoiceForm = FormBuilder::create('invoice_form')
    ->setAction('/invoices/save')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())
    ->setData($invoiceData)

    ->addText('invoice_number', 'Invoice Number')
        ->required()
        ->add()

    ->addText('customer_name', 'Customer Name')
        ->required()
        ->add()

    // Collection of line items
    ->addCollection('items', 'Line Items', function(FormBuilder $itemForm) {
        $itemForm
            ->addText('product', 'Product Name')
                ->required()
                ->add()

            ->addNumber('quantity', 'Quantity')
                ->required()
                ->min(1)
                ->add()

            ->addNumber('price', 'Unit Price')
                ->required()
                ->min(0.01)
                ->add();
    })
    ->allowAdd()      // Allow adding new items
    ->allowDelete()   // Allow deleting items
    ->min(1)          // At least 1 item required
    ->max(20)         // Maximum 20 items
    ->add()           // Return to main form builder

    ->addSubmit('save', 'Save Invoice')
    ->buildForm();

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_form'])) {
    $invoiceForm->handleRequest($_POST);

    if ($invoiceForm->isSubmitted() && $invoiceForm->isValid()) {
        $savedInvoice = $invoiceForm->getData();
        echo "<div class='alert alert-success'>Invoice saved!</div>\n";
        echo "<pre>" . print_r($savedInvoice, true) . "</pre>\n";

        // Calculate total
        $total = 0;
        foreach ($savedInvoice['items'] as $item) {
            $total += $item['quantity'] * $item['price'];
        }
        echo "<p><strong>Total: $" . number_format($total, 2) . "</strong></p>\n";
    }
}

// echo $invoiceForm->render();

echo "\n\n";

// ============================================================================
// EXAMPLE 3: Deeply Nested Structure (Company → Departments → Employees)
// ============================================================================

echo "<h2>Example 3: Deeply Nested - Company with Departments and Employees</h2>\n\n";

$companyData = [
    'company_name' => 'TechCorp Inc',
    'founded_year' => 2010,
    'departments' => [
        [
            'dept_name' => 'Engineering',
            'budget' => 500000,
            'employees' => [
                ['name' => 'Alice Smith', 'email' => 'alice@techcorp.com', 'salary' => 120000],
                ['name' => 'Bob Jones', 'email' => 'bob@techcorp.com', 'salary' => 110000],
            ],
        ],
        [
            'dept_name' => 'Marketing',
            'budget' => 300000,
            'employees' => [
                ['name' => 'Carol White', 'email' => 'carol@techcorp.com', 'salary' => 90000],
            ],
        ],
    ],
];

$companyForm = FormBuilder::create('company_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())
    ->setData($companyData)

    ->addText('company_name', 'Company Name')
        ->required()
        ->add()

    ->addNumber('founded_year', 'Founded Year')
        ->required()
        ->add()

    // Collection of departments
    ->addCollection('departments', 'Departments', function(FormBuilder $deptForm) {
        $deptForm
            ->addText('dept_name', 'Department Name')
                ->required()
                ->add()

            ->addNumber('budget', 'Annual Budget')
                ->required()
                ->min(0)
                ->add()

            // Nested collection: employees within departments
            ->addCollection('employees', 'Employees', function(FormBuilder $empForm) {
                $empForm
                    ->addText('name', 'Employee Name')
                        ->required()
                        ->add()

                    ->addEmail('email', 'Email')
                        ->required()
                        ->add()

                    ->addNumber('salary', 'Salary')
                        ->required()
                        ->min(0)
                        ->add();
            })
            ->allowAdd()
            ->allowDelete()
            ->min(1)
            ->add(); // Back to department form
    })
    ->allowAdd()
    ->allowDelete()
    ->min(1)
    ->max(10)
    ->add() // Back to main form

    ->addSubmit('save', 'Save Company')
    ->buildForm();

echo "<pre>Company Form Structure:\n";
echo "  company_name: text\n";
echo "  founded_year: number\n";
echo "  departments: collection\n";
echo "    └─ dept_name: text\n";
echo "    └─ budget: number\n";
echo "    └─ employees: collection (nested!)\n";
echo "         └─ name: text\n";
echo "         └─ email: email\n";
echo "         └─ salary: number\n";
echo "</pre>\n\n";

// ============================================================================
// EXAMPLE 4: Stateful Form Operations
// ============================================================================

echo "<h2>Example 4: Stateful Form Operations</h2>\n\n";

$form = FormBuilder::create('demo_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addText('username', 'Username')
        ->required()
        ->minLength(3)
        ->add()

    ->addPassword('password', 'Password')
        ->required()
        ->minLength(8)
        ->add()

    ->buildForm();

// Form states
echo "Form state after build: " . $form->getState()->value . "\n";
echo "Is submitted? " . ($form->isSubmitted() ? 'Yes' : 'No') . "\n";
echo "Is valid? " . ($form->isValid() ? 'Yes' : 'No') . "\n";
echo "Is empty? " . ($form->isEmpty() ? 'Yes' : 'No') . "\n";
echo "\n";

// Submit form with data
$form->submit(['username' => 'john', 'password' => 'securepass123']);

echo "Form state after submit: " . $form->getState()->value . "\n";
echo "Is submitted? " . ($form->isSubmitted() ? 'Yes' : 'No') . "\n";
echo "Is valid? " . ($form->isValid() ? 'Yes' : 'No') . "\n";
echo "Data: " . json_encode($form->getData()) . "\n";
echo "\n";

// Submit with invalid data
$form2 = FormBuilder::create('demo_form2')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())
    ->addText('username')->required()->minLength(3)->add()
    ->buildForm();

$form2->submit(['username' => 'ab']); // Too short

echo "Invalid form state: " . $form2->getState()->value . "\n";
echo "Has errors? " . ($form2->hasErrors() ? 'Yes' : 'No') . "\n";
echo "Errors: " . json_encode($form2->getErrors()) . "\n";
echo "\n";

// ============================================================================
// EXAMPLE 5: FormView - Separation of Data and Presentation
// ============================================================================

echo "<h2>Example 5: FormView - Data vs Presentation</h2>\n\n";

$profileForm = FormBuilder::create('profile')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())
    ->setData(['name' => 'John', 'bio' => 'Developer'])

    ->addText('name', 'Name')
        ->required()
        ->add()

    ->addTextarea('bio', 'Biography')
        ->add()

    ->buildForm();

// Create view
$view = $profileForm->createView();

echo "Form View Variables:\n";
echo "  Name: " . $view->vars['name'] . "\n";
echo "  Submitted: " . ($view->vars['submitted'] ? 'Yes' : 'No') . "\n";
echo "  Valid: " . ($view->vars['valid'] ? 'Yes' : 'No') . "\n";
echo "  Compound: " . ($view->vars['compound'] ? 'Yes' : 'No') . "\n";
echo "  Children count: " . count($view->children) . "\n";
echo "\n";

echo "Child fields:\n";
foreach ($view->children as $childName => $childView) {
    echo "  - {$childName}: {$childView->vars['label']}\n";
}
echo "\n";

// ============================================================================
// EXAMPLE 6: FormDataMapper - Object Mapping
// ============================================================================

echo "<h2>Example 6: FormDataMapper - Object Mapping</h2>\n\n";

// Sample DTO class
class UserDTO
{
    public ?string $name = null;
    public ?string $email = null;
    public ?AddressDTO $address = null;
}

class AddressDTO
{
    public ?string $city = null;
    public ?string $zipcode = null;
}

$mapper = new FormDataMapper();

// Create object with data
$user = new UserDTO();
$user->name = 'Jane Doe';
$user->email = 'jane@example.com';
$user->address = new AddressDTO();
$user->address->city = 'Los Angeles';
$user->address->zipcode = '90001';

echo "Original object:\n";
echo "  Name: {$user->name}\n";
echo "  Email: {$user->email}\n";
echo "  City: {$user->address->city}\n";
echo "  ZIP: {$user->address->zipcode}\n";
echo "\n";

// Convert object to array
$arrayData = $mapper->objectToArray($user);
echo "Converted to array:\n";
print_r($arrayData);
echo "\n";

// Map back to object
$newUser = new UserDTO();
$newUser->address = new AddressDTO();
$mapper->arrayToObject([
    'name' => 'John Updated',
    'email' => 'john.updated@example.com',
    'address' => ['city' => 'San Francisco', 'zipcode' => '94102'],
], $newUser);

echo "Mapped back to object:\n";
echo "  Name: {$newUser->name}\n";
echo "  Email: {$newUser->email}\n";
echo "  City: {$newUser->address->city}\n";
echo "  ZIP: {$newUser->address->zipcode}\n";
echo "\n";

// ============================================================================
// OUTPUT
// ============================================================================

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ FormGenerator V2.4.0 - Nested Forms & Collections\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";
echo "New Features:\n";
echo "  • Nested forms for hierarchical data\n";
echo "  • Form collections with add/delete support\n";
echo "  • Stateful Form objects (isSubmitted, isValid, etc.)\n";
echo "  • FormView for presentation layer separation\n";
echo "  • FormDataMapper for object/array conversion\n";
echo "  • Full parent-child form relationships\n";
echo "  • Deeply nested structures (collections in collections)\n";
echo "\n";
echo "This is a MAJOR step towards Symfony Form Component parity!\n";
echo "\n";
