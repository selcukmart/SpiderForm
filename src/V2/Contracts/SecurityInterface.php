<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Security Interface
 *
 * Handles CSRF, XSS, and other security concerns
 *
 * @author selcukmart
 * @since 2.0.0
 */
interface SecurityInterface
{
    /**
     * Generate CSRF token
     *
     * @param string $formName Form identifier
     * @return string Token value
     */
    public function generateCsrfToken(string $formName): string;

    /**
     * Validate CSRF token
     *
     * @param string $formName Form identifier
     * @param string $token Token to validate
     */
    public function validateCsrfToken(string $formName, string $token): bool;

    /**
     * Sanitize input value (XSS prevention)
     *
     * @param mixed $value Input value
     * @param bool $allowHtml Allow HTML tags
     * @return mixed Sanitized value
     */
    public function sanitize(mixed $value, bool $allowHtml = false): mixed;

    /**
     * Escape output for HTML (XSS prevention)
     *
     * @param string $value Value to escape
     */
    public function escapeHtml(string $value): string;

    /**
     * Escape output for JavaScript context
     *
     * @param string $value Value to escape
     */
    public function escapeJs(string $value): string;

    /**
     * Validate input against allowed patterns
     *
     * @param string $value Value to validate
     * @param string $pattern Regex pattern
     */
    public function validatePattern(string $value, string $pattern): bool;

    /**
     * Check if request is from same origin
     */
    public function checkOrigin(): bool;

    /**
     * Generate secure random string
     *
     * @param int $length String length
     */
    public function generateRandomString(int $length = 32): string;
}
