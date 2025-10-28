<?php

declare(strict_types=1);

namespace FormGenerator\V2\Error;

/**
 * Error Level - Enum for Error Severity
 *
 * Defines severity levels for form errors, allowing different
 * treatment of critical errors vs warnings vs informational messages.
 *
 * Usage:
 * ```php
 * $error = new FormError('Invalid email', ErrorLevel::ERROR);
 * $warning = new FormError('Weak password', ErrorLevel::WARNING);
 * $info = new FormError('Optional field not filled', ErrorLevel::INFO);
 *
 * // Filter by level
 * $criticalErrors = $form->getErrors(ErrorLevel::ERROR);
 * ```
 *
 * @author selcukmart
 * @since 2.9.0
 */
enum ErrorLevel: string
{
    /**
     * Critical error - Form cannot be submitted
     * Used for: Required fields, validation failures, data integrity issues
     */
    case ERROR = 'error';

    /**
     * Warning - Form can be submitted but user should be aware
     * Used for: Weak passwords, suspicious patterns, best practice violations
     */
    case WARNING = 'warning';

    /**
     * Informational message - No action required
     * Used for: Suggestions, tips, optional improvements
     */
    case INFO = 'info';

    /**
     * Get human-readable label
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ERROR => 'Error',
            self::WARNING => 'Warning',
            self::INFO => 'Info',
        };
    }

    /**
     * Get icon/emoji representation
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::ERROR => '❌',
            self::WARNING => '⚠️',
            self::INFO => 'ℹ️',
        };
    }

    /**
     * Get CSS class for styling
     */
    public function getCssClass(): string
    {
        return match ($this) {
            self::ERROR => 'error',
            self::WARNING => 'warning',
            self::INFO => 'info',
        };
    }

    /**
     * Check if this is a blocking error
     */
    public function isBlocking(): bool
    {
        return $this === self::ERROR;
    }
}
