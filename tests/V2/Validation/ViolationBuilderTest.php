<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Validation;

use FormGenerator\V2\Validation\ExecutionContext;
use FormGenerator\V2\Validation\ViolationBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ViolationBuilder
 *
 * @covers \FormGenerator\V2\Validation\ViolationBuilder
 */
class ViolationBuilderTest extends TestCase
{
    private ExecutionContext $context;

    protected function setUp(): void
    {
        $this->context = new ExecutionContext(['test' => 'value']);
    }

    public function testConstructorUsesContextCurrentPath(): void
    {
        $this->context->setCurrentPath('field1');
        $builder = new ViolationBuilder($this->context, 'Error message');

        $builder->addViolation();

        $violations = $this->context->getViolations();
        $this->assertArrayHasKey('field1', $violations);
    }

    public function testAtPathSetsCustomPath(): void
    {
        $builder = $this->context->buildViolation('Error message');

        $builder->atPath('custom_field')->addViolation();

        $violations = $this->context->getViolations();
        $this->assertArrayHasKey('custom_field', $violations);
    }

    public function testAtPathReturnsThis(): void
    {
        $builder = $this->context->buildViolation('Error');

        $result = $builder->atPath('field');

        $this->assertSame($builder, $result);
    }

    public function testSetParametersStoresParameters(): void
    {
        $builder = $this->context->buildViolation('Value must be at least {{ min }}');

        $builder->setParameters(['min' => 5])
                ->atPath('field')
                ->addViolation();

        $violations = $this->context->getViolations();
        $this->assertEquals('Value must be at least 5', $violations['field'][0]);
    }

    public function testSetParametersReturnsThis(): void
    {
        $builder = $this->context->buildViolation('Error');

        $result = $builder->setParameters(['key' => 'value']);

        $this->assertSame($builder, $result);
    }

    public function testSetParameterSetsSingleParameter(): void
    {
        $builder = $this->context->buildViolation('Min: {{ min }}, Max: {{ max }}');

        $builder->setParameter('min', 5)
                ->setParameter('max', 10)
                ->atPath('field')
                ->addViolation();

        $violations = $this->context->getViolations();
        $this->assertEquals('Min: 5, Max: 10', $violations['field'][0]);
    }

    public function testSetParameterReturnsThis(): void
    {
        $builder = $this->context->buildViolation('Error');

        $result = $builder->setParameter('key', 'value');

        $this->assertSame($builder, $result);
    }

    public function testAddViolationAddsToContext(): void
    {
        $builder = $this->context->buildViolation('Error message');

        $builder->atPath('field')->addViolation();

        $this->assertTrue($this->context->hasViolations());
        $violations = $this->context->getViolations();
        $this->assertContains('Error message', $violations['field']);
    }

    public function testFluentInterface(): void
    {
        $this->context->buildViolation('Value must be between {{ min }} and {{ max }}')
                      ->atPath('age')
                      ->setParameter('min', 18)
                      ->setParameter('max', 100)
                      ->addViolation();

        $violations = $this->context->getViolations();
        $this->assertEquals('Value must be between 18 and 100', $violations['age'][0]);
    }

    public function testMultipleViolationsWithBuilder(): void
    {
        $this->context->buildViolation('Error 1')
                      ->atPath('field1')
                      ->addViolation();

        $this->context->buildViolation('Error 2')
                      ->atPath('field2')
                      ->addViolation();

        $violations = $this->context->getViolations();
        $this->assertCount(2, $violations);
        $this->assertArrayHasKey('field1', $violations);
        $this->assertArrayHasKey('field2', $violations);
    }

    public function testSetParametersOverwritesPreviousParameters(): void
    {
        $builder = $this->context->buildViolation('Value: {{ value }}');

        $builder->setParameters(['value' => 'first'])
                ->setParameters(['value' => 'second'])
                ->atPath('field')
                ->addViolation();

        $violations = $this->context->getViolations();
        $this->assertEquals('Value: second', $violations['field'][0]);
    }

    public function testSetParameterAddsToExistingParameters(): void
    {
        $builder = $this->context->buildViolation('{{ a }}, {{ b }}, {{ c }}');

        $builder->setParameters(['a' => '1', 'b' => '2'])
                ->setParameter('c', '3')
                ->atPath('field')
                ->addViolation();

        $violations = $this->context->getViolations();
        $this->assertEquals('1, 2, 3', $violations['field'][0]);
    }

    public function testBuilderUsesEmptyPathByDefault(): void
    {
        $builder = $this->context->buildViolation('Error');

        $builder->addViolation();

        $violations = $this->context->getViolations();
        $this->assertArrayHasKey('', $violations);
    }

    public function testNestedPathSupport(): void
    {
        $builder = $this->context->buildViolation('Invalid city');

        $builder->atPath('address.city')->addViolation();

        $violations = $this->context->getViolations();
        $this->assertArrayHasKey('address.city', $violations);
    }

    public function testComplexValidationScenario(): void
    {
        // Password validation
        $this->context->buildViolation('Password must be at least {{ min }} characters')
                      ->atPath('password')
                      ->setParameter('min', 8)
                      ->addViolation();

        // Email validation
        $this->context->buildViolation('Email format is invalid')
                      ->atPath('email')
                      ->addViolation();

        // Cross-field validation
        $this->context->buildViolation('Passwords do not match')
                      ->atPath('password_confirm')
                      ->addViolation();

        $violations = $this->context->getViolations();

        $this->assertCount(3, $violations);
        $this->assertEquals('Password must be at least 8 characters', $violations['password'][0]);
        $this->assertEquals('Email format is invalid', $violations['email'][0]);
        $this->assertEquals('Passwords do not match', $violations['password_confirm'][0]);
    }

    public function testBuilderCanBeReused(): void
    {
        $builder = $this->context->buildViolation('Required field');

        $builder->atPath('field1')->addViolation();
        $builder->atPath('field2')->addViolation();

        $violations = $this->context->getViolations();

        // Both violations should use the same message but different paths
        $this->assertCount(2, $violations);
        $this->assertEquals('Required field', $violations['field1'][0]);
        $this->assertEquals('Required field', $violations['field2'][0]);
    }

    public function testParameterInterpolationWithSpecialCharacters(): void
    {
        $builder = $this->context->buildViolation('Value {{ value }} is invalid');

        $builder->setParameter('value', 'test@example.com')
                ->atPath('field')
                ->addViolation();

        $violations = $this->context->getViolations();
        $this->assertEquals('Value test@example.com is invalid', $violations['field'][0]);
    }

    public function testViolationWithoutPathUsesContextPath(): void
    {
        $this->context->setCurrentPath('default_field');
        $builder = $this->context->buildViolation('Error');

        $builder->addViolation();

        $violations = $this->context->getViolations();
        $this->assertArrayHasKey('default_field', $violations);
    }
}
