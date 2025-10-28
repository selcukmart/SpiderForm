<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Validation;

use FormGenerator\V2\Validation\{Validator, ValidatorFactory, ValidationException};
use PHPUnit\Framework\TestCase;

/**
 * Test ValidatorFactory Class
 */
class ValidatorFactoryTest extends TestCase
{
    public function testMakeCreatesValidator(): void
    {
        $data = ['email' => 'test@example.com'];
        $rules = ['email' => 'required|email'];

        $validator = ValidatorFactory::make($data, $rules);

        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertTrue($validator->passes());
    }

    public function testMakeWithCustomMessages(): void
    {
        $data = ['email' => 'invalid'];
        $rules = ['email' => 'email'];
        $messages = ['email.email' => 'Custom error'];

        $validator = ValidatorFactory::make($data, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertEquals('Custom error', $errors['email'][0]);
    }

    public function testMakeWithCustomAttributes(): void
    {
        $data = ['user_email' => ''];
        $rules = ['user_email' => 'required'];
        $attributes = ['user_email' => 'Email Address'];

        $validator = ValidatorFactory::make($data, $rules, [], $attributes);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertStringContainsString('Email Address', $errors['user_email'][0]);
    }

    public function testValidateReturnsValidatedData(): void
    {
        $data = ['email' => 'test@example.com'];
        $rules = ['email' => 'required|email'];

        $validated = ValidatorFactory::validate($data, $rules);

        $this->assertEquals($data, $validated);
    }

    public function testValidateThrowsExceptionOnFailure(): void
    {
        $this->expectException(ValidationException::class);

        $data = ['email' => 'invalid'];
        $rules = ['email' => 'email'];

        ValidatorFactory::validate($data, $rules);
    }

    public function testMakeBailCreatesValidatorWithBailEnabled(): void
    {
        $data = ['email' => 'invalid'];
        $rules = ['email' => 'required|email|min:10'];

        $validator = ValidatorFactory::makeBail($data, $rules);
        $validator->passes();

        $errors = $validator->errors();
        // Should stop after first failure
        $this->assertCount(1, $errors['email']);
    }

    public function testSetDefaultConnection(): void
    {
        $pdo = $this->createMock(\PDO::class);
        ValidatorFactory::setDefaultConnection($pdo);

        // This test just ensures the method exists and can be called
        $this->assertTrue(true);
    }
}
