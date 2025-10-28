<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

/**
 * Form Collection - Dynamic Form Collections (Symfony-inspired)
 *
 * Manages collections of forms/fields that can be dynamically added/removed.
 * Similar to Symfony's CollectionType.
 *
 * Usage:
 * ```php
 * $collection = new FormCollection('items', $prototypeBuilder);
 * $collection->setData([
 *     ['product' => 'Item 1', 'qty' => 5],
 *     ['product' => 'Item 2', 'qty' => 3],
 * ]);
 * ```
 *
 * @author selcukmart
 * @since 2.4.0
 */
class FormCollection extends Form
{
    /**
     * Prototype form builder (used for creating new entries)
     */
    private ?\Closure $prototypeBuilder = null;

    /**
     * Prototype form instance
     */
    private ?FormInterface $prototype = null;

    /**
     * Allow adding new entries
     */
    private bool $allowAdd = true;

    /**
     * Allow deleting entries
     */
    private bool $allowDelete = true;

    /**
     * Minimum number of entries
     */
    private int $min = 0;

    /**
     * Maximum number of entries (0 = unlimited)
     */
    private int $max = 0;

    /**
     * Entry options
     */
    private array $entryOptions = [];

    /**
     * Set prototype builder
     */
    public function setPrototypeBuilder(\Closure $builder): self
    {
        $this->prototypeBuilder = $builder;
        return $this;
    }

    /**
     * Get prototype form
     */
    public function getPrototype(): ?FormInterface
    {
        if ($this->prototype === null && $this->prototypeBuilder !== null) {
            $this->prototype = $this->createPrototype();
        }

        return $this->prototype;
    }

    /**
     * Create prototype form
     */
    private function createPrototype(): FormInterface
    {
        $config = new FormConfig(
            name: '__name__', // Placeholder name for prototype
            type: 'form',
            compound: true,
            options: $this->entryOptions
        );

        $prototype = new Form('__name__', $config);

        // Build prototype using closure
        if ($this->prototypeBuilder !== null) {
            ($this->prototypeBuilder)($prototype);
        }

        return $prototype;
    }

    /**
     * Set collection data (array of items)
     */
    public function setData(array $data): self
    {
        // Clear existing children
        foreach (array_keys($this->all()) as $key) {
            $this->remove($key);
        }

        // Create child forms for each data item
        foreach ($data as $index => $itemData) {
            $this->addEntry($index, $itemData);
        }

        return parent::setData($data);
    }

    /**
     * Add a new entry to the collection
     */
    public function addEntry(int|string $index, array $data = []): FormInterface
    {
        $config = new FormConfig(
            name: (string) $index,
            type: 'form',
            compound: true,
            options: $this->entryOptions
        );

        $entry = new Form((string) $index, $config);

        // Build entry using prototype builder
        if ($this->prototypeBuilder !== null) {
            ($this->prototypeBuilder)($entry);
        }

        // Set data on entry
        $entry->setData($data);
        $entry->setParent($this);

        // Add to children
        $this->add((string) $index, $entry);

        return $entry;
    }

    /**
     * Remove an entry from the collection
     */
    public function removeEntry(int|string $index): self
    {
        return $this->remove((string) $index);
    }

    /**
     * Get all entries
     *
     * @return array<FormInterface>
     */
    public function getEntries(): array
    {
        return $this->all();
    }

    /**
     * Count entries
     */
    public function countEntries(): int
    {
        return count($this->all());
    }

    /**
     * Set allow add
     */
    public function setAllowAdd(bool $allowAdd): self
    {
        $this->allowAdd = $allowAdd;
        return $this;
    }

    /**
     * Check if adding is allowed
     */
    public function canAdd(): bool
    {
        if (!$this->allowAdd) {
            return false;
        }

        if ($this->max > 0 && $this->countEntries() >= $this->max) {
            return false;
        }

        return true;
    }

    /**
     * Set allow delete
     */
    public function setAllowDelete(bool $allowDelete): self
    {
        $this->allowDelete = $allowDelete;
        return $this;
    }

    /**
     * Check if deleting is allowed
     */
    public function canDelete(): bool
    {
        if (!$this->allowDelete) {
            return false;
        }

        if ($this->min > 0 && $this->countEntries() <= $this->min) {
            return false;
        }

        return true;
    }

    /**
     * Set minimum entries
     */
    public function setMin(int $min): self
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Get minimum entries
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * Set maximum entries (0 = unlimited)
     */
    public function setMax(int $max): self
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Get maximum entries
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * Set entry options
     */
    public function setEntryOptions(array $options): self
    {
        $this->entryOptions = $options;
        return $this;
    }

    /**
     * Get entry options
     */
    public function getEntryOptions(): array
    {
        return $this->entryOptions;
    }

    /**
     * Validate collection (including min/max constraints)
     */
    public function validate(): array
    {
        $errors = parent::validate();

        $count = $this->countEntries();

        // Validate minimum
        if ($this->min > 0 && $count < $this->min) {
            $errors['_collection'] = [
                sprintf('Collection must have at least %d entries, got %d', $this->min, $count)
            ];
        }

        // Validate maximum
        if ($this->max > 0 && $count > $this->max) {
            $errors['_collection'] = [
                sprintf('Collection must have at most %d entries, got %d', $this->max, $count)
            ];
        }

        return $errors;
    }

    /**
     * Create collection view with prototype
     */
    public function createView(): FormView
    {
        $view = parent::createView();

        // Add collection-specific vars
        $view->setVars([
            'allow_add' => $this->allowAdd,
            'allow_delete' => $this->allowDelete,
            'min' => $this->min,
            'max' => $this->max,
            'prototype' => $this->getPrototype()?->createView(),
        ]);

        return $view;
    }

    /**
     * Submit collection data (handles added/removed entries)
     */
    public function submit(array $data): self
    {
        // Get current entry keys
        $currentKeys = array_keys($this->all());
        $submittedKeys = array_keys($data);

        // Remove entries not in submitted data (if deletion allowed)
        if ($this->allowDelete) {
            foreach (array_diff($currentKeys, $submittedKeys) as $removedKey) {
                $this->removeEntry($removedKey);
            }
        }

        // Add new entries (if addition allowed)
        if ($this->allowAdd) {
            foreach (array_diff($submittedKeys, $currentKeys) as $newKey) {
                if (isset($data[$newKey])) {
                    $this->addEntry($newKey, $data[$newKey]);
                }
            }
        }

        // Submit to existing entries
        return parent::submit($data);
    }
}
