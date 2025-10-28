<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Reset Type - Reset Button
 *
 * @author selcukmart
 * @since 2.5.0
 */
class ResetType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::RESET);
        $this->applyCommonOptions($builder, $options);
    }

    public function getParent(): ?string
    {
        return 'button';
    }
}
