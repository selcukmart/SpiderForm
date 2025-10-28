<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

use ArrayAccess;
use Iterator;
use Countable;

/**
 * Form View - Presentation Layer for Forms
 *
 * Symfony-inspired FormView that separates form data from presentation.
 * Provides a read-only view of the form structure for rendering.
 *
 * Usage:
 * ```php
 * $view = $form->createView();
 * echo $view->vars['name'];      // Form name
 * echo $view->vars['value'];     // Current value
 * echo $view->vars['label'];     // Label
 * echo $view->vars['errors'];    // Validation errors
 *
 * foreach ($view->children as $child) {
 *     echo $child->vars['name'];
 * }
 * ```
 *
 * @author selcukmart
 * @since 2.4.0
 */
class FormView implements ArrayAccess, Iterator, Countable
{
    /**
     * View variables (name, value, label, errors, attributes, etc.)
     */
    public array $vars = [];

    /**
     * Child views (nested forms/fields)
     *
     * @var array<string, FormView>
     */
    public array $children = [];

    /**
     * Parent view
     */
    public ?FormView $parent = null;

    /**
     * Is this view rendered?
     */
    private bool $rendered = false;

    /**
     * Iterator position
     */
    private int $position = 0;

    public function __construct(
        ?FormView $parent = null
    ) {
        $this->parent = $parent;
    }

    /**
     * Set view variables
     */
    public function setVars(array $vars): self
    {
        $this->vars = array_merge($this->vars, $vars);
        return $this;
    }

    /**
     * Set a view variable
     */
    public function setVar(string $key, mixed $value): self
    {
        $this->vars[$key] = $value;
        return $this;
    }

    /**
     * Get a view variable
     */
    public function getVar(string $key, mixed $default = null): mixed
    {
        return $this->vars[$key] ?? $default;
    }

    /**
     * Check if view has a variable
     */
    public function hasVar(string $key): bool
    {
        return isset($this->vars[$key]);
    }

    /**
     * Add a child view
     */
    public function addChild(string $name, FormView $child): self
    {
        $this->children[$name] = $child;
        $child->parent = $this;
        return $this;
    }

    /**
     * Get a child view
     */
    public function getChild(string $name): ?FormView
    {
        return $this->children[$name] ?? null;
    }

    /**
     * Check if view has a child
     */
    public function hasChild(string $name): bool
    {
        return isset($this->children[$name]);
    }

    /**
     * Get all children
     *
     * @return array<string, FormView>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Count children
     */
    public function count(): int
    {
        return count($this->children);
    }

    /**
     * Check if view is rendered
     */
    public function isRendered(): bool
    {
        return $this->rendered;
    }

    /**
     * Mark view as rendered
     */
    public function setRendered(bool $rendered = true): self
    {
        $this->rendered = $rendered;
        return $this;
    }

    /**
     * Check if view is a root view
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * Get root view
     */
    public function getRoot(): FormView
    {
        $view = $this;
        while ($view->parent !== null) {
            $view = $view->parent;
        }
        return $view;
    }

    // ArrayAccess implementation for backward compatibility

    public function offsetExists(mixed $offset): bool
    {
        return $this->hasChild((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getChild((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof FormView) {
            $this->addChild((string) $offset, $value);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->children[(string) $offset]);
    }

    // Iterator implementation for foreach support

    public function current(): mixed
    {
        $keys = array_keys($this->children);
        return $this->children[$keys[$this->position]];
    }

    public function key(): mixed
    {
        $keys = array_keys($this->children);
        return $keys[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        $keys = array_keys($this->children);
        return isset($keys[$this->position]);
    }

    /**
     * Magic method to access children as properties
     */
    public function __get(string $name): ?FormView
    {
        return $this->getChild($name);
    }

    /**
     * Magic method to check child existence
     */
    public function __isset(string $name): bool
    {
        return $this->hasChild($name);
    }

    /**
     * Debug representation
     */
    public function __debugInfo(): array
    {
        return [
            'vars' => $this->vars,
            'children' => array_keys($this->children),
            'rendered' => $this->rendered,
            'isRoot' => $this->isRoot(),
        ];
    }
}
