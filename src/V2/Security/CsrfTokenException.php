<?php

declare(strict_types=1);

namespace FormGenerator\V2\Security;

/**
 * CSRF Token Exception
 *
 * Thrown when CSRF token validation fails.
 *
 * @author selcukmart
 * @since 3.0.0
 */
class CsrfTokenException extends \RuntimeException
{
}
