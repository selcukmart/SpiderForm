<?php

declare(strict_types=1);

namespace FormGenerator\V2\Security;

use FormGenerator\V2\Contracts\SecurityInterface;

/**
 * Security Manager
 *
 * Handles CSRF, XSS, input sanitization, and other security concerns
 *
 * @author selcukmart
 * @since 2.0.0
 */
class SecurityManager implements SecurityInterface
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_LIFETIME = 3600; // 1 hour

    private array $tokens = [];

    public function __construct(
        private readonly bool $useSession = true,
        private readonly ?string $hashAlgo = 'sha256'
    ) {
        if ($this->useSession && session_status() === PHP_SESSION_NONE) {
            // Don't start session automatically - let the application handle it
        }
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(string $formName): string
    {
        $token = $this->generateRandomString(self::TOKEN_LENGTH);
        $tokenData = [
            'value' => $token,
            'timestamp' => time(),
            'hash' => hash($this->hashAlgo, $token . $formName),
        ];

        if ($this->useSession && isset($_SESSION)) {
            $_SESSION['csrf_tokens'][$formName] = $tokenData;
        } else {
            $this->tokens[$formName] = $tokenData;
        }

        return $token;
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $formName, string $token): bool
    {
        $tokenData = $this->getStoredToken($formName);

        if ($tokenData === null) {
            return false;
        }

        // Check token expiry
        if ((time() - $tokenData['timestamp']) > self::TOKEN_LIFETIME) {
            $this->removeToken($formName);
            return false;
        }

        // Validate hash
        $expectedHash = hash($this->hashAlgo, $token . $formName);
        $isValid = hash_equals($tokenData['hash'], $expectedHash) && hash_equals($tokenData['value'], $token);

        if ($isValid) {
            // Remove token after successful validation (one-time use)
            $this->removeToken($formName);
        }

        return $isValid;
    }

    /**
     * Sanitize input value (XSS prevention)
     */
    public function sanitize(mixed $value, bool $allowHtml = false): mixed
    {
        if (is_array($value)) {
            return array_map(fn($v) => $this->sanitize($v, $allowHtml), $value);
        }

        if (!is_string($value)) {
            return $value;
        }

        if ($allowHtml) {
            // Allow HTML but strip dangerous tags/attributes
            return $this->sanitizeHtml($value);
        }

        // Strip all HTML tags
        return strip_tags($value);
    }

    /**
     * Sanitize HTML content
     */
    private function sanitizeHtml(string $value): string
    {
        // Allowed tags
        $allowedTags = '<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';

        // Strip dangerous tags
        $value = strip_tags($value, $allowedTags);

        // Remove dangerous attributes
        $dangerousAttributes = ['onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur', 'javascript:'];

        foreach ($dangerousAttributes as $attr) {
            $value = preg_replace('/' . preg_quote($attr, '/') . '[^>]*?/i', '', $value);
        }

        return $value;
    }

    /**
     * Escape output for HTML (XSS prevention)
     */
    public function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape output for JavaScript context
     */
    public function escapeJs(string $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
    }

    /**
     * Validate input against allowed patterns
     */
    public function validatePattern(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Check if request is from same origin
     */
    public function checkOrigin(): bool
    {
        if (!isset($_SERVER['HTTP_ORIGIN']) || !isset($_SERVER['HTTP_HOST'])) {
            return false;
        }

        $origin = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
        $host = $_SERVER['HTTP_HOST'];

        return $origin === $host;
    }

    /**
     * Generate secure random string
     */
    public function generateRandomString(int $length = 32): string
    {
        try {
            $bytes = random_bytes($length);
            return bin2hex($bytes);
        } catch (\Exception $e) {
            // Fallback to openssl if random_bytes fails
            $bytes = openssl_random_pseudo_bytes($length);
            return bin2hex($bytes);
        }
    }

    /**
     * Get stored token data
     */
    private function getStoredToken(string $formName): ?array
    {
        if ($this->useSession && isset($_SESSION['csrf_tokens'][$formName])) {
            return $_SESSION['csrf_tokens'][$formName];
        }

        return $this->tokens[$formName] ?? null;
    }

    /**
     * Remove token from storage
     */
    private function removeToken(string $formName): void
    {
        if ($this->useSession && isset($_SESSION['csrf_tokens'][$formName])) {
            unset($_SESSION['csrf_tokens'][$formName]);
        } else {
            unset($this->tokens[$formName]);
        }
    }

    /**
     * Clear all expired tokens
     */
    public function clearExpiredTokens(): void
    {
        $now = time();

        if ($this->useSession && isset($_SESSION['csrf_tokens'])) {
            foreach ($_SESSION['csrf_tokens'] as $formName => $tokenData) {
                if (($now - $tokenData['timestamp']) > self::TOKEN_LIFETIME) {
                    unset($_SESSION['csrf_tokens'][$formName]);
                }
            }
        } else {
            foreach ($this->tokens as $formName => $tokenData) {
                if (($now - $tokenData['timestamp']) > self::TOKEN_LIFETIME) {
                    unset($this->tokens[$formName]);
                }
            }
        }
    }

    /**
     * Validate file upload security
     */
    public function validateFileUpload(array $file): bool
    {
        // Check if file was actually uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Check file size (max 10MB by default)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }

        return true;
    }

    /**
     * Get safe filename for uploads
     */
    public function getSafeFilename(string $filename): string
    {
        // Remove any path components
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Prevent double extensions
        $filename = preg_replace('/\.+/', '.', $filename);

        return $filename;
    }

    /**
     * Validate allowed file types
     */
    public function validateFileType(string $filename, array $allowedExtensions): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, array_map('strtolower', $allowedExtensions), true);
    }
}
