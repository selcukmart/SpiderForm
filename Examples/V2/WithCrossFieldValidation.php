<?php

/**
 * FormGenerator V2.7.0 - Cross-Field Validation & Validation Groups Example
 *
 * This example demonstrates:
 * 1. Cross-field validation using Callback constraint
 * 2. Validation groups for conditional validation
 * 3. ExecutionContext for building violations
 * 4. Complex validation scenarios
 *
 * @since 2.7.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Validation\Constraints\Callback;
use FormGenerator\V2\Validation\ExecutionContext;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;

// ============================================================================
// EXAMPLE 1: Password Confirmation (Basic Cross-Field Validation)
// ============================================================================

echo "<h2>Example 1: Password Confirmation</h2>\n\n";

$passwordForm = FormBuilder::create('password_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addPassword('password', 'Password')
        ->required()
        ->minLength(8)
        ->add()

    ->addPassword('password_confirm', 'Confirm Password')
        ->required()
        ->add()

    // Cross-field validation
    ->addConstraint(new Callback(function(array $data, ExecutionContext $context) {
        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
            $context->buildViolation('Passwords do not match')
                    ->atPath('password_confirm')
                    ->addViolation();
        }
    }))

    ->buildForm();

// Simulate submission with mismatched passwords
$testData1 = [
    'password' => 'secret123',
    'password_confirm' => 'secret456',
];

$errors1 = $passwordForm->validateWithConstraints($testData1);

echo "Test: Mismatched passwords\n";
echo "Errors: " . json_encode($errors1, JSON_PRETTY_PRINT) . "\n\n";

// Test with matching passwords
$testData2 = [
    'password' => 'secret123',
    'password_confirm' => 'secret123',
];

$errors2 = $passwordForm->validateWithConstraints($testData2);
echo "Test: Matching passwords\n";
echo "Errors: " . (empty($errors2) ? 'None - Valid!' : json_encode($errors2)) . "\n\n";

// ============================================================================
// EXAMPLE 2: Date Range Validation
// ============================================================================

echo "<h2>Example 2: Date Range Validation</h2>\n\n";

$bookingForm = FormBuilder::create('booking_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addDate('check_in', 'Check-in Date')
        ->required()
        ->add()

    ->addDate('check_out', 'Check-out Date')
        ->required()
        ->add()

    ->addNumber('guests', 'Number of Guests')
        ->required()
        ->min(1)
        ->max(10)
        ->add()

    // Date range validation
    ->addConstraint(new Callback(function(array $data, ExecutionContext $context) {
        $checkIn = strtotime($data['check_in'] ?? '');
        $checkOut = strtotime($data['check_out'] ?? '');

        if ($checkIn && $checkOut && $checkOut <= $checkIn) {
            $context->buildViolation('Check-out date must be after check-in date')
                    ->atPath('check_out')
                    ->addViolation();
        }

        // Minimum stay validation
        if ($checkIn && $checkOut) {
            $days = ($checkOut - $checkIn) / 86400;
            if ($days < 1) {
                $context->buildViolation('Minimum stay is 1 night')
                        ->atPath('check_out')
                        ->addViolation();
            }
        }
    }))

    ->buildForm();

// Test invalid date range
$testBooking1 = [
    'check_in' => '2025-11-01',
    'check_out' => '2025-10-30', // Before check-in!
    'guests' => 2,
];

$errors3 = $bookingForm->validateWithConstraints($testBooking1);
echo "Test: Invalid date range\n";
echo "Errors: " . json_encode($errors3, JSON_PRETTY_PRINT) . "\n\n";

// ============================================================================
// EXAMPLE 3: Complex Business Rules
// ============================================================================

echo "<h2>Example 3: Complex Business Rules</h2>\n\n";

$orderForm = FormBuilder::create('order_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addNumber('quantity', 'Quantity')
        ->required()
        ->min(1)
        ->add()

    ->addNumber('unit_price', 'Unit Price')
        ->required()
        ->min(0.01)
        ->add()

    ->addNumber('discount_percent', 'Discount %')
        ->min(0)
        ->max(100)
        ->add()

    ->addNumber('total', 'Total Amount')
        ->required()
        ->min(0)
        ->add()

    // Business rule: Total must match calculation
    ->addConstraint(new Callback(function(array $data, ExecutionContext $context) {
        $quantity = (float) ($data['quantity'] ?? 0);
        $unitPrice = (float) ($data['unit_price'] ?? 0);
        $discount = (float) ($data['discount_percent'] ?? 0);
        $submittedTotal = (float) ($data['total'] ?? 0);

        $subtotal = $quantity * $unitPrice;
        $discountAmount = $subtotal * ($discount / 100);
        $expectedTotal = $subtotal - $discountAmount;

        if (abs($expectedTotal - $submittedTotal) > 0.01) {
            $context->buildViolation('Total amount does not match calculation')
                    ->atPath('total')
                    ->setParameter('expected', number_format($expectedTotal, 2))
                    ->setParameter('actual', number_format($submittedTotal, 2))
                    ->addViolation();
        }
    }))

    ->buildForm();

$testOrder = [
    'quantity' => 5,
    'unit_price' => 100.00,
    'discount_percent' => 10,
    'total' => 400.00, // Should be 450 (500 - 50)
];

$errors4 = $orderForm->validateWithConstraints($testOrder);
echo "Test: Invalid total calculation\n";
echo "Errors: " . json_encode($errors4, JSON_PRETTY_PRINT) . "\n\n";

// ============================================================================
// EXAMPLE 4: Validation Groups
// ============================================================================

echo "<h2>Example 4: Validation Groups</h2>\n\n";

$userForm = FormBuilder::create('user_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addText('username', 'Username')
        ->required(['groups' => ['registration', 'profile']])
        ->minLength(3, ['groups' => ['registration']])
        ->add()

    ->addEmail('email', 'Email')
        ->required(['groups' => ['registration']])
        ->add()

    ->addPassword('password', 'Password')
        ->required(['groups' => ['registration', 'password_change']])
        ->minLength(8, ['groups' => ['registration', 'password_change']])
        ->add()

    ->addPassword('current_password', 'Current Password')
        ->required(['groups' => ['password_change']])
        ->add()

    ->addText('bio', 'Biography')
        ->maxLength(500, ['groups' => ['profile']])
        ->add()

    // Group-specific constraint
    ->addConstraint(new Callback(function(array $data, ExecutionContext $context) {
        // Only validate password match for password_change group
        if (isset($data['password']) && isset($data['current_password'])) {
            if ($data['password'] === $data['current_password']) {
                $context->buildViolation('New password must be different from current password')
                        ->atPath('password')
                        ->addViolation();
            }
        }
    }, ['password_change'])) // Only applies to password_change group

    ->buildForm();

// Scenario 1: Registration (validate registration group)
echo "Scenario 1: Registration Validation\n";
$registrationData = [
    'username' => 'jo', // Too short (min 3 for registration)
    'email' => 'invalid', // Invalid email
    'password' => 'sec', // Too short
];

$regErrors = $userForm->validateWithConstraints($registrationData, ['registration']);
echo "Registration errors: " . json_encode($regErrors, JSON_PRETTY_PRINT) . "\n\n";

// Scenario 2: Profile Update (validate profile group)
echo "Scenario 2: Profile Update Validation\n";
$profileData = [
    'username' => 'jo', // Too short NOT checked (min 3 only in registration group)
    'bio' => str_repeat('a', 600), // Too long
];

$profileErrors = $userForm->validateWithConstraints($profileData, ['profile']);
echo "Profile errors: " . json_encode($profileErrors, JSON_PRETTY_PRINT) . "\n\n";

// Scenario 3: Password Change (validate password_change group)
echo "Scenario 3: Password Change Validation\n";
$passwordData = [
    'current_password' => 'oldpass123',
    'password' => 'oldpass123', // Same as current!
];

$pwdErrors = $userForm->validateWithConstraints($passwordData, ['password_change']);
echo "Password change errors: " . json_encode($pwdErrors, JSON_PRETTY_PRINT) . "\n\n";

// ============================================================================
// EXAMPLE 5: Multiple Cross-Field Validations
// ============================================================================

echo "<h2>Example 5: Multiple Cross-Field Validations</h2>\n\n";

$paymentForm = FormBuilder::create('payment_form')
    ->setRenderer(new TwigRenderer())
    ->setTheme(new Bootstrap5Theme())

    ->addSelect('payment_method', 'Payment Method')
        ->options(['card' => 'Credit Card', 'paypal' => 'PayPal', 'bank' => 'Bank Transfer'])
        ->required()
        ->add()

    ->addText('card_number', 'Card Number')
        ->add()

    ->addText('paypal_email', 'PayPal Email')
        ->add()

    ->addText('bank_account', 'Bank Account')
        ->add()

    // Validate: Card number required for card payment
    ->addConstraint(new Callback(function(array $data, ExecutionContext $context) {
        if (($data['payment_method'] ?? '') === 'card') {
            if (empty($data['card_number'])) {
                $context->buildViolation('Card number is required for credit card payment')
                        ->atPath('card_number')
                        ->addViolation();
            }
        }
    }))

    // Validate: PayPal email required for PayPal
    ->addConstraint(new Callback(function(array $data, ExecutionContext $context) {
        if (($data['payment_method'] ?? '') === 'paypal') {
            if (empty($data['paypal_email'])) {
                $context->buildViolation('PayPal email is required')
                        ->atPath('paypal_email')
                        ->addViolation();
            } elseif (!filter_var($data['paypal_email'], FILTER_VALIDATE_EMAIL)) {
                $context->buildViolation('Invalid PayPal email address')
                        ->atPath('paypal_email')
                        ->addViolation();
            }
        }
    }))

    // Validate: Bank account required for bank transfer
    ->addConstraint(new Callback(function(array $data, ExecutionContext $context) {
        if (($data['payment_method'] ?? '') === 'bank') {
            if (empty($data['bank_account'])) {
                $context->buildViolation('Bank account is required for bank transfer')
                        ->atPath('bank_account')
                        ->addViolation();
            }
        }
    }))

    ->buildForm();

// Test: Card payment without card number
$payment1 = ['payment_method' => 'card'];
$paymentErrors1 = $paymentForm->validateWithConstraints($payment1);
echo "Test: Card payment without card number\n";
echo "Errors: " . json_encode($paymentErrors1, JSON_PRETTY_PRINT) . "\n\n";

// Test: PayPal with invalid email
$payment2 = ['payment_method' => 'paypal', 'paypal_email' => 'not-an-email'];
$paymentErrors2 = $paymentForm->validateWithConstraints($payment2);
echo "Test: PayPal with invalid email\n";
echo "Errors: " . json_encode($paymentErrors2, JSON_PRETTY_PRINT) . "\n\n";

// ============================================================================
// OUTPUT
// ============================================================================

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ FormGenerator V2.7.0 - Cross-Field Validation & Groups\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";
echo "New Features:\n";
echo "  • Cross-field validation with Callback constraint\n";
echo "  • ExecutionContext for building violations\n";
echo "  • ViolationBuilder for fluent error creation\n";
echo "  • Validation groups for conditional validation\n";
echo "  • Group-specific constraints\n";
echo "  • Multiple cross-field validations\n";
echo "\n";
echo "Use Cases Demonstrated:\n";
echo "  • Password confirmation matching\n";
echo "  • Date range validation\n";
echo "  • Business rule validation (totals, calculations)\n";
echo "  • Validation groups (registration, profile, password change)\n";
echo "  • Conditional field requirements\n";
echo "\n";
echo "Symfony Parity: Cross-Field Validation ✓ | Validation Groups ✓\n";
echo "\n";
