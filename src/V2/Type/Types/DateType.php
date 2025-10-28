<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Type\OptionsResolver;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Date Type - Date Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class DateType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::DATE);

        if ($options['min'] !== null) {
            $builder->addAttribute('min', $options['min']);
        }

        if ($options['max'] !== null) {
            $builder->addAttribute('max', $options['max']);
        }

        $this->applyCommonOptions($builder, $options);

        // Auto-add date validation
        $builder->date();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'min' => null,
            'max' => null,
            'format' => 'Y-m-d',
        ]);

        $resolver->setAllowedTypes('min', ['null', 'string']);
        $resolver->setAllowedTypes('max', ['null', 'string']);
        $resolver->setAllowedTypes('format', 'string');
    }
}
