<?php

declare(strict_types=1);

namespace FormGenerator\V2\Translation;

use FormGenerator\V2\Translation\Loader\LoaderInterface;

/**
 * Form Translator - Native Translation System
 *
 * Provides translation capabilities with support for multiple loaders
 * (YAML, PHP arrays, JSON) and locale fallback.
 *
 * Usage:
 * ```php
 * $translator = new FormTranslator(__DIR__ . '/translations');
 * $translator->addLoader('yaml', new YamlLoader());
 * $translator->setLocale('tr_TR');
 *
 * echo $translator->trans('form.label.name'); // "İsim"
 * echo $translator->trans('welcome.message', ['name' => 'Ahmet']); // "Hoş geldin, Ahmet"
 * ```
 *
 * @author selcukmart
 * @since 3.0.0
 */
class FormTranslator implements TranslatorInterface
{
    /**
     * Current locale
     */
    private string $locale = 'en_US';

    /**
     * Fallback locale
     */
    private string $fallbackLocale = 'en_US';

    /**
     * Loaded translations [locale][key] = value
     */
    private array $translations = [];

    /**
     * Translation loaders [format] = loader
     *
     * @var array<string, LoaderInterface>
     */
    private array $loaders = [];

    /**
     * Translation resource paths
     */
    private array $resources = [];

    /**
     * @param string|null $translationPath Base path for translation files
     */
    public function __construct(?string $translationPath = null)
    {
        if ($translationPath !== null) {
            $this->addResource($translationPath);
        }
    }

    public function trans(string $key, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        // Load translations for locale if not loaded
        $this->loadTranslationsForLocale($locale);

        // Try to find translation
        $message = $this->getTranslation($key, $locale);

        // Try fallback locale
        if ($message === null && $locale !== $this->fallbackLocale) {
            $this->loadTranslationsForLocale($this->fallbackLocale);
            $message = $this->getTranslation($key, $this->fallbackLocale);
        }

        // Return key if no translation found
        if ($message === null) {
            $message = $key;
        }

        // Interpolate parameters
        return $this->interpolate($message, $parameters);
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;
        $this->loadTranslationsForLocale($locale);
        return $this->getTranslation($key, $locale) !== null;
    }

    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    /**
     * Add translation loader
     *
     * @param string $format File format (yaml, php, json)
     * @param LoaderInterface $loader Loader instance
     */
    public function addLoader(string $format, LoaderInterface $loader): self
    {
        $this->loaders[$format] = $loader;
        return $this;
    }

    /**
     * Add translation resource path
     *
     * @param string $path Path to translation directory
     */
    public function addResource(string $path): self
    {
        $this->resources[] = rtrim($path, '/');
        return $this;
    }

    /**
     * Add translations directly (useful for testing)
     *
     * @param string $locale Locale code
     * @param array $translations Translation array
     */
    public function addTranslations(string $locale, array $translations): self
    {
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = [];
        }

        $this->translations[$locale] = array_merge(
            $this->translations[$locale],
            $this->flattenArray($translations)
        );

        return $this;
    }

    /**
     * Load translations for a locale
     */
    private function loadTranslationsForLocale(string $locale): void
    {
        // Already loaded
        if (isset($this->translations[$locale])) {
            return;
        }

        $this->translations[$locale] = [];

        // Try to load from each resource path
        foreach ($this->resources as $path) {
            foreach ($this->loaders as $format => $loader) {
                $filePath = "{$path}/forms.{$locale}.{$format}";

                if (file_exists($filePath)) {
                    $loaded = $loader->load($filePath);
                    $this->translations[$locale] = array_merge(
                        $this->translations[$locale],
                        $this->flattenArray($loaded)
                    );
                }
            }
        }
    }

    /**
     * Get translation for key
     */
    private function getTranslation(string $key, string $locale): ?string
    {
        return $this->translations[$locale][$key] ?? null;
    }

    /**
     * Interpolate parameters in message
     *
     * Replaces {{ parameter }} or %parameter% with values
     */
    private function interpolate(string $message, array $parameters): string
    {
        if (empty($parameters)) {
            return $message;
        }

        // Replace {{ param }} style
        $message = preg_replace_callback(
            '/\{\{\s*(\w+)\s*\}\}/',
            fn($matches) => $parameters[$matches[1]] ?? $matches[0],
            $message
        );

        // Replace %param% style (Symfony compatibility)
        $message = preg_replace_callback(
            '/%(\w+)%/',
            fn($matches) => $parameters[$matches[1]] ?? $matches[0],
            $message
        );

        return $message;
    }

    /**
     * Flatten nested array to dot notation
     *
     * Example: ['form' => ['label' => ['name' => 'Name']]]
     * Becomes: ['form.label.name' => 'Name']
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Get all translations for current locale
     */
    public function all(?string $locale = null): array
    {
        $locale = $locale ?? $this->locale;
        $this->loadTranslationsForLocale($locale);
        return $this->translations[$locale] ?? [];
    }
}
