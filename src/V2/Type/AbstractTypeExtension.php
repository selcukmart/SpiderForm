<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type;

use FormGenerator\V2\Builder\InputBuilder;

/**
 * Abstract Type Extension - Base for Type Extensions
 *
 * Provides empty implementations for all interface methods.
 * Override only the methods you need.
 *
 * @author selcukmart
 * @since 2.5.0
 */
abstract class AbstractTypeExtension implements TypeExtensionInterface
{
    /**
     * Get the type(s) this extension applies to
     *
     * @return string|array Type name(s)
     */
    abstract public function extendType(): string|array;

    /**
     * Build field (called after type's buildField)
     */
    public function buildField(InputBuilder $builder, array $options): void
    {
        // Override in subclasses if needed
    }

    /**
     * Configure options (called after type's configureOptions)
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Override in subclasses if needed
    }

    /**
     * Finish view (called after type's finishView)
     */
    public function finishView(InputBuilder $builder, array $options): void
    {
        // Override in subclasses if needed
    }
}
