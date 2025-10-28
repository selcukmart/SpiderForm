<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Renderer;

use FormGenerator\V2\Renderer\BladeRenderer;
use PHPUnit\Framework\TestCase;
use Illuminate\Filesystem\Filesystem;

/**
 * Test BladeRenderer Class
 */
class BladeRendererTest extends TestCase
{
    private string $tempDir;
    private BladeRenderer $renderer;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/blade_test_' . uniqid();
        $viewsDir = $this->tempDir . '/views';
        $cacheDir = $this->tempDir . '/cache';

        mkdir($viewsDir, 0755, true);
        mkdir($cacheDir, 0755, true);

        // Create a test template
        file_put_contents(
            $viewsDir . '/test.blade.php',
            'Hello {{ $name }}!'
        );

        $this->renderer = new BladeRenderer($viewsDir, $cacheDir);
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
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

    public function testRendererName(): void
    {
        $this->assertEquals('Blade', $this->renderer->getName());
    }

    public function testRenderTemplate(): void
    {
        $result = $this->renderer->render('test', ['name' => 'World']);
        $this->assertEquals('Hello World!', $result);
    }

    public function testTemplateExists(): void
    {
        $this->assertTrue($this->renderer->exists('test'));
        $this->assertFalse($this->renderer->exists('nonexistent'));
    }

    public function testAddGlobal(): void
    {
        $this->renderer->addGlobal('app_name', 'FormGenerator');

        file_put_contents(
            $this->tempDir . '/views/global.blade.php',
            '{{ $app_name }}'
        );

        $result = $this->renderer->render('global', []);
        $this->assertEquals('FormGenerator', $result);
    }

    public function testGetTemplatePaths(): void
    {
        $paths = $this->renderer->getTemplatePaths();
        $this->assertIsArray($paths);
        $this->assertCount(1, $paths);
    }

    public function testRenderAttributesHelper(): void
    {
        $attributes = [
            'class' => 'form-control',
            'id' => 'username',
            'required' => true,
            'disabled' => false,
        ];

        $result = BladeRenderer::renderAttributes($attributes);

        $this->assertStringContainsString('class="form-control"', $result);
        $this->assertStringContainsString('id="username"', $result);
        $this->assertStringContainsString('required', $result);
        $this->assertStringNotContainsString('disabled', $result);
    }

    public function testRenderClassesHelperWithArray(): void
    {
        $classes = ['form-control', 'is-valid', '', null];
        $result = BladeRenderer::renderClasses($classes);

        $this->assertEquals('form-control is-valid', $result);
    }

    public function testRenderClassesHelperWithString(): void
    {
        $classes = 'form-control is-valid';
        $result = BladeRenderer::renderClasses($classes);

        $this->assertEquals('form-control is-valid', $result);
    }

    public function testClearCache(): void
    {
        // Render a template to create cache
        $this->renderer->render('test', ['name' => 'World']);

        // Clear cache
        $this->renderer->clearCache();

        // Cache directory should be empty
        $cacheDir = $this->tempDir . '/cache';
        $files = array_diff(scandir($cacheDir), ['.', '..']);
        $this->assertEmpty($files);
    }
}
