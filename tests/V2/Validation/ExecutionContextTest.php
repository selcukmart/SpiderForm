<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Validation;

use FormGenerator\V2\Validation\ExecutionContext;
use FormGenerator\V2\Validation\ViolationBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ExecutionContext
 *
 * @covers \FormGenerator\V2\Validation\ExecutionContext
 */
class ExecutionContextTest extends TestCase
{
    private ExecutionContext $context;
    private array $data;

    protected function setUp(): void
    {
        $this->data = [
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ];
        $this->context = new ExecutionContext($this->data);
    }

    public function testConstructorStoresData(): void
    {
        $this->assertEquals($this->data, $this->context->getData());
    }

    public function testGetValueReturnsFieldValue(): void
    {
        $this->assertEquals('John', $this->context->getValue('name'));
        $this->assertEquals('john@example.com', $this->context->getValue('email'));
    }

    public function testGetValueReturnsNullForNonexistentField(): void
    {
        $this->assertNull($this->context->getValue('nonexistent'));
    }

    public function testBuildViolationReturnsViolationBuilder(): void
    {
        $builder = $this->context->buildViolation('Test error');

        $this->assertInstanceOf(ViolationBuilder::class, $builder);
    }

    public function testAddViolationStoresViolation(): void
    {
        $this->context->addViolation('Error message', 'field_name');

        $violations = $this->context->getViolations();

        $this->assertArrayHasKey('field_name', $violations);
        $this->assertContains('Error message', $violations['field_name']);
    }

    public function testAddViolationSupportsMultipleViolationsPerField(): void
    {
        $this->context->addViolation('Error 1', 'field');
        $this->context->addViolation('Error 2', 'field');

        $violations = $this->context->getViolations();

        $this->assertCount(2, $violations['field']);
        $this->assertContains('Error 1', $violations['field']);
        $this->assertContains('Error 2', $violations['field']);
    }

    public function testAddViolationInterpolatesParameters(): void
    {
        $this->context->addViolation(
            'Value must be at least {{ min }} characters',
            'field',
            ['min' => 5]
        );

        $violations = $this->context->getViolations();

        $this->assertEquals('Value must be at least 5 characters', $violations['field'][0]);
    }

    public function testAddViolationInterpolatesMultipleParameters(): void
    {
        $this->context->addViolation(
            'Value must be between {{ min }} and {{ max }}',
            'field',
            ['min' => 5, 'max' => 10]
        );

        $violations = $this->context->getViolations();

        $this->assertEquals('Value must be between 5 and 10', $violations['field'][0]);
    }

    public function testGetViolationsReturnsEmptyArrayInitially(): void
    {
        $violations = $this->context->getViolations();

        $this->assertIsArray($violations);
        $this->assertEmpty($violations);
    }

    public function testHasViolationsReturnsFalseInitially(): void
    {
        $this->assertFalse($this->context->hasViolations());
    }

    public function testHasViolationsReturnsTrueAfterAddingViolation(): void
    {
        $this->context->addViolation('Error', 'field');

        $this->assertTrue($this->context->hasViolations());
    }

    public function testSetCurrentPathStoresPath(): void
    {
        $this->context->setCurrentPath('user.email');

        $this->assertEquals('user.email', $this->context->getCurrentPath());
    }

    public function testGetCurrentPathReturnsEmptyStringInitially(): void
    {
        $this->assertEquals('', $this->context->getCurrentPath());
    }

    public function testSetCurrentPathReturnsThis(): void
    {
        $result = $this->context->setCurrentPath('field');

        $this->assertSame($this->context, $result);
    }

    public function testSetCurrentGroupStoresGroup(): void
    {
        $this->context->setCurrentGroup('registration');

        $this->assertEquals('registration', $this->context->getCurrentGroup());
    }

    public function testGetCurrentGroupReturnsNullInitially(): void
    {
        $this->assertNull($this->context->getCurrentGroup());
    }

    public function testSetCurrentGroupReturnsThis(): void
    {
        $result = $this->context->setCurrentGroup('registration');

        $this->assertSame($this->context, $result);
    }

    public function testSetCurrentGroupAcceptsNull(): void
    {
        $this->context->setCurrentGroup('test');
        $this->context->setCurrentGroup(null);

        $this->assertNull($this->context->getCurrentGroup());
    }

    public function testClearRemovesAllViolations(): void
    {
        $this->context->addViolation('Error 1', 'field1');
        $this->context->addViolation('Error 2', 'field2');

        $this->context->clear();

        $this->assertEmpty($this->context->getViolations());
        $this->assertFalse($this->context->hasViolations());
    }

    public function testViolationsAreGroupedByPath(): void
    {
        $this->context->addViolation('Error 1', 'email');
        $this->context->addViolation('Error 2', 'password');
        $this->context->addViolation('Error 3', 'email');

        $violations = $this->context->getViolations();

        $this->assertArrayHasKey('email', $violations);
        $this->assertArrayHasKey('password', $violations);
        $this->assertCount(2, $violations['email']);
        $this->assertCount(1, $violations['password']);
    }

    public function testNestedPathViolations(): void
    {
        $this->context->addViolation('Invalid city', 'address.city');
        $this->context->addViolation('Invalid zip', 'address.zipcode');

        $violations = $this->context->getViolations();

        $this->assertArrayHasKey('address.city', $violations);
        $this->assertArrayHasKey('address.zipcode', $violations);
    }

    public function testFluentInterface(): void
    {
        $this->context
            ->setCurrentPath('user.email')
            ->setCurrentGroup('registration');

        $this->assertEquals('user.email', $this->context->getCurrentPath());
        $this->assertEquals('registration', $this->context->getCurrentGroup());
    }

    public function testAddViolationWithEmptyParameters(): void
    {
        $this->context->addViolation('Simple error', 'field', []);

        $violations = $this->context->getViolations();

        $this->assertEquals('Simple error', $violations['field'][0]);
    }

    public function testParameterInterpolationWithMissingKey(): void
    {
        $this->context->addViolation(
            'Value must be at least {{ min }} characters',
            'field',
            ['max' => 10] // Missing 'min' parameter
        );

        $violations = $this->context->getViolations();

        // Should keep placeholder if parameter not provided
        $this->assertStringContainsString('{{ min }}', $violations['field'][0]);
    }

    public function testGetDataDoesNotModifyOriginal(): void
    {
        $data = $this->context->getData();
        $data['modified'] = 'value';

        $this->assertArrayNotHasKey('modified', $this->context->getData());
    }

    public function testComplexValidationScenario(): void
    {
        // Simulate complex cross-field validation
        $context = new ExecutionContext([
            'password' => 'secret123',
            'password_confirm' => 'different',
            'email' => 'invalid-email',
        ]);

        $context->setCurrentPath('password_confirm');
        $context->addViolation('Passwords do not match', 'password_confirm');

        $context->setCurrentPath('email');
        $context->addViolation('Email format is invalid', 'email');

        $violations = $context->getViolations();

        $this->assertCount(2, $violations);
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('password_confirm', $violations);
        $this->assertArrayHasKey('email', $violations);
    }
}
