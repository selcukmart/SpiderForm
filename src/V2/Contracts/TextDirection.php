<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Text direction enumeration for form layout
 *
 * Defines text direction for forms and inputs:
 * - LTR: Left-to-right (English, Turkish, etc.)
 * - RTL: Right-to-left (Arabic, Hebrew, etc.)
 *
 * @author selcukmart
 * @since 2.0.0
 */
enum TextDirection: string
{
    case LTR = 'ltr';
    case RTL = 'rtl';

    /**
     * Check if direction is RTL
     */
    public function isRtl(): bool
    {
        return $this === self::RTL;
    }

    /**
     * Check if direction is LTR
     */
    public function isLtr(): bool
    {
        return $this === self::LTR;
    }

    /**
     * Get opposite direction
     */
    public function opposite(): self
    {
        return match ($this) {
            self::LTR => self::RTL,
            self::RTL => self::LTR,
        };
    }
}

