<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Type\Types;

use FormGenerator\V2\Type\Types\TextType;
use FormGenerator\V2\Type\Types\EmailType;
use FormGenerator\V2\Type\Types\SelectType;
use FormGenerator\V2\Type\Types\PasswordType;
use FormGenerator\V2\Type\Types\CheckboxType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for built-in field types
 *
 * @covers \FormGenerator\V2\Type\Types\TextType
 * @covers \FormGenerator\V2\Type\Types\EmailType
 * @covers \FormGenerator\V2\Type\Types\SelectType
 * @covers \FormGenerator\V2\Type\Types\PasswordType
 * @covers \FormGenerator\V2\Type\Types\CheckboxType
 */
class BuiltInTypesTest extends TestCase
{
    // ===== TextType Tests =====

    public function testTextTypeGetName(): void
    {
        $type = new TextType();

        $this->assertEquals('text', $type->getName());
    }

    public function testTextTypeHasNoParent(): void
    {
        $type = new TextType();

        $this->assertNull($type->getParent());
    }

    public function testTextTypeConfigureOptionsDefinesOptions(): void
    {
        $type = new TextType();
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([]);

        $this->assertArrayHasKey('minlength', $resolved);
        $this->assertArrayHasKey('maxlength', $resolved);
        $this->assertArrayHasKey('pattern', $resolved);
    }

    public function testTextTypeBuildFieldSetsType(): void
    {
        $type = new TextType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('setType')
            ->with(InputType::TEXT);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $type->buildField($builder, $options);
    }

    public function testTextTypeBuildFieldAppliesMinLength(): void
    {
        $type = new TextType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('minLength')
            ->with(5);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['minlength' => 5]);

