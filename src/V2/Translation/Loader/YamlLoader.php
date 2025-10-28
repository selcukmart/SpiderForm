<?php

declare(strict_types=1);

namespace FormGenerator\V2\Translation\Loader;

/**
 * YAML Loader - Load Translations from YAML Files
 *
 * Loads translations from YAML files.
 *
 * File format:
 * ```yaml
 * # translations/forms.en_US.yaml
 * form:
 *   label:
 *     name: "Name"
 *     email: "Email Address"
 *   error:
 *     required: "This field is required"
 * ```
 *
 * @author selcukmart
 * @since 3.0.0
 */
class YamlLoader implements LoaderInterface
{
    public function load(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Translation file not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException("Translation file not readable: {$filePath}");
        }

        // Try to use symfony/yaml if available
        if (function_exists('yaml_parse_file')) {
            $translations = yaml_parse_file($filePath);

            if ($translations === false) {
                throw new \RuntimeException("Failed to parse YAML file: {$filePath}");
            }

            return $translations;
        }

        // Fallback: Simple YAML parser (basic support)
        $content = file_get_contents($filePath);
        return $this->parseYaml($content);
    }

    /**
     * Simple YAML parser for basic translation files
     *
     * Note: This is a simplified parser. For complex YAML files,
     * use symfony/yaml component.
     */
    private function parseYaml(string $content): array
    {
        $lines = explode("\n", $content);
        $result = [];
        $stack = [&$result];
        $indentStack = [-1];

        foreach ($lines as $line) {
            // Skip empty lines and comments
            if (trim($line) === '' || str_starts_with(trim($line), '#')) {
                continue;
            }

            // Calculate indentation
            preg_match('/^(\s*)(.*)$/', $line, $matches);
            $indent = strlen($matches[1]);
            $content = $matches[2];

            // Parse key-value
            if (preg_match('/^([^:]+):\s*(.*)$/', $content, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);

                // Remove quotes from value
                $value = trim($value, '"\'');

                // Pop stack until we find the right level
                while (count($indentStack) > 1 && $indent <= $indentStack[count($indentStack) - 1]) {
                    array_pop($stack);
                    array_pop($indentStack);
                }

                $current = &$stack[count($stack) - 1];

                if ($value === '' || $value === '{}' || $value === '[]') {
                    // This is a parent node
                    $current[$key] = [];
                    $stack[] = &$current[$key];
                    $indentStack[] = $indent;
                } else {
                    // This is a leaf node
                    $current[$key] = $value;
                }
            }
        }

        return $result;
    }
}
