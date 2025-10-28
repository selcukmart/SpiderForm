<?php

declare(strict_types=1);

namespace FormGenerator\V2\DataTransformer;

use FormGenerator\V2\Contracts\DataTransformerInterface;

/**
 * Callback Transformer
 *
 * Allows custom transformation logic using closures/callbacks.
 * Useful for quick transformations without creating a dedicated transformer class.
 *
 * Usage Example:
 * ```php
 * // Convert value to uppercase on display, lowercase on submit
 * $form->addText('code', 'Code')
 *     ->addTransformer(new CallbackTransformer(
 *         fn($value) => strtoupper($value),  // transform
 *         fn($value) => strtolower($value)   // reverseTransform
 *     ))
 *     ->add();
 *
 * // Load entity by ID
 * $form->addSelect('user_id', 'User')
 *     ->addTransformer(new CallbackTransformer(
 *         fn($user) => $user?->getId(),                    // transform: User -> ID
 *         fn($id) => $userRepository->findById($id)        // reverseTransform: ID -> User
 *     ))
 *     ->add();
 * ```
 *
 * @author selcukmart
 * @since 2.3.1
 */
class CallbackTransformer implements DataTransformerInterface
{
    /**
     * @param callable $transformCallback Callback for transform() method
     * @param callable $reverseTransformCallback Callback for reverseTransform() method
     */
    public function __construct(
        private readonly mixed $transformCallback,
        private readonly mixed $reverseTransformCallback
    ) {
        if (!is_callable($this->transformCallback)) {
            throw new \InvalidArgumentException('Transform callback must be callable');
        }

        if (!is_callable($this->reverseTransformCallback)) {
            throw new \InvalidArgumentException('Reverse transform callback must be callable');
        }
    }

    /**
     * Transforms a value using the transform callback.
     *
     * @param mixed $value The value to transform
     * @return mixed The transformed value
     * @throws \Exception If the callback throws an exception
     */
    public function transform(mixed $value): mixed
    {
        try {
            return call_user_func($this->transformCallback, $value);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf(
                'Error during transform callback: %s',
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * Transforms a value using the reverse transform callback.
     *
     * @param mixed $value The value to reverse transform
     * @return mixed The reverse transformed value
     * @throws \Exception If the callback throws an exception
     */
    public function reverseTransform(mixed $value): mixed
    {
        try {
            return call_user_func($this->reverseTransformCallback, $value);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf(
                'Error during reverse transform callback: %s',
                $e->getMessage()
            ), 0, $e);
        }
    }
}
