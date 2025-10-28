<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * Form Section
 *
 * Groups form inputs under a section with title, description, and optional HTML content
 *
 * @author selcukmart
 * @since 2.0.0
 */
class Section
{
    private string $title;
    private string $description = '';
    private string $htmlContent = '';
    private array $attributes = [];
    private array $classes = [];
    private bool $collapsible = false;
    private bool $collapsed = false;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * Set section description
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set custom HTML content (supports HTML)
     */
    public function setHtmlContent(string $html): self
    {
        $this->htmlContent = $html;
        return $this;
    }

    /**
     * Set custom attributes
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Set custom CSS classes
     */
    public function setClasses(array $classes): self
    {
        $this->classes = array_merge($this->classes, $classes);
        return $this;
    }

    /**
     * Make section collapsible
     */
    public function collapsible(bool $collapsed = false): self
    {
        $this->collapsible = true;
        $this->collapsed = $collapsed;
        return $this;
    }

    /**
     * Get section title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get section description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get HTML content
     */
    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    /**
     * Get attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get classes
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Check if collapsible
     */
    public function isCollapsible(): bool
    {
        return $this->collapsible;
    }

    /**
     * Check if initially collapsed
     */
    public function isCollapsed(): bool
    {
        return $this->collapsed;
    }

    /**
     * Convert section data to array for template rendering
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'htmlContent' => $this->htmlContent,
            'attributes' => $this->attributes,
            'classes' => $this->classes,
            'collapsible' => $this->collapsible,
            'collapsed' => $this->collapsed
        ];
    }
}
