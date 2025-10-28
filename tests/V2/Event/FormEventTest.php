<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Event;

use FormGenerator\V2\Event\FormEvent;
use FormGenerator\V2\Form\Form;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FormEvent
 *
 * @covers \FormGenerator\V2\Event\FormEvent
 */
class FormEventTest extends TestCase
{
    public function testConstructorStoresForm(): void
    {
        $form = new Form('test');
        $event = new FormEvent($form);

        $this->assertSame($form, $event->getForm());
    }

    public function testConstructorStoresData(): void
    {
        $form = new Form('test');
        $data = ['name' => 'John', 'email' => 'john@example.com'];
        $event = new FormEvent($form, $data);

        $this->assertEquals($data, $event->getData());
    }

    public function testConstructorStoresContext(): void
    {
        $form = new Form('test');
        $context = ['request_id' => '12345', 'user_ip' => '192.168.1.1'];
        $event = new FormEvent($form, null, $context);

        $this->assertEquals($context, $event->getContext());
    }

    public function testGetFormReturnsForm(): void
    {
        $form = new Form('test');
        $event = new FormEvent($form);

        $this->assertInstanceOf(Form::class, $event->getForm());
    }

    public function testGetDataReturnsData(): void
    {
        $data = ['field' => 'value'];
        $event = new FormEvent(new Form('test'), $data);

        $this->assertEquals($data, $event->getData());
    }

    public function testSetDataChangesData(): void
    {
        $event = new FormEvent(new Form('test'), ['old' => 'data']);

        $newData = ['new' => 'data'];
        $event->setData($newData);

        $this->assertEquals($newData, $event->getData());
    }

    public function testGetDataReturnsNullByDefault(): void
    {
        $event = new FormEvent(new Form('test'));

        $this->assertNull($event->getData());
    }

    public function testGetContextWithoutKeyReturnsAllContext(): void
    {
        $context = ['key1' => 'value1', 'key2' => 'value2'];
        $event = new FormEvent(new Form('test'), null, $context);

        $this->assertEquals($context, $event->getContext());
    }

    public function testGetContextWithKeyReturnsValue(): void
    {
        $context = ['request_id' => '12345', 'user_ip' => '192.168.1.1'];
        $event = new FormEvent(new Form('test'), null, $context);

        $this->assertEquals('12345', $event->getContext('request_id'));
        $this->assertEquals('192.168.1.1', $event->getContext('user_ip'));
    }

    public function testGetContextWithNonexistentKeyReturnsDefault(): void
    {
        $event = new FormEvent(new Form('test'));

        $this->assertNull($event->getContext('nonexistent'));
        $this->assertEquals('default', $event->getContext('nonexistent', 'default'));
    }

    public function testSetContextAddsKeyValue(): void
    {
        $event = new FormEvent(new Form('test'));

        $event->setContext('key', 'value');

        $this->assertEquals('value', $event->getContext('key'));
    }

    public function testSetContextOverwritesExisting(): void
    {
        $event = new FormEvent(new Form('test'), null, ['key' => 'old']);

        $event->setContext('key', 'new');

        $this->assertEquals('new', $event->getContext('key'));
    }

    public function testHasContextReturnsTrueForExistingKey(): void
    {
        $event = new FormEvent(new Form('test'), null, ['key' => 'value']);

        $this->assertTrue($event->hasContext('key'));
        $this->assertFalse($event->hasContext('nonexistent'));
    }

    public function testHasContextReturnsTrueForNullValue(): void
    {
        $event = new FormEvent(new Form('test'), null, ['key' => null]);

        $this->assertTrue($event->hasContext('key'));
    }

    public function testStopPropagationSetsFlag(): void
    {
        $event = new FormEvent(new Form('test'));

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        $this->assertTrue($event->isPropagationStopped());
    }

    public function testIsPropagationStoppedDefaultsFalse(): void
    {
        $event = new FormEvent(new Form('test'));

        $this->assertFalse($event->isPropagationStopped());
    }

