<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Input types enumeration for form fields
 *
 * @author selcukmart
 * @since 2.0.0
 */
enum InputType: string
{
    case TEXT = 'text';
    case EMAIL = 'email';
    case PASSWORD = 'password';
    case TEXTAREA = 'textarea';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case FILE = 'file';
    case IMAGE = 'image';
    case HIDDEN = 'hidden';
    case NUMBER = 'number';
    case DATE = 'date';
    case DATETIME = 'datetime-local';
    case TIME = 'time';
    case MONTH = 'month';
    case WEEK = 'week';
    case COLOR = 'color';
    case RANGE = 'range';
    case TEL = 'tel';
    case URL = 'url';
    case SEARCH = 'search';
    case TIMEZONE = 'timezone';
    case BUTTON = 'button';
    case SUBMIT = 'submit';
    case RESET = 'reset';
    case CHECKBOX_TREE = 'checkbox_tree';
    case REPEATER = 'repeater';

    /**
     * Check if input type requires options (select, radio, checkbox)
     */
    public function requiresOptions(): bool
    {
        return in_array($this, [self::SELECT, self::RADIO, self::CHECKBOX, self::CHECKBOX_TREE], true);
    }

    /**
     * Check if input type is complex (checkbox tree, repeater)
     */
    public function isComplex(): bool
    {
        return in_array($this, [self::CHECKBOX_TREE, self::REPEATER], true);
    }

    /**
     * Check if input type is file-based
     */
    public function isFile(): bool
    {
        return in_array($this, [self::FILE, self::IMAGE], true);
    }

    /**
     * Check if input type is button-based
     */
    public function isButton(): bool
    {
        return in_array($this, [self::BUTTON, self::SUBMIT, self::RESET], true);
    }

    /**
     * Check if input type supports picker
     */
    public function supportsPicker(): bool
    {
        return in_array($this, [self::DATE, self::DATETIME, self::TIME, self::RANGE], true);
    }

    /**
     * Get picker type for this input
     */
    public function getPickerType(): ?string
    {
        return match ($this) {
            self::DATE => 'date',
            self::DATETIME => 'datetime',
            self::TIME => 'time',
            self::RANGE => 'range',
            default => null,
        };
    }

    /**
     * Get HTML5 input type attribute value
     */
    public function getHtmlType(): string
    {
        return match ($this) {
            self::TEXTAREA => 'textarea',
            self::SELECT => 'select',
            self::TIMEZONE => 'select',
            default => $this->value,
        };
    }
}
