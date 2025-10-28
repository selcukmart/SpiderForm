<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * DateTime Type - DateTime Local Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class DateTimeType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::DATETIME_LOCAL);
        $this->applyCommonOptions($builder, $options);
    }

    public function getParent(): ?string
    {
        return 'text';
    }
}
