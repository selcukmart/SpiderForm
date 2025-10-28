<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Type\OptionsResolver;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Email Type - Email Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class EmailType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::EMAIL);

        if ($options['multiple']) {
            $builder->addAttribute('multiple', 'multiple');
        }

        $this->applyCommonOptions($builder, $options);

        // Auto-add email validation
        $builder->email();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'multiple' => false,
        ]);

        $resolver->setAllowedTypes('multiple', 'bool');
    }

    public function getParent(): ?string
    {
        return 'text';
    }
}
