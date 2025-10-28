<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Type;

use FormGenerator\V2\Type\TypeRegistry;
use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\Types\TextType;
use FormGenerator\V2\Type\Types\EmailType;
use FormGenerator\V2\Builder\InputBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TypeRegistry
 *
 * @covers \FormGenerator\V2\Type\TypeRegistry
 */
class TypeRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear registry before each test
        TypeRegistry::clear();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        TypeRegistry::clear();
    }

    public function testRegisterStoresType(): void
    {
        TypeRegistry::register('custom', TextType::class);

        $this->assertTrue(TypeRegistry::has('custom'));
    }

    public function testRegisterAcceptsInstance(): void
    {
        $instance = new TextType();

        TypeRegistry::register('text', $instance);

        $this->assertTrue(TypeRegistry::has('text'));
    }

    public function testRegisterManyRegist ersMultipleTypes(): void
    {
        TypeRegistry::registerMany([
            'text' => TextType::class,
            'email' => EmailType::class,
        ]);

        $this->assertTrue(TypeRegistry::has('text'));
        $this->assertTrue(TypeRegistry::has('email'));
    }

    public function testHasReturnsTrueForRegisteredType(): void
    {
        TypeRegistry::register('text', TextType::class);

        $this->assertTrue(TypeRegistry::has('text'));
        $this->assertFalse(TypeRegistry::has('nonexistent'));
    }

    public function testGetReturnsTypeInstance(): void
    {
        TypeRegistry::register('text', TextType::class);

        $type = TypeRegistry::get('text');

        $this->assertInstanceOf(TextType::class, $type);
    }

    public function testGetCachesInstances(): void
    {
        TypeRegistry::register('text', TextType::class);

        $type1 = TypeRegistry::get('text');
        $type2 = TypeRegistry::get('text');

        $this->assertSame($type1, $type2);
    }

    public function testGetThrowsExceptionForUnregisteredType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type "nonexistent" is not registered');

        TypeRegistry::get('nonexistent');
    }

    public function testGetThrowsExceptionForNonexistentClass(): void
    {
        TypeRegistry::register('invalid', 'NonexistentClass');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type class "NonexistentClass" does not exist');

        TypeRegistry::get('invalid');
    }

    public function testGetThrowsExceptionForInvalidType(): void
    {
        TypeRegistry::register('invalid', \stdClass::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must extend AbstractType');

        TypeRegistry::get('invalid');
    }

    public function testUnregisterRemovesType(): void
    {
        TypeRegistry::register('text', TextType::class);
        $this->assertTrue(TypeRegistry::has('text'));

        TypeRegistry::unregister('text');

        $this->assertFalse(TypeRegistry::has('text'));
    }

    public function testUnregisterRemovesCachedInstance(): void
    {
        TypeRegistry::register('text', TextType::class);
        $type1 = TypeRegistry::get('text');

        TypeRegistry::unregister('text');
        TypeRegistry::register('text', TextType::class);
        $type2 = TypeRegistry::get('text');

        $this->assertNotSame($type1, $type2);
    }

    public function testAliasCreatesTypeAlias(): void
    {
        TypeRegistry::register('text', TextType::class);
        TypeRegistry::alias('string', 'text');

        $this->assertTrue(TypeRegistry::has('string'));
    }

    public function testAliasResolvesToRealType(): void
    {
        TypeRegistry::register('text', TextType::class);
        TypeRegistry::alias('string', 'text');

        $textType = TypeRegistry::get('text');
        $stringType = TypeRegistry::get('string');

        $this->assertSame($textType, $stringType);
    }

    public function testGetTypeNamesReturnsAllRegisteredNames(): void
    {
        TypeRegistry::registerMany([
            'text' => TextType::class,
            'email' => EmailType::class,
        ]);

        $names = TypeRegistry::getTypeNames();

        $this->assertContains('text', $names);
        $this->assertContains('email', $names);
        $this->assertCount(2, $names);
    }

    public function testGetAliasesReturnsAllAliases(): void
    {
        TypeRegistry::alias('string', 'text');
        TypeRegistry::alias('mail', 'email');

        $aliases = TypeRegistry::getAliases();

        $this->assertEquals('text', $aliases['string']);
        $this->assertEquals('email', $aliases['mail']);
        $this->assertCount(2, $aliases);
    }

    public function testClearRemovesAllTypes(): void
    {
        TypeRegistry::registerMany([
            'text' => TextType::class,
            'email' => EmailType::class,
        ]);
        TypeRegistry::alias('string', 'text');

        TypeRegistry::clear();

        $this->assertFalse(TypeRegistry::has('text'));
        $this->assertFalse(TypeRegistry::has('email'));
        $this->assertEmpty(TypeRegistry::getTypeNames());
        $this->assertEmpty(TypeRegistry::getAliases());
    }

    public function testRegisterBuiltInTypesRegistersStandardTypes(): void
    {
        TypeRegistry::registerBuiltInTypes();

        $this->assertTrue(TypeRegistry::has('text'));
        $this->assertTrue(TypeRegistry::has('email'));
        $this->assertTrue(TypeRegistry::has('password'));
        $this->assertTrue(TypeRegistry::has('number'));
        $this->assertTrue(TypeRegistry::has('checkbox'));
        $this->assertTrue(TypeRegistry::has('select'));
    }

    public function testRegisterBuiltInTypesRegistersAliases(): void
    {
        TypeRegistry::registerBuiltInTypes();

        $this->assertTrue(TypeRegistry::has('string')); // Alias for text
        $this->assertTrue(TypeRegistry::has('int')); // Alias for integer
        $this->assertTrue(TypeRegistry::has('phone')); // Alias for tel
    }

    public function testRegisterBuiltInTypesIsIdempotent(): void
    {
        TypeRegistry::registerBuiltInTypes();
        $count1 = count(TypeRegistry::getTypeNames());

        TypeRegistry::registerBuiltInTypes();
        $count2 = count(TypeRegistry::getTypeNames());

        $this->assertEquals($count1, $count2);
    }

    public function testGetTypeHierarchyReturnsTypeChain(): void
    {
        TypeRegistry::registerBuiltInTypes();

        $hierarchy = TypeRegistry::getTypeHierarchy('email');

        $this->assertNotEmpty($hierarchy);
        $this->assertInstanceOf(EmailType::class, $hierarchy[0]);
    }

    public function testIsTypeOfChecksTypeInheritance(): void
    {
        TypeRegistry::registerBuiltInTypes();

        // Email extends text
        $this->assertTrue(TypeRegistry::isTypeOf('email', 'text'));
        $this->assertFalse(TypeRegistry::isTypeOf('text', 'email'));
    }

    public function testRegisterOverwritesExistingType(): void
    {
        TypeRegistry::register('custom', TextType::class);
        $type1 = TypeRegistry::get('custom');

        TypeRegistry::register('custom', EmailType::class);
        $type2 = TypeRegistry::get('custom');

        $this->assertInstanceOf(EmailType::class, $type2);
        $this->assertNotSame($type1, $type2);
    }

    public function testRegisterClearsCachedInstance(): void
    {
        TypeRegistry::register('text', TextType::class);
        $instance1 = TypeRegistry::get('text');

        // Re-register the same type
        TypeRegistry::register('text', TextType::class);
        $instance2 = TypeRegistry::get('text');

        // Should be different instances
        $this->assertNotSame($instance1, $instance2);
    }

    public function testCustomTypeRegistration(): void
    {
        $customType = new class extends AbstractType {
            public function getName(): string
            {
                return 'custom';
            }
        };

        TypeRegistry::register('custom', $customType);

        $retrieved = TypeRegistry::get('custom');

        $this->assertSame($customType, $retrieved);
    }

    public function testAliasWithoutRegisteredTypeThrowsException(): void
    {
        TypeRegistry::alias('string', 'text');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type "text" is not registered');

        TypeRegistry::get('string');
    }

    public function testMultipleAliasesForSameType(): void
    {
        TypeRegistry::register('text', TextType::class);
        TypeRegistry::alias('string', 'text');
        TypeRegistry::alias('str', 'text');

        $text = TypeRegistry::get('text');
        $string = TypeRegistry::get('string');
        $str = TypeRegistry::get('str');

        $this->assertSame($text, $string);
        $this->assertSame($text, $str);
    }

    public function testGetTypeHierarchyForTypeWithoutParent(): void
    {
        TypeRegistry::register('text', TextType::class);

        $hierarchy = TypeRegistry::getTypeHierarchy('text');

        $this->assertCount(1, $hierarchy);
        $this->assertInstanceOf(TextType::class, $hierarchy[0]);
    }

    public function testIsTypeOfForSameType(): void
    {
        TypeRegistry::register('text', TextType::class);

        $this->assertTrue(TypeRegistry::isTypeOf('text', 'text'));
    }
}
