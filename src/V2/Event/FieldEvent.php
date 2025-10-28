<?php

declare(strict_types=1);

namespace FormGenerator\V2\Event;

use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Builder\FormBuilder;

/**
 * Field Event - Event object for field-level events
 *
 * This event is dispatched for individual field operations
 * like value changes, visibility changes, etc.
 *
 * Example:
 * ```php
 * $event = new FieldEvent($field, $formBuilder, [
 *     'old_value' => 'personal',
 *     'new_value' => 'business',
 *     'trigger_field' => 'user_type'
 * ]);
 * ```
 *
 * @author selcukmart
 * @since 2.3.0
 */
class FieldEvent
{
    private bool $propagationStopped = false;
    private array $context;

    /**
     * @param InputBuilder $field The field this event relates to
     * @param FormBuilder $form The form builder instance
     * @param array $context Additional context data
     */
    public function __construct(
        private InputBuilder $field,
        private FormBuilder $form,
        array $context = []
    ) {
        $this->context = $context;
    }

    /**
     * Get the field this event relates to
     */
    public function getField(): InputBuilder
    {
        return $this->field;
    }

    /**
     * Get the form builder instance
     */
    public function getForm(): FormBuilder
    {
        return $this->form;
    }

    /**
     * Get event context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get specific context value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Set context value
     */
    public function set(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Check if context has key
     */
    public function has(string $key): bool
    {
        return isset($this->context[$key]);
    }

    /**
     * Get field value
     */
    public function getValue(): mixed
    {
        return $this->field->getValue();
    }

    /**
     * Get field name
     */
    public function getFieldName(): string
    {
        return $this->field->getName();
    }

    /**
     * Check if this is a dependency-triggered event
     */
    public function isDependencyTriggered(): bool
    {
        return isset($this->context['trigger_field']);
    }

    /**
     * Get the field that triggered this event (for dependency events)
     */
    public function getTriggerField(): ?string
    {
        return $this->context['trigger_field'] ?? null;
    }

    /**
     * Get the value that triggered this event (for dependency events)
     */
    public function getTriggerValue(): mixed
    {
        return $this->context['trigger_value'] ?? null;
    }

    /**
     * Check if field should be visible based on context
     */
    public function shouldBeVisible(): bool
    {
        return $this->context['visible'] ?? true;
    }

    /**
     * Set field visibility in context
     */
    public function setVisible(bool $visible): self
    {
        $this->context['visible'] = $visible;
        return $this;
    }

    /**
     * Stop event propagation to other listeners
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation is stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Get data from form
     */
    public function getFormData(): array
    {
        return $this->form->getData();
    }

    /**
     * Get value of another field from form
     */
    public function getFieldValue(string $fieldName): mixed
    {
        $data = $this->getFormData();
        return $data[$fieldName] ?? null;
    }
}
