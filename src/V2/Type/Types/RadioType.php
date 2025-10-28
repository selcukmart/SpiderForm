<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Type\OptionsResolver;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Radio Type - Radio Button Input
 *
 * @author selcukmart
 * @since 2.5.0
 */
class RadioType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::RADIO);

        if (!empty($options['choices'])) {
            $builder->options($options['choices']);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choices' => [],
        ]);

        $resolver->setAllowedTypes('choices', 'array');
    }

    public function getParent(): ?string
    {
        return 'choice';
    }
}
