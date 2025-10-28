<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Hidden Type - Hidden Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class HiddenType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::HIDDEN);
        $this->applyCommonOptions($builder, $options);
    }
}
