<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type;

use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Contracts\InputType;

/**
 * Abstract Type - Base Class for Field Types
 *
 * Provides a foundation for creating custom field types with
 * inheritance, option configuration, and field building.
 *
 * Usage:
 * ```php
 * class PhoneType extends AbstractType
 * {
 *     public function buildField(InputBuilder $builder, array $options): void
 *     {
 *         $builder->setType(InputType::TEL)
 *                 ->setPlaceholder($options['placeholder'])
 *                 ->addAttribute('pattern', '^[0-9]{3}-[0-9]{3}-[0-9]{4}$');
 *     }
 *
 *     public function configureOptions(OptionsResolver $resolver): void
 *     {
 *         $resolver->setDefaults([
 *             'placeholder' => '555-123-4567',
 *             'country' => 'US',
 *         ]);
 *     }
 *
 *     public function getParent(): ?string
 *     {
 *         return 'text'; // Inherits from TextType
 *     }
 * }
 * ```
 *
 * @author selcukmart
 * @since 2.5.0
 */
abstract class AbstractType
{
    /**
     * Build the field
     *
     * Configure the InputBuilder with type-specific settings
     *
     * @param InputBuilder $builder Field builder
     * @param array $options Resolved options
     */
    public function buildField(InputBuilder $builder, array $options): void
    {
        // Override in subclasses to configure field
    }

    /**
     * Configure options for this type
     *
     * Define defaults, required options, allowed types, etc.
     *
     * @param OptionsResolver $resolver Options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Override in subclasses
        $resolver->setDefaults([
            'label' => null,
            'required' => false,
            'disabled' => false,
            'readonly' => false,
            'placeholder' => null,
            'help' => null,
            'attr' => [],
            'label_attr' => [],
            'wrapper_attr' => [],
        ]);

        $resolver->setAllowedTypes('label', ['null', 'string']);
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('disabled', 'bool');
        $resolver->setAllowedTypes('readonly', 'bool');
        $resolver->setAllowedTypes('placeholder', ['null', 'string']);
        $resolver->setAllowedTypes('help', ['null', 'string']);
        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('label_attr', 'array');
        $resolver->setAllowedTypes('wrapper_attr', 'array');
    }

    /**
     * Get parent type name
     *
     * Return the name of the parent type to inherit from.
     * Return null for base types.
     *
     * @return string|null Parent type name or null
     */
    public function getParent(): ?string
    {
        return null;
    }

    /**
     * Get type name
     *
     * By default, converts class name to lowercase
     * PhoneType -> phone
     *
     * @return string Type name
     */
    public function getName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();

        // Remove 'Type' suffix
        $name = preg_replace('/Type$/', '', $className);

        // Convert to lowercase
        return strtolower($name ?? '');
    }

    /**
     * Finish view (called after field is built)
     *
     * Can be used to modify the field after all configuration is applied
     *
     * @param InputBuilder $builder Field builder
     * @param array $options Resolved options
     */
    public function finishView(InputBuilder $builder, array $options): void
    {
        // Override in subclasses if needed
    }

    /**
     * Get block prefix for template rendering
     *
     * Used to find the template block for this type
     *
     * @return string Block prefix
     */
    public function getBlockPrefix(): string
    {
        return $this->getName();
    }

    /**
     * Apply common options to InputBuilder
     *
     * Utility method to apply standard options
     *
     * @param InputBuilder $builder Field builder
     * @param array $options Resolved options
     */
    protected function applyCommonOptions(InputBuilder $builder, array $options): void
    {
        if ($options['label'] !== null) {
            $builder->label($options['label']);
        }

        if ($options['required']) {
            $builder->required();
        }

        if ($options['disabled']) {
            $builder->disabled();
        }

        if ($options['readonly']) {
            $builder->readonly();
        }

        if ($options['placeholder'] !== null) {
            $builder->placeholder($options['placeholder']);
        }

        if ($options['help'] !== null) {
            $builder->helpText($options['help']);
        }

        // Apply attributes
        foreach ($options['attr'] as $key => $value) {
            $builder->addAttribute($key, $value);
        }

        foreach ($options['label_attr'] as $key => $value) {
            $builder->addLabelAttribute($key, $value);
        }

        foreach ($options['wrapper_attr'] as $key => $value) {
            $builder->addWrapperAttribute($key, $value);
        }
    }
}
