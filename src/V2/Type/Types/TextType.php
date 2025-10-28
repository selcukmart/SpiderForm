<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Type\OptionsResolver;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Text Type - Basic Text Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class TextType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::TEXT);

        // Apply min/max length
        if ($options['minlength'] !== null) {
            $builder->minLength($options['minlength']);
        }

        if ($options['maxlength'] !== null) {
            $builder->maxLength($options['maxlength']);
        }

        // Apply pattern
        if ($options['pattern'] !== null) {
            $builder->regex($options['pattern']);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'minlength' => null,
            'maxlength' => null,
            'pattern' => null,
        ]);

        $resolver->setAllowedTypes('minlength', ['null', 'int']);
        $resolver->setAllowedTypes('maxlength', ['null', 'int']);
        $resolver->setAllowedTypes('pattern', ['null', 'string']);
    }
}
