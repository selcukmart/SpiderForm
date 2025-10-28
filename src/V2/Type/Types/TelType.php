<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type\Types;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;

/**
 * Tel Type - Telephone Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class TelType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::TEL);
        $this->applyCommonOptions($builder, $options);
    }

    public function getParent(): ?string
    {
        return 'text';
    }
}
