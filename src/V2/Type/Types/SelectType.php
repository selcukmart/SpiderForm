<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Type\OptionsResolver;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Select Type - Select Dropdown
 *
 * @author selcukmart
 * @since 2.5.0
 */
class SelectType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::SELECT);

        if (!empty($options['choices'])) {
            $builder->options($options['choices']);
        }

        if ($options['multiple']) {
            $builder->addAttribute('multiple', 'multiple');
        }

        if ($options['size'] !== null) {
            $builder->addAttribute('size', (string) $options['size']);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choices' => [],
            'multiple' => false,
            'size' => null,
            'placeholder' => null,
        ]);

        $resolver->setAllowedTypes('choices', 'array');
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('size', ['null', 'int']);
    }

    public function getParent(): ?string
    {
        return 'choice';
    }
}
