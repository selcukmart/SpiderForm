<?php

declare(strict_types=1);

namespace SpiderForm\Tests\Unit\Theme;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Contracts\InputType;
use SpiderForm\V2\Renderer\TwigRenderer;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Renderer\BladeRenderer;
use SpiderForm\V2\Theme\Bootstrap3Theme;
use SpiderForm\V2\Theme\Bootstrap5Theme;
use SpiderForm\V2\Theme\TailwindTheme;
use SpiderForm\V2\Theme\AbstractTheme;
use SpiderForm\Tests\Unit\Renderer\MockRenderer;

/**
 * Test Renderer-Theme Integration with Dynamic Extension Resolution
 *
 * This test ensures that themes work correctly with any renderer by
 * dynamically resolving template extensions based on the active renderer.
 */
#[CoversClass(AbstractTheme::class)]
#[CoversClass(FormBuilder::class)]
class RendererThemeIntegrationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/spiderform_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Test that each renderer returns the correct template extension
     */
    public function testRenderersReturnCorrectExtensions(): void
    {
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $mockRenderer = new MockRenderer($mockTemplateDir);
        $this->assertEquals('mock', $mockRenderer->getTemplateExtension());

        $twigTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/twig';
        if (is_dir($twigTemplateDir)) {
            $twigRenderer = new TwigRenderer($twigTemplateDir);
            $this->assertEquals('twig', $twigRenderer->getTemplateExtension());
        }

        $smartyTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/smarty';
        if (is_dir($smartyTemplateDir)) {
            $smartyRenderer = new SmartyRenderer(null, $smartyTemplateDir, $this->tempDir . '/compile');
            $this->assertEquals('tpl', $smartyRenderer->getTemplateExtension());
        }

        $bladeViewsDir = $this->tempDir . '/views';
        $bladeCacheDir = $this->tempDir . '/cache';
        mkdir($bladeViewsDir, 0755, true);
        mkdir($bladeCacheDir, 0755, true);
        $bladeRenderer = new BladeRenderer($bladeViewsDir, $bladeCacheDir);
        $this->assertEquals('blade.php', $bladeRenderer->getTemplateExtension());
    }

    /**
     * Test that theme receives extension from renderer when set
     */
    public function testThemeReceivesExtensionFromRenderer(): void
    {
        $theme = new Bootstrap3Theme();
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $renderer = new MockRenderer($mockTemplateDir);

        // Initially no extension set
        $template = $theme->getInputTemplate(InputType::TEXT);
        $this->assertEquals('bootstrap3/input_text', $template);

        // Set extension
        $theme->setTemplateExtension($renderer->getTemplateExtension());

        // Now extension should be appended
        $template = $theme->getInputTemplate(InputType::TEXT);
        $this->assertEquals('bootstrap3/input_text.mock', $template);
    }

    /**
     * Test FormBuilder synchronizes renderer and theme (renderer set first)
     */
    public function testFormBuilderSynchronizesRendererFirst(): void
    {
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $renderer = new MockRenderer($mockTemplateDir);
        $theme = new Bootstrap3Theme();

        $formBuilder = FormBuilder::create('test_form')
            ->setRenderer($renderer)  // Set renderer first
            ->setTheme($theme);       // Then theme

        // Theme should have received extension from renderer
        $template = $theme->getInputTemplate(InputType::TEXT);
        $this->assertEquals('bootstrap3/input_text.mock', $template);
    }

    /**
     * Test FormBuilder synchronizes renderer and theme (theme set first)
     */
    public function testFormBuilderSynchronizesThemeFirst(): void
    {
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $renderer = new MockRenderer($mockTemplateDir);
        $theme = new Bootstrap3Theme();

        $formBuilder = FormBuilder::create('test_form')
            ->setTheme($theme)        // Set theme first
            ->setRenderer($renderer); // Then renderer

        // Theme should have received extension from renderer
        $template = $theme->getInputTemplate(InputType::TEXT);
        $this->assertEquals('bootstrap3/input_text.mock', $template);
    }

    /**
     * Test Bootstrap3Theme with MockRenderer
     */
    public function testBootstrap3ThemeWithMockRenderer(): void
    {
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $renderer = new MockRenderer($mockTemplateDir);
        $theme = new Bootstrap3Theme();

        $theme->setTemplateExtension($renderer->getTemplateExtension());

        // Test various input types
        $this->assertEquals('bootstrap3/input_text.mock', $theme->getInputTemplate(InputType::TEXT));
        $this->assertEquals('bootstrap3/input_text.mock', $theme->getInputTemplate(InputType::EMAIL));
        $this->assertEquals('bootstrap3/button.mock', $theme->getInputTemplate(InputType::SUBMIT));
        $this->assertEquals('bootstrap3/input_hidden.mock', $theme->getInputTemplate(InputType::HIDDEN));
        $this->assertEquals('bootstrap3/form.mock', $theme->getFormTemplate());
        $this->assertEquals('bootstrap3/input_capsule.mock', $theme->getInputCapsuleTemplate());
    }

    /**
     * Test Bootstrap5Theme with MockRenderer
     */
    public function testBootstrap5ThemeWithMockRenderer(): void
    {
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $renderer = new MockRenderer($mockTemplateDir);
        $theme = new Bootstrap5Theme();

        $theme->setTemplateExtension($renderer->getTemplateExtension());

        $this->assertEquals('bootstrap5/input_text.mock', $theme->getInputTemplate(InputType::TEXT));
        $this->assertEquals('bootstrap5/button.mock', $theme->getInputTemplate(InputType::SUBMIT));
    }

    /**
     * Test TailwindTheme with MockRenderer
     */
    public function testTailwindThemeWithMockRenderer(): void
    {
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $renderer = new MockRenderer($mockTemplateDir);
        $theme = new TailwindTheme();

        $theme->setTemplateExtension($renderer->getTemplateExtension());

        $this->assertEquals('tailwind/input_text.mock', $theme->getInputTemplate(InputType::TEXT));
        $this->assertEquals('tailwind/button.mock', $theme->getInputTemplate(InputType::SUBMIT));
    }

    /**
     * Test that same theme works with different renderers
     */
    public function testSameThemeWorksWithDifferentRenderers(): void
    {
        $theme = new Bootstrap3Theme();

        // Test with MockRenderer
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $mockRenderer = new MockRenderer($mockTemplateDir);
        $theme->setTemplateExtension($mockRenderer->getTemplateExtension());
        $this->assertEquals('bootstrap3/input_text.mock', $theme->getInputTemplate(InputType::TEXT));

        // Test with TwigRenderer
        $twigTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/twig';
        if (is_dir($twigTemplateDir)) {
            $twigRenderer = new TwigRenderer($twigTemplateDir);
            $theme->setTemplateExtension($twigRenderer->getTemplateExtension());
            $this->assertEquals('bootstrap3/input_text.twig', $theme->getInputTemplate(InputType::TEXT));
        }

        // Test with SmartyRenderer
        $smartyTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/smarty';
        if (is_dir($smartyTemplateDir)) {
            $smartyRenderer = new SmartyRenderer(null, $smartyTemplateDir, $this->tempDir . '/compile');
            $theme->setTemplateExtension($smartyRenderer->getTemplateExtension());
            $this->assertEquals('bootstrap3/input_text.tpl', $theme->getInputTemplate(InputType::TEXT));
        }
    }

    /**
     * Test backward compatibility: theme without extension set
     */
    public function testBackwardCompatibilityWithoutExtension(): void
    {
        $theme = new Bootstrap3Theme();

        // Without extension set, templates should be returned without extension
        $template = $theme->getInputTemplate(InputType::TEXT);
        $this->assertEquals('bootstrap3/input_text', $template);
    }

    /**
     * Test that templates with existing extensions are not modified
     */
    public function testTemplatesWithExistingExtensionsNotModified(): void
    {
        $theme = new Bootstrap3Theme();
        $mockTemplateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/mock';
        $renderer = new MockRenderer($mockTemplateDir);

        $theme->setTemplateExtension($renderer->getTemplateExtension());

        // Create a custom config with template that already has extension
        $reflection = new \ReflectionClass($theme);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($theme);
        $config['form_template'] = 'custom/form.twig';  // Already has extension
        $configProperty->setValue($theme, $config);

        // Should not append .mock since .twig is already present
        $template = $theme->getFormTemplate();
        $this->assertEquals('custom/form.twig', $template);
    }

    /**
     * Test all themes with all available renderers
     *
     * @dataProvider themeRendererCombinationsProvider
     */
    public function testAllThemeRendererCombinations(string $themeName, string $rendererName, string $expectedExtension): void
    {
        // Skip if templates don't exist
        $templateDir = dirname(__DIR__, 3) . '/src/V2/Theme/templates/' . strtolower($rendererName);
        if (!is_dir($templateDir)) {
            $this->markTestSkipped("Template directory $templateDir not found");
        }

        // Create theme
        $theme = match ($themeName) {
            'Bootstrap3' => new Bootstrap3Theme(),
            'Bootstrap5' => new Bootstrap5Theme(),
            'Tailwind' => new TailwindTheme(),
            default => throw new \InvalidArgumentException("Unknown theme: $themeName"),
        };

        // Create renderer
        $renderer = match ($rendererName) {
            'mock' => new MockRenderer($templateDir),
            'twig' => new TwigRenderer($templateDir),
            'smarty' => new SmartyRenderer(null, $templateDir, $this->tempDir . '/compile_' . uniqid()),
            'blade' => new BladeRenderer($this->tempDir . '/views_' . uniqid(), $this->tempDir . '/cache_' . uniqid()),
            default => throw new \InvalidArgumentException("Unknown renderer: $rendererName"),
        };

        // Verify extension
        $this->assertEquals($expectedExtension, $renderer->getTemplateExtension());

        // Set extension on theme
        $theme->setTemplateExtension($renderer->getTemplateExtension());

        // Verify template path includes correct extension
        $inputTemplate = $theme->getInputTemplate(InputType::TEXT);
        $themePrefix = strtolower($themeName === 'Tailwind' ? 'tailwind' : $themeName);
        $expectedTemplate = $themePrefix . '/input_text.' . $expectedExtension;

        $this->assertEquals($expectedTemplate, $inputTemplate);
    }

    /**
     * Data provider for theme-renderer combinations
     */
    public static function themeRendererCombinationsProvider(): array
    {
        return [
            // Theme, Renderer, Expected Extension
            ['Bootstrap3', 'mock', 'mock'],
            ['Bootstrap3', 'twig', 'twig'],
            ['Bootstrap3', 'smarty', 'tpl'],
            ['Bootstrap5', 'mock', 'mock'],
            ['Bootstrap5', 'twig', 'twig'],
            ['Bootstrap5', 'smarty', 'tpl'],
            ['Tailwind', 'mock', 'mock'],
            ['Tailwind', 'twig', 'twig'],
            ['Tailwind', 'smarty', 'tpl'],
        ];
    }
}
