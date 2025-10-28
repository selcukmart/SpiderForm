<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Type;

use FormGenerator\V2\Type\TypeExtensionRegistry;
use FormGenerator\V2\Type\TypeExtensionInterface;
use FormGenerator\V2\Type\AbstractTypeExtension;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TypeExtensionRegistry
 *
 * @covers \FormGenerator\V2\Type\TypeExtensionRegistry
 */
class TypeExtensionRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        TypeExtensionRegistry::clear();
    }

    protected function tearDown(): void
    {
        TypeExtensionRegistry::clear();
    }

    public function testRegisterStoresExtension(): void
    {
        $extension = $this->createMockExtension('text');

        TypeExtensionRegistry::register($extension);

        $extensions = TypeExtensionRegistry::getExtensionsForType('text');
        $this->assertCount(1, $extensions);
        $this->assertSame($extension, $extensions[0]);
    }

    public function testRegisterManySto resMultipleExtensions(): void
    {
        $extension1 = $this->createMockExtension('text');
        $extension2 = $this->createMockExtension('email');

        TypeExtensionRegistry::registerMany([$extension1, $extension2]);

        $this->assertCount(1, TypeExtensionRegistry::getExtensionsForType('text'));
        $this->assertCount(1, TypeExtensionRegistry::getExtensionsForType('email'));
    }

    public function testGetExtensionsForTypeReturnsMatchingExtensions(): void
    {
        $textExtension = $this->createMockExtension('text');
        $emailExtension = $this->createMockExtension('email');

        TypeExtensionRegistry::register($textExtension);
        TypeExtensionRegistry::register($emailExtension);

        $textExtensions = TypeExtensionRegistry::getExtensionsForType('text');
        $emailExtensions = TypeExtensionRegistry::getExtensionsForType('email');

        $this->assertCount(1, $textExtensions);
        $this->assertCount(1, $emailExtensions);
        $this->assertSame($textExtension, $textExtensions[0]);
        $this->assertSame($emailExtension, $emailExtensions[0]);
    }

    public function testGetExtensionsForTypeReturnsEmptyArrayWhenNoneMatch(): void
    {
        $extension = $this->createMockExtension('text');
        TypeExtensionRegistry::register($extension);

        $extensions = TypeExtensionRegistry::getExtensionsForType('select');

        $this->assertIsArray($extensions);
        $this->assertEmpty($extensions);
    }

    public function testExtensionApplyingToMultipleTypes(): void
    {
        $extension = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return ['text', 'email', 'password'];
            }
        };

        TypeExtensionRegistry::register($extension);

        $this->assertContains($extension, TypeExtensionRegistry::getExtensionsForType('text'));
        $this->assertContains($extension, TypeExtensionRegistry::getExtensionsForType('email'));
        $this->assertContains($extension, TypeExtensionRegistry::getExtensionsForType('password'));
    }

    public function testGetExtensionsForTypeCachesResults(): void
    {
        $extension = $this->createMockExtension('text');
        TypeExtensionRegistry::register($extension);

        $extensions1 = TypeExtensionRegistry::getExtensionsForType('text');
        $extensions2 = TypeExtensionRegistry::getExtensionsForType('text');

        $this->assertSame($extensions1, $extensions2);
    }

    public function testRegisterClearsCacheForAllTypes(): void
    {
        $extension1 = $this->createMockExtension('text');
        TypeExtensionRegistry::register($extension1);

        $extensions1 = TypeExtensionRegistry::getExtensionsForType('text');

        $extension2 = $this->createMockExtension('text');
        TypeExtensionRegistry::register($extension2);

        $extensions2 = TypeExtensionRegistry::getExtensionsForType('text');

        $this->assertNotSame($extensions1, $extensions2);
        $this->assertCount(2, $extensions2);
    }

    public function testGetAllExtensionsReturnsAllRegistered(): void
    {
        $extension1 = $this->createMockExtension('text');
        $extension2 = $this->createMockExtension('email');
        $extension3 = $this->createMockExtension('select');

        TypeExtensionRegistry::registerMany([$extension1, $extension2, $extension3]);

        $all = TypeExtensionRegistry::getAllExtensions();

        $this->assertCount(3, $all);
        $this->assertContains($extension1, $all);
        $this->assertContains($extension2, $all);
        $this->assertContains($extension3, $all);
    }

    public function testClearRemovesAllExtensions(): void
    {
        $extension1 = $this->createMockExtension('text');
        $extension2 = $this->createMockExtension('email');

        TypeExtensionRegistry::registerMany([$extension1, $extension2]);
        $this->assertCount(2, TypeExtensionRegistry::getAllExtensions());

        TypeExtensionRegistry::clear();

        $this->assertEmpty(TypeExtensionRegistry::getAllExtensions());
        $this->assertEmpty(TypeExtensionRegistry::getExtensionsForType('text'));
    }

    public function testHasExtensionsForTypeReturnsTrueWhenExtensionsExist(): void
    {
        $extension = $this->createMockExtension('text');
        TypeExtensionRegistry::register($extension);

        $this->assertTrue(TypeExtensionRegistry::hasExtensionsForType('text'));
        $this->assertFalse(TypeExtensionRegistry::hasExtensionsForType('email'));
    }

    public function testCountReturnsNumberOfRegisteredExtensions(): void
    {
        $this->assertEquals(0, TypeExtensionRegistry::count());

        TypeExtensionRegistry::register($this->createMockExtension('text'));
        $this->assertEquals(1, TypeExtensionRegistry::count());

        TypeExtensionRegistry::register($this->createMockExtension('email'));
        $this->assertEquals(2, TypeExtensionRegistry::count());
    }

    public function testMultipleExtensionsForSameType(): void
    {
        $extension1 = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return 'text';
            }

            public function buildField(InputBuilder $builder, array $options): void
            {
                // Extension 1 logic
            }
        };

        $extension2 = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return 'text';
            }

            public function buildField(InputBuilder $builder, array $options): void
            {
                // Extension 2 logic
            }
        };

        TypeExtensionRegistry::registerMany([$extension1, $extension2]);

        $extensions = TypeExtensionRegistry::getExtensionsForType('text');

        $this->assertCount(2, $extensions);
        $this->assertContains($extension1, $extensions);
        $this->assertContains($extension2, $extensions);
    }

    public function testExtensionOrderIsPreserved(): void
    {
        $extension1 = $this->createMockExtension('text');
        $extension2 = $this->createMockExtension('text');
        $extension3 = $this->createMockExtension('text');

        TypeExtensionRegistry::register($extension1);
        TypeExtensionRegistry::register($extension2);
        TypeExtensionRegistry::register($extension3);

        $extensions = TypeExtensionRegistry::getExtensionsForType('text');

        $this->assertSame($extension1, $extensions[0]);
        $this->assertSame($extension2, $extensions[1]);
        $this->assertSame($extension3, $extensions[2]);
    }

    public function testAbstractTypeExtensionProvidesDefaults(): void
    {
        $extension = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return 'text';
            }
        };

        $builder = $this->createMock(InputBuilder::class);
        $resolver = new OptionsResolver();

        // Should not throw exceptions
        $extension->buildField($builder, []);
        $extension->configureOptions($resolver);
        $extension->finishView($builder, []);

        $this->assertTrue(true);
    }

    public function testAbstractTypeExtensionCanOverrideMethods(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addAttribute')
            ->with('data-extension', 'applied');

        $extension = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return 'text';
            }

            public function buildField(InputBuilder $builder, array $options): void
            {
                $builder->addAttribute('data-extension', 'applied');
            }
        };

        $extension->buildField($builder, []);
    }

    public function testExtensionConfiguresOptionsCorrectly(): void
    {
        $extension = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return 'text';
            }

            public function configureOptions(OptionsResolver $resolver): void
            {
                $resolver->setDefault('custom_option', 'default_value');
                $resolver->setAllowedTypes('custom_option', 'string');
            }
        };

        $resolver = new OptionsResolver();
        $extension->configureOptions($resolver);
        $resolved = $resolver->resolve([]);

        $this->assertEquals('default_value', $resolved['custom_option']);
    }

    public function testExtensionFinishViewCanModifyBuilder(): void
    {
        $builder = $this->createMock(InputBuilder::class);
        $builder->expects($this->once())
            ->method('addAttribute')
            ->with('data-finalized', 'true');

        $extension = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return 'text';
            }

            public function finishView(InputBuilder $builder, array $options): void
            {
                $builder->addAttribute('data-finalized', 'true');
            }
        };

        $extension->finishView($builder, []);
    }

    public function testComplexExtensionScenario(): void
    {
        // Extension that adds help text functionality
        $helpTextExtension = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return ['text', 'email', 'password'];
            }

            public function configureOptions(OptionsResolver $resolver): void
            {
                $resolver->setDefined('help_text');
                $resolver->setAllowedTypes('help_text', ['null', 'string']);
            }

            public function buildField(InputBuilder $builder, array $options): void
            {
                if (!empty($options['help_text'])) {
                    $builder->helpText($options['help_text']);
                }
            }
        };

        // Extension that adds data attributes
        $dataAttributeExtension = new class extends AbstractTypeExtension {
            public function extendType(): string|array
            {
                return 'text';
            }

            public function buildField(InputBuilder $builder, array $options): void
            {
                $builder->addAttribute('data-type', 'extended');
            }
        };

        TypeExtensionRegistry::registerMany([$helpTextExtension, $dataAttributeExtension]);

        // Text type should have both extensions
        $textExtensions = TypeExtensionRegistry::getExtensionsForType('text');
        $this->assertCount(2, $textExtensions);

        // Email type should only have help text extension
        $emailExtensions = TypeExtensionRegistry::getExtensionsForType('email');
        $this->assertCount(1, $emailExtensions);
    }

    /**
     * Helper method to create a mock extension for testing
     */
    private function createMockExtension(string|array $extendedType): TypeExtensionInterface
    {
        return new class($extendedType) extends AbstractTypeExtension {
            public function __construct(private string|array $extendedType)
            {
            }

            public function extendType(): string|array
            {
                return $this->extendedType;
            }
        };
    }
}
