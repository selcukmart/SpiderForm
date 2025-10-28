<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Form scope types (add/edit/view modes)
 *
 * @author selcukmart
 * @since 2.0.0
 */
enum ScopeType: string
{
    case ADD = 'add';
    case EDIT = 'edit';
    case VIEW = 'view';

    /**
     * Check if scope allows editing
     */
    public function isEditable(): bool
    {
        return $this !== self::VIEW;
    }

    /**
     * Check if scope requires pre-filled data
     */
    public function requiresData(): bool
    {
        return in_array($this, [self::EDIT, self::VIEW], true);
    }
}
