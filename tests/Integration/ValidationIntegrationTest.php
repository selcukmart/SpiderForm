<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Integration;

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Validation\{ValidationException};
use PHPUnit\Framework\TestCase;

/**
 * Integration Tests for Validation System with FormBuilder
 */
class ValidationIntegrationTest extends TestCase
{
    public function testFormBuilderExtractsValidationRules(): void
    {
        $form = FormBuilder::create('test_form')
            ->addText('email', 'Email')
                ->required()
                ->email()
                ->add()
            ->addText('name', 'Name')
                ->required()
                ->minLength(3)
                ->add()
            ->build();

        // Validate valid data
        $validData = [
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ];

        $validated = $form->validateData($validData);
        $this->assertEquals($validData, $validated);
    }

    public function testFormBuilderValidationFailsWithInvalidData(): void
    {
        $this->expectException(ValidationException::class);

        $form = FormBuilder::create('test_form')
            ->addEmail('email', 'Email')
                ->required()
                ->email()
                ->add()
            ->build();

        $form->validateData(['email' => 'invalid-email']);
    }

    public function testCompleteUserRegistrationForm(): void
    {
        $form = FormBuilder::create('register')
            ->addText('username', 'Username')
                ->required()
                ->alphaNumeric()
                ->minLength(3)
                ->maxLength(20)
                ->add()
            ->addEmail('email', 'Email')
                ->required()
                ->email()
                ->add()
            ->addPassword('password', 'Password')
                ->required()
                ->minLength(8)
                ->add()
            ->addNumber('age', 'Age')
                ->integer()
                ->between(18, 100)
                ->add()
            ->addSelect('role', 'Role')
                ->required()
                ->in(['user', 'admin'])
                ->options(['user' => 'User', 'admin' => 'Admin'])
                ->add()
            ->build();

        // Test valid registration
        $validData = [
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
            'age' => 25,
            'role' => 'user',
        ];

        $validated = $form->validateData($validData);
        $this->assertEquals($validData, $validated);

        // Test invalid age
        try {
            $invalidData = $validData;
            $invalidData['age'] = 15; // Too young

            $form->validateData($invalidData);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('age', $errors);
        }

        // Test invalid role
        try {
            $invalidData = $validData;
            $invalidData['role'] = 'superadmin'; // Not in allowed list

            $form->validateData($invalidData);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('role', $errors);
        }
    }

    public function testPasswordConfirmationValidation(): void
    {
        $form = FormBuilder::create('password_form')
            ->addPassword('password', 'Password')
                ->required()
                ->minLength(8)
                ->confirmed()
                ->add()
            ->addPassword('password_confirmation', 'Confirm Password')
                ->required()
                ->add()
            ->build();

        // Test matching passwords
        $validData = [
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ];

        $validated = $form->validateData($validData);
        $this->assertArrayHasKey('password', $validated);

        // Test non-matching passwords
        try {
            $invalidData = [
                'password' => 'SecurePass123',
                'password_confirmation' => 'DifferentPass456',
            ];

            $form->validateData($invalidData);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('password', $errors);
        }
    }

    public function testMultipleValidationRulesOnSingleField(): void
    {
        $form = FormBuilder::create('profile_form')
            ->addText('bio', 'Biography')
                ->required()
                ->string()
                ->minLength(10)
                ->maxLength(500)
                ->add()
            ->build();

        // Valid data
        $validData = ['bio' => 'This is a long enough biography that meets the minimum requirements.'];
        $validated = $form->validateData($validData);
        $this->assertArrayHasKey('bio', $validated);

        // Too short
        try {
            $form->validateData(['bio' => 'Too short']);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('bio', $e->errors());
        }

        // Too long
        try {
            $longBio = str_repeat('a', 501);
            $form->validateData(['bio' => $longBio]);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('bio', $e->errors());
        }
    }

    public function testDateValidationInForm(): void
    {
        $form = FormBuilder::create('appointment_form')
            ->addDate('appointment_date', 'Appointment Date')
                ->required()
                ->date()
                ->after('today')
                ->add()
            ->build();

        // Future date should pass
        $futureDate = date('Y-m-d', strtotime('+7 days'));
        $validated = $form->validateData(['appointment_date' => $futureDate]);
        $this->assertArrayHasKey('appointment_date', $validated);

        // Past date should fail
        try {
            $pastDate = date('Y-m-d', strtotime('-7 days'));
            $form->validateData(['appointment_date' => $pastDate]);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('appointment_date', $e->errors());
        }
    }

