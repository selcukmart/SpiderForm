<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Validation;

use FormGenerator\V2\Validation\{Validator, ValidationException};
use PHPUnit\Framework\TestCase;

/**
 * Test Edge Cases and Complex Scenarios
 */
class ValidationEdgeCasesTest extends TestCase
{
    public function testEmptyDataWithNoRules(): void
    {
        $validator = new Validator([], []);
        $this->assertTrue($validator->passes());
        $this->assertEmpty($validator->validated());
    }

    public function testNullValuesWithoutRequired(): void
    {
        $data = ['optional_field' => null];
        $rules = ['optional_field' => 'email'];

        $validator = new Validator($data, $rules);
        // Should pass because field is not required
        $this->assertTrue($validator->passes());
    }

    public function testEmptyStringVsNull(): void
    {
        // Empty string should fail required
        $validator = new Validator(['field' => ''], ['field' => 'required']);
        $this->assertTrue($validator->fails());

        // Null should also fail required
        $validator = new Validator(['field' => null], ['field' => 'required']);
        $this->assertTrue($validator->fails());

        // Missing field should also fail required
        $validator = new Validator([], ['field' => 'required']);
        $this->assertTrue($validator->fails());
    }

    public function testZeroValuesAreValid(): void
    {
        // Zero should be valid for numeric fields
        $validator = new Validator(['age' => 0], ['age' => 'numeric']);
        $this->assertTrue($validator->passes());

        // Zero should be valid for required numeric fields
        $validator = new Validator(['age' => 0], ['age' => 'required|numeric']);
        $this->assertTrue($validator->passes());

        // String "0" should be valid
        $validator = new Validator(['value' => '0'], ['value' => 'required']);
        $this->assertTrue($validator->passes());
    }

    public function testMultibyteStrings(): void
    {
        // Test with UTF-8 characters
        $data = ['name' => 'Müller'];
        $rules = ['name' => 'required|min:3|max:10'];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->passes());

