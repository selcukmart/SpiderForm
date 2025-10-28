<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

/**
 * Form Configuration - Immutable Form Configuration
 *
 * @author selcukmart
 * @since 2.4.0
 */
readonly class FormConfig implements FormConfigInterface
{
    public function __construct(
        private string $name,
        private string $type,
        private array $options = [],
        private string $method = 'POST',
        private string $action = '',
        private array $attributes = [],
        private bool $compound = false,
        private bool $csrfProtection = true,
        private bool $validation = true,
        private bool $errorBubbling = false,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasCsrfProtection(): bool
    {
        return $this->csrfProtection;
    }

    public function hasValidation(): bool
    {
        return $this->validation;
    }

    public function hasErrorBubbling(): bool
    {
        return $this->errorBubbling;
    }

    public function isCompound(): bool
    {
        return $this->compound;
    }
}
