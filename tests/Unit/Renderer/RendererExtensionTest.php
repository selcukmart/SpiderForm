<?php

declare(strict_types=1);

namespace SpiderForm\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use SpiderForm\V2\Renderer\TwigRenderer;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Renderer\BladeRenderer;

/**
 * Test Renderer Template Extension Methods
 *
 * Verifies that each renderer returns the correct template extension
 * for dynamic template resolution.
 *
 * @covers \SpiderForm\V2\Renderer\TwigRenderer::getTemplateExtension
 * @covers \SpiderForm\V2\Renderer\SmartyRenderer::getTemplateExtension
 * @covers \SpiderForm\V2\Renderer\BladeRenderer::getTemplateExtension
 */
class RendererExtensionTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/renderer_test_' . uniqid();
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
     * Test TwigRenderer returns correct extension
     */
    public function testTwigRendererReturnsCorrectExtension(): void
    {
        $templateDir = $this->tempDir . '/twig';
        mkdir($templateDir, 0755, true);

        $renderer = new TwigRenderer($templateDir);

        $this->assertEquals('twig', $renderer->getTemplateExtension());
    }

    /**
     * Test SmartyRenderer returns correct extension
     */
    public function testSmartyRendererReturnsCorrectExtension(): void
    {
        $templateDir = $this->tempDir . '/smarty';
        $compileDir = $this->tempDir . '/compile';
        mkdir($templateDir, 0755, true);
        mkdir($compileDir, 0755, true);

        $renderer = new SmartyRenderer(null, $templateDir, $compileDir);

        $this->assertEquals('tpl', $renderer->getTemplateExtension());
    }

    /**
     * Test BladeRenderer returns correct extension
     */
    public function testBladeRendererReturnsCorrectExtension(): void
    {
        $viewsDir = $this->tempDir . '/views';
        $cacheDir = $this->tempDir . '/cache';
        mkdir($viewsDir, 0755, true);
        mkdir($cacheDir, 0755, true);

        $renderer = new BladeRenderer($viewsDir, $cacheDir);

        $this->assertEquals('blade.php', $renderer->getTemplateExtension());
    }

    /**
     * Test MockRenderer returns correct extension
     */
    public function testMockRendererReturnsCorrectExtension(): void
    {
        $templateDir = $this->tempDir . '/mock';
        mkdir($templateDir, 0755, true);

        $renderer = new MockRenderer($templateDir);

        $this->assertEquals('mock', $renderer->getTemplateExtension());
    }

    /**
     * Test all renderers implement getTemplateExtension method
     */
    public function testAllRenderersHaveTemplateExtensionMethod(): void
    {
        $renderers = [
            new TwigRenderer($this->tempDir),
            new SmartyRenderer(null, $this->tempDir, $this->tempDir),
            new BladeRenderer($this->tempDir, $this->tempDir),
            new MockRenderer($this->tempDir),
        ];

        foreach ($renderers as $renderer) {
            $this->assertTrue(
                method_exists($renderer, 'getTemplateExtension'),
                sprintf('Renderer %s must have getTemplateExtension method', get_class($renderer))
            );

            $extension = $renderer->getTemplateExtension();
            $this->assertIsString($extension);
            $this->assertNotEmpty($extension);
        }
    }
}
