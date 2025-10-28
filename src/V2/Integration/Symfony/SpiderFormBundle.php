<?php

declare(strict_types=1);

namespace SpiderForm\V2\Integration\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * SpiderForm Symfony Bundle
 *
 * @author selcukmart
 * @since 2.0.0
 */
class SpiderFormBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__, 3);
    }
}
