<?php

declare(strict_types=1);

namespace FormGenerator\V2\Validation;

/**
 * Validator Factory - Create validators with a fluent interface
 *
 * Usage:
 * ```php
 * $validator = ValidatorFactory::make($data, $rules)
 *     ->setMessages($messages)
 *     ->setCustomAttributes($attributes)
 *     ->setDatabaseConnection($pdo);
 *
 * if ($validator->fails()) {
 *     // Handle errors
 * }
 * ```
 */
class ValidatorFactory
{
    private static ?\PDO $defaultConnection = null;

    /**
     * Set default database connection
     */
    public static function setDefaultConnection(\PDO $connection): void
    {
        self::$defaultConnection = $connection;
    }

    /**
     * Create a new validator instance
     */
    public static function make(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): Validator {
        $validator = new Validator($data, $rules, $messages, $customAttributes);

        // Set default database connection if available
        if (self::$defaultConnection !== null) {
            $validator->setDatabaseConnection(self::$defaultConnection);
        }

        return $validator;
    }

    /**
     * Validate data and return validated data or throw exception
     *
     * @throws ValidationException
     */
    public static function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): array {
        return self::make($data, $rules, $messages, $customAttributes)->validate();
    }

    /**
     * Create validator with bail mode enabled
     */
    public static function makeBail(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): Validator {
        return self::make($data, $rules, $messages, $customAttributes)->bail();
    }
}
