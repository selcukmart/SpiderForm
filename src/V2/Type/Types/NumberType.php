<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type\Types;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;

/**
 * Number Type - Number Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class NumberType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::NUMBER);

        if ($options['min'] !== null) {
            $builder->min($options['min']);
        }

        if ($options['max'] !== null) {
            $builder->max($options['max']);
        }

        if ($options['step'] !== null) {
            $builder->addAttribute('step', (string) $options['step']);
        }

        $this->applyCommonOptions($builder, $options);

        // Auto-add numeric validation
        $builder->numeric();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'min' => null,
            'max' => null,
            'step' => null,
        ]);

        $resolver->setAllowedTypes('min', ['null', 'int', 'float']);
        $resolver->setAllowedTypes('max', ['null', 'int', 'float']);
        $resolver->setAllowedTypes('step', ['null', 'int', 'float', 'string']); // 'any' is also valid
    }
}
