<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataMapper;

use FormGenerator\V2\Form\FormInterface;

/**
 * Form Data Mapper - Maps Nested Data to/from Forms
 *
 * Handles the complex task of mapping hierarchical data structures
 * to and from nested form objects.
 *
 * Usage:
 * ```php
 * $mapper = new FormDataMapper();
 *
 * // Map data TO form
 * $mapper->mapDataToForms($data, $form);
 *
 * // Map data FROM form
 * $data = $mapper->mapFormsToData($form);
 * ```
 *
 * @author selcukmart
 * @since 2.4.0
 */
class FormDataMapper
{
    /**
     * Map data to form and its children
     *
     * @param array $data Source data (model/entity data)
     * @param FormInterface $form Target form
     */
    public function mapDataToForms(array $data, FormInterface $form): void
    {
        foreach ($form->all() as $name => $child) {
            if (!isset($data[$name])) {
                continue;
            }

            $value = $data[$name];

            if ($child->getConfig()->isCompound()) {
                // Nested form or collection
                if (is_array($value)) {
                    $child->setData($value);

                    // Recursively map to children
                    $this->mapDataToForms($value, $child);
                }
            } else {
                // Simple field - wrap in array for Form::setData
                $child->setData(['value' => $value]);
            }
        }
    }

    /**
     * Map form data back to array
     *
     * @param FormInterface $form Source form
     * @return array Mapped data
     */
    public function mapFormsToData(FormInterface $form): array
    {
        $data = [];

        foreach ($form->all() as $name => $child) {
            if ($child->getConfig()->isCompound()) {
                // Nested form or collection - recurse
                $data[$name] = $this->mapFormsToData($child);
            } else {
                // Simple field
                $childData = $child->getData();
                $data[$name] = $childData['value'] ?? null;
            }
        }

        return $data;
    }

    /**
     * Map data to form with property path support
     *
     * Supports dot notation for nested properties: 'address.city'
     *
     * @param array $data Source data
     * @param FormInterface $form Target form
     * @param string|null $propertyPath Property path (null for root)
     */
    public function mapDataToFormsWithPropertyPath(
        array $data,
        FormInterface $form,
        ?string $propertyPath = null
    ): void {
        if ($propertyPath === null) {
            $this->mapDataToForms($data, $form);
            return;
        }

        // Navigate to the nested property
        $value = $this->getValueByPropertyPath($data, $propertyPath);

        if (is_array($value)) {
            $this->mapDataToForms($value, $form);
        }
    }

    /**
     * Get value from array using dot notation property path
     *
     * Example: getValueByPropertyPath(['user' => ['name' => 'John']], 'user.name') returns 'John'
     */
    private function getValueByPropertyPath(array $data, string $propertyPath): mixed
    {
        $keys = explode('.', $propertyPath);
        $value = $data;

        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Set value in array using dot notation property path
     */
    private function setValueByPropertyPath(array &$data, string $propertyPath, mixed $value): void
    {
        $keys = explode('.', $propertyPath);
        $current = &$data;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                // Last key - set value
                $current[$key] = $value;
            } else {
                // Navigate deeper
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }

    /**
     * Map object properties to form
     *
     * @param object $object Source object
     * @param FormInterface $form Target form
     */
    public function mapObjectToForms(object $object, FormInterface $form): void
    {
        $data = $this->objectToArray($object);
        $this->mapDataToForms($data, $form);
    }

    /**
     * Map form data to object
     *
     * @param FormInterface $form Source form
     * @param object $object Target object
     */
    public function mapFormsToObject(FormInterface $form, object $object): void
    {
        $data = $this->mapFormsToData($form);
        $this->arrayToObject($data, $object);
    }

    /**
     * Convert object to array
     */
    private function objectToArray(object $object): array
    {
        $data = [];
        $reflection = new \ReflectionClass($object);

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            $value = $property->getValue($object);

            // Handle nested objects
            if (is_object($value)) {
                $data[$name] = $this->objectToArray($value);
            } elseif (is_array($value)) {
                // Handle array of objects
                $data[$name] = array_map(
                    fn($item) => is_object($item) ? $this->objectToArray($item) : $item,
                    $value
                );
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Convert array to object properties
     */
    private function arrayToObject(array $data, object $object): void
    {
        $reflection = new \ReflectionClass($object);

        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);

                // Handle nested objects
                $propertyType = $property->getType();
                if ($propertyType instanceof \ReflectionNamedType && !$propertyType->isBuiltin()) {
                    // Property is an object type
                    $className = $propertyType->getName();

                    if (is_array($value)) {
                        // Create new instance and populate
                        $nestedObject = new $className();
                        $this->arrayToObject($value, $nestedObject);
                        $value = $nestedObject;
                    }
                }

                $property->setValue($object, $value);
            }
        }
    }

    /**
     * Flatten nested errors to dot notation
     *
     * Example: ['address' => ['city' => ['Required']]] becomes ['address.city' => ['Required']]
     */
    public function flattenErrors(array $errors, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($errors as $key => $value) {
            $path = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && !$this->isErrorArray($value)) {
                // Nested errors - recurse
                $flattened = array_merge($flattened, $this->flattenErrors($value, $path));
            } else {
                // Error messages
                $flattened[$path] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Check if array is an error array (array of strings)
     */
    private function isErrorArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        foreach ($array as $item) {
            if (!is_string($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Unflatten errors from dot notation to nested structure
     */
    public function unflattenErrors(array $errors): array
    {
        $result = [];

        foreach ($errors as $path => $messages) {
            $keys = explode('.', $path);
            $current = &$result;

            foreach ($keys as $i => $key) {
                if ($i === count($keys) - 1) {
                    $current[$key] = $messages;
                } else {
                    if (!isset($current[$key])) {
                        $current[$key] = [];
                    }
                    $current = &$current[$key];
                }
            }
        }

        return $result;
    }
}
