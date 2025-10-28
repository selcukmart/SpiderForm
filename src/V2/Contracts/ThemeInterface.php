<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Theme Interface
 *
 * Manages form templates and styling for different frameworks
 *
 * @author selcukmart
 * @since 2.0.0
 */
interface ThemeInterface
{
    /**
     * Get theme name
     */
    public function getName(): string;

    /**
     * Get theme version
     */
    public function getVersion(): string;

    /**
     * Get template for input type
     *
     * @param InputType $inputType
     * @return string Template name/path
     */
    public function getInputTemplate(InputType $inputType): string;

    /**
     * Get form wrapper template
     *
     * @return string Template name/path
     */
    public function getFormTemplate(): string;

    /**
     * Get input wrapper/capsule template
     *
     * @return string Template name/path
     */
    public function getInputCapsuleTemplate(): string;

    /**
     * Get CSS classes for input type
     *
     * @param InputType $inputType
     * @return array<string, string> Array of class names (e.g., ['input' => 'form-control', 'wrapper' => 'form-group'])
     */
    public function getInputClasses(InputType $inputType): array;

    /**
     * Get CSS classes for form
     *
     * @return array<string, string>
     */
    public function getFormClasses(): array;

    /**
     * Get theme assets (CSS/JS files)
     *
     * @return array{css: array<int, string>, js: array<int, string>}
     */
    public function getAssets(): array;

    /**
     * Get theme configuration
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Check if theme supports given input type
     */
    public function supports(InputType $inputType): bool;
}
