<?php

declare(strict_types=1);

namespace FormGenerator\V2\Theme;

use FormGenerator\V2\Contracts\{InputType, ThemeInterface};

/**
 * Abstract Theme Base Class
 *
 * @author selcukmart
 * @since 2.0.0
 */
abstract class AbstractTheme implements ThemeInterface
{
    protected array $config = [];
    protected array $templateMap = [];
    protected array $inputClasses = [];
    protected array $formClasses = [];
    protected array $assets = ['css' => [], 'js' => []];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initialize();
    }

    /**
     * Initialize theme (called in constructor)
     */
    abstract protected function initialize(): void;

    /**
     * Get default theme configuration
     */
    abstract protected function getDefaultConfig(): array;

    /**
     * Get theme name
     */
    abstract public function getName(): string;

    /**
     * Get theme version
     */
    abstract public function getVersion(): string;

    /**
     * Get template for input type
     */
    public function getInputTemplate(InputType $inputType): string
    {
        return $this->templateMap[$inputType->value] ?? $this->templateMap['default'] ?? 'input.twig';
    }

    /**
     * Get form wrapper template
     */
    public function getFormTemplate(): string
    {
        return $this->config['form_template'] ?? 'form.twig';
    }

    /**
     * Get input wrapper/capsule template
     */
    public function getInputCapsuleTemplate(): string
    {
        return $this->config['input_capsule_template'] ?? 'input_capsule.twig';
    }

    /**
     * Get CSS classes for input type
     */
    public function getInputClasses(InputType $inputType): array
    {
        return $this->inputClasses[$inputType->value] ?? $this->inputClasses['default'] ?? [];
    }

    /**
     * Get CSS classes for form
     */
    public function getFormClasses(): array
    {
        return $this->formClasses;
    }

    /**
     * Get theme assets (CSS/JS files)
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * Get theme configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Check if theme supports given input type
     */
    public function supports(InputType $inputType): bool
    {
        return isset($this->templateMap[$inputType->value]) || isset($this->templateMap['default']);
    }

    /**
     * Set template for input type
     */
    protected function setInputTemplate(InputType $inputType, string $template): void
    {
        $this->templateMap[$inputType->value] = $template;
    }

    /**
     * Set input classes for input type
     */
    protected function setInputClasses(InputType $inputType, array $classes): void
    {
        $this->inputClasses[$inputType->value] = $classes;
    }
}
