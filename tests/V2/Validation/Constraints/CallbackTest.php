<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Validation\Constraints;

use FormGenerator\V2\Validation\Constraints\Callback;
use FormGenerator\V2\Validation\ExecutionContext;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Callback constraint
 *
 * @covers \FormGenerator\V2\Validation\Constraints\Callback
 */
class CallbackTest extends TestCase
{
    public function testConstructorStoresCallback(): void
    {
        $callback = function($data, $context) {
            // Test callback
        };

        $constraint = new Callback($callback);

        $this->assertInstanceOf(Callback::class, $constraint);
    }

    public function testGetGroupsReturnsDefaultGroup(): void
    {
        $constraint = new Callback(fn() => null);

        $groups = $constraint->getGroups();

        $this->assertContains('Default', $groups);
        $this->assertCount(1, $groups);
    }

    public function testGetGroupsReturnsCustomGroups(): void
    {
        $constraint = new Callback(fn() => null, ['registration', 'profile']);

        $groups = $constraint->getGroups();

        $this->assertContains('registration', $groups);
        $this->assertContains('profile', $groups);
        $this->assertCount(2, $groups);
    }

    public function testAppliesToGroupReturnsTrueForMatchingGroup(): void
    {
        $constraint = new Callback(fn() => null, ['registration', 'profile']);

        $this->assertTrue($constraint->appliesToGroup('registration'));
        $this->assertTrue($constraint->appliesToGroup('profile'));
    }

    public function testAppliesToGroupReturnsFalseForNonMatchingGroup(): void
    {
        $constraint = new Callback(fn() => null, ['registration']);

        $this->assertFalse($constraint->appliesToGroup('profile'));
        $this->assertFalse($constraint->appliesToGroup('admin'));
    }