    public function testDataCanBeArray(): void
    {
        $data = ['field1' => 'value1', 'field2' => 'value2'];
        $event = new FormEvent(new Form('test'), $data);

        $this->assertEquals($data, $event->getData());
    }

    public function testDataCanBeString(): void
    {
        $data = 'simple string data';
        $event = new FormEvent(new Form('test'), $data);

        $this->assertEquals($data, $event->getData());
    }

    public function testDataCanBeObject(): void
    {
        $data = (object) ['prop' => 'value'];
        $event = new FormEvent(new Form('test'), $data);

        $this->assertEquals($data, $event->getData());
    }

    public function testDataCanBeNull(): void
    {
        $event = new FormEvent(new Form('test'), null);

        $this->assertNull($event->getData());
    }

    public function testSetDataCanSetNull(): void
    {
        $event = new FormEvent(new Form('test'), ['data' => 'value']);

        $event->setData(null);

        $this->assertNull($event->getData());
    }

    public function testContextCanBeEmpty(): void
    {
        $event = new FormEvent(new Form('test'), null, []);

        $this->assertIsArray($event->getContext());
        $this->assertEmpty($event->getContext());
    }

    public function testMultipleContextKeys(): void
    {
        $event = new FormEvent(new Form('test'));

        $event->setContext('key1', 'value1');
        $event->setContext('key2', 'value2');
        $event->setContext('key3', 'value3');

        $context = $event->getContext();

        $this->assertCount(3, $context);
        $this->assertEquals('value1', $context['key1']);
        $this->assertEquals('value2', $context['key2']);
        $this->assertEquals('value3', $context['key3']);
    }

    public function testComplexDataModificationScenario(): void
    {
        $initialData = [
            'username' => 'john_doe',
            'email' => 'john@example.com',
        ];

        $event = new FormEvent(new Form('test'), $initialData);

        // Simulate listener modifying data
        $data = $event->getData();
        $data['email'] = strtolower($data['email']);
        $data['username'] = trim($data['username']);
        $data['timestamp'] = time();
        $event->setData($data);

        $modifiedData = $event->getData();

        $this->assertEquals('john@example.com', $modifiedData['email']);
        $this->assertEquals('john_doe', $modifiedData['username']);
        $this->assertArrayHasKey('timestamp', $modifiedData);
    }

    public function testContextUsedForMetadata(): void
    {
        $event = new FormEvent(new Form('test'));

        // Simulate adding metadata via context
        $event->setContext('submitted_at', '2024-01-15 10:30:00');
        $event->setContext('ip_address', '192.168.1.100');
        $event->setContext('user_agent', 'Mozilla/5.0');
        $event->setContext('form_version', '2.8.0');

        $this->assertEquals('2024-01-15 10:30:00', $event->getContext('submitted_at'));
        $this->assertEquals('192.168.1.100', $event->getContext('ip_address'));
        $this->assertEquals('Mozilla/5.0', $event->getContext('user_agent'));
        $this->assertEquals('2.8.0', $event->getContext('form_version'));
    }

    public function testEventImmutableByDefault(): void
    {
        $form = new Form('test');
        $data = ['field' => 'value'];
        $context = ['key' => 'value'];

        $event = new FormEvent($form, $data, $context);

        // Get references
        $form1 = $event->getForm();
        $data1 = $event->getData();
        $context1 = $event->getContext();

        // Get again
        $form2 = $event->getForm();
        $data2 = $event->getData();
        $context2 = $event->getContext();

        // Should be same references
        $this->assertSame($form1, $form2);
        $this->assertEquals($data1, $data2);
        $this->assertEquals($context1, $context2);
    }

    public function testStopPropagationCannotBeReversed(): void
    {
        $event = new FormEvent(new Form('test'));

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());

        // No way to un-stop propagation
        // Once stopped, it stays stopped
        $this->assertTrue($event->isPropagationStopped());
    }
}
