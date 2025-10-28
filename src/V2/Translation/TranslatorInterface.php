<?php

declare(strict_types=1);

namespace FormGenerator\V2\Translation;

/**
 * Translator Interface - Contract for Translation Systems
 *
 * Defines the contract for translation implementations, allowing
 * multiple backends (native, Symfony, Laravel, etc.).
 *
 * Usage:
 * ```php
 * $translator = new FormTranslator();
 * $translator->setLocale('fr_FR');
 * echo $translator->trans('form.label.name'); // "Nom"
 * ```
 *
 * @author selcukmart
 * @since 3.0.0
 */
interface TranslatorInterface
{
    /**
     * Translate a message
     *
     * @param string $key Translation key (e.g., 'form.label.name')
     * @param array $parameters Parameters for interpolation
     * @param string|null $locale Locale to use (null = current locale)
     * @return string Translated message
     */
    public function trans(string $key, array $parameters = [], ?string $locale = null): string;

    /**
     * Set current locale
     *
     * @param string $locale Locale code (e.g., 'en_US', 'tr_TR', 'fr_FR')
     */
    public function setLocale(string $locale): void;

    /**
     * Get current locale
     *
     * @return string Current locale code
     */
    public function getLocale(): string;

    /**
     * Check if translation exists for key
     *
     * @param string $key Translation key
     * @param string|null $locale Locale to check (null = current locale)
     * @return bool True if translation exists
     */
    public function has(string $key, ?string $locale = null): bool;

    /**
     * Get fallback locale
     *
     * @return string Fallback locale code
     */
    public function getFallbackLocale(): string;

    /**
     * Set fallback locale
     *
     * @param string $locale Fallback locale code
     */
    public function setFallbackLocale(string $locale): void;
}
