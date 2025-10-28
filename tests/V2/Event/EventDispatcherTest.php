<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Event;

use FormGenerator\V2\Event\EventDispatcher;
use FormGenerator\V2\Event\EventSubscriberInterface;
use FormGenerator\V2\Event\FormEvent;
use FormGenerator\V2\Event\FormEvents;
use FormGenerator\V2\Form\Form;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for EventDispatcher
 *
 * @covers \FormGenerator\V2\Event\EventDispatcher
 */
class EventDispatcherTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testAddEventListenerRegistersListener(): void
    {
        $listener = fn() => null;

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);

        $this->assertTrue($this->dispatcher->hasListeners(FormEvents::PRE_SUBMIT));
    }

    public function testHasListenersReturnsFalseForUnregisteredEvent(): void
    {
        $this->assertFalse($this->dispatcher->hasListeners('nonexistent.event'));
    }

    public function testDispatchCallsRegisteredListeners(): void
    {
        $called = false;
        $listener = function(FormEvent $event) use (&$called) {
            $called = true;
        };

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);
        $event = new FormEvent(new Form('test'));
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        $this->assertTrue($called);
    }

    public function testDispatchPassesEventToListener(): void
    {
        $receivedEvent = null;
        $listener = function(FormEvent $event) use (&$receivedEvent) {
            $receivedEvent = $event;
        };

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);
        $originalEvent = new FormEvent(new Form('test'), ['data' => 'value']);
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $originalEvent);

        $this->assertSame($originalEvent, $receivedEvent);
    }

    public function testDispatchReturnsEvent(): void
    {
        $listener = fn() => null;

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);
        $event = new FormEvent(new Form('test'));
        $result = $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        $this->assertSame($event, $result);
    }

    public function testDispatchReturnsEventWhenNoListeners(): void
    {
        $event = new FormEvent(new Form('test'));
        $result = $this->dispatcher->dispatch('no.listeners', $event);

        $this->assertSame($event, $result);
    }

    public function testMultipleListenersAreCalled(): void
    {
        $count = 0;
        $listener1 = function() use (&$count) { $count++; };
        $listener2 = function() use (&$count) { $count++; };
        $listener3 = function() use (&$count) { $count++; };

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener1);
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener2);
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener3);

        $event = new FormEvent(new Form('test'));
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        $this->assertEquals(3, $count);
    }

    public function testListenerPriorityOrdering(): void
    {
        $order = [];
        $listener1 = function() use (&$order) { $order[] = 1; };
        $listener2 = function() use (&$order) { $order[] = 2; };
        $listener3 = function() use (&$order) { $order[] = 3; };

        // Add in mixed priority order
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener1, 0);
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener2, 10); // Highest priority
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener3, 5);

        $event = new FormEvent(new Form('test'));
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        // Should execute in priority order: 2 (10), 3 (5), 1 (0)
        $this->assertEquals([2, 3, 1], $order);
    }

    public function testDefaultPriorityIsZero(): void
    {
        $order = [];
        $listener1 = function() use (&$order) { $order[] = 1; };
        $listener2 = function() use (&$order) { $order[] = 2; };

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener1); // Default priority
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener2, 5);

        $event = new FormEvent(new Form('test'));
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        // listener2 should execute first (priority 5 > 0)
        $this->assertEquals([2, 1], $order);
    }

    public function testRemoveEventListenerRemovesListener(): void
    {
        $listener = fn() => null;

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);
        $this->assertTrue($this->dispatcher->hasListeners(FormEvents::PRE_SUBMIT));

        $this->dispatcher->removeEventListener(FormEvents::PRE_SUBMIT, $listener);

        $this->assertFalse($this->dispatcher->hasListeners(FormEvents::PRE_SUBMIT));
    }

    public function testRemoveEventListenerOnlyRemovesSpecifiedListener(): void
    {
        $listener1 = fn() => null;
        $listener2 = fn() => null;

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener1);
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener2);

        $this->dispatcher->removeEventListener(FormEvents::PRE_SUBMIT, $listener1);

        $listeners = $this->dispatcher->getListeners(FormEvents::PRE_SUBMIT);
        $this->assertCount(1, $listeners);
    }

    public function testGetListenersReturnsAllListeners(): void
    {
        $listener1 = fn() => null;
        $listener2 = fn() => null;

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener1);
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener2);

        $listeners = $this->dispatcher->getListeners(FormEvents::PRE_SUBMIT);

        $this->assertCount(2, $listeners);
        $this->assertContains($listener1, $listeners);
        $this->assertContains($listener2, $listeners);
    }

    public function testGetListenersReturnsEmptyArrayForUnregisteredEvent(): void
    {
        $listeners = $this->dispatcher->getListeners('nonexistent');

        $this->assertIsArray($listeners);
        $this->assertEmpty($listeners);
    }

    public function testGetEventNamesReturnsAllEventNames(): void
    {
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, fn() => null);
        $this->dispatcher->addEventListener(FormEvents::POST_SUBMIT, fn() => null);

        $names = $this->dispatcher->getEventNames();

        $this->assertCount(2, $names);
        $this->assertContains(FormEvents::PRE_SUBMIT, $names);
        $this->assertContains(FormEvents::POST_SUBMIT, $names);
    }

    public function testAddSubscriberRegistersAllSubscribedEvents(): void
    {
        $subscriber = new class implements EventSubscriberInterface {
            public function getSubscribedEvents(): array
            {
                return [
                    FormEvents::PRE_SUBMIT => 'onPreSubmit',
                    FormEvents::POST_SUBMIT => 'onPostSubmit',
                ];
            }

            public function onPreSubmit(FormEvent $event): void {}
            public function onPostSubmit(FormEvent $event): void {}
        };

        $this->dispatcher->addSubscriber($subscriber);

        $this->assertTrue($this->dispatcher->hasListeners(FormEvents::PRE_SUBMIT));
        $this->assertTrue($this->dispatcher->hasListeners(FormEvents::POST_SUBMIT));
    }

    public function testAddSubscriberWithPriorities(): void
    {
        $order = [];

        $subscriber = new class($order) implements EventSubscriberInterface {
            private array &$order;

            public function __construct(array &$order)
            {
                $this->order = &$order;
            }

            public function getSubscribedEvents(): array
            {
                return [
                    FormEvents::PRE_SUBMIT => ['onPreSubmit', 10],
                ];
            }

            public function onPreSubmit(FormEvent $event): void
            {
                $this->order[] = 'subscriber';
            }
        };

        $listener = function() use (&$order) { $order[] = 'listener'; };

        $this->dispatcher->addSubscriber($subscriber);
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener, 0);

        $event = new FormEvent(new Form('test'));
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        $this->assertEquals(['subscriber', 'listener'], $order);
    }

    public function testRemoveSubscriberRemovesAllSubscribedEvents(): void
    {
        $subscriber = new class implements EventSubscriberInterface {
            public function getSubscribedEvents(): array
            {
                return [
                    FormEvents::PRE_SUBMIT => 'onPreSubmit',
                    FormEvents::POST_SUBMIT => 'onPostSubmit',
                ];
            }

            public function onPreSubmit(FormEvent $event): void {}
            public function onPostSubmit(FormEvent $event): void {}
        };

        $this->dispatcher->addSubscriber($subscriber);
        $this->dispatcher->removeSubscriber($subscriber);

        $this->assertFalse($this->dispatcher->hasListeners(FormEvents::PRE_SUBMIT));
        $this->assertFalse($this->dispatcher->hasListeners(FormEvents::POST_SUBMIT));
    }

    public function testStopPropagationPreventsSubsequentListeners(): void
    {
        $count = 0;

        $listener1 = function(FormEvent $event) use (&$count) {
            $count++;
            $event->stopPropagation();
        };

        $listener2 = function() use (&$count) {
            $count++; // Should not be called
        };

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener1, 10);
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener2, 0);

        $event = new FormEvent(new Form('test'));
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        $this->assertEquals(1, $count);
    }

    public function testRemoveAllListenersClearsAll(): void
    {
        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, fn() => null);
        $this->dispatcher->addEventListener(FormEvents::POST_SUBMIT, fn() => null);

        $this->dispatcher->removeAllListeners();

        $this->assertEmpty($this->dispatcher->getEventNames());
        $this->assertFalse($this->dispatcher->hasListeners(FormEvents::PRE_SUBMIT));
        $this->assertFalse($this->dispatcher->hasListeners(FormEvents::POST_SUBMIT));
    }

    public function testListenerCanModifyEventData(): void
    {
        $listener = function(FormEvent $event) {
            $data = $event->getData();
            $data['modified'] = true;
            $event->setData($data);
        };

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);

        $event = new FormEvent(new Form('test'), ['original' => 'data']);
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);

        $data = $event->getData();
        $this->assertArrayHasKey('modified', $data);
        $this->assertTrue($data['modified']);
    }

    public function testMultipleEventsForSameListener(): void
    {
        $count = 0;
        $listener = function() use (&$count) { $count++; };

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);
        $this->dispatcher->addEventListener(FormEvents::POST_SUBMIT, $listener);

        $event = new FormEvent(new Form('test'));
        $this->dispatcher->dispatch(FormEvents::PRE_SUBMIT, $event);
        $this->dispatcher->dispatch(FormEvents::POST_SUBMIT, $event);

        $this->assertEquals(2, $count);
    }

    public function testRemoveEventListenerDoesNotAffectOtherEvents(): void
    {
        $listener = fn() => null;

        $this->dispatcher->addEventListener(FormEvents::PRE_SUBMIT, $listener);
        $this->dispatcher->addEventListener(FormEvents::POST_SUBMIT, $listener);

        $this->dispatcher->removeEventListener(FormEvents::PRE_SUBMIT, $listener);

        $this->assertFalse($this->dispatcher->hasListeners(FormEvents::PRE_SUBMIT));
        $this->assertTrue($this->dispatcher->hasListeners(FormEvents::POST_SUBMIT));
    }
}
