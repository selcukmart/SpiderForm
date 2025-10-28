<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use FormGenerator\V2\Builder\{FormBuilder, InputBuilder};
use FormGenerator\V2\Contracts\InputType;
use FormGenerator\V2\Event\{FieldEvent, FieldEvents};
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * Tests for Field Event System
 *
 * @author selcukmart
 * @since 2.3.0
 */
class FieldEventTest extends TestCase
{
    private FormBuilder $form;
    private TwigRenderer $renderer;
    private Bootstrap5Theme $theme;

    protected function setUp(): void
    {
        $this->renderer = new TwigRenderer(__DIR__ . '/../../../src/V2/Theme/templates');
        $this->theme = new Bootstrap5Theme();

        $this->form = FormBuilder::create('test_form')
            ->setRenderer($this->renderer)
            ->setTheme($this->theme);
    }

    public function testFieldEventListenerIsAttached(): void
    {
        $called = false;

        $this->form->addText('test_field', 'Test Field')
            ->onShow(function(FieldEvent $event) use (&$called) {
                $called = true;
            })
            ->add();

        $field = $this->form->getInputBuilder('test_field');
        $this->assertNotNull($field);

        // Dispatch show event
        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_SHOW);

        $this->assertTrue($called, 'Field event listener should be called');
    }

    public function testFieldEventCanModifyFieldProperties(): void
    {
        $this->form->addText('company_name', 'Company Name')
            ->onShow(function(FieldEvent $event) {
                $event->getField()->required(true);
            })
            ->add();

        $field = $this->form->getInputBuilder('company_name');

        // Initially not required
        $config = $field->toArray();
        $this->assertFalse($config['required']);

        // Dispatch show event
        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_SHOW);

        // Now should be required
        $config = $field->toArray();
        $this->assertTrue($config['required']);
    }

    public function testServerSideDependencyEvaluation(): void
    {
        $this->form
            ->enableServerSideDependencyEvaluation()
            ->setData([
                'user_type' => 'business',
            ]);

        $this->form->addSelect('user_type', 'User Type')
            ->options(['personal' => 'Personal', 'business' => 'Business'])
            ->isDependency()
            ->add();

        $this->form->addText('company_name', 'Company Name')
            ->dependsOn('user_type', 'business')
            ->add();

        $field = $this->form->getInputBuilder('company_name');
        $isVisible = $this->form->evaluateFieldDependencies($field);

        $this->assertTrue($isVisible, 'Field should be visible when dependency is met');
    }

    public function testServerSideDependencyNotMet(): void
    {
        $this->form
            ->enableServerSideDependencyEvaluation()
            ->setData([
                'user_type' => 'personal', // Different value
            ]);

        $this->form->addSelect('user_type', 'User Type')
            ->options(['personal' => 'Personal', 'business' => 'Business'])
            ->isDependency()
            ->add();

        $this->form->addText('company_name', 'Company Name')
            ->dependsOn('user_type', 'business') // Requires 'business'
            ->add();

        $field = $this->form->getInputBuilder('company_name');
        $isVisible = $this->form->evaluateFieldDependencies($field);

        $this->assertFalse($isVisible, 'Field should be hidden when dependency is not met');
    }

    public function testFieldEventContext(): void
    {
        $contextData = null;

        $this->form->addText('test_field', 'Test Field')
            ->onShow(function(FieldEvent $event) use (&$contextData) {
                $contextData = $event->getContext();
            })
            ->add();

        $field = $this->form->getInputBuilder('test_field');

        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_SHOW, [
            'custom_key' => 'custom_value',
            'visible' => true,
        ]);

        $this->assertNotNull($contextData);
        $this->assertArrayHasKey('custom_key', $contextData);
        $this->assertEquals('custom_value', $contextData['custom_key']);
    }

    public function testMultipleFieldEvents(): void
    {
        $events = [];

        $this->form->addText('test_field', 'Test Field')
            ->onShow(function(FieldEvent $event) use (&$events) {
                $events[] = 'show';
            })
            ->onHide(function(FieldEvent $event) use (&$events) {
                $events[] = 'hide';
            })
            ->onValueChange(function(FieldEvent $event) use (&$events) {
                $events[] = 'value_change';
            })
            ->add();

        $field = $this->form->getInputBuilder('test_field');

        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_SHOW);
        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_VALUE_CHANGE);
        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_HIDE);

        $this->assertCount(3, $events);
        $this->assertEquals(['show', 'value_change', 'hide'], $events);
    }

    public function testDependencyCheckEvent(): void
    {
        $this->form->setData([
            'country' => 'US',
            'role' => 'admin',
        ]);

        $this->form->addSelect('country', 'Country')
            ->options(['US' => 'USA', 'UK' => 'UK'])
            ->isDependency()
            ->add();

        $this->form->addHidden('role', 'admin')->add();

        $this->form->addText('state', 'State')
            ->dependsOn('country', 'US')
            ->onDependencyCheck(function(FieldEvent $event) {
                // Custom logic: only show for US and admin
                $country = $event->getFieldValue('country');
                $role = $event->getFieldValue('role');

                $visible = ($country === 'US' && $role === 'admin');
                $event->setVisible($visible);
            })
            ->add();

        $field = $this->form->getInputBuilder('state');
        $isVisible = $this->form->evaluateFieldDependencies($field);

        $this->assertTrue($isVisible, 'Custom dependency check should determine visibility');
    }

    public function testDependencyCheckEventCanOverride(): void
    {
        $this->form->setData([
            'country' => 'US', // Dependency would normally be met
        ]);

        $this->form->addSelect('country', 'Country')
            ->options(['US' => 'USA'])
            ->isDependency()
            ->add();

        $this->form->addText('state', 'State')
            ->dependsOn('country', 'US')
            ->onDependencyCheck(function(FieldEvent $event) {
                // Override: always hide regardless of dependency
                $event->setVisible(false);
            })
            ->add();

        $field = $this->form->getInputBuilder('state');
        $isVisible = $this->form->evaluateFieldDependencies($field);

        $this->assertFalse($isVisible, 'Dependency check event should override normal dependency logic');
    }

    public function testEventPropagationStopping(): void
    {
        $secondListenerCalled = false;

        $this->form->addText('test_field', 'Test Field')
            ->addEventListener(FieldEvents::FIELD_SHOW, function(FieldEvent $event) {
                $event->stopPropagation();
            }, 10) // Higher priority
            ->addEventListener(FieldEvents::FIELD_SHOW, function(FieldEvent $event) use (&$secondListenerCalled) {
                $secondListenerCalled = true;
            }, 0) // Lower priority
            ->add();

        $field = $this->form->getInputBuilder('test_field');
        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_SHOW);

        $this->assertFalse($secondListenerCalled, 'Second listener should not be called after stopPropagation');
    }

    public function testTriggerFieldValueChange(): void
    {
        $valueChangeTriggered = false;

        $this->form->addSelect('category', 'Category')
            ->options(['electronics' => 'Electronics'])
            ->isDependency()
            ->onValueChange(function(FieldEvent $event) use (&$valueChangeTriggered) {
                $valueChangeTriggered = true;
            })
            ->add();

        $this->form->addText('subcategory', 'Subcategory')
            ->dependsOn('category', 'electronics')
            ->add();

        // Trigger value change programmatically
        $this->form->triggerFieldValueChange('category', 'electronics', null);

        $this->assertTrue($valueChangeTriggered, 'Value change event should be triggered');
    }

    public function testFieldEventAccessesFormData(): void
    {
        $this->form->setData([
            'user_type' => 'business',
            'company_name' => 'Acme Corp',
        ]);

        $formDataFromEvent = null;

        $this->form->addText('test_field', 'Test Field')
            ->onPreRender(function(FieldEvent $event) use (&$formDataFromEvent) {
                $formDataFromEvent = $event->getFormData();
            })
            ->add();

        $field = $this->form->getInputBuilder('test_field');
        $this->form->dispatchFieldEvent($field, FieldEvents::FIELD_PRE_RENDER);

        $this->assertNotNull($formDataFromEvent);
        $this->assertEquals('business', $formDataFromEvent['user_type']);
        $this->assertEquals('Acme Corp', $formDataFromEvent['company_name']);
    }

    public function testGetInputBuilderReturnsCorrectField(): void
    {
        $this->form->addText('field1', 'Field 1')->add();
        $this->form->addText('field2', 'Field 2')->add();

        $field1 = $this->form->getInputBuilder('field1');
        $field2 = $this->form->getInputBuilder('field2');

        $this->assertNotNull($field1);
        $this->assertNotNull($field2);
        $this->assertEquals('field1', $field1->getName());
        $this->assertEquals('field2', $field2->getName());
    }

    public function testGetInputBuilderReturnsNullForNonExistentField(): void
    {
        $field = $this->form->getInputBuilder('non_existent');
        $this->assertNull($field);
    }
}
