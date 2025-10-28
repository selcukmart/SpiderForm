<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type\Types;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;

/**
 * File Type - File Upload Input
 *
 * @author selcukmart
 * @since 2.5.0
 */
class FileType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::FILE);

        if ($options['multiple']) {
            $builder->addAttribute('multiple', 'multiple');
        }

        if ($options['accept'] !== null) {
            $builder->addAttribute('accept', $options['accept']);
        }

        $this->applyCommonOptions($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'multiple' => false,
            'accept' => null, // e.g., 'image/*', '.pdf,.doc'
        ]);

        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('accept', ['null', 'string']);
    }
}
