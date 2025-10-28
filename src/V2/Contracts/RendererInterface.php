<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Renderer Interface
 *
 * Provides abstraction for template rendering engines:
 * - Twig
 * - Smarty
 * - Blade
 * - Native PHP
 *
 * @author selcukmart
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Render a template with given context
     *
     * @param string $template Template name/path
     * @param array<string, mixed> $context Variables to pass to template
     * @return string Rendered HTML
     * @throws \RuntimeException If template not found or rendering fails
     */
    public function render(string $template, array $context = []): string;

    /**
     * Check if template exists
     *
     * @param string $template Template name/path
     */
    public function exists(string $template): bool;

    /**
     * Add global variable available to all templates
     *
     * @param string $key Variable name
     * @param mixed $value Variable value
     */
    public function addGlobal(string $key, mixed $value): void;

    /**
     * Set template directory/path
     *
     * @param string|array<int, string> $paths Single path or array of paths
     */
    public function setTemplatePath(string|array $paths): void;

    /**
     * Get template directory/paths
     *
     * @return array<int, string>
     */
    public function getTemplatePaths(): array;

    /**
     * Enable/disable caching
     *
     * @param bool $enabled
     * @param string|null $cacheDir Cache directory path
     */
    public function setCaching(bool $enabled, ?string $cacheDir = null): void;

    /**
     * Clear template cache
     */
    public function clearCache(): void;

    /**
     * Get renderer engine name
     */
    public function getName(): string;

    /**
     * Get renderer version
     */
    public function getVersion(): string;
}
