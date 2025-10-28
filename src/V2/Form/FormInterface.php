<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

use FormGenerator\V2\Contracts\RendererInterface;
use FormGenerator\V2\Contracts\ThemeInterface;

/**
 * Form Interface - Contract for Stateful Form Objects
 *
 * Defines the interface for form objects with state management,
 * nested form support, and lifecycle management.
 *
 * @author selcukmart
 * @since 2.4.0
 */
interface FormInterface
{
    /**
     * Get form name
     */
    public function getName(): string;

    /**
     * Set form data (bind model data to form)
     */
    public function setData(array $data): self;

    /**
     * Get form data (retrieve bound data)
     */
    public function getData(): array;

    /**
     * Handle HTTP request (submit form)
     */
    public function handleRequest(array $data): self;

    /**
     * Submit form data programmatically
     */
    public function submit(array $data): self;

    /**
     * Check if form was submitted
     */
    public function isSubmitted(): bool;

    /**
     * Check if form is valid (submitted and passed validation)
     */
    public function isValid(): bool;

    /**
     * Check if form has no data
     */
    public function isEmpty(): bool;

    /**
     * Validate form data
     *
     * @return array Validation errors
     */
    public function validate(): array;

    /**
     * Get validation errors
     */
    public function getErrors(bool $deep = false): array;

    /**
     * Check if form has errors
     */
    public function hasErrors(): bool;

    /**
     * Add a child form (nested form)
     */
    public function add(string $name, string|FormInterface $type = 'text', array $options = []): self;

    /**
     * Remove a child form/field
     */
    public function remove(string $name): self;

    /**
     * Check if form has a child
     */
    public function has(string $name): bool;

    /**
     * Get a child form/field
     */
    public function get(string $name): FormInterface;

    /**
     * Get all children
     *
     * @return array<string, FormInterface>
     */
    public function all(): array;

    /**
     * Get parent form (null if root)
     */
    public function getParent(): ?FormInterface;

    /**
     * Set parent form
     */
    public function setParent(?FormInterface $parent): self;

    /**
     * Get root form
     */
    public function getRoot(): FormInterface;

    /**
     * Check if this is the root form
     */
    public function isRoot(): bool;

    /**
     * Create form view for rendering
     */
    public function createView(): FormView;

    /**
     * Render form as string
     */
    public function render(?RendererInterface $renderer = null, ?ThemeInterface $theme = null): string;

    /**
     * Get form configuration
     */
    public function getConfig(): FormConfigInterface;
}
