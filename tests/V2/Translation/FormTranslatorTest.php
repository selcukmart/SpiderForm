<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Translation;

use FormGenerator\V2\Translation\FormTranslator;
use FormGenerator\V2\Translation\Loader\PhpLoader;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FormTranslator
 *
 * @covers \FormGenerator\V2\Translation\FormTranslator
 */
class FormTranslatorTest extends TestCase
{
    private FormTranslator $translator;
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/../../fixtures/translations';
        $this->translator = new FormTranslator($this->fixturesPath);
        $this->translator->addLoader('php', new PhpLoader());
    }

    public function testSetAndGetLocale(): void
    {
        $this->translator->setLocale('en_US');
        $this->assertEquals('en_US', $this->translator->getLocale());

        $this->translator->setLocale('tr_TR');
        $this->assertEquals('tr_TR', $this->translator->getLocale());
    }

    public function testSetAndGetFallbackLocale(): void
    {
        $this->translator->setFallbackLocale('en_US');
        $this->assertEquals('en_US', $this->translator->getFallbackLocale());

        $this->translator->setFallbackLocale('fr_FR');
        $this->assertEquals('fr_FR', $this->translator->getFallbackLocale());
    }

    public function testTransReturnsTranslatedMessage(): void
    {
        $this->translator->setLocale('en_US');

        $result = $this->translator->trans('form.label.name');
        $this->assertEquals('Name', $result);
    }

    public function testTransWithParameters(): void
    {
        $this->translator->setLocale('en_US');

        $result = $this->translator->trans('form.error.minLength', ['min' => 5]);
        $this->assertEquals('Must be at least 5 characters', $result);
    }

    public function testTransWithParametersSymfonyStyle(): void
    {
        $this->translator->addTranslations('en_US', [
            'test.message' => 'Hello %name%'
        ]);

        $result = $this->translator->trans('test.message', ['name' => 'John']);
        $this->assertEquals('Hello John', $result);
    }

    public function testTransFallsBackToDefaultLocale(): void
    {
        $this->translator->setLocale('es_ES'); // Non-existent locale
        $this->translator->setFallbackLocale('en_US');

        $result = $this->translator->trans('form.label.name');
        $this->assertEquals('Name', $result);
    }

    public function testTransReturnsKeyWhenNotFound(): void
    {
        $this->translator->setLocale('en_US');

        $result = $this->translator->trans('nonexistent.key');
        $this->assertEquals('nonexistent.key', $result);
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $this->translator->setLocale('en_US');

        $this->assertTrue($this->translator->has('form.label.name'));
        $this->assertFalse($this->translator->has('nonexistent.key'));
    }

    public function testAddTranslationsDirectly(): void
    {
        $translations = [
            'custom' => [
                'message' => 'Custom Message',
            ],
        ];

        $this->translator->addTranslations('en_US', $translations);
        $this->translator->setLocale('en_US');

        $result = $this->translator->trans('custom.message');
        $this->assertEquals('Custom Message', $result);
    }

    public function testTransWithNestedKeys(): void
    {
        $this->translator->setLocale('en_US');

        $result = $this->translator->trans('form.label.email');
        $this->assertEquals('Email Address', $result);
    }

    public function testTransWithMultipleParameters(): void
    {
        $this->translator->addTranslations('en_US', [
            'test.multi' => 'Field {{ field }} must be between {{ min }} and {{ max }}'
        ]);

        $result = $this->translator->trans('test.multi', [
            'field' => 'age',
            'min' => 18,
            'max' => 100
        ]);

        $this->assertEquals('Field age must be between 18 and 100', $result);
    }

    public function testGetAllTranslations(): void
    {
        $this->translator->setLocale('en_US');

        $all = $this->translator->all();

        $this->assertIsArray($all);
        $this->assertArrayHasKey('form.label.name', $all);
        $this->assertEquals('Name', $all['form.label.name']);
    }

    public function testAddResource(): void
    {
        $translator = new FormTranslator();
        $translator->addLoader('php', new PhpLoader());
        $translator->addResource($this->fixturesPath);
        $translator->setLocale('en_US');

        $result = $translator->trans('form.label.name');
        $this->assertEquals('Name', $result);
    }

    public function testTranslationLoadingIsCached(): void
    {
        // First call loads translations
        $result1 = $this->translator->trans('form.label.name');

        // Second call should use cached translations
        $result2 = $this->translator->trans('form.label.email');

        $this->assertEquals('Name', $result1);
        $this->assertEquals('Email Address', $result2);
    }

    public function testDifferentLocalesHaveDifferentTranslations(): void
    {
        $this->translator->setLocale('en_US');
        $englishName = $this->translator->trans('form.label.name');

        $this->translator->setLocale('tr_TR');
        $turkishName = $this->translator->trans('form.label.name');

        $this->assertEquals('Name', $englishName);
        $this->assertEquals('İsim', $turkishName);
        $this->assertNotEquals($englishName, $turkishName);
    }

    public function testTransWithNullLocaleUsesCurrentLocale(): void
    {
        $this->translator->setLocale('tr_TR');

        $result = $this->translator->trans('form.label.name', [], null);
        $this->assertEquals('İsim', $result);
    }

    public function testTransWithExplicitLocaleOverridesCurrentLocale(): void
    {
        $this->translator->setLocale('tr_TR');

        $result = $this->translator->trans('form.label.name', [], 'en_US');
        $this->assertEquals('Name', $result);
    }

    public function testEmptyParametersDoesNotModifyMessage(): void
    {
        $this->translator->addTranslations('en_US', [
            'test.simple' => 'Simple message without parameters'
        ]);

        $result = $this->translator->trans('test.simple', []);
        $this->assertEquals('Simple message without parameters', $result);
    }

    public function testUnusedParametersAreIgnored(): void
    {
        $this->translator->addTranslations('en_US', [
            'test.unused' => 'Message with {{ used }}'
        ]);

        $result = $this->translator->trans('test.unused', [
            'used' => 'value',
            'unused' => 'ignored'
        ]);

        $this->assertEquals('Message with value', $result);
    }

    public function testMissingParameterLeavesPlaceholder(): void
    {
        $this->translator->addTranslations('en_US', [
            'test.missing' => 'Message with {{ missing }}'
        ]);

        $result = $this->translator->trans('test.missing', []);
        $this->assertEquals('Message with {{ missing }}', $result);
    }
}
