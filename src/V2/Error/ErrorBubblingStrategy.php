<?php

declare(strict_types=1);

namespace FormGenerator\V2\Error;

use FormGenerator\V2\Form\FormInterface;

/**
 * Error Bubbling Strategy - Controls Error Propagation
 *
 * Determines how errors propagate from child forms to parent forms
 * in nested form structures.
 *
 * Usage:
 * ```php
 * $strategy = new ErrorBubblingStrategy(enabled: true);
 *
 * // Collect errors from child form
 * $parentErrors = $strategy->collectErrors($parentForm, $childForm);
 * ```
 *
 * @author selcukmart
 * @since 2.9.0
 */
class ErrorBubblingStrategy
{
    /**
     * @param bool $enabled Whether error bubbling is enabled
     * @param bool $stopOnBlocking Stop bubbling after first blocking error
     * @param int $maxDepth Maximum depth for bubbling (0 = unlimited)
     */
    public function __construct(
        private readonly bool $enabled = true,
        private readonly bool $stopOnBlocking = false,
        private readonly int $maxDepth = 0
    ) {
    }

    /**
     * Check if bubbling is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Collect errors from child form to bubble to parent
     *
     * @param FormInterface $parent Parent form
     * @param FormInterface $child Child form
     * @return ErrorList Errors to bubble to parent
     */
    public function collectErrors(FormInterface $parent, FormInterface $child): ErrorList
    {
        if (!$this->enabled) {
            return new ErrorList();
        }

        $childErrors = $child->getErrorList(deep: true);

        if ($childErrors->isEmpty()) {
            return new ErrorList();
        }

        // Transform errors to include child form path
        $bubbledErrors = new ErrorList();
        $childName = $child->getName();

        foreach ($childErrors as $error) {
            $originalPath = $error->getPath();
            $newPath = $originalPath ? "{$childName}.{$originalPath}" : $childName;

            $bubbledError = new FormError(
                message: $error->getRawMessage(),
                level: $error->getLevel(),
                path: $newPath,
                parameters: $error->getParameters(),
                cause: $error->getCause(),
                origin: $error->getOrigin() ?? $child
            );

            $bubbledErrors->add($bubbledError);

            // Stop if we hit a blocking error
            if ($this->stopOnBlocking && $bubbledError->isBlocking()) {
                break;
            }
        }

        return $bubbledErrors;
    }

    /**
     * Bubble errors up the form hierarchy
     *
     * @param FormInterface $form Starting form
     * @return ErrorList All errors including bubbled ones
     */
    public function bubbleUp(FormInterface $form): ErrorList
    {
        $allErrors = new ErrorList();
        $current = $form;
        $depth = 0;

        while ($current !== null) {
            // Add errors from current level
            $currentErrors = $current->getErrorList(deep: false);
            $allErrors = $allErrors->merge($currentErrors);

            // Check depth limit
            if ($this->maxDepth > 0 && $depth >= $this->maxDepth) {
                break;
            }

            // Move to parent
            $current = $current->getParent();
            $depth++;
        }

        return $allErrors;
    }

    /**
     * Check if error should bubble based on strategy
     *
     * @param FormError $error Error to check
     * @param int $currentDepth Current bubbling depth
     * @return bool True if error should bubble
     */
    public function shouldBubble(FormError $error, int $currentDepth = 0): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // Check depth limit
        if ($this->maxDepth > 0 && $currentDepth >= $this->maxDepth) {
            return false;
        }

        // Check if we should stop on blocking errors
        if ($this->stopOnBlocking && $error->isBlocking()) {
            return false;
        }

        return true;
    }

    /**
     * Create strategy with no bubbling
     */
    public static function disabled(): self
    {
        return new self(enabled: false);
    }

    /**
     * Create strategy with full bubbling
     */
    public static function enabled(): self
    {
        return new self(enabled: true);
    }

    /**
     * Create strategy that stops on first blocking error
     */
    public static function stopOnBlocking(): self
    {
        return new self(enabled: true, stopOnBlocking: true);
    }

    /**
     * Create strategy with depth limit
     */
    public static function withDepthLimit(int $maxDepth): self
    {
        return new self(enabled: true, maxDepth: $maxDepth);
    }
}
