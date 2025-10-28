<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation\Rules;

/**
 * Ip Rule - Value must be a valid IP address
 */
class Ip implements RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Support both IPv4 and IPv6 by default
        // Parameters can specify: 'ipv4' or 'ipv6'
        $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;

        if (!empty($parameters[0])) {
            if ($parameters[0] === 'ipv4') {
                $flag = FILTER_FLAG_IPV4;
            } elseif ($parameters[0] === 'ipv6') {
                $flag = FILTER_FLAG_IPV6;
            }
        }

        return filter_var($value, FILTER_VALIDATE_IP, $flag) !== false;
    }

    public function message(): string
    {
        return 'The :attribute must be a valid IP address.';
    }

    public function name(): string
    {
        return 'ip';
    }
}
