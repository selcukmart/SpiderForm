<?php

declare(strict_types=1);

namespace FormGenerator\V2\Error;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Error List - Collection of Form Errors
 *
 * Manages a collection of FormError objects with filtering,
 * grouping, and transformation capabilities.
 *
 * Usage:
 * ```php
 * $errors = new ErrorList();
 * $errors->add(new FormError('Email is required', ErrorLevel::ERROR, 'email'));
 * $errors->add(new FormError('Weak password', ErrorLevel::WARNING, 'password'));
 *
 * echo $errors->count();                      // 2
 * $critical = $errors->byLevel(ErrorLevel::ERROR);
 * $emailErrors = $errors->byPath('email');
 * ```
 *
 * @author selcukmart
 * @since 2.9.0
 */
class ErrorList implements Countable, IteratorAggregate
{
    /**
     * @var FormError[]
     */
    private array $errors = [];

    /**
     * @param FormError[] $errors Initial errors
     */
    public function __construct(array $errors = [])
    {
        foreach ($errors as $error) {
            $this->add($error);
        }
    }

    /**
     * Add an error to the list
     */
    public function add(FormError $error): self
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * Add multiple errors
     *
     * @param FormError[] $errors
     */
    public function addAll(array $errors): self
    {
        foreach ($errors as $error) {
            $this->add($error);
        }
        return $this;
    }

    /**
     * Get all errors
     *
     * @return FormError[]
     */
    public function all(): array
    {
        return $this->errors;
    }

    /**
     * Filter errors by severity level
     *
     * @return self New ErrorList with filtered errors
     */
    public function byLevel(ErrorLevel $level): self
    {
        $filtered = array_filter(
            $this->errors,
            fn(FormError $error) => $error->getLevel() === $level
        );

        return new self($filtered);
    }

    /**
     * Filter errors by path
     *
     * @param string $path Field path (e.g., 'email' or 'address.zipcode')
     * @param bool $deep Include child paths (e.g., 'address' includes 'address.street')
     * @return self New ErrorList with filtered errors
     */
    public function byPath(string $path, bool $deep = false): self
    {
        $filtered = array_filter(
            $this->errors,
            function (FormError $error) use ($path, $deep) {
                $errorPath = $error->getPath();
                if ($errorPath === null) {
                    return false;
                }

                if ($deep) {
                    // Match exact path or child paths
                    return $errorPath === $path || str_starts_with($errorPath, $path . '.');
                }

                return $errorPath === $path;
            }
        );

        return new self($filtered);
    }

    /**
     * Get only blocking errors (ERROR level)
     *
     * @return self New ErrorList with only blocking errors
     */
    public function blocking(): self
    {
        return $this->byLevel(ErrorLevel::ERROR);
    }

    /**
     * Check if list has any blocking errors
     */
    public function hasBlocking(): bool
    {
        return $this->blocking()->count() > 0;
    }

    /**
     * Convert to nested array structure
     *
     * Returns:
     * [
     *   'email' => ['Email is required', 'Invalid format'],
     *   'address' => [
     *     'street' => ['Street is required'],
     *     'zipcode' => ['Invalid ZIP']
     *   ]
     * ]
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->errors as $error) {
            $path = $error->getPath();
            $message = $error->getMessage();

            if ($path === null) {
                // Form-level error
                $result['_form'][] = $message;
                continue;
            }

            // Navigate nested structure
            $keys = explode('.', $path);
            $current = &$result;

            foreach ($keys as $key) {
                if (!isset($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }

            // Add message to the deepest level
            if (!is_array($current)) {
                $current = [$current];
            }
            $current[] = $message;
        }

        return $result;
    }

    /**
     * Convert to flat array with dot notation
     *
     * Returns:
     * [
     *   'email' => 'Email is required',
     *   'address.street' => 'Street is required',
     *   'address.zipcode' => 'Invalid ZIP'
     * ]
     *
     * Note: If multiple errors exist for same path, only first is returned
     */
    public function toFlat(): array
    {
        $result = [];

        foreach ($this->errors as $error) {
            $path = $error->getPath() ?? '_form';

            // Only keep first error per path
            if (!isset($result[$path])) {
                $result[$path] = $error->getMessage();
            }
        }

        return $result;
    }

    /**
     * Get first error for a path
     */
    public function first(?string $path = null): ?FormError
    {
        if ($path === null) {
            return $this->errors[0] ?? null;
        }

        foreach ($this->errors as $error) {
            if ($error->getPath() === $path) {
                return $error;
            }
        }

        return null;
    }

    /**
     * Check if list is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->errors);
    }

    /**
     * Count errors
     */
    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * Get iterator for traversal
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->errors);
    }

    /**
     * Clear all errors
     */
    public function clear(): self
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Merge with another ErrorList
     */
    public function merge(ErrorList $other): self
    {
        return new self([...$this->errors, ...$other->all()]);
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        return implode("\n", array_map(
            fn(FormError $error) => (string) $error,
            $this->errors
        ));
    }
}
