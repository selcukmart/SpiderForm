<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type;

use FormGenerator\V2\Builder\InputBuilder;

/**
 * Type Extension Interface
 *
 * Allows extending existing types without modifying them.
 *
 * Usage:
 * ```php
 * class HelpTextExtension implements TypeExtensionInterface
 * {
 *     public function extendType(): string|array
 *     {
 *         return ['text', 'email', 'number']; // Apply to these types
 *     }
 *
 *     public function buildField(InputBuilder $builder, array $options): void
 *     {
 *         if (isset($options['help_text'])) {
 *             $builder->helpText($options['help_text']);
 *         }
 *     }
 *
 *     public function configureOptions(OptionsResolver $resolver): void
 *     {
 *         $resolver->setDefined('help_text');
 *         $resolver->setAllowedTypes('help_text', 'string');
 *     }
 * }
 * ```
 *
 * @author selcukmart
 * @since 2.5.0
 */
interface TypeExtensionInterface
{
    /**
     * Get the type(s) this extension applies to
     *
     * @return string|array Type name(s)
     */
    public function extendType(): string|array;

    /**
     * Build field (called after type's buildField)
     *
     * @param InputBuilder $builder Field builder
     * @param array $options Resolved options
     */
    public function buildField(InputBuilder $builder, array $options): void;

    /**
     * Configure options (called after type's configureOptions)
     *
     * @param OptionsResolver $resolver Options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void;

    /**
     * Finish view (called after type's finishView)
     *
     * @param InputBuilder $builder Field builder
     * @param array $options Resolved options
     */
    public function finishView(InputBuilder $builder, array $options): void;
}
