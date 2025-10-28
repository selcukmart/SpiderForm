<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type\Types;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;

/**
 * Choice Type - Base for Select/Radio/Checkbox Lists
 *
 * @author selcukmart
 * @since 2.5.0
 */
class ChoiceType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        // Choices are handled by child types (select, radio, checkbox)
        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choices' => [],
            'choice_label' => null,
            'choice_value' => null,
            'expanded' => false,  // true = radio/checkbox, false = select
            'multiple' => false,  // true = checkbox/multi-select, false = radio/select
        ]);

        $resolver->setAllowedTypes('choices', 'array');
        $resolver->setAllowedTypes('choice_label', ['null', 'string', 'callable']);
        $resolver->setAllowedTypes('choice_value', ['null', 'string', 'callable']);
        $resolver->setAllowedTypes('expanded', 'bool');
        $resolver->setAllowedTypes('multiple', 'bool');
    }
}
