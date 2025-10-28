<?php

declare(strict_types=1);

namespace SpiderForm\V2\Type\Types;

use SpiderForm\V2\Type\AbstractType;
use SpiderForm\V2\Builder\InputBuilder;
use SpiderForm\V2\Contracts\InputType;

/**
 * URL Type - URL Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class UrlType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::URL);
        $this->applyCommonOptions($builder, $options);

        // Auto-add URL validation
        $builder->url();
    }

    public function getParent(): ?string
    {
        return 'text';
    }
}
