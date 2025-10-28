<?php

declare(strict_types=1);

namespace FormGenerator\V2\Contracts;

/**
 * Data Transformer Interface
 *
 * Transforms data between model (normalized) format and view (denormalized) format.
 * Inspired by Symfony's DataTransformerInterface.
 *
 * Example Use Cases:
 * - Convert DateTime objects to/from strings for date inputs
 * - Convert entity objects to/from IDs for select inputs
 * - Convert arrays to/from comma-separated strings
 * - Apply custom business logic transformations
 *
 * @author selcukmart
 * @since 2.3.1
 */
interface DataTransformerInterface
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * This method is called when the data is being prepared for display in the form.
     * It converts from the model/normalized format to the view/denormalized format.
     *
     * Example: DateTime object -> '2024-01-15' string
     *
     * @param mixed $value The value in the original representation
     * @return mixed The value in the transformed representation
     * @throws \Exception When the transformation fails
     */
    public function transform(mixed $value): mixed;

    /**
     * Transforms a value from the transformed representation to its original representation.
     *
     * This method is called when the form is submitted to convert the submitted data
     * back to the model/normalized format.
     *
     * Example: '2024-01-15' string -> DateTime object
     *
     * @param mixed $value The value in the transformed representation
     * @return mixed The value in the original representation
     * @throws \Exception When the reverse transformation fails
     */
    public function reverseTransform(mixed $value): mixed;
}