    public function testCustomErrorMessages(): void
    {
        $form = FormBuilder::create('contact_form')
            ->addEmail('email', 'Email')
                ->required()
                ->email()
                ->add()
            ->build();

        $customMessages = [
            'email.required' => 'Please provide your email address',
            'email.email' => 'Please provide a valid email format',
        ];

        try {
            $form->validateData(['email' => ''], $customMessages);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertEquals('Please provide your email address', $errors['email'][0]);
        }

        try {
            $form->validateData(['email' => 'invalid'], $customMessages);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertEquals('Please provide a valid email format', $errors['email'][0]);
        }
    }

    public function testUrlAndIpValidation(): void
    {
        $form = FormBuilder::create('server_form')
            ->addText('website', 'Website')
                ->url()
                ->add()
            ->addText('ip_address', 'IP Address')
                ->ip()
                ->add()
            ->build();

        // Valid data
        $validData = [
            'website' => 'https://example.com',
            'ip_address' => '192.168.1.1',
        ];

        $validated = $form->validateData($validData);
        $this->assertEquals($validData, $validated);

        // Invalid URL
        try {
            $form->validateData([
                'website' => 'not-a-url',
                'ip_address' => '192.168.1.1',
            ]);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website', $e->errors());
        }

        // Invalid IP
        try {
            $form->validateData([
                'website' => 'https://example.com',
                'ip_address' => '999.999.999.999',
            ]);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('ip_address', $e->errors());
        }
    }

    public function testValidatedDataExcludesUnvalidatedFields(): void
    {
        $form = FormBuilder::create('partial_form')
            ->addText('name', 'Name')
                ->required()
                ->add()
            ->addEmail('email', 'Email')
                ->required()
                ->email()
                ->add()
            ->build();

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'extra_field' => 'This should not be validated',
            'another_field' => 'This too',
        ];

        $validated = $form->validateData($data);

        // Only validated fields should be present
        $this->assertArrayHasKey('name', $validated);
        $this->assertArrayHasKey('email', $validated);
        $this->assertArrayNotHasKey('extra_field', $validated);
        $this->assertArrayNotHasKey('another_field', $validated);
        $this->assertCount(2, $validated);
    }

    public function testAlphaAndAlphaNumericValidation(): void
    {
        $form = FormBuilder::create('text_form')
            ->addText('letters_only', 'Letters Only')
                ->alpha()
                ->add()
            ->addText('alphanumeric', 'Alphanumeric')
                ->alphaNumeric()
                ->add()
            ->build();

        // Valid data
        $validData = [
            'letters_only' => 'abc',
            'alphanumeric' => 'abc123',
        ];

        $validated = $form->validateData($validData);
        $this->assertEquals($validData, $validated);

        // Invalid: numbers in alpha field
        try {
            $form->validateData([
                'letters_only' => 'abc123',
                'alphanumeric' => 'abc123',
            ]);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('letters_only', $e->errors());
        }

        // Invalid: special characters in alphanumeric
        try {
            $form->validateData([
                'letters_only' => 'abc',
                'alphanumeric' => 'abc-123',
            ]);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('alphanumeric', $e->errors());
        }
    }

    public function testDigitsValidation(): void
    {
        $form = FormBuilder::create('pin_form')
            ->addText('pin', 'PIN')
                ->required()
                ->digits(4)
                ->add()
            ->build();

        // Valid 4-digit PIN
        $validated = $form->validateData(['pin' => '1234']);
        $this->assertEquals('1234', $validated['pin']);

        // Invalid: wrong length
        try {
            $form->validateData(['pin' => '12345']);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('pin', $e->errors());
        }

        // Invalid: contains letters
        try {
            $form->validateData(['pin' => '12ab']);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('pin', $e->errors());
        }
    }

    public function testRegexPatternValidation(): void
    {
        $form = FormBuilder::create('code_form')
            ->addText('code', 'Code')
                ->required()
                ->regex('/^[A-Z]{3}-[0-9]{4}$/', 'Code must be in format ABC-1234')
                ->add()
            ->build();

        // Valid format
        $validated = $form->validateData(['code' => 'ABC-1234']);
        $this->assertEquals('ABC-1234', $validated['code']);

        // Invalid format
        try {
            $form->validateData(['code' => 'abc-1234']);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('code', $errors);
            $this->assertStringContainsString('format', $errors['code'][0]);
        }
    }

    public function testLaravelStyleRuleString(): void
    {
        $form = FormBuilder::create('laravel_form')
            ->addText('email', 'Email')
                ->rules('required|email|min:5|max:255')
                ->add()
            ->build();

        // Valid data
        $validated = $form->validateData(['email' => 'test@example.com']);
        $this->assertEquals('test@example.com', $validated['email']);

        // Invalid: too short
        try {
            $form->validateData(['email' => 'a@b']);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }
}
