<?php

declare(strict_types=1);

namespace FormGenerator\V2\Type;

/**
 * Options Resolver - Symfony-inspired Option Configuration
 *
 * Validates and resolves options with defaults, allowed values, and types.
 *
 * Usage:
 * ```php
 * $resolver = new OptionsResolver();
 * $resolver->setDefaults([
 *     'required' => false,
 *     'label' => null,
 *     'attr' => [],
 * ]);
 * $resolver->setAllowedTypes('required', 'bool');
 * $resolver->setAllowedTypes('label', ['null', 'string']);
 * $resolver->setAllowedTypes('attr', 'array');
 *
 * $resolved = $resolver->resolve(['required' => true, 'label' => 'Name']);
 * ```
 *
 * @author selcukmart
 * @since 2.5.0
 */
class OptionsResolver
{
    private array $defaults = [];
    private array $required = [];
    private array $defined = [];
    private array $allowedTypes = [];
    private array $allowedValues = [];
    private array $normalizers = [];
    private array $info = [];

    /**
     * Set default values for options
     */
    public function setDefaults(array $defaults): self
    {
        foreach ($defaults as $option => $value) {
            $this->defaults[$option] = $value;
            $this->defined[] = $option;
        }
        return $this;
    }

    /**
     * Set a single default value
     */
    public function setDefault(string $option, mixed $value): self
    {
        $this->defaults[$option] = $value;
        $this->defined[] = $option;
        return $this;
    }

    /**
     * Mark options as required
     */
    public function setRequired(string|array $options): self
    {
        $options = is_array($options) ? $options : [$options];
        foreach ($options as $option) {
            $this->required[] = $option;
            $this->defined[] = $option;
        }
        return $this;
    }

    /**
     * Define allowed option names
     */
    public function setDefined(string|array $options): self
    {
        $options = is_array($options) ? $options : [$options];
        $this->defined = array_merge($this->defined, $options);
        return $this;
    }

    /**
     * Set allowed types for an option
     *
     * @param string $option Option name
     * @param string|array $types Allowed type(s): 'string', 'int', 'bool', 'array', 'callable', 'object', class name
     */
    public function setAllowedTypes(string $option, string|array $types): self
    {
        $this->allowedTypes[$option] = is_array($types) ? $types : [$types];
        return $this;
    }

    /**
     * Set allowed values for an option
     */
    public function setAllowedValues(string $option, array|callable $allowedValues): self
    {
        $this->allowedValues[$option] = $allowedValues;
        return $this;
    }

    /**
     * Add a normalizer for an option
     *
     * Normalizer is called after validation to transform the value
     */
    public function setNormalizer(string $option, callable $normalizer): self
    {
        $this->normalizers[$option] = $normalizer;
        return $this;
    }

    /**
     * Set info/documentation for an option
     */
    public function setInfo(string $option, string $info): self
    {
        $this->info[$option] = $info;
        return $this;
    }

    /**
     * Resolve options with validation
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function resolve(array $options = []): array
    {
        // Check for undefined options
        $undefined = array_diff(array_keys($options), $this->defined);
        if (!empty($undefined)) {
            throw new \InvalidArgumentException(sprintf(
                'The option(s) "%s" do not exist. Defined options are: "%s".',
                implode('", "', $undefined),
                implode('", "', $this->defined)
            ));
        }

        // Check required options
        $missing = array_diff($this->required, array_keys($options));
        if (!empty($missing)) {
            throw new \InvalidArgumentException(sprintf(
                'The required option(s) "%s" are missing.',
                implode('", "', $missing)
            ));
        }

        // Merge with defaults
        $resolved = array_merge($this->defaults, $options);

        // Validate types
        foreach ($this->allowedTypes as $option => $types) {
            if (!isset($resolved[$option])) {
                continue;
            }

            $value = $resolved[$option];
            $valid = false;

            foreach ($types as $type) {
                if ($this->isValidType($value, $type)) {
                    $valid = true;
                    break;
                }
            }

            if (!$valid) {
                throw new \InvalidArgumentException(sprintf(
                    'The option "%s" with value %s is expected to be of type "%s", but is of type "%s".',
                    $option,
                    json_encode($value),
                    implode('" or "', $types),
                    get_debug_type($value)
                ));
            }
        }

        // Validate allowed values
        foreach ($this->allowedValues as $option => $allowedValues) {
            if (!isset($resolved[$option])) {
                continue;
            }

            $value = $resolved[$option];

            if (is_callable($allowedValues)) {
                if (!$allowedValues($value)) {
                    throw new \InvalidArgumentException(sprintf(
                        'The option "%s" with value %s is invalid.',
                        $option,
                        json_encode($value)
                    ));
                }
            } elseif (!in_array($value, $allowedValues, true)) {
                throw new \InvalidArgumentException(sprintf(
                    'The option "%s" with value %s is invalid. Accepted values are: "%s".',
                    $option,
                    json_encode($value),
                    implode('", "', array_map('json_encode', $allowedValues))
                ));
            }
        }

        // Apply normalizers
        foreach ($this->normalizers as $option => $normalizer) {
            if (isset($resolved[$option])) {
                $resolved[$option] = $normalizer($resolved[$option], $resolved);
            }
        }

        return $resolved;
    }

    /**
     * Check if value matches type
     */
    private function isValidType(mixed $value, string $type): bool
    {
        // Handle null
        if ($type === 'null') {
            return $value === null;
        }

        if ($value === null) {
            return false;
        }

        // Built-in types
        return match ($type) {
            'bool', 'boolean' => is_bool($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value),
            'string' => is_string($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'callable' => is_callable($value),
            'iterable' => is_iterable($value),
            'resource' => is_resource($value),
            default => is_object($value) && ($value instanceof $type || is_subclass_of($value, $type))
        };
    }

    /**
     * Get default value for an option
     */
    public function getDefault(string $option): mixed
    {
        return $this->defaults[$option] ?? null;
    }

    /**
     * Check if option has default
     */
    public function hasDefault(string $option): bool
    {
        return array_key_exists($option, $this->defaults);
    }

    /**
     * Check if option is required
     */
    public function isRequired(string $option): bool
    {
        return in_array($option, $this->required, true);
    }

    /**
     * Check if option is defined
     */
    public function isDefined(string $option): bool
    {
        return in_array($option, $this->defined, true);
    }

    /**
     * Get info for an option
     */
    public function getInfo(string $option): ?string
    {
        return $this->info[$option] ?? null;
    }

    /**
     * Get all defined options
     */
    public function getDefinedOptions(): array
    {
        return $this->defined;
    }

    /**
     * Get all required options
     */
    public function getRequiredOptions(): array
    {
        return $this->required;
    }

    /**
     * Clear all configuration
     */
    public function clear(): self
    {
        $this->defaults = [];
        $this->required = [];
        $this->defined = [];
        $this->allowedTypes = [];
        $this->allowedValues = [];
        $this->normalizers = [];
        $this->info = [];
        return $this;
    }
}
