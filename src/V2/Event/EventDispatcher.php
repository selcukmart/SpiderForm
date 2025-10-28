<?php

declare(strict_types=1);

namespace FormGenerator\V2\Event;

/**
 * Event Dispatcher - Manages and dispatches form events
 *
 * Allows you to register listeners and dispatch events throughout
 * the form lifecycle.
 *
 * Example:
 * ```php
 * $dispatcher = new EventDispatcher();
 *
 * // Add listener
 * $dispatcher->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
 *     $data = $event->getData();
 *     // Process data
 * });
 *
 * // Add subscriber
 * $dispatcher->addSubscriber(new MyFormSubscriber());
 *
 * // Dispatch event
 * $event = new FormEvent($builder, $data);
 * $dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);
 * ```
 */
class EventDispatcher
{
    /**
     * @var array<string, array<callable>> Event listeners
     */
    private array $listeners = [];

    /**
     * @var array<EventSubscriberInterface> Event subscribers
     */
    private array $subscribers = [];

    /**
     * Add an event listener
     *
     * @param string $eventName Event name (use FormEvents constants)
     * @param callable $listener Listener callable
     * @param int $priority Priority (higher = earlier execution)
     */
    public function addEventListener(string $eventName, callable $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority
        ];

        // Sort by priority (highest first)
        usort($this->listeners[$eventName], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Remove an event listener
     *
     * @param string $eventName Event name
     * @param callable $listener Listener to remove
     */
    public function removeEventListener(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        $this->listeners[$eventName] = array_filter(
            $this->listeners[$eventName],
            fn($item) => $item['listener'] !== $listener
        );
    }

    /**
     * Add an event subscriber
     *
     * Subscribers can listen to multiple events
     *
     * @param EventSubscriberInterface $subscriber Event subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->subscribers[] = $subscriber;

        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                // Format: 'eventName' => 'methodName'
                $this->addEventListener($eventName, [$subscriber, $params]);
            } elseif (is_array($params) && isset($params[0])) {
                // Format: 'eventName' => ['methodName', priority]
                $method = $params[0];
                $priority = $params[1] ?? 0;
                $this->addEventListener($eventName, [$subscriber, $method], $priority);
            }
        }
    }

    /**
     * Remove an event subscriber
     *
     * @param EventSubscriberInterface $subscriber Subscriber to remove
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $key = array_search($subscriber, $this->subscribers, true);
        if ($key !== false) {
            unset($this->subscribers[$key]);
        }

        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->removeEventListener($eventName, [$subscriber, $params]);
            } elseif (is_array($params) && isset($params[0])) {
                $this->removeEventListener($eventName, [$subscriber, $params[0]]);
            }
        }
    }

    /**
     * Dispatch an event
     *
     * @param string $eventName Event name
     * @param FormEvent $event Event instance
     * @return FormEvent The event (possibly modified by listeners)
     */
    public function dispatch(string $eventName, FormEvent $event): FormEvent
    {
        if (!isset($this->listeners[$eventName])) {
            return $event;
        }

        foreach ($this->listeners[$eventName] as $listenerData) {
            if ($event->isPropagationStopped()) {
                break;
            }

            call_user_func($listenerData['listener'], $event);
        }

        return $event;
    }

    /**
     * Check if event has listeners
     *
     * @param string $eventName Event name
     * @return bool True if event has listeners
     */
    public function hasListeners(string $eventName): bool
    {
        return isset($this->listeners[$eventName]) && !empty($this->listeners[$eventName]);
    }

    /**
     * Get all listeners for an event
     *
     * @param string $eventName Event name
     * @return array<callable> Array of listeners
     */
    public function getListeners(string $eventName): array
    {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        return array_column($this->listeners[$eventName], 'listener');
    }

    /**
     * Get all event names that have listeners
     *
     * @return array<string> Array of event names
     */
    public function getEventNames(): array
    {
        return array_keys($this->listeners);
    }

    /**
     * Remove all listeners
     */
    public function removeAllListeners(): void
    {
        $this->listeners = [];
        $this->subscribers = [];
    }
}
