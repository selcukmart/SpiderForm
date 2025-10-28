<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

/**
 * Violation Builder - Fluent Interface for Building Violations
 *
 * Provides a fluent API for configuring validation violations
 * with paths, parameters, and causes.
 *
 * @author selcukmart
 * @since 2.7.0
 */
class ViolationBuilder
{
    private string $message;
    private string $path = '';
    private array $parameters = [];

    public function __construct(
        private readonly ExecutionContext $context,
        string $message
    ) {
        $this->message = $message;
        $this->path = $context->getCurrentPath();
    }

    /**
     * Set the property path for the violation
     *
     * @param string $path Field path (e.g., 'password_confirm', 'address.zipcode')
     */
    public function atPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Set parameters for message interpolation
     *
     * @param array $parameters Parameters to replace in message
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Set a single parameter
     */
    public function setParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Add the violation to the context
     */
    public function addViolation(): void
    {
        $this->context->addViolation($this->message, $this->path, $this->parameters);
    }
}
