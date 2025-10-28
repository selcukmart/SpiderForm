<?php

declare(strict_types=1);

namespace FormGenerator\V2\Translation\Loader;

/**
 * PHP Array Loader - Load Translations from PHP Files
 *
 * Loads translations from PHP files that return arrays.
 *
 * File format:
 * ```php
 * // translations/forms.en_US.php
 * return [
 *     'form' => [
 *         'label' => [
 *             'name' => 'Name',
 *             'email' => 'Email Address',
 *         ],
 *         'error' => [
 *             'required' => 'This field is required',
 *         ],
 *     ],
 * ];
 * ```
 *
 * @author selcukmart
 * @since 3.0.0
 */
class PhpLoader implements LoaderInterface
{
    public function load(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Translation file not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException("Translation file not readable: {$filePath}");
        }

        $translations = require $filePath;

        if (!is_array($translations)) {
            throw new \RuntimeException("Translation file must return an array: {$filePath}");
        }

        return $translations;
    }
}
