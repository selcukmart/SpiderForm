<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type;

/**
 * Type Extension Registry
 *
 * Manages registration and retrieval of type extensions.
 *
 * @author selcukmart
 * @since 2.5.0
 */
class TypeExtensionRegistry
{
    /**
     * Registered extensions
     *
     * @var array<TypeExtensionInterface>
     */
    private static array $extensions = [];

    /**
     * Extensions by type cache
     *
     * @var array<string, array<TypeExtensionInterface>>
     */
    private static array $extensionsByType = [];

    /**
     * Register a type extension
     */
    public static function register(TypeExtensionInterface $extension): void
    {
        self::$extensions[] = $extension;
        self::$extensionsByType = []; // Clear cache
    }

    /**
     * Register multiple extensions
     *
     * @param array<TypeExtensionInterface> $extensions
     */
    public static function registerMany(array $extensions): void
    {
        foreach ($extensions as $extension) {
            self::register($extension);
        }
    }

    /**
     * Get all extensions for a specific type
     *
     * @param string $typeName Type name
     * @return array<TypeExtensionInterface>
     */
    public static function getExtensionsForType(string $typeName): array
    {
        // Return cached if available
        if (isset(self::$extensionsByType[$typeName])) {
            return self::$extensionsByType[$typeName];
        }

        $extensions = [];

        foreach (self::$extensions as $extension) {
            $extendedTypes = $extension->extendType();
            $extendedTypes = is_array($extendedTypes) ? $extendedTypes : [$extendedTypes];

            if (in_array($typeName, $extendedTypes, true)) {
                $extensions[] = $extension;
            }
        }

        // Cache for next time
        self::$extensionsByType[$typeName] = $extensions;

        return $extensions;
    }

    /**
     * Get all registered extensions
     *
     * @return array<TypeExtensionInterface>
     */
    public static function getAllExtensions(): array
    {
        return self::$extensions;
    }

    /**
     * Clear all registered extensions
     */
    public static function clear(): void
    {
        self::$extensions = [];
        self::$extensionsByType = [];
    }

    /**
     * Check if any extensions are registered for a type
     */
    public static function hasExtensionsForType(string $typeName): bool
    {
        return !empty(self::getExtensionsForType($typeName));
    }

    /**
     * Get count of registered extensions
     */
    public static function count(): int
    {
        return count(self::$extensions);
    }
}
