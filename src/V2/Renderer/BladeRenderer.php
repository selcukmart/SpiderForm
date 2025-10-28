<?php

declare(strict_types=1);

namespace FormGenerator\V2\Renderer;

use FormGenerator\V2\Contracts\RendererInterface;
use Illuminate\View\Factory;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Events\Dispatcher;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;

/**
 * Blade Renderer Implementation
 *
 * Provides Laravel Blade template engine support for FormGenerator V2.
 *
 * Usage:
 * ```php
 * $renderer = new BladeRenderer(__DIR__ . '/views', __DIR__ . '/cache');
 * $form = FormBuilder::create('user_form')
 *     ->setRenderer($renderer)
 *     ->build();
 * ```
 *
 * @author selcukmart
 * @since 2.2.0
 */
class BladeRenderer implements RendererInterface
{
    private Factory $factory;
    private BladeCompiler $blade;
    private Filesystem $filesystem;
    private array $globals = [];
    private array $viewPaths = [];

    public function __construct(
        string|array $templatePaths,
        ?string $cachePath = null,
        ?Filesystem $filesystem = null,
        ?Dispatcher $events = null
    ) {
        $this->filesystem = $filesystem ?? new Filesystem();
        $this->viewPaths = is_array($templatePaths) ? $templatePaths : [$templatePaths];

        // Set up cache directory
        $cachePath = $cachePath ?? sys_get_temp_dir() . '/blade_cache';
        if (!is_dir($cachePath)) {
            $this->filesystem->makeDirectory($cachePath, 0755, true);
        }

        // Create Blade compiler
        $this->blade = new BladeCompiler($this->filesystem, $cachePath);

        // Create engine resolver
        $resolver = new EngineResolver();

        // Register PHP engine
        $resolver->register('php', function () {
            return new PhpEngine($this->filesystem);
        });

        // Register Blade engine
        $resolver->register('blade', function () {
            return new CompilerEngine($this->blade, $this->filesystem);
        });

        // Create view finder
        $finder = new \Illuminate\View\FileViewFinder($this->filesystem, $this->viewPaths);

        // Create view factory
        $this->factory = new Factory(
            $resolver,
            $finder,
            $events ?? new Dispatcher()
        );

        // Share global variables with all views
        foreach ($this->globals as $key => $value) {
            $this->factory->share($key, $value);
        }

        $this->registerCustomDirectives();
    }

    /**
     * Render a template with given context
     */
    public function render(string $template, array $context = []): string
    {
        // Merge global variables with context
        $context = array_merge($this->globals, $context);

        try {
            return $this->factory->make($template, $context)->render();
        } catch (\Exception $e) {
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
        return $this->factory->exists($template);
    }

    /**
     * Add global variable available to all templates
     */
    public function addGlobal(string $key, mixed $value): void
    {
        $this->globals[$key] = $value;
        $this->factory->share($key, $value);
    }

    /**
     * Set template directory/path
     */
    public function setTemplatePath(string|array $paths): void
    {
        $this->viewPaths = is_array($paths) ? $paths : [$paths];
        $finder = $this->factory->getFinder();

        if ($finder instanceof ViewFinderInterface) {
            // Update view paths
            $reflection = new \ReflectionClass($finder);
            $property = $reflection->getProperty('paths');
            $property->setAccessible(true);
            $property->setValue($finder, $this->viewPaths);
        }
    }

    /**
     * Get template directory/paths
     */
    public function getTemplatePaths(): array
    {
        return $this->viewPaths;
    }

    /**
     * Enable/disable caching
     */
    public function setCaching(bool $enabled, ?string $cacheDir = null): void
    {
        if (!$enabled) {
            // Disable caching by setting cache path to null (not directly supported in Blade)
            // We'll clear the cache instead
            $this->clearCache();
        } elseif ($cacheDir !== null) {
            // Update cache directory
            $reflection = new \ReflectionClass($this->blade);
            $property = $reflection->getProperty('cachePath');
            $property->setAccessible(true);
            $property->setValue($this->blade, $cacheDir);
        }
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        $cachePath = $this->blade->getCachePath();

        if (is_dir($cachePath)) {
            $files = $this->filesystem->files($cachePath);
            foreach ($files as $file) {
                $this->filesystem->delete($file);
            }
        }
    }

    /**
     * Get renderer engine name
     */
    public function getName(): string
    {
        return 'Blade';
    }

    /**
     * Get renderer version
     */
    public function getVersion(): string
    {
        // Get Laravel/Illuminate version
        if (defined('\Illuminate\Foundation\Application::VERSION')) {
            return \Illuminate\Foundation\Application::VERSION;
        }

        return 'standalone';
    }

    /**
     * Get Blade compiler instance
     */
    public function getBladeCompiler(): BladeCompiler
    {
        return $this->blade;
    }

    /**
     * Get View Factory instance
     */
    public function getFactory(): Factory
    {
        return $this->factory;
    }

    /**
     * Add custom Blade directive
     */
    public function directive(string $name, callable $callback): void
    {
        $this->blade->directive($name, $callback);
    }

    /**
     * Add a custom component
     */
    public function component(string $name, string $class): void
    {
        $this->blade->component($class, $name);
    }

    /**
     * Register custom Blade directives
     */
    private function registerCustomDirectives(): void
    {
        // @attributes directive: converts array to HTML attributes string
        $this->directive('attributes', function ($expression) {
            return "<?php echo FormGenerator\\V2\\Renderer\\BladeRenderer::renderAttributes($expression); ?>";
        });

        // @classes directive: converts array to CSS classes string
        $this->directive('classes', function ($expression) {
            return "<?php echo FormGenerator\\V2\\Renderer\\BladeRenderer::renderClasses($expression); ?>";
        });

        // @csrf directive for CSRF token
        $this->directive('csrf', function ($expression) {
            $formName = $expression ?: "'default'";
            return "<?php echo '<input type=\"hidden\" name=\"_csrf_token\" value=\"' . htmlspecialchars($formName) . '\" />'; ?>";
        });
    }

    /**
     * Helper: Render HTML attributes from array
     */
    public static function renderAttributes(array $attributes): string
    {
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
    }

    /**
     * Helper: Render CSS classes from array
     */
    public static function renderClasses(array|string $classes): string
    {
        if (is_string($classes)) {
            return $classes;
        }
        return implode(' ', array_filter($classes));
    }
}
