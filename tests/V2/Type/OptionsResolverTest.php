<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Type;

use FormGenerator\V2\Type\OptionsResolver;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for OptionsResolver
 *
 * @covers \FormGenerator\V2\Type\OptionsResolver
 */
class OptionsResolverTest extends TestCase
{
    private OptionsResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new OptionsResolver();
    }

    public function testSetDefaultsStoresDefaultValues(): void
    {
        $defaults = ['name' => 'test', 'required' => false];

        $this->resolver->setDefaults($defaults);

        $this->assertTrue($this->resolver->hasDefault('name'));
        $this->assertTrue($this->resolver->hasDefault('required'));
        $this->assertEquals('test', $this->resolver->getDefault('name'));
        $this->assertFalse($this->resolver->getDefault('required'));
    }

    public function testSetDefaultSingleValue(): void
    {
        $this->resolver->setDefault('label', 'Default Label');

        $this->assertTrue($this->resolver->hasDefault('label'));
        $this->assertEquals('Default Label', $this->resolver->getDefault('label'));
    }

    public function testSetDefaultMakesOptionDefined(): void
    {
        $this->resolver->setDefault('custom', 'value');

        $this->assertTrue($this->resolver->isDefined('custom'));
    }

    public function testSetRequiredMarksOptionAsRequired(): void
    {
        $this->resolver->setRequired('email');

        $this->assertTrue($this->resolver->isRequired('email'));
        $this->assertTrue($this->resolver->isDefined('email'));
    }

    public function testSetRequiredAcceptsArray(): void
    {
        $this->resolver->setRequired(['name', 'email', 'password']);

        $this->assertTrue($this->resolver->isRequired('name'));
        $this->assertTrue($this->resolver->isRequired('email'));
        $this->assertTrue($this->resolver->isRequired('password'));
    }

    public function testSetDefinedMarksOptionsAsDefined(): void
    {
        $this->resolver->setDefined(['option1', 'option2']);

        $this->assertTrue($this->resolver->isDefined('option1'));
        $this->assertTrue($this->resolver->isDefined('option2'));
    }

    public function testResolveWithDefaults(): void
    {
        $this->resolver->setDefaults([
            'name' => 'default',
            'required' => false,
        ]);

        $resolved = $this->resolver->resolve(['name' => 'custom']);

        $this->assertEquals('custom', $resolved['name']);
        $this->assertFalse($resolved['required']);
    }

    public function testResolveThrowsExceptionForUndefinedOption(): void
    {
        $this->resolver->setDefaults(['name' => 'test']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The option(s) "email" do not exist');

        $this->resolver->resolve(['email' => 'test@example.com']);
    }

    public function testResolveThrowsExceptionForMissingRequiredOption(): void
    {
        $this->resolver->setRequired('email');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The required option(s) "email" are missing');

        $this->resolver->resolve([]);
    }

    public function testSetAllowedTypesValidatesStringType(): void
    {
        $this->resolver->setDefault('name', 'test');
        $this->resolver->setAllowedTypes('name', 'string');

        $resolved = $this->resolver->resolve(['name' => 'valid string']);

        $this->assertEquals('valid string', $resolved['name']);
    }

    public function testSetAllowedTypesThrowsExceptionForInvalidType(): void
    {
        $this->resolver->setDefault('age', 0);
        $this->resolver->setAllowedTypes('age', 'int');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('expected to be of type "int", but is of type "string"');

        $this->resolver->resolve(['age' => 'not a number']);
    }

    public function testSetAllowedTypesAcceptsMultipleTypes(): void
    {
        $this->resolver->setDefault('value', null);
        $this->resolver->setAllowedTypes('value', ['null', 'string']);

        $resolved1 = $this->resolver->resolve(['value' => null]);
        $resolved2 = $this->resolver->resolve(['value' => 'string']);

        $this->assertNull($resolved1['value']);
        $this->assertEquals('string', $resolved2['value']);
    }

    public function testSetAllowedTypesValidatesBoolType(): void
    {
        $this->resolver->setDefault('required', false);
        $this->resolver->setAllowedTypes('required', 'bool');

        $resolved = $this->resolver->resolve(['required' => true]);

        $this->assertTrue($resolved['required']);
    }

    public function testSetAllowedTypesValidatesArrayType(): void
    {
        $this->resolver->setDefault('options', []);
        $this->resolver->setAllowedTypes('options', 'array');

        $resolved = $this->resolver->resolve(['options' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $resolved['options']);
    }

    public function testSetAllowedTypesValidatesCallableType(): void
    {
        $this->resolver->setDefault('callback', null);
        $this->resolver->setAllowedTypes('callback', 'callable');

        $resolved = $this->resolver->resolve(['callback' => fn() => 'test']);

        $this->assertIsCallable($resolved['callback']);
    }

    public function testSetAllowedValuesWithArray(): void
    {
        $this->resolver->setDefault('size', 'medium');
        $this->resolver->setAllowedValues('size', ['small', 'medium', 'large']);

        $resolved = $this->resolver->resolve(['size' => 'large']);

        $this->assertEquals('large', $resolved['size']);
    }

    public function testSetAllowedValuesThrowsExceptionForInvalidValue(): void
    {
        $this->resolver->setDefault('size', 'medium');
        $this->resolver->setAllowedValues('size', ['small', 'medium', 'large']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The option "size" with value "extra-large" is invalid');

        $this->resolver->resolve(['size' => 'extra-large']);
    }

    public function testSetAllowedValuesWithCallable(): void
    {
        $this->resolver->setDefault('count', 5);
        $this->resolver->setAllowedValues('count', fn($value) => $value > 0 && $value <= 10);

        $resolved = $this->resolver->resolve(['count' => 7]);

        $this->assertEquals(7, $resolved['count']);
    }

    public function testSetAllowedValuesCallableThrowsExceptionForInvalidValue(): void
    {
        $this->resolver->setDefault('count', 5);
        $this->resolver->setAllowedValues('count', fn($value) => $value > 0 && $value <= 10);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The option "count" with value 15 is invalid');

        $this->resolver->resolve(['count' => 15]);
    }

    public function testSetNormalizerTransformsValue(): void
    {
        $this->resolver->setDefault('name', 'test');
        $this->resolver->setNormalizer('name', fn($value) => strtoupper($value));

        $resolved = $this->resolver->resolve(['name' => 'hello']);

        $this->assertEquals('HELLO', $resolved['name']);
    }

    public function testNormalizerReceivesAllResolvedOptions(): void
    {
        $this->resolver->setDefaults([
            'prefix' => 'Mr.',
            'name' => 'Smith',
        ]);
        $this->resolver->setNormalizer('name', fn($value, $options) => $options['prefix'] . ' ' . $value);

        $resolved = $this->resolver->resolve(['name' => 'John']);

        $this->assertEquals('Mr. John', $resolved['name']);
    }

    public function testSetInfoStoresDocumentation(): void
    {
        $this->resolver->setInfo('custom_option', 'This is a custom option');

        $this->assertEquals('This is a custom option', $this->resolver->getInfo('custom_option'));
    }

    public function testGetInfoReturnsNullForUndocumented(): void
    {
        $this->assertNull($this->resolver->getInfo('nonexistent'));
    }

    public function testGetDefinedOptionsReturnsAllDefined(): void
    {
        $this->resolver->setDefaults(['opt1' => 1, 'opt2' => 2]);
        $this->resolver->setRequired('opt3');
        $this->resolver->setDefined('opt4');

        $defined = $this->resolver->getDefinedOptions();

        $this->assertContains('opt1', $defined);
        $this->assertContains('opt2', $defined);
        $this->assertContains('opt3', $defined);
        $this->assertContains('opt4', $defined);
    }

    public function testGetRequiredOptionsReturnsAllRequired(): void
    {
        $this->resolver->setRequired(['opt1', 'opt2']);

        $required = $this->resolver->getRequiredOptions();

        $this->assertContains('opt1', $required);
        $this->assertContains('opt2', $required);
        $this->assertCount(2, $required);
    }

    public function testClearResetsAllConfiguration(): void
    {
        $this->resolver->setDefaults(['name' => 'test']);
        $this->resolver->setRequired('email');
        $this->resolver->setAllowedTypes('name', 'string');

        $this->resolver->clear();

        $this->assertFalse($this->resolver->hasDefault('name'));
        $this->assertFalse($this->resolver->isRequired('email'));
        $this->assertEmpty($this->resolver->getDefinedOptions());
    }

    public function testResolveEmptyOptionsReturnsDefaults(): void
    {
        $this->resolver->setDefaults([
            'name' => 'default',
            'required' => false,
        ]);

        $resolved = $this->resolver->resolve([]);

        $this->assertEquals('default', $resolved['name']);
        $this->assertFalse($resolved['required']);
    }

    public function testComplexOptionsConfiguration(): void
    {
        $this->resolver->setDefaults([
            'label' => null,
            'required' => false,
            'constraints' => [],
        ]);
        $this->resolver->setRequired('name');
        $this->resolver->setAllowedTypes('label', ['null', 'string']);
        $this->resolver->setAllowedTypes('required', 'bool');
        $this->resolver->setAllowedTypes('constraints', 'array');
        $this->resolver->setNormalizer('label', fn($value) => $value ?? 'Unnamed');

        $resolved = $this->resolver->resolve([
            'name' => 'email',
            'required' => true,
        ]);

        $this->assertEquals('email', $resolved['name']);
        $this->assertTrue($resolved['required']);
        $this->assertEquals('Unnamed', $resolved['label']);
        $this->assertIsArray($resolved['constraints']);
    }

    public function testSetDefaultsReturnsThis(): void
    {
        $result = $this->resolver->setDefaults(['test' => 1]);

        $this->assertSame($this->resolver, $result);
    }

    public function testSetDefaultReturnsThis(): void
    {
        $result = $this->resolver->setDefault('test', 1);

        $this->assertSame($this->resolver, $result);
    }

    public function testSetRequiredReturnsThis(): void
    {
        $result = $this->resolver->setRequired('test');

        $this->assertSame($this->resolver, $result);
    }

    public function testFluentInterface(): void
    {
        $this->resolver
            ->setDefaults(['opt1' => 1])
            ->setDefault('opt2', 2)
            ->setRequired('opt3')
            ->setDefined('opt4')
            ->setAllowedTypes('opt1', 'int')
            ->setNormalizer('opt2', fn($v) => $v * 2);

        $this->assertTrue($this->resolver->hasDefault('opt1'));
        $this->assertTrue($this->resolver->isRequired('opt3'));
    }

    public function testMultipleNormalizersAreNotSupported(): void
    {
        $this->resolver->setDefault('value', 10);
        $this->resolver->setNormalizer('value', fn($v) => $v * 2);
        $this->resolver->setNormalizer('value', fn($v) => $v * 3); // Overwrites previous

        $resolved = $this->resolver->resolve([]);

        $this->assertEquals(30, $resolved['value']); // Only last normalizer applied
    }

    public function testNormalizerIsNotCalledForMissingOption(): void
    {
        $called = false;
        $this->resolver->setDefined('optional');
        $this->resolver->setNormalizer('optional', function($v) use (&$called) {
            $called = true;
            return $v;
        });

        $this->resolver->resolve([]);

        $this->assertFalse($called);
    }

    public function testValidationOrderRequiredBeforeTypes(): void
    {
        $this->resolver->setRequired('name');
        $this->resolver->setAllowedTypes('name', 'string');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('required option(s) "name" are missing');

        // Should fail on missing required, not type validation
        $this->resolver->resolve([]);
    }
}
