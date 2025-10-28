<?php

declare(strict_types=1);

namespace FormGenerator\V2\Security;

use FormGenerator\V2\Form\FormInterface;

/**
 * CSRF Protection - Automatic CSRF Token Management for Forms
 *
 * Provides automatic CSRF protection for forms by managing token
 * generation, rendering, and validation.
 *
 * Usage:
 * ```php
 * $protection = new CsrfProtection();
 *
 * // Add CSRF field to form
 * $protection->addCsrfField($form, 'user_form');
 *
 * // Validate submitted form
 * if ($protection->validateToken('user_form', $_POST)) {
 *     // Form is valid
 * }
 * ```
 *
 * @author selcukmart
 * @since 3.0.0
 */
class CsrfProtection
{
    /**
     * Default CSRF field name
     */
    public const DEFAULT_FIELD_NAME = '_csrf_token';

    /**
     * CSRF token manager
     */
    private readonly CsrfTokenManager $tokenManager;

    /**
     * @param CsrfTokenManager|null $tokenManager Token manager instance
     */
    public function __construct(?CsrfTokenManager $tokenManager = null)
    {
        $this->tokenManager = $tokenManager ?? new CsrfTokenManager();
    }

    /**
     * Get token manager
     */
    public function getTokenManager(): CsrfTokenManager
    {
        return $this->tokenManager;
    }

    /**
     * Generate CSRF token for form
     *
     * @param string $tokenId Token identifier (usually form name)
     * @return string Generated token
     */
    public function generateToken(string $tokenId): string
    {
        return $this->tokenManager->generateToken($tokenId);
    }

    /**
     * Validate CSRF token from request
     *
     * @param string $tokenId Token identifier
     * @param array $data Request data (e.g., $_POST)
     * @param string $fieldName CSRF field name
     * @return bool True if token is valid
     */
    public function validateToken(
        string $tokenId,
        array $data,
        string $fieldName = self::DEFAULT_FIELD_NAME
    ): bool {
        if (!isset($data[$fieldName])) {
            return false;
        }

        return $this->tokenManager->isTokenValid($tokenId, $data[$fieldName]);
    }

    /**
     * Get CSRF field HTML
     *
     * @param string $tokenId Token identifier
     * @param string $fieldName CSRF field name
     * @return string Hidden input HTML
     */
    public function getCsrfFieldHtml(
        string $tokenId,
        string $fieldName = self::DEFAULT_FIELD_NAME
    ): string {
        $token = $this->tokenManager->hasToken($tokenId)
            ? $this->tokenManager->getToken($tokenId)
            : $this->generateToken($tokenId);

        $fieldName = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
        $token = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<input type="hidden" name="%s" value="%s" />',
            $fieldName,
            $token
        );
    }

    /**
     * Add CSRF field to form (if form supports it)
     *
     * @param FormInterface $form Form instance
     * @param string $tokenId Token identifier
     * @param string $fieldName CSRF field name
     */
    public function addCsrfField(
        FormInterface $form,
        string $tokenId,
        string $fieldName = self::DEFAULT_FIELD_NAME
    ): void {
        // Generate token
        $token = $this->generateToken($tokenId);

        // Add as hidden field
        $form->add($fieldName, 'hidden', [
            'value' => $token,
            'required' => true,
        ]);
    }

    /**
     * Validate form submission with CSRF check
     *
     * @param FormInterface $form Form instance
     * @param string $tokenId Token identifier
     * @param array $data Request data
     * @param string $fieldName CSRF field name
     * @throws CsrfTokenException If token is invalid
     */
    public function validateFormSubmission(
        FormInterface $form,
        string $tokenId,
        array $data,
        string $fieldName = self::DEFAULT_FIELD_NAME
    ): void {
        if (!$form->isSubmitted()) {
            return;
        }

        if (!$this->validateToken($tokenId, $data, $fieldName)) {
            throw new CsrfTokenException('Invalid CSRF token');
        }
    }

    /**
     * Get CSRF token value (for AJAX requests)
     *
     * @param string $tokenId Token identifier
     * @return string Token value
     */
    public function getTokenValue(string $tokenId): string
    {
        if (!$this->tokenManager->hasToken($tokenId)) {
            return $this->generateToken($tokenId);
        }

        return $this->tokenManager->getToken($tokenId);
    }

    /**
     * Create CSRF meta tags for AJAX (for <head> section)
     *
     * @param string $tokenId Token identifier
     * @return string Meta tags HTML
     */
    public function getCsrfMetaTags(string $tokenId): string
    {
        $token = $this->getTokenValue($tokenId);
        $token = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<meta name="csrf-token" content="%s" />' . "\n" .
            '<meta name="csrf-param" content="%s" />',
            $token,
            self::DEFAULT_FIELD_NAME
        );
    }
}
