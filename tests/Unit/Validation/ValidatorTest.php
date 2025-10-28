<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Validation;

use FormGenerator\V2\Validation\{Validator, ValidationException};
use PHPUnit\Framework\TestCase;

/**
 * Test Validator Class
 */
class ValidatorTest extends TestCase
{
    public function testValidatorPassesWithValidData(): void
    {
        $data = [
            'email' => 'test@example.com',
            'age' => 25,
        ];

        $rules = [
            'email' => 'required|email',
            'age' => 'required|numeric|min:18',
        ];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors());
    }

    public function testValidatorFailsWithInvalidData(): void
    {
        $data = [
            'email' => 'invalid-email',
            'age' => 15,
        ];

        $rules = [
            'email' => 'required|email',
            'age' => 'required|numeric|min:18',
        ];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->passes());
        $this->assertNotEmpty($validator->errors());
    }

    public function testValidateThrowsExceptionOnFailure(): void
    {
        $this->expectException(ValidationException::class);

        $data = ['email' => 'invalid'];
        $rules = ['email' => 'email'];

        $validator = new Validator($data, $rules);
        $validator->validate();
    }

    public function testValidateReturnsValidatedData(): void
    {
        $data = [
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'extra' => 'This should not be in validated data',
        ];

        $rules = [
            'email' => 'required|email',
            'name' => 'required|string',
        ];

        $validator = new Validator($data, $rules);
        $validated = $validator->validate();

        $this->assertArrayHasKey('email', $validated);
        $this->assertArrayHasKey('name', $validated);
        $this->assertArrayNotHasKey('extra', $validated);
    }

    public function testCustomErrorMessages(): void
    {
        $data = ['email' => 'invalid'];
        $rules = ['email' => 'email'];
        $messages = ['email.email' => 'Custom email error message'];

        $validator = new Validator($data, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertEquals('Custom email error message', $errors['email'][0]);
    }

    public function testCustomAttributeNames(): void
    {
        $data = ['user_email' => ''];
        $rules = ['user_email' => 'required'];
        $attributes = ['user_email' => 'Email Address'];

        $validator = new Validator($data, $rules, [], $attributes);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertStringContainsString('Email Address', $errors['user_email'][0]);
    }

    public function testBailMode(): void
    {
        $data = ['email' => 'invalid'];
        $rules = ['email' => 'required|email|min:10|max:255'];

        $validator = new Validator($data, $rules);
        $validator->bail();
        $validator->passes();

        $errors = $validator->errors();
        // In bail mode, should stop after first failure (email validation)
        $this->assertCount(1, $errors['email']);
    }

    public function testRequiredRule(): void
    {
        $validator = new Validator(['name' => ''], ['name' => 'required']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['name' => 'John'], ['name' => 'required']);
        $this->assertTrue($validator->passes());
    }

    public function testEmailRule(): void
    {
        $validator = new Validator(['email' => 'invalid'], ['email' => 'email']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['email' => 'test@example.com'], ['email' => 'email']);
        $this->assertTrue($validator->passes());
    }

    public function testMinRule(): void
    {
        // Numeric value
        $validator = new Validator(['age' => 15], ['age' => 'min:18']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['age' => 25], ['age' => 'min:18']);
        $this->assertTrue($validator->passes());

        // String length
        $validator = new Validator(['name' => 'ab'], ['name' => 'min:3']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['name' => 'abc'], ['name' => 'min:3']);
        $this->assertTrue($validator->passes());
    }

    public function testMaxRule(): void
    {
        // Numeric value
        $validator = new Validator(['age' => 101], ['age' => 'max:100']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['age' => 50], ['age' => 'max:100']);
        $this->assertTrue($validator->passes());
    }

    public function testNumericRule(): void
    {
        $validator = new Validator(['value' => 'abc'], ['value' => 'numeric']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => '123'], ['value' => 'numeric']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['value' => 123.45], ['value' => 'numeric']);
        $this->assertTrue($validator->passes());
    }

    public function testIntegerRule(): void
    {
        $validator = new Validator(['value' => '123.45'], ['value' => 'integer']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => '123'], ['value' => 'integer']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['value' => 123], ['value' => 'integer']);
        $this->assertTrue($validator->passes());
    }

    public function testStringRule(): void
    {
        $validator = new Validator(['value' => 123], ['value' => 'string']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => 'hello'], ['value' => 'string']);
        $this->assertTrue($validator->passes());
    }

    public function testBooleanRule(): void
    {
        $validator = new Validator(['value' => 'yes'], ['value' => 'boolean']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => true], ['value' => 'boolean']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['value' => 1], ['value' => 'boolean']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['value' => '1'], ['value' => 'boolean']);
        $this->assertTrue($validator->passes());
    }

    public function testArrayRule(): void
    {
        $validator = new Validator(['value' => 'not-array'], ['value' => 'array']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => [1, 2, 3]], ['value' => 'array']);
        $this->assertTrue($validator->passes());
    }

    public function testUrlRule(): void
    {
        $validator = new Validator(['url' => 'not-a-url'], ['url' => 'url']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['url' => 'https://example.com'], ['url' => 'url']);
        $this->assertTrue($validator->passes());
    }

    public function testIpRule(): void
    {
        $validator = new Validator(['ip' => 'not-an-ip'], ['ip' => 'ip']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['ip' => '192.168.1.1'], ['ip' => 'ip']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334'], ['ip' => 'ip']);
        $this->assertTrue($validator->passes());

        // IPv4 specific
        $validator = new Validator(['ip' => '192.168.1.1'], ['ip' => 'ip:ipv4']);
        $this->assertTrue($validator->passes());
    }

    public function testJsonRule(): void
    {
        $validator = new Validator(['data' => 'not-json'], ['data' => 'json']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['data' => '{"key":"value"}'], ['data' => 'json']);
        $this->assertTrue($validator->passes());
    }

    public function testAlphaRule(): void
    {
        $validator = new Validator(['value' => 'abc123'], ['value' => 'alpha']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => 'abc'], ['value' => 'alpha']);
        $this->assertTrue($validator->passes());
    }

    public function testAlphaNumericRule(): void
    {
        $validator = new Validator(['value' => 'abc-123'], ['value' => 'alpha_numeric']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => 'abc123'], ['value' => 'alpha_numeric']);
        $this->assertTrue($validator->passes());
    }

    public function testDigitsRule(): void
    {
        $validator = new Validator(['value' => 'abc'], ['value' => 'digits']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => '123'], ['value' => 'digits']);
        $this->assertTrue($validator->passes());

        // With exact length
        $validator = new Validator(['value' => '123'], ['value' => 'digits:5']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['value' => '12345'], ['value' => 'digits:5']);
        $this->assertTrue($validator->passes());
    }

    public function testDateRule(): void
    {
        $validator = new Validator(['date' => 'not-a-date'], ['date' => 'date']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['date' => '2024-01-15'], ['date' => 'date']);
        $this->assertTrue($validator->passes());
    }

    public function testDateFormatRule(): void
    {
        $validator = new Validator(['date' => '01/15/2024'], ['date' => 'date_format:Y-m-d']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['date' => '2024-01-15'], ['date' => 'date_format:Y-m-d']);
        $this->assertTrue($validator->passes());
    }

    public function testBeforeRule(): void
    {
        $validator = new Validator(['date' => '2024-12-31'], ['date' => 'before:2024-01-01']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['date' => '2023-12-31'], ['date' => 'before:2024-01-01']);
        $this->assertTrue($validator->passes());
    }

    public function testAfterRule(): void
    {
        $validator = new Validator(['date' => '2023-01-01'], ['date' => 'after:2024-01-01']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['date' => '2024-12-31'], ['date' => 'after:2024-01-01']);
        $this->assertTrue($validator->passes());
    }

    public function testBetweenRule(): void
    {
        // Numeric
        $validator = new Validator(['age' => 15], ['age' => 'between:18,65']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['age' => 25], ['age' => 'between:18,65']);
        $this->assertTrue($validator->passes());

        // String length
        $validator = new Validator(['name' => 'ab'], ['name' => 'between:3,10']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['name' => 'abcde'], ['name' => 'between:3,10']);
        $this->assertTrue($validator->passes());
    }

    public function testInRule(): void
    {
        $validator = new Validator(['role' => 'guest'], ['role' => 'in:admin,user,moderator']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['role' => 'admin'], ['role' => 'in:admin,user,moderator']);
        $this->assertTrue($validator->passes());
    }

    public function testNotInRule(): void
    {
        $validator = new Validator(['role' => 'admin'], ['role' => 'not_in:admin,root']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['role' => 'user'], ['role' => 'not_in:admin,root']);
        $this->assertTrue($validator->passes());
    }

    public function testRegexRule(): void
    {
        $validator = new Validator(['code' => 'ABC'], ['code' => 'regex:/^[0-9]+$/']);
        $this->assertTrue($validator->fails());

        $validator = new Validator(['code' => '123'], ['code' => 'regex:/^[0-9]+$/']);
        $this->assertTrue($validator->passes());
    }

    public function testMultipleRules(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'age' => 25,
        ];

        $rules = [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'age' => 'required|integer|min:18|max:100',
        ];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->passes());
    }
}
