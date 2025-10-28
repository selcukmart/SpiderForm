<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Validation;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Validation\NativeValidator;
use FormGenerator\V2\Contracts\ValidationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(NativeValidator::class)]
class NativeValidatorTest extends TestCase
{
    private NativeValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new NativeValidator();
    }

    #[Test]
    public function it_validates_required_field(): void
    {
        $result = $this->validator->validate('username', '', ['required' => true]);
        
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
    }

    #[Test]
    public function it_passes_required_with_value(): void
    {
        $result = $this->validator->validate('username', 'john', ['required' => true]);
        
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
    }

    #[Test]
    public function it_validates_email(): void
    {
        $result = $this->validator->validate('email', 'invalid-email', ['email' => true]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_valid_email(): void
    {
        $result = $this->validator->validate('email', 'user@example.com', ['email' => true]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    #[DataProvider('emailProvider')]
    public function it_validates_various_email_formats(string $email, bool $shouldPass): void
    {
        $result = $this->validator->validate('email', $email, ['email' => true]);
        
        $this->assertEquals($shouldPass, $result->isValid());
    }

    public static function emailProvider(): array
    {
        return [
            ['user@example.com', true],
            ['user.name@example.com', true],
            ['user+tag@example.co.uk', true],
            ['invalid', false],
            ['@example.com', false],
            ['user@', false],
            ['user @example.com', false],
        ];
    }

    #[Test]
    public function it_validates_min_length(): void
    {
        $result = $this->validator->validate('username', 'ab', ['minLength' => 3]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_min_length(): void
    {
        $result = $this->validator->validate('username', 'abc', ['minLength' => 3]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_max_length(): void
    {
        $result = $this->validator->validate('username', 'verylongusername', ['maxLength' => 10]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_max_length(): void
    {
        $result = $this->validator->validate('username', 'shortname', ['maxLength' => 10]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_min_value(): void
    {
        $result = $this->validator->validate('age', '15', ['min' => 18]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_min_value(): void
    {
        $result = $this->validator->validate('age', '21', ['min' => 18]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_max_value(): void
    {
        $result = $this->validator->validate('age', '150', ['max' => 120]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_max_value(): void
    {
        $result = $this->validator->validate('age', '65', ['max' => 120]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_pattern(): void
    {
        $result = $this->validator->validate('username', 'invalid-name!', ['pattern' => '^[a-zA-Z0-9_]+$']);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_pattern(): void
    {
        $result = $this->validator->validate('username', 'valid_name123', ['pattern' => '^[a-zA-Z0-9_]+$']);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_url(): void
    {
        $result = $this->validator->validate('website', 'not-a-url', ['url' => true]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    #[DataProvider('urlProvider')]
    public function it_validates_various_url_formats(string $url, bool $shouldPass): void
    {
        $result = $this->validator->validate('website', $url, ['url' => true]);
        
        $this->assertEquals($shouldPass, $result->isValid());
    }

    public static function urlProvider(): array
    {
        return [
            ['https://example.com', true],
            ['http://example.com', true],
            ['https://sub.example.com/path', true],
            ['ftp://files.example.com', true],
            ['not-a-url', false],
            ['example.com', false],
            ['//example.com', false],
        ];
    }

    #[Test]
    public function it_validates_numeric(): void
    {
        $result = $this->validator->validate('price', 'abc', ['numeric' => true]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_numeric(): void
    {
        $result = $this->validator->validate('price', '123.45', ['numeric' => true]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_integer(): void
    {
        $result = $this->validator->validate('count', '123.45', ['integer' => true]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_integer(): void
    {
        $result = $this->validator->validate('count', '123', ['integer' => true]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_alpha(): void
    {
        $result = $this->validator->validate('name', 'John123', ['alpha' => true]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_alpha(): void
    {
        $result = $this->validator->validate('name', 'John', ['alpha' => true]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_alphanumeric(): void
    {
        $result = $this->validator->validate('code', 'ABC-123', ['alphanumeric' => true]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_alphanumeric(): void
    {
        $result = $this->validator->validate('code', 'ABC123', ['alphanumeric' => true]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_date(): void
    {
        $result = $this->validator->validate('birthdate', 'not-a-date', ['date' => true]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    #[DataProvider('dateProvider')]
    public function it_validates_various_date_formats(string $date, bool $shouldPass): void
    {
        $result = $this->validator->validate('date', $date, ['date' => true]);
        
        $this->assertEquals($shouldPass, $result->isValid());
    }

    public static function dateProvider(): array
    {
        return [
            ['2024-01-15', true],
            ['2024/01/15', true],
            ['15-01-2024', true],
            ['15/01/2024', true],
            ['not-a-date', false],
            ['2024-13-01', false],
            ['2024-01-32', false],
        ];
    }

    #[Test]
    public function it_validates_match_field(): void
    {
        $formData = [
            'password' => 'secret123',
            'password_confirm' => 'different'
        ];
        
        $result = $this->validator->validateForm($formData, [
            'password_confirm' => ['match' => 'password']
        ]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_match_field(): void
    {
        $formData = [
            'password' => 'secret123',
            'password_confirm' => 'secret123'
        ];
        
        $result = $this->validator->validateForm($formData, [
            'password_confirm' => ['match' => 'password']
        ]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_in_array(): void
    {
        $result = $this->validator->validate('status', 'invalid', ['in' => ['active', 'pending', 'inactive']]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_in_array(): void
    {
        $result = $this->validator->validate('status', 'active', ['in' => ['active', 'pending', 'inactive']]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_not_in_array(): void
    {
        $result = $this->validator->validate('username', 'admin', ['notIn' => ['admin', 'root', 'system']]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_not_in_array(): void
    {
        $result = $this->validator->validate('username', 'john', ['notIn' => ['admin', 'root', 'system']]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_multiple_rules(): void
    {
        $result = $this->validator->validate('email', 'invalid', [
            'required' => true,
            'email' => true,
            'maxLength' => 255
        ]);
        
        $this->assertFalse($result->isValid());
    }

    #[Test]
    public function it_passes_multiple_rules(): void
    {
        $result = $this->validator->validate('email', 'user@example.com', [
            'required' => true,
            'email' => true,
            'maxLength' => 255
        ]);
        
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_validates_entire_form(): void
    {
        $formData = [
            'username' => 'ab',
            'email' => 'invalid-email',
            'age' => '15'
        ];
        
        $rules = [
            'username' => ['required' => true, 'minLength' => 3],
            'email' => ['required' => true, 'email' => true],
            'age' => ['required' => true, 'min' => 18]
        ];
        
        $result = $this->validator->validateForm($formData, $rules);
        
        $this->assertFalse($result->isValid());
        $this->assertCount(3, $result->getErrors());
    }

    #[Test]
    public function it_generates_javascript_code(): void
    {
        $js = $this->validator->getJavaScriptCode('email', ['required' => true, 'email' => true]);
        
        $this->assertIsJavaScript($js);
        $this->assertStringContainsString('errors', $js);
    }

    #[Test]
    public function it_skips_validation_for_empty_optional_fields(): void
    {
        $result = $this->validator->validate('website', '', ['url' => true]);
        
        // Empty value on optional field should pass
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_custom_error_messages(): void
    {
        $this->validator->setMessage('required', 'Custom required message');
        
        $result = $this->validator->validate('field', '', ['required' => true]);
        
        $this->assertStringContainsString('Custom required message', $result->getErrors()[0]);
    }

    #[Test]
    public function it_validates_length_range(): void
    {
        $result = $this->validator->validate('username', 'ab', [
            'minLength' => 3,
            'maxLength' => 20
        ]);
        
        $this->assertFalse($result->isValid());
        
        $result = $this->validator->validate('username', 'thisisaverylongusernamethatexceedslimit', [
            'minLength' => 3,
            'maxLength' => 20
        ]);
        
        $this->assertFalse($result->isValid());
        
        $result = $this->validator->validate('username', 'validname', [
            'minLength' => 3,
            'maxLength' => 20
        ]);
        
        $this->assertTrue($result->isValid());
    }
}
