<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Type;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for AbstractType
 *
 * @covers \FormGenerator\V2\Type\AbstractType
 */
class AbstractTypeTest extends TestCase
{
    public function testGetNameReturnsLowercaseNameWithoutTypeSuffix(): void
    {
        $type = new class extends AbstractType {
            // Uses default getName implementation
        };

        $name = $type->getName();

        $this->assertIsString($name);
        $this->assertStringNotContainsString('Type', $name);
    }

    public function testGetNameForCustomType(): void
    {
        $type = new class extends AbstractType {
            public function getName(): string
            {
                return 'custom_field';
            }
        };

        $this->assertEquals('custom_field', $type->getName());
    }

    public function testGetParentReturnsNullByDefault(): void
    {
        $type = new class extends AbstractType {};

        $this->assertNull($type->getParent());
    }

    public function testGetParentCanReturnParentTypeName(): void
    {
        $type = new class extends AbstractType {
            public function getParent(): ?string
            {
                return 'text';
            }
        };

        $this->assertEquals('text', $type->getParent());
    }

    public function testBuildFieldCanBeOverridden(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('setType')
            ->with('custom');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $builder->setType('custom');
            }
        };

        $type->buildField($builder, []);
    }

    public function testConfigureOptionsSetsCo mmonDefaults(): void
    {
        $type = new class extends AbstractType {};
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([]);

        $this->assertArrayHasKey('label', $resolved);
        $this->assertArrayHasKey('required', $resolved);
        $this->assertArrayHasKey('disabled', $resolved);
        $this->assertArrayHasKey('readonly', $resolved);
        $this->assertArrayHasKey('placeholder', $resolved);
        $this->assertArrayHasKey('help', $resolved);
        $this->assertArrayHasKey('attr', $resolved);
    }

    public function testConfigureOptionsDefaultValues(): void
    {
        $type = new class extends AbstractType {};
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([]);

        $this->assertNull($resolved['label']);
        $this->assertFalse($resolved['required']);
        $this->assertFalse($resolved['disabled']);
        $this->assertFalse($resolved['readonly']);
        $this->assertNull($resolved['placeholder']);
        $this->assertNull($resolved['help']);
        $this->assertIsArray($resolved['attr']);
    }

    public function testConfigureOptionsCanBeExtended(): void
    {
        $type = new class extends AbstractType {
            public function configureOptions(OptionsResolver $resolver): void
            {
                parent::configureOptions($resolver);
                $resolver->setDefault('custom_option', 'custom_value');
            }
        };

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([]);

        $this->assertEquals('custom_value', $resolved['custom_option']);
    }

    public function testConfigureOptionsValidatesTypes(): void
    {
        $type = new class extends AbstractType {};
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->expectException(\InvalidArgumentException::class);

        $resolver->resolve(['required' => 'not a boolean']);
    }

    public function testGetBlockPrefixReturnsTypeName(): void
    {
        $type = new class extends AbstractType {
            public function getName(): string
            {
                return 'custom';
            }
        };

        $this->assertEquals('custom', $type->getBlockPrefix());
    }

    public function testGetBlockPrefixCanBeOverridden(): void
    {
        $type = new class extends AbstractType {
            public function getBlockPrefix(): string
            {
                return 'custom_block';
            }
        };

        $this->assertEquals('custom_block', $type->getBlockPrefix());
    }

    public function testFinishViewDoesNothingByDefault(): void
    {
        $type = new class extends AbstractType {};
        $builder = $this->createMock(InputBuilder::class);

        // Should not throw exception
        $type->finishView($builder, []);

        $this->assertTrue(true);
    }

    public function testFinishViewCanBeOverridden(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addAttribute')
            ->with('data-custom', 'value');

        $type = new class extends AbstractType {
            public function finishView(InputBuilder $builder, array $options): void
            {
                $builder->addAttribute('data-custom', 'value');
            }
        };

        $type->finishView($builder, []);
    }

    public function testApplyCommonOptionsAppliesLabel(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('label')
            ->with('Test Label');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['label' => 'Test Label']);
    }

    public function testApplyCommonOptionsAppliesRequired(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('required');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['required' => true]);
    }

    public function testApplyCommonOptionsAppliesDisabled(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('disabled');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['disabled' => true]);
    }

    public function testApplyCommonOptionsAppliesReadonly(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('readonly');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['readonly' => true]);
    }

    public function testApplyCommonOptionsAppliesPlaceholder(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('placeholder')
            ->with('Enter text');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['placeholder' => 'Enter text']);
    }

    public function testApplyCommonOptionsAppliesHelp(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('helpText')
            ->with('Help message');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['help' => 'Help message']);
    }

    public function testApplyCommonOptionsAppliesAttributes(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->exactly(2))
            ->method('addAttribute')
            ->withConsecutive(
                ['data-test', 'value1'],
                ['class', 'custom-class']
            );

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, [
            'attr' => [
                'data-test' => 'value1',
                'class' => 'custom-class',
            ]
        ]);
    }

    public function testApplyCommonOptionsAppliesLabelAttributes(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addLabelAttribute')
            ->with('class', 'label-class');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, [
            'label_attr' => ['class' => 'label-class']
        ]);
    }

    public function testApplyCommonOptionsAppliesWrapperAttributes(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addWrapperAttribute')
            ->with('class', 'wrapper-class');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, [
            'wrapper_attr' => ['class' => 'wrapper-class']
        ]);
    }

    public function testApplyCommonOptionsSkipsNullLabel(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->never())
            ->method('label');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['label' => null]);
    }

    public function testApplyCommonOptionsSkipsFalseRequired(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->never())
            ->method('required');

        $type = new class extends AbstractType {
            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $type->buildField($builder, ['required' => false]);
    }

    public function testTypeInheritance(): void
    {
        $parentType = new class extends AbstractType {
            public function getName(): string
            {
                return 'parent';
            }
        };

        $childType = new class extends AbstractType {
            public function getName(): string
            {
                return 'child';
            }

            public function getParent(): ?string
            {
                return 'parent';
            }
        };

        $this->assertNull($parentType->getParent());
        $this->assertEquals('parent', $childType->getParent());
    }

    public function testComplexTypeConfiguration(): void
    {
        $type = new class extends AbstractType {
            public function configureOptions(OptionsResolver $resolver): void
            {
                parent::configureOptions($resolver);

                $resolver->setDefaults([
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                ]);

                $resolver->setAllowedTypes('min', 'int');
                $resolver->setAllowedTypes('max', 'int');
                $resolver->setAllowedTypes('step', 'int');
            }

            public function buildField(InputBuilder $builder, array $options): void
            {
                $this->applyCommonOptions($builder, $options);
            }
        };

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $resolved = $resolver->resolve([
            'label' => 'Number Input',
            'required' => true,
            'min' => 10,
            'max' => 50,
        ]);

        $this->assertEquals('Number Input', $resolved['label']);
        $this->assertTrue($resolved['required']);
        $this->assertEquals(10, $resolved['min']);
        $this->assertEquals(50, $resolved['max']);
        $this->assertEquals(1, $resolved['step']);
    }
}