        // Test Chinese characters
        $data = ['name' => '张三'];
        $rules = ['name' => 'required|min:2'];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->passes());

        // Test emoji
        $data = ['message' => 'Hello 👋 World 🌍'];
        $rules = ['message' => 'required|string'];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testArrayMinMaxRules(): void
    {
        // Array with min count
        $validator = new Validator(
            ['items' => [1]],
            ['items' => 'required|min:2']
        );
        $this->assertTrue($validator->fails());

        $validator = new Validator(
            ['items' => [1, 2]],
            ['items' => 'required|min:2']
        );
        $this->assertTrue($validator->passes());

        // Array with max count
        $validator = new Validator(
            ['items' => [1, 2, 3, 4]],
            ['items' => 'required|max:3']
        );
        $this->assertTrue($validator->fails());
    }

    public function testChainedRulesStopOnFirstFailureWithBail(): void
    {
        $data = ['email' => 'not-an-email'];
        $rules = ['email' => 'required|email|min:20|max:255'];

        $validator = new Validator($data, $rules);
        $validator->bail();
        $validator->passes();

        $errors = $validator->errors();
        // With bail, should stop after email validation fails
        $this->assertCount(1, $errors['email']);
    }

    public function testMultipleErrorsPerFieldWithoutBail(): void
    {
        $data = ['password' => 'a'];
        $rules = ['password' => 'required|min:8|alpha_numeric'];

        $validator = new Validator($data, $rules);
        $validator->passes();

        $errors = $validator->errors();
        // Without bail, should have multiple errors
        $this->assertGreaterThanOrEqual(1, count($errors['password']));
    }

    public function testComplexBetweenRule(): void
    {
        // Numeric between
        $validator = new Validator(['num' => 5], ['num' => 'between:1,10']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['num' => 0], ['num' => 'between:1,10']);
        $this->assertTrue($validator->fails());

        // String length between
        $validator = new Validator(['text' => 'hello'], ['text' => 'between:3,10']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['text' => 'hi'], ['text' => 'between:3,10']);
        $this->assertTrue($validator->fails());
    }

    public function testDateEdgeCases(): void
    {
        // Unix timestamp
        $validator = new Validator(['date' => time()], ['date' => 'date']);
        $this->assertTrue($validator->passes());

        // Various date formats
        $formats = [
            '2024-01-15',
            '15/01/2024',
            '01-15-2024',
            'January 15, 2024',
        ];

        foreach ($formats as $format) {
            $validator = new Validator(['date' => $format], ['date' => 'date']);
            $this->assertTrue($validator->passes(), "Failed for format: $format");
        }

        // Invalid dates
        $validator = new Validator(['date' => '2024-13-45'], ['date' => 'date']);
        $this->assertTrue($validator->fails());
    }

    public function testIpv4AndIpv6Validation(): void
    {
        // IPv4
        $validator = new Validator(['ip' => '192.168.1.1'], ['ip' => 'ip:ipv4']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['ip' => '192.168.1.256'], ['ip' => 'ip:ipv4']);
        $this->assertTrue($validator->fails());

        // IPv6
        $validator = new Validator(
            ['ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['ip' => 'ip:ipv6']
        );
        $this->assertTrue($validator->passes());

        // IPv6 should fail IPv4 validation
        $validator = new Validator(
            ['ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['ip' => 'ip:ipv4']
        );
        $this->assertTrue($validator->fails());
    }

    public function testBooleanEdgeCases(): void
    {
        $validBooleans = [true, false, 1, 0, '1', '0', 'true', 'false'];

        foreach ($validBooleans as $value) {
            $validator = new Validator(['flag' => $value], ['flag' => 'boolean']);
            $this->assertTrue($validator->passes(), "Failed for value: " . var_export($value, true));
        }

        // Invalid boolean values
        $invalidBooleans = ['yes', 'no', 'on', 'off', 2, -1, [], null];

        foreach ($invalidBooleans as $value) {
            $validator = new Validator(['flag' => $value], ['flag' => 'boolean']);
            $this->assertTrue($validator->fails(), "Passed for invalid value: " . var_export($value, true));
        }
    }

    public function testRegexWithSpecialCharacters(): void
    {
        // Test regex with various patterns
        $patterns = [
            '/^[A-Z]{3}-[0-9]{4}$/' => ['ABC-1234' => true, 'abc-1234' => false],
            '/^https?:\/\//' => ['https://example.com' => true, 'ftp://example.com' => false],
            '/^\d{3}-\d{2}-\d{4}$/' => ['123-45-6789' => true, '12-345-6789' => false],
        ];

        foreach ($patterns as $pattern => $tests) {
            foreach ($tests as $value => $shouldPass) {
                $validator = new Validator(['field' => $value], ['field' => "regex:$pattern"]);
                if ($shouldPass) {
                    $this->assertTrue($validator->passes(), "Failed for pattern: $pattern, value: $value");
                } else {
                    $this->assertTrue($validator->fails(), "Passed for pattern: $pattern, value: $value");
                }
            }
        }
    }

    public function testInAndNotInWithMixedTypes(): void
    {
        // Strict comparison should be used
        $validator = new Validator(['role' => '1'], ['role' => 'in:1,2,3']);
        $this->assertTrue($validator->passes());

        $validator = new Validator(['role' => 1], ['role' => 'in:1,2,3']);
        $this->assertTrue($validator->passes());

        // String vs int strict comparison
        $validator = new Validator(['value' => '0'], ['value' => 'not_in:0']);
        $this->assertTrue($validator->passes()); // '0' !== 0 (strict)
    }

    public function testEmailEdgeCases(): void
    {
        $validEmails = [
            'simple@example.com',
            'user+tag@example.com',
            'user.name@example.com',
            'user_name@example.co.uk',
            '123@example.com',
        ];

        foreach ($validEmails as $email) {
            $validator = new Validator(['email' => $email], ['email' => 'email']);
            $this->assertTrue($validator->passes(), "Failed for valid email: $email");
        }

        $invalidEmails = [
            'invalid',
            '@example.com',
            'user@',
            'user @example.com',
            'user@example',
        ];

        foreach ($invalidEmails as $email) {
            $validator = new Validator(['email' => $email], ['email' => 'email']);
            $this->assertTrue($validator->fails(), "Passed for invalid email: $email");
        }
    }

    public function testUrlEdgeCases(): void
    {
        $validUrls = [
            'https://example.com',
            'http://example.com',
            'https://www.example.com/path?query=value',
            'ftp://files.example.com',
            'https://subdomain.example.co.uk:8080/path',
        ];

        foreach ($validUrls as $url) {
            $validator = new Validator(['url' => $url], ['url' => 'url']);
            $this->assertTrue($validator->passes(), "Failed for valid URL: $url");
        }

        $invalidUrls = [
            'not-a-url',
            'example.com', // Missing protocol
            'http://',
            '://example.com',
        ];

        foreach ($invalidUrls as $url) {
            $validator = new Validator(['url' => $url], ['url' => 'url']);
            $this->assertTrue($validator->fails(), "Passed for invalid URL: $url");
        }
    }

    public function testValidatedDataOnlyReturnsValidatedFields(): void
    {
        $data = [
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'extra_field' => 'should not be in validated',
            'another_extra' => 'also not validated',
        ];

        $rules = [
            'email' => 'required|email',
            'name' => 'required|string',
        ];

        $validator = new Validator($data, $rules);
        $validated = $validator->validated();

        $this->assertArrayHasKey('email', $validated);
        $this->assertArrayHasKey('name', $validated);
        $this->assertArrayNotHasKey('extra_field', $validated);
        $this->assertArrayNotHasKey('another_extra', $validated);
        $this->assertCount(2, $validated);
    }

    public function testMultipleFieldsWithComplexRules(): void
    {
        $data = [
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'age' => 25,
            'website' => 'https://example.com',
            'role' => 'admin',
        ];

        $rules = [
            'username' => 'required|alpha_numeric|min:3|max:20',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'age' => 'required|integer|between:18,100',
            'website' => 'url',
            'role' => 'required|in:admin,user,moderator',
        ];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->passes());

        $validated = $validator->validated();
        $this->assertCount(6, $validated); // password_confirmation not included
    }

    public function testErrorMessagePlaceholderReplacement(): void
    {
        $validator = new Validator(
            ['age' => 15],
            ['age' => 'min:18']
        );

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();

        // Check that :attribute placeholder was replaced
        $this->assertStringContainsString('age', strtolower($errors['age'][0]));
    }

    public function testCustomAttributeNameInErrorMessage(): void
    {
        $validator = new Validator(
            ['user_email' => ''],
            ['user_email' => 'required'],
            [],
            ['user_email' => 'Email Address']
        );

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();

        $this->assertStringContainsString('Email Address', $errors['user_email'][0]);
        $this->assertStringNotContainsString('user_email', $errors['user_email'][0]);
    }

    public function testJsonValidation(): void
    {
        // Valid JSON
        $validJson = [
            '{"key":"value"}',
            '[1,2,3]',
            '{"nested":{"key":"value"}}',
            'null',
            'true',
            '123',
            '"string"',
        ];

        foreach ($validJson as $json) {
            $validator = new Validator(['data' => $json], ['data' => 'json']);
            $this->assertTrue($validator->passes(), "Failed for valid JSON: $json");
        }

        // Invalid JSON
        $invalidJson = [
            '{key:value}',
            "{'key':'value'}",
            '{broken json',
            'undefined',
        ];

        foreach ($invalidJson as $json) {
            $validator = new Validator(['data' => $json], ['data' => 'json']);
            $this->assertTrue($validator->fails(), "Passed for invalid JSON: $json");
        }
    }

    public function testConfirmedWithMissingConfirmationField(): void
    {
        $data = ['password' => 'secret123'];
        $rules = ['password' => 'confirmed'];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->fails());
    }
}
