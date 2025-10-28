<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

use FormGenerator\V2\Form\{FormCollection, FormConfig};

/**
 * Collection Builder - Fluent Interface for Form Collections
 *
 * Helper class for configuring form collections with a fluent API.
 *
 * Usage:
 * ```php
 * $form->addCollection('items', 'Items', function($item) {
 *     $item->addText('name')->add();
 * })
 * ->allowAdd()
 * ->allowDelete()
 * ->min(1)
 * ->max(10);
 * ```
 *
 * @author selcukmart
 * @since 2.4.0
 */
class CollectionBuilder
{
    private bool $allowAdd = true;
    private bool $allowDelete = true;
    private int $min = 0;
    private int $max = 0;
    private array $entryOptions = [];

    public function __construct(
        private readonly FormBuilder $formBuilder,
        private readonly string $name,
        private readonly ?string $label,
        private readonly \Closure $prototypeBuilder
    ) {
    }

    /**
     * Allow adding new entries to collection
     */
    public function allowAdd(bool $allow = true): self
    {
        $this->allowAdd = $allow;
        return $this;
    }

    /**
     * Allow deleting entries from collection
     */
    public function allowDelete(bool $allow = true): self
    {
        $this->allowDelete = $allow;
        return $this;
    }

    /**
     * Set minimum number of entries
     */
    public function min(int $min): self
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Set maximum number of entries (0 = unlimited)
     */
    public function max(int $max): self
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Set options for each entry
     */
    public function entryOptions(array $options): self
    {
        $this->entryOptions = $options;
        return $this;
    }

    /**
     * Finish collection configuration and return to FormBuilder
     */
    public function add(): FormBuilder
    {
        return $this->formBuilder;
    }

    /**
     * Build FormCollection instance
     *
     * @internal Used by FormBuilder
     */
    public function buildCollectionForm(): FormCollection
    {
        $config = new FormConfig(
            name: $this->name,
            type: 'collection',
            options: [
                'label' => $this->label,
                'allow_add' => $this->allowAdd,
                'allow_delete' => $this->allowDelete,
                'min' => $this->min,
                'max' => $this->max,
                'entry_options' => $this->entryOptions,
            ],
            compound: true
        );

        $collection = new FormCollection($this->name, $config);
        $collection->setAllowAdd($this->allowAdd);
        $collection->setAllowDelete($this->allowDelete);
        $collection->setMin($this->min);
        $collection->setMax($this->max);
        $collection->setEntryOptions($this->entryOptions);

        // Set prototype builder
        $collection->setPrototypeBuilder(function($entryForm) {
            $builder = new FormBuilder('__name__');
            ($this->prototypeBuilder)($builder);

            // Add fields from builder to entry form
            foreach ($builder->getInputs() as $item) {
                $input = $item['input'];
                $fieldConfig = new FormConfig(
                    name: $input->getName(),
                    type: $input->getType()->value,
                    options: $input->toArray(),
                    compound: false
                );

                $field = new \FormGenerator\V2\Form\Form($input->getName(), $fieldConfig, $input->toArray());
                $entryForm->add($input->getName(), $field);
            }
        });

        return $collection;
    }

    /**
     * Get collection name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get collection label
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Get prototype builder
     */
    public function getPrototypeBuilder(): \Closure
    {
        return $this->prototypeBuilder;
    }
}
