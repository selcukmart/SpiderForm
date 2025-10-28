<?php

declare(strict_types=1);

namespace FormGenerator\V2\Event;

/**
 * Event Subscriber Interface
 *
 * Event subscribers can listen to multiple events and define
 * which methods should be called for each event.
 *
 * Example:
 * ```php
 * class UserFormSubscriber implements EventSubscriberInterface
 * {
 *     public function getSubscribedEvents(): array
 *     {
 *         return [
 *             FormEvents::PRE_SET_DATA => 'onPreSetData',
 *             FormEvents::POST_SUBMIT => ['onPostSubmit', 10], // with priority
 *         ];
 *     }
 *
 *     public function onPreSetData(FormEvent $event): void
 *     {
 *         // Handle pre set data
 *     }
 *
 *     public function onPostSubmit(FormEvent $event): void
 *     {
 *         // Handle post submit
 *     }
 * }
 * ```
 */
interface EventSubscriberInterface
{
    /**
     * Get subscribed events
     *
     * Returns an array where keys are event names and values are either:
     * - Method name (string): 'onEventName'
     * - Array: ['methodName', priority]
     *
     * Example:
     * ```php
     * return [
     *     FormEvents::PRE_SET_DATA => 'onPreSetData',
     *     FormEvents::POST_SUBMIT => ['onPostSubmit', 10],
     *     FormEvents::VALIDATION_ERROR => ['onValidationError', -10],
     * ];
     * ```
     *
     * @return array<string, string|array> Event subscriptions
     */
    public function getSubscribedEvents(): array;
}
