<?php

declare(strict_types=1);

namespace FormGenerator\V2\Renderer;

use FormGenerator\V2\Contracts\RendererInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig Renderer Implementation
 *
 * @author selcukmart
 * @since 2.0.0
 */
class TwigRenderer implements RendererInterface
{
    private Environment $twig;
    private FilesystemLoader $loader;
    private array $globals = [];

    public function __construct(
        string|array $templatePaths,
        ?string $cacheDir = null,
        bool $debug = false
    ) {
        $paths = is_array($templatePaths) ? $templatePaths : [$templatePaths];
        $this->loader = new FilesystemLoader($paths);

        $this->twig = new Environment($this->loader, [
            'cache' => $cacheDir ?? false,
            'debug' => $debug,
            'autoescape' => 'html',
            'strict_variables' => false,
        ]);

        $this->registerCustomFilters();
        $this->registerCustomFunctions();
    }

    /**
     * Render a template with given context
     */
    public function render(string $template, array $context = []): string
    {
        // Merge global variables with context
        $context = array_merge($this->globals, $context);

        try {
            return $this->twig->render($template, $context);
        } catch (\Twig\Error\Error $e) {
            throw new \RuntimeException(
                sprintf('Failed to render template "%s": %s', $template, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * Check if template exists
     */
    public function exists(string $template): bool
    {
        return $this->loader->exists($template);
    }

    /**
     * Add global variable available to all templates
     */
    public function addGlobal(string $key, mixed $value): void
    {
        $this->globals[$key] = $value;
        $this->twig->addGlobal($key, $value);
    }

    /**
     * Set template directory/path
     */
    public function setTemplatePath(string|array $paths): void
    {
        $paths = is_array($paths) ? $paths : [$paths];

        // Clear existing paths
        foreach ($this->loader->getPaths() as $path) {
            $this->loader->prependPath($path);
        }

        // Add new paths
        foreach ($paths as $path) {
            $this->loader->addPath($path);
        }
    }

    /**
     * Get template directory/paths
     */
    public function getTemplatePaths(): array
    {
        return $this->loader->getPaths();
    }

    /**
     * Enable/disable caching
     */
    public function setCaching(bool $enabled, ?string $cacheDir = null): void
    {
        $this->twig->setCache($enabled ? ($cacheDir ?? sys_get_temp_dir() . '/twig_cache') : false);
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        $this->twig->clearCacheFiles();
    }

    /**
     * Get renderer engine name
     */
    public function getName(): string
    {
        return 'Twig';
    }

    /**
     * Get renderer version
     */
    public function getVersion(): string
    {
        return Environment::VERSION;
    }

    /**
     * Get Twig environment instance
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * Add custom Twig filter
     */
    public function addFilter(string $name, callable $callback): void
    {
        $this->twig->addFilter(new TwigFilter($name, $callback));
    }

    /**
     * Add custom Twig function
     */
    public function addFunction(string $name, callable $callback): void
    {
        $this->twig->addFunction(new TwigFunction($name, $callback));
    }

    /**
     * Register custom filters
     */
    private function registerCustomFilters(): void
    {
        // Attributes filter: converts array to HTML attributes string
        $this->addFilter('attributes', function (array $attributes): string {
            $parts = [];
            foreach ($attributes as $key => $value) {
                if (is_bool($value)) {
                    if ($value) {
                        $parts[] = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
                    }
                } elseif ($value !== null) {
                    $parts[] = sprintf(
                        '%s="%s"',
                        htmlspecialchars($key, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
                    );
                }
            }
            return implode(' ', $parts);
        });

        // Classes filter: converts array to CSS classes string
        $this->addFilter('classes', function (array|string $classes): string {
            if (is_string($classes)) {
                return $classes;
            }
            return implode(' ', array_filter($classes));
        });
    }

    /**
     * Register custom functions
     */
    private function registerCustomFunctions(): void
    {
        // Form CSRF token function
        $this->addFunction('csrf_token', function (string $formName): string {
            // This will be replaced by actual security manager in context
            return sprintf('<input type="hidden" name="_csrf_token" value="%s" />', $formName);
        });

        // Asset function for theme assets
        $this->addFunction('asset', function (string $path): string {
            // Simple asset URL generator
            return '/' . ltrim($path, '/');
        });
    }
}
