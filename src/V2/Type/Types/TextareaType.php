<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type\Types;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;

/**
 * Textarea Type - Multi-line Text Input
 *
 * @author selcukmart
 * @since 2.5.0
 */
class TextareaType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::TEXTAREA);

        if ($options['rows'] !== null) {
            $builder->addAttribute('rows', (string) $options['rows']);
        }

        if ($options['cols'] !== null) {
            $builder->addAttribute('cols', (string) $options['cols']);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'rows' => 5,
            'cols' => null,
        ]);

        $resolver->setAllowedTypes('rows', ['null', 'int']);
        $resolver->setAllowedTypes('cols', ['null', 'int']);
    }
}
