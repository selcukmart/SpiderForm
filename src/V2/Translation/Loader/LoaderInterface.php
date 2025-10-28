<?php

declare(strict_types=1);

namespace FormGenerator\V2\Translation\Loader;

/**
 * Loader Interface - Contract for Translation File Loaders
 *
 * Defines the contract for loading translations from different file formats.
 *
 * @author selcukmart
 * @since 3.0.0
 */
interface LoaderInterface
{
    /**
     * Load translations from file
     *
     * @param string $filePath Path to translation file
     * @return array Loaded translations
     */
    public function load(string $filePath): array;
}
