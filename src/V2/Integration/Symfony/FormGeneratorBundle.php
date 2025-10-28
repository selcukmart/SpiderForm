<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Form Generator Symfony Bundle
 *
 * @author selcukmart
 * @since 2.0.0
 */
class FormGeneratorBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__, 3);
    }
}
