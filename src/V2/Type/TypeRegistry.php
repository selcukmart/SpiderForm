<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type;

use FormGenerator\V2\Form\AbstractFormType;

/**
 * Type Registry - Central Registry for Form Types
 *
 * Manages registration and retrieval of form types.
 * Supports custom types, type aliases, and lazy loading.
 *
 * Usage:
 * ```php
 * // Register a custom type
 * TypeRegistry::register('phone', PhoneType::class);
 * TypeRegistry::register('wysiwyg', WysiwygType::class);
 *
 * // Use in forms
 * $form->addField('mobile', 'phone', ['country' => 'US']);
 *
 * // Get type instance
 * $phoneType = TypeRegistry::get('phone');
 * ```
 *
 * @author selcukmart
 * @since 2.5.0
 */
class TypeRegistry
{
    /**
     * Registered types (name => class name or instance)
     *
     * @var array<string, string|AbstractFormType>
     */
    private static array $types = [];

    /**
     * Type instances cache
     *
     * @var array<string, AbstractFormType>
     */
    private static array $instances = [];

    /**
     * Type aliases (alias => real type name)
     *
     * @var array<string, string>
     */
    private static array $aliases = [];

    /**
     * Register a form type
     *
     * @param string $name Type name
     * @param string|AbstractFormType $type Class name or instance
     */
    public static function register(string $name, string|AbstractFormType $type): void
    {
        self::$types[$name] = $type;

        // Clear cached instance if exists
        if (isset(self::$instances[$name])) {
            unset(self::$instances[$name]);
        }
    }

    /**
     * Register multiple types at once
     *
     * @param array<string, string|AbstractFormType> $types
     */
    public static function registerMany(array $types): void
    {
        foreach ($types as $name => $type) {
            self::register($name, $type);
        }
    }

    /**
     * Register a type alias
     *
     * @param string $alias Alias name
     * @param string $realType Real type name
     */
    public static function alias(string $alias, string $realType): void
    {
        self::$aliases[$alias] = $realType;
    }

    /**
     * Get a type instance
     *
     * @param string $name Type name or alias
     * @return AbstractFormType Type instance
     * @throws \InvalidArgumentException If type not found
     */
    public static function get(string $name): AbstractFormType
    {
        // Resolve alias
        $name = self::$aliases[$name] ?? $name;

        // Return cached instance
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        // Check if type is registered
        if (!isset(self::$types[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Type "%s" is not registered. Registered types: %s',
                $name,
                implode(', ', array_keys(self::$types))
            ));
        }

        $type = self::$types[$name];

        // Instantiate if class name
        if (is_string($type)) {
            if (!class_exists($type)) {
                throw new \InvalidArgumentException(sprintf(
                    'Type class "%s" does not exist.',
                    $type
                ));
            }

            $type = new $type();

            if (!$type instanceof AbstractFormType) {
                throw new \InvalidArgumentException(sprintf(
                    'Type class "%s" must extend AbstractFormType.',
                    get_class($type)
                ));
            }
        }

        // Cache instance
        self::$instances[$name] = $type;

        return $type;
    }

    /**
     * Check if type is registered
     */
    public static function has(string $name): bool
    {
        $name = self::$aliases[$name] ?? $name;
        return isset(self::$types[$name]);
    }

    /**
     * Unregister a type
     */
    public static function unregister(string $name): void
    {
        unset(self::$types[$name], self::$instances[$name]);
    }

    /**
     * Get all registered type names
     *
     * @return array<string>
     */
    public static function getTypeNames(): array
    {
        return array_keys(self::$types);
    }

    /**
     * Get all type aliases
     *
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return self::$aliases;
    }

    /**
     * Clear all registered types
     */
    public static function clear(): void
    {
        self::$types = [];
        self::$instances = [];
        self::$aliases = [];
    }

    /**
     * Register built-in types
     *
     * Called automatically on first use
     */
    public static function registerBuiltInTypes(): void
    {
        if (!empty(self::$types)) {
            return; // Already registered
        }

        self::registerMany([
            'text' => Types\TextType::class,
            'email' => Types\EmailType::class,
            'password' => Types\PasswordType::class,
            'number' => Types\NumberType::class,
            'integer' => Types\IntegerType::class,
            'tel' => Types\TelType::class,
            'url' => Types\UrlType::class,
            'date' => Types\DateType::class,
            'time' => Types\TimeType::class,
            'datetime' => Types\DateTimeType::class,
            'textarea' => Types\TextareaType::class,
            'checkbox' => Types\CheckboxType::class,
            'radio' => Types\RadioType::class,
            'select' => Types\SelectType::class,
            'choice' => Types\ChoiceType::class,
            'file' => Types\FileType::class,
            'hidden' => Types\HiddenType::class,
            'submit' => Types\SubmitType::class,
            'button' => Types\ButtonType::class,
            'reset' => Types\ResetType::class,
        ]);

        // Aliases
        self::alias('string', 'text');
        self::alias('int', 'integer');
        self::alias('phone', 'tel');
        self::alias('datetime-local', 'datetime');
    }

    /**
     * Get type hierarchy (type and all its parents)
     *
     * @return array<AbstractFormType>
     */
    public static function getTypeHierarchy(string $name): array
    {
        $hierarchy = [];
        $type = self::get($name);

        while ($type !== null) {
            $hierarchy[] = $type;

            $parentType = $type->getParent();
            if ($parentType === null) {
                break;
            }

            $type = self::get($parentType);
        }

        return $hierarchy;
    }

    /**
     * Check if type extends another type
     */
    public static function isTypeOf(string $typeName, string $parentTypeName): bool
    {
        $type = self::get($typeName);

        while ($type !== null) {
            if ($type->getName() === $parentTypeName) {
                return true;
            }

            $parentType = $type->getParent();
            if ($parentType === null) {
                break;
            }

            $type = self::get($parentType);
        }

        return false;
    }
}
