<?php

declare(strict_types=1);

namespace SpiderForm\V2\Form;

/**
 * Form Configuration - Immutable Form Configuration
 *
 * @author selcukmart
 * @since 2.4.0
 */
class FormConfig implements FormConfigInterface
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly array $options = [],
        private readonly string $method = 'POST',
        private readonly string $action = '',
        private readonly array $attributes = [],
        private readonly bool $compound = false,
        private readonly bool $csrfProtection = true,
        private readonly bool $validation = true,
        private readonly bool $errorBubbling = false,
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
