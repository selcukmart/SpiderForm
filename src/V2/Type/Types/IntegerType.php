<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\OptionsResolver;
use SpiderForm\V2\Builder\InputBuilder;

/**
 * Integer Type - Integer Number Input
 *
 * @author selcukmart
 * @since 2.5.0
 */
class IntegerType extends NumberType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        parent::buildField($builder, $options);

        // Override step to 1 for integers
        $builder->addAttribute('step', '1');

        // Use integer validation instead of numeric
        $builder->integer();
    }

    public function getParent(): ?string
    {
        return 'number';
    }
}
