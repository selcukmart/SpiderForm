<?php

declare(strict_types=1);

namespace FormGenerator\V2\Security;

/**
 * CSRF Token Manager - Token Generation and Validation
 *
 * Manages CSRF tokens for form security. Generates cryptographically
 * secure tokens and validates them against stored values.
 *
 * Usage:
 * ```php
 * $manager = new CsrfTokenManager();
 *
 * // Generate token
 * $token = $manager->generateToken('user_form');
 *
 * // Validate token
 * if ($manager->isTokenValid('user_form', $_POST['_token'])) {
 *     // Process form
 * }
 * ```
 *
 * @author selcukmart
 * @since 3.0.0
 */
class CsrfTokenManager
{
    /**
     * Session key for storing tokens
     */
    private const SESSION_KEY = '_csrf_tokens';

    /**
     * Default token lifetime in seconds (2 hours)
     */
    private const TOKEN_LIFETIME = 7200;

    /**
     * @param bool $useSession Use PHP sessions for token storage
     */
    public function __construct(
        private readonly bool $useSession = true
    ) {
        if ($this->useSession && session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Generate a CSRF token
     *
     * @param string $tokenId Token identifier (e.g., form name)
     * @return string Generated token
     */
    public function generateToken(string $tokenId): string
    {
        // Generate cryptographically secure token
        $token = bin2hex(random_bytes(32));

        // Store token with timestamp
        $this->storeToken($tokenId, $token);

        return $token;
    }

    /**
     * Validate a CSRF token
     *
     * @param string $tokenId Token identifier
     * @param string $token Token to validate
     * @return bool True if token is valid
     */
    public function isTokenValid(string $tokenId, string $token): bool
    {
        $storedData = $this->getStoredToken($tokenId);

        if ($storedData === null) {
            return false;
        }

        // Check if token expired
        if (time() - $storedData['timestamp'] > self::TOKEN_LIFETIME) {
            $this->removeToken($tokenId);
            return false;
        }

        // Timing-safe comparison
        return hash_equals($storedData['token'], $token);
    }

    /**
     * Refresh a token (generate new one, invalidate old)
     *
     * @param string $tokenId Token identifier
     * @return string New token
     */
    public function refreshToken(string $tokenId): string
    {
        $this->removeToken($tokenId);
        return $this->generateToken($tokenId);
    }

    /**
     * Remove a token
     *
     * @param string $tokenId Token identifier
     */
    public function removeToken(string $tokenId): void
    {
        if ($this->useSession) {
            if (isset($_SESSION[self::SESSION_KEY][$tokenId])) {
                unset($_SESSION[self::SESSION_KEY][$tokenId]);
            }
        }
    }

    /**
     * Get token for identifier (without generating new one)
     *
     * @param string $tokenId Token identifier
     * @return string|null Token or null if not found
     */
    public function getToken(string $tokenId): ?string
    {
        $storedData = $this->getStoredToken($tokenId);
        return $storedData['token'] ?? null;
    }

    /**
     * Check if token exists
     *
     * @param string $tokenId Token identifier
     * @return bool True if token exists
     */
    public function hasToken(string $tokenId): bool
    {
        return $this->getStoredToken($tokenId) !== null;
    }

    /**
     * Store token in session
     */
    private function storeToken(string $tokenId, string $token): void
    {
        if ($this->useSession) {
            if (!isset($_SESSION[self::SESSION_KEY])) {
                $_SESSION[self::SESSION_KEY] = [];
            }

            $_SESSION[self::SESSION_KEY][$tokenId] = [
                'token' => $token,
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Get stored token data
     *
     * @return array|null Token data or null
     */
    private function getStoredToken(string $tokenId): ?array
    {
        if ($this->useSession) {
            return $_SESSION[self::SESSION_KEY][$tokenId] ?? null;
        }

        return null;
    }

    /**
     * Clean expired tokens
     */
    public function cleanExpiredTokens(): void
    {
        if (!$this->useSession || !isset($_SESSION[self::SESSION_KEY])) {
            return;
        }

        $now = time();

        foreach ($_SESSION[self::SESSION_KEY] as $tokenId => $data) {
            if ($now - $data['timestamp'] > self::TOKEN_LIFETIME) {
                unset($_SESSION[self::SESSION_KEY][$tokenId]);
            }
        }
    }
}
