<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

/**
 * Form State - Enum for Form Lifecycle States
 *
 * @author selcukmart
 * @since 2.4.0
 */
enum FormState: string
{
    /**
     * Form is being built but not yet finalized
     */
    case BUILDING = 'building';

    /**
     * Form is ready but not yet submitted
     */
    case READY = 'ready';

    /**
     * Form has been submitted
     */
    case SUBMITTED = 'submitted';

    /**
     * Form is valid (submitted and passed validation)
     */
    case VALID = 'valid';

    /**
     * Form is invalid (submitted but failed validation)
     */
    case INVALID = 'invalid';

    /**
     * Check if form is in a submitted state
     */
    public function isSubmitted(): bool
    {
        return in_array($this, [self::SUBMITTED, self::VALID, self::INVALID]);
    }

    /**
     * Check if form is valid
     */
    public function isValid(): bool
    {
        return $this === self::VALID;
    }

    /**
     * Check if form is ready for submission
     */
    public function isReady(): bool
    {
        return in_array($this, [self::READY, self::SUBMITTED, self::VALID, self::INVALID]);
    }
}
