<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Output format enumeration for form rendering
 *
 * Defines available output formats for the build() method:
 * - HTML: Standard HTML output (default)
 * - JSON: JSON representation of form structure
 * - XML: XML representation of form structure
 *
 * @author selcukmart
 * @since 2.0.0
 */
enum OutputFormat: string
{
    case HTML = 'html';
    case JSON = 'json';
    case XML = 'xml';

    /**
     * Get content type header for this format
     */
    public function getContentType(): string
    {
        return match ($this) {
            self::HTML => 'text/html',
            self::JSON => 'application/json',
            self::XML => 'application/xml',
        };
    }

    /**
     * Get file extension for this format
     */
    public function getExtension(): string
    {
        return match ($this) {
            self::HTML => 'html',
            self::JSON => 'json',
            self::XML => 'xml',
        };
    }
}

