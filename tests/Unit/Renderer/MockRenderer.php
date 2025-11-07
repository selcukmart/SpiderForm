<?php

declare(strict_types=1);

namespace SpiderForm\Tests\Unit\Renderer;

use SpiderForm\V2\Contracts\RendererInterface;

/**
 * Mock Renderer for Testing
 *
 * A simple mock renderer that uses .mock extension for testing
 * the dynamic template extension resolution feature.
 */
class MockRenderer implements RendererInterface
{
    private array $templatePaths = [];
    private array $globals = [];
    private array $renderedTemplates = [];

    public function __construct(string|array $templatePaths)
    {
        $this->templatePaths = is_array($templatePaths) ? $templatePaths : [$templatePaths];
    }

    public function render(string $template, array $context = []): string
    {
        // Track which templates were rendered
        $this->renderedTemplates[] = $template;

        // Find template file
        foreach ($this->templatePaths as $path) {
            $filePath = $path . '/' . $template;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);

                // Simple variable replacement: {{variable}}
                $mergedContext = array_merge($this->globals, $context);
                foreach ($mergedContext as $key => $value) {
                    if (is_scalar($value) || $value === null) {
                        $content = str_replace('{{' . $key . '}}', (string) $value, $content);
                    }
                }

                return $content;
            }
        }

        throw new \RuntimeException(sprintf('Template "%s" not found', $template));
    }

    public function exists(string $template): bool
    {
        foreach ($this->templatePaths as $path) {
            if (file_exists($path . '/' . $template)) {
                return true;
            }
        }
        return false;
    }

    public function addGlobal(string $key, mixed $value): void
    {
        $this->globals[$key] = $value;
    }

    public function setTemplatePath(string|array $paths): void
    {
        $this->templatePaths = is_array($paths) ? $paths : [$paths];
    }

    public function getTemplatePaths(): array
    {
        return $this->templatePaths;
    }

    public function setCaching(bool $enabled, ?string $cacheDir = null): void
    {
        // No caching in mock renderer
    }

    public function clearCache(): void
    {
        // No caching in mock renderer
    }

    public function getName(): string
    {
        return 'Mock';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getTemplateExtension(): string
    {
        return 'mock';
    }

    /**
     * Get list of rendered templates (for testing)
     */
    public function getRenderedTemplates(): array
    {
        return $this->renderedTemplates;
    }

    /**
     * Reset rendered templates list (for testing)
     */
    public function resetRenderedTemplates(): void
    {
        $this->renderedTemplates = [];
    }
}
