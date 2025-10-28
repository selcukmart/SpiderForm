<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type\Types;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;

/**
 * Checkbox Type - Checkbox Input
 *
 * @author selcukmart
 * @since 2.5.0
 */
class CheckboxType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::CHECKBOX);

        if ($options['checked']) {
            $builder->addAttribute('checked', 'checked');
        }

        if ($options['value'] !== null) {
            $builder->value($options['value']);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'checked' => false,
            'value' => '1',
        ]);

        $resolver->setAllowedTypes('checked', 'bool');
        $resolver->setAllowedTypes('value', ['null', 'string', 'int']);
    }
}