    public function testValidateExecutesCallback(): void
    {
        $executed = false;
        $callback = function($data, $context) use (&$executed) {
            $executed = true;
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([]);

        $constraint->validate([], $context);

        $this->assertTrue($executed);
    }

    public function testValidatePassesDataToCallback(): void
    {
        $receivedData = null;
        $callback = function($data, $context) use (&$receivedData) {
            $receivedData = $data;
        };

        $data = ['name' => 'John', 'email' => 'john@example.com'];
        $constraint = new Callback($callback);
        $context = new ExecutionContext($data);

        $constraint->validate($data, $context);

        $this->assertEquals($data, $receivedData);
    }

    public function testValidatePassesContextToCallback(): void
    {
        $receivedContext = null;
        $callback = function($data, $context) use (&$receivedContext) {
            $receivedContext = $context;
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([]);

        $constraint->validate([], $context);

        $this->assertSame($context, $receivedContext);
    }

    public function testCallbackCanAddViolations(): void
    {
        $callback = function($data, $context) {
            $context->buildViolation('Custom error')
                    ->atPath('field')
                    ->addViolation();
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([]);

        $constraint->validate([], $context);

        $this->assertTrue($context->hasViolations());
        $violations = $context->getViolations();
        $this->assertArrayHasKey('field', $violations);
    }

    public function testPasswordConfirmationValidation(): void
    {
        $callback = function($data, $context) {
            if ($data['password'] !== $data['password_confirm']) {
                $context->buildViolation('Passwords do not match')
                        ->atPath('password_confirm')
                        ->addViolation();
            }
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([
            'password' => 'secret123',
            'password_confirm' => 'different',
        ]);

        $constraint->validate($context->getData(), $context);

        $this->assertTrue($context->hasViolations());
        $violations = $context->getViolations();
        $this->assertEquals('Passwords do not match', $violations['password_confirm'][0]);
    }

    public function testDateRangeValidation(): void
    {
        $callback = function($data, $context) {
            $start = strtotime($data['start_date']);
            $end = strtotime($data['end_date']);

            if ($end < $start) {
                $context->buildViolation('End date must be after start date')
                        ->atPath('end_date')
                        ->addViolation();
            }
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([
            'start_date' => '2024-01-10',
            'end_date' => '2024-01-05',
        ]);

        $constraint->validate($context->getData(), $context);

        $this->assertTrue($context->hasViolations());
        $violations = $context->getViolations();
        $this->assertArrayHasKey('end_date', $violations);
    }

    public function testCallbackWithNoViolations(): void
    {
        $callback = function($data, $context) {
            if ($data['password'] !== $data['password_confirm']) {
                $context->buildViolation('Passwords do not match')
                        ->atPath('password_confirm')
                        ->addViolation();
            }
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([
            'password' => 'secret123',
            'password_confirm' => 'secret123',
        ]);

        $constraint->validate($context->getData(), $context);

        $this->assertFalse($context->hasViolations());
    }

    public function testMultipleViolationsInCallback(): void
    {
        $callback = function($data, $context) {
            if (empty($data['name'])) {
                $context->buildViolation('Name is required')
                        ->atPath('name')
                        ->addViolation();
            }

            if (empty($data['email'])) {
                $context->buildViolation('Email is required')
                        ->atPath('email')
                        ->addViolation();
            }
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([]);

        $constraint->validate($context->getData(), $context);

        $violations = $context->getViolations();
        $this->assertCount(2, $violations);
        $this->assertArrayHasKey('name', $violations);
        $this->assertArrayHasKey('email', $violations);
    }

    public function testCallbackWithParameters(): void
    {
        $callback = function($data, $context) {
            $total = $data['quantity'] * $data['price'];
            if ($total > 1000) {
                $context->buildViolation('Total amount {{ total }} exceeds limit')
                        ->atPath('quantity')
                        ->setParameter('total', $total)
                        ->addViolation();
            }
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([
            'quantity' => 100,
            'price' => 15,
        ]);

        $constraint->validate($context->getData(), $context);

        $violations = $context->getViolations();
        $this->assertEquals('Total amount 1500 exceeds limit', $violations['quantity'][0]);
    }

    public function testCallbackAccessingContextData(): void
    {
        $callback = function($data, $context) {
            $name = $context->getValue('name');
            if ($name === 'admin') {
                $context->buildViolation('Reserved username')
                        ->atPath('name')
                        ->addViolation();
            }
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext(['name' => 'admin']);

        $constraint->validate($context->getData(), $context);

        $this->assertTrue($context->hasViolations());
    }

    public function testComplexCrossFieldValidation(): void
    {
        $callback = function($data, $context) {
            // Age verification
            if (!empty($data['adult_content']) && $data['age'] < 18) {
                $context->buildViolation('Must be 18 or older for adult content')
                        ->atPath('adult_content')
                        ->addViolation();
            }

            // Email/phone requirement
            if (empty($data['email']) && empty($data['phone'])) {
                $context->buildViolation('Either email or phone is required')
                        ->atPath('email')
                        ->addViolation();
            }

            // Discount validation
            if ($data['discount'] > $data['price']) {
                $context->buildViolation('Discount cannot exceed price')
                        ->atPath('discount')
                        ->addViolation();
            }
        };

        $constraint = new Callback($callback);
        $context = new ExecutionContext([
            'adult_content' => true,
            'age' => 16,
            'email' => '',
            'phone' => '',
            'price' => 100,
            'discount' => 150,
        ]);

        $constraint->validate($context->getData(), $context);

        $violations = $context->getViolations();
        $this->assertCount(3, $violations);
        $this->assertArrayHasKey('adult_content', $violations);
        $this->assertArrayHasKey('email', $violations);
        $this->assertArrayHasKey('discount', $violations);
    }

    public function testCallbackWithGroupFiltering(): void
    {
        $registrationCallback = new Callback(
            fn($data, $context) => $context->buildViolation('Registration error')->atPath('field')->addViolation(),
            ['registration']
        );

        $profileCallback = new Callback(
            fn($data, $context) => $context->buildViolation('Profile error')->atPath('field')->addViolation(),
            ['profile']
        );

        $this->assertTrue($registrationCallback->appliesToGroup('registration'));
        $this->assertFalse($registrationCallback->appliesToGroup('profile'));

        $this->assertTrue($profileCallback->appliesToGroup('profile'));
        $this->assertFalse($profileCallback->appliesToGroup('registration'));
    }
}