        $type->buildField($builder, $options);
    }

    public function testTextTypeBuildFieldAppliesMaxLength(): void
    {
        $type = new TextType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('maxLength')
            ->with(100);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['maxlength' => 100]);

        $type->buildField($builder, $options);
    }

    public function testTextTypeBuildFieldAppliesPattern(): void
    {
        $type = new TextType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('regex')
            ->with('[A-Z]+');

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['pattern' => '[A-Z]+']);

        $type->buildField($builder, $options);
    }

    // ===== EmailType Tests =====

    public function testEmailTypeGetName(): void
    {
        $type = new EmailType();

        $this->assertEquals('email', $type->getName());
    }

    public function testEmailTypeExtendsText(): void
    {
        $type = new EmailType();

        $this->assertEquals('text', $type->getParent());
    }

    public function testEmailTypeBuildFieldSetsEmailType(): void
    {
        $type = new EmailType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('setType')
            ->with(InputType::EMAIL);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $type->buildField($builder, $options);
    }

    public function testEmailTypeBuildFieldAddsEmailValidation(): void
    {
        $type = new EmailType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('email');

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $type->buildField($builder, $options);
    }

    public function testEmailTypeSupportsMultiple(): void
    {
        $type = new EmailType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addAttribute')
            ->with('multiple', 'multiple');

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['multiple' => true]);

        $type->buildField($builder, $options);
    }

    public function testEmailTypeConfigureOptionsDefinesMultiple(): void
    {
        $type = new EmailType();
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([]);

        $this->assertArrayHasKey('multiple', $resolved);
        $this->assertFalse($resolved['multiple']);
    }

    // ===== SelectType Tests =====

    public function testSelectTypeGetName(): void
    {
        $type = new SelectType();

        $this->assertEquals('select', $type->getName());
    }

    public function testSelectTypeExtendsChoice(): void
    {
        $type = new SelectType();

        $this->assertEquals('choice', $type->getParent());
    }

    public function testSelectTypeBuildFieldSetsSelectType(): void
    {
        $type = new SelectType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('setType')
            ->with(InputType::SELECT);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $type->buildField($builder, $options);
    }

    public function testSelectTypeBuildFieldAppliesChoices(): void
    {
        $type = new SelectType();
        $builder = $this->createMock(InputBuilder::class);
        $choices = ['option1' => 'Option 1', 'option2' => 'Option 2'];
        $builder->expects($this->once())
            ->method('options')
            ->with($choices);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['choices' => $choices]);

        $type->buildField($builder, $options);
    }

    public function testSelectTypeSupportsMultiple(): void
    {
        $type = new SelectType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addAttribute')
            ->with('multiple', 'multiple');

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['multiple' => true]);

        $type->buildField($builder, $options);
    }

    public function testSelectTypeSupportsSize(): void
    {
        $type = new SelectType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addAttribute')
            ->with('size', '5');

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['size' => 5]);

        $type->buildField($builder, $options);
    }

    public function testSelectTypeConfigureOptionsDefaults(): void
    {
        $type = new SelectType();
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([]);

        $this->assertArrayHasKey('choices', $resolved);
        $this->assertArrayHasKey('multiple', $resolved);
        $this->assertArrayHasKey('size', $resolved);
        $this->assertIsArray($resolved['choices']);
        $this->assertFalse($resolved['multiple']);
        $this->assertNull($resolved['size']);
    }

    // ===== PasswordType Tests =====

    public function testPasswordTypeGetName(): void
    {
        $type = new PasswordType();

        $this->assertEquals('password', $type->getName());
    }

    public function testPasswordTypeBuildFieldSetsPasswordType(): void
    {
        $type = new PasswordType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('setType')
            ->with(InputType::PASSWORD);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $type->buildField($builder, $options);
    }

    // ===== CheckboxType Tests =====

    public function testCheckboxTypeGetName(): void
    {
        $type = new CheckboxType();

        $this->assertEquals('checkbox', $type->getName());
    }

    public function testCheckboxTypeBuildFieldSetsCheckboxType(): void
    {
        $type = new CheckboxType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('setType')
            ->with(InputType::CHECKBOX);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $type->buildField($builder, $options);
    }

    public function testCheckboxTypeSupportsCheckedOption(): void
    {
        $type = new CheckboxType();
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolved = $resolver->resolve(['checked' => true]);

        $this->assertTrue($resolved['checked']);
    }

    // ===== Integration Tests =====

    public function testAllTypesInheritCommonOptions(): void
    {
        $types = [
            new TextType(),
            new EmailType(),
            new SelectType(),
            new PasswordType(),
            new CheckboxType(),
        ];

        foreach ($types as $type) {
            $resolver = new OptionsResolver();
            $type->configureOptions($resolver);
            $resolved = $resolver->resolve([]);

            $this->assertArrayHasKey('label', $resolved);
            $this->assertArrayHasKey('required', $resolved);
            $this->assertArrayHasKey('disabled', $resolved);
            $this->assertArrayHasKey('readonly', $resolved);
            $this->assertArrayHasKey('attr', $resolved);
        }
    }

    public function testTypesCanOverrideCommonOptions(): void
    {
        $type = new TextType();
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([
            'label' => 'Custom Label',
            'required' => true,
            'disabled' => false,
        ]);

        $this->assertEquals('Custom Label', $resolved['label']);
        $this->assertTrue($resolved['required']);
        $this->assertFalse($resolved['disabled']);
    }

    public function testTypeOptionsAreValidated(): void
    {
        $type = new TextType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->expectException(\InvalidArgumentException::class);

        $resolver->resolve(['minlength' => 'not an integer']);
    }

    public function testSelectTypeEmptyChoicesDoesNotCallOptions(): void
    {
        $type = new SelectType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->never())
            ->method('options');

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['choices' => []]);

        $type->buildField($builder, $options);
    }

    public function testEmailTypeMultipleDefaultsFalse(): void
    {
        $type = new EmailType();
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->never())
            ->method('addAttribute')
            ->with('multiple', $this->anything());

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        $type->buildField($builder, $options);
    }
}
