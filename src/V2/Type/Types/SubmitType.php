<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * Submit Type - Submit Button
 *
 * @author selcukmart
 * @since 2.5.0
 */
class SubmitType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::SUBMIT);
        $this->applyCommonOptions($builder, $options);
    }

    public function getParent(): ?string
    {
        return 'button';
    }
}
