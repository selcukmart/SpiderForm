<?php

declare(strict_types=1);

namespace SpiderForm\V2\Renderer;

use SpiderForm\V2\Contracts\RendererInterface;
use Smarty;

/**
 * Smarty Renderer Implementation
 *
 * @author selcukmart
 * @since 2.0.0
 */
class SmartyRenderer implements RendererInterface
{
    private Smarty $smarty;
    private array $globals = [];

    public function __construct(
        ?Smarty $smarty = null,
        ?string $templateDir = null,
        ?string $compileDir = null,
        ?string $cacheDir = null
    ) {
        $this->smarty = $smarty ?? new Smarty();

        if ($templateDir !== null) {
            $this->smarty->setTemplateDir($templateDir);
        }

        if ($compileDir !== null) {
            $this->smarty->setCompileDir($compileDir);
        } else {
            $this->smarty->setCompileDir(sys_get_temp_dir() . '/smarty_compile');
        }

        if ($cacheDir !== null) {
            $this->smarty->setCacheDir($cacheDir);
        } else {
            $this->smarty->setCacheDir(sys_get_temp_dir() . '/smarty_cache');
        }

        // Disable caching by default
        $this->smarty->setCaching(Smarty::CACHING_OFF);

        $this->registerCustomModifiers();
        $this->registerCustomFunctions();
    }

    /**
     * Render a template with given context
     */
    public function render(string $template, array $context = []): string
    {
        // Convert .twig extension to .tpl for Smarty compatibility
        $template = $this->normalizeTemplateName($template);

        // Merge global variables with context
        $context = array_merge($this->globals, $context);

        // Assign variables to Smarty
        foreach ($context as $key => $value) {
            $this->smarty->assign($key, $value);
        }

        try {
            return $this->smarty->fetch($template);
        } catch (\SmartyException $e) {
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
        // Normalize template name before checking
        $template = $this->normalizeTemplateName($template);
        return $this->smarty->templateExists($template);
    }

    /**
     * Add global variable available to all templates
     */
    public function addGlobal(string $key, mixed $value): void
    {
        $this->globals[$key] = $value;
        $this->smarty->assign($key, $value);
    }

    /**
     * Set template directory/path
     */
    public function setTemplatePath(string|array $paths): void
    {
        $this->smarty->setTemplateDir($paths);
    }

    /**
     * Get template directory/paths
     */
    public function getTemplatePaths(): array
    {
        $dirs = $this->smarty->getTemplateDir();
        return is_array($dirs) ? $dirs : [$dirs];
    }

    /**
     * Enable/disable caching
     */
    public function setCaching(bool $enabled, ?string $cacheDir = null): void
    {
        $this->smarty->setCaching($enabled ? Smarty::CACHING_LIFETIME_CURRENT : Smarty::CACHING_OFF);

        if ($cacheDir !== null) {
            $this->smarty->setCacheDir($cacheDir);
        }
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        $this->smarty->clearAllCache();
        $this->smarty->clearCompiledTemplate();
    }

    /**
     * Get renderer engine name
     */
    public function getName(): string
    {
        return 'Smarty';
    }

    /**
     * Get renderer version
     */
    public function getVersion(): string
    {
        return Smarty::SMARTY_VERSION;
    }

    /**
     * Get Smarty instance
     */
    public function getSmarty(): Smarty
    {
        return $this->smarty;
    }

    /**
     * Register custom modifier
     */
    public function registerModifier(string $name, callable $callback): void
    {
        $this->smarty->registerPlugin('modifier', $name, $callback);
    }

    /**
     * Register custom function
     */
    public function registerFunction(string $name, callable $callback): void
    {
        $this->smarty->registerPlugin('function', $name, $callback);
    }

    /**
     * Register custom modifiers
     */
    private function registerCustomModifiers(): void
    {
        // Attributes modifier: converts array to HTML attributes string
        $this->registerModifier('attributes', function (array $attributes): string {
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

        // Classes modifier: converts array to CSS classes string
        $this->registerModifier('classes', function (array|string $classes): string {
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
        // CSRF token function
        $this->registerFunction('csrf_token', function (array $params, Smarty $smarty): string {
            $formName = $params['form'] ?? 'default';
            return sprintf('<input type="hidden" name="_csrf_token" value="%s" />', $formName);
        });

        // Asset function
        $this->registerFunction('asset', function (array $params): string {
            $path = $params['path'] ?? '';
            return '/' . ltrim($path, '/');
        });
    }

    /**
     * Normalize template name by converting .twig to .tpl extension
     *
     * This allows Smarty renderer to use the same theme configuration as Twig renderer
     * while automatically loading the .tpl versions of templates from the smarty directory
     *
     * Note: Smarty templates should be stored in a separate directory from Twig templates
     * to avoid confusion. Typically: templates/smarty/ for .tpl files
     */
    private function normalizeTemplateName(string $template): string
    {
        // If template ends with .twig, replace it with .tpl
        if (str_ends_with($template, '.twig')) {
            return substr($template, 0, -5) . '.tpl';
        }

        return $template;
    }
}
