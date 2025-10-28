<?php

declare(strict_types=1);

namespace FormGenerator\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base Test Case for FormGenerator V2
 * 
 * Provides common functionality and utilities for all test classes.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Assert that a string contains HTML
     */
    protected function assertIsHtml(string $content, string $message = ''): void
    {
        $this->assertMatchesRegularExpression('/<[^>]+>/', $content, $message);
    }

    /**
     * Assert that HTML contains a specific tag
     */
    protected function assertHtmlContainsTag(string $html, string $tag, string $message = ''): void
    {
        $this->assertMatchesRegularExpression("/<{$tag}[^>]*>/", $html, $message ?: "HTML should contain <{$tag}> tag");
    }

    /**
     * Assert that HTML contains an attribute with value
     */
    protected function assertHtmlContainsAttribute(string $html, string $attribute, string $value, string $message = ''): void
    {
        $pattern = '/' . preg_quote($attribute, '/') . '=["\']' . preg_quote($value, '/') . '["\']' . '/';
        $this->assertMatchesRegularExpression($pattern, $html, $message ?: "HTML should contain {$attribute}=\"{$value}\"");
    }

    /**
     * Assert that a string contains JavaScript code
     */
    protected function assertIsJavaScript(string $content, string $message = ''): void
    {
        $this->assertMatchesRegularExpression('/function|const|let|var|\(function\(\)/', $content, $message);
    }

    /**
     * Assert that JavaScript contains a specific function
     */
    protected function assertJavaScriptContainsFunction(string $js, string $functionName, string $message = ''): void
    {
        $pattern = '/function\s+' . preg_quote($functionName, '/') . '\s*\(|' . 
                   preg_quote($functionName, '/') . '\s*:\s*function\s*\(|' .
                   'const\s+' . preg_quote($functionName, '/') . '\s*=/';
        $this->assertMatchesRegularExpression($pattern, $js, $message ?: "JavaScript should contain function {$functionName}");
    }

    /**
     * Extract attribute value from HTML
     */
    protected function extractAttributeValue(string $html, string $tag, string $attribute): ?string
    {
        $pattern = '/<' . preg_quote($tag, '/') . '[^>]*' . preg_quote($attribute, '/') . '=["\']([^"\']*)["\'][^>]*>/';
        if (preg_match($pattern, $html, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Create a temporary file for testing
     */
    protected function createTempFile(string $content = ''): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'formgen_test_');
        file_put_contents($tempFile, $content);
        return $tempFile;
    }

    /**
     * Clean up temporary file
     */
    protected function deleteTempFile(string $file): void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
