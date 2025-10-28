<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type\Types;

use FormGenerator\V2\Type\AbstractType;
use FormGenerator\V2\Type\OptionsResolver;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;

/**
 * Password Type - Password Input Field
 *
 * @author selcukmart
 * @since 2.5.0
 */
class PasswordType extends AbstractType
{
    public function buildField(InputBuilder $builder, array $options): void
    {
        $builder->setType(InputType::PASSWORD);

        // Always hash is not applied here - handle in application layer
        // Password fields should never display existing values

        $this->applyCommonOptions($builder, $options);
    }

    public function getParent(): ?string
    {
        return 'text';
    }
}
