<?php

declare(strict_types=1);

namespace SpiderForm\Tests\V2\Form;

use SpiderForm\V2\Form\{Form, FormConfig, FormState};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Unit tests for Form class
 */
#[CoversClass(Form::class)]
class FormTest extends TestCase
{
    private Form $form;

    protected function setUp(): void
    {
        $this->form = new Form('test_form');
    }

    public function testConstructorCreatesFormWithName(): void
    {
        $form = new Form('user_form');

        $this->assertEquals('user_form', $form->getName());
    }

    public function testConstructorSetsStateToReady(): void
    {
        $form = new Form('test');

        $this->assertEquals(FormState::READY, $form->getState());
    }

    public function testSetDataStoresData(): void
    {
        $data = ['name' => 'John', 'email' => 'john@example.com'];

        $this->form->setData($data);

        $this->assertEquals($data, $this->form->getData());
    }

    public function testAddFieldAddsChildForm(): void
    {
        $this->form->add('name', 'text', ['label' => 'Name']);

        $this->assertTrue($this->form->has('name'));
    }

    public function testHasReturnsTrueForExistingField(): void
    {
        $this->form->add('email', 'email');

        $this->assertTrue($this->form->has('email'));
        $this->assertFalse($this->form->has('nonexistent'));
    }

    public function testGetReturnsChildForm(): void
    {
        $this->form->add('name', 'text');

        $child = $this->form->get('name');

        $this->assertInstanceOf(Form::class, $child);
        $this->assertEquals('name', $child->getName());
    }

    public function testGetThrowsExceptionForNonexistentField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Child "nonexistent" does not exist');

        $this->form->get('nonexistent');
    }

    public function testRemoveRemovesChild(): void
    {
        $this->form->add('email', 'email');
        $this->assertTrue($this->form->has('email'));

        $this->form->remove('email');

        $this->assertFalse($this->form->has('email'));
    }

    public function testAllReturnsAllChildren(): void
    {
        $this->form->add('name', 'text');
        $this->form->add('email', 'email');

        $children = $this->form->all();

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('name', $children);
        $this->assertArrayHasKey('email', $children);
    }

    public function testSubmitSetsStateToSubmitted(): void
    {
        $this->form->submit(['name' => 'John']);

        $this->assertTrue($this->form->isSubmitted());
        $this->assertEquals(FormState::SUBMITTED, $this->form->getState());
    }

    public function testSubmitValidatesData(): void
    {
        $this->form->add('email', 'email', ['required' => true]);

        $this->form->submit([]); // Missing required field

        $this->assertTrue($this->form->isSubmitted());
    }

    public function testIsValidReturnsFalseWhenNotSubmitted(): void
    {
        $this->assertFalse($this->form->isValid());
    }

    public function testHandleRequestSubmitsFormWhenDataPresent(): void
    {
        $this->form->add('name', 'text');

        $this->form->handleRequest(['test_form' => ['name' => 'John']]);

        $this->assertTrue($this->form->isSubmitted());
    }

    public function testHandleRequestDoesNotSubmitWhenFormNameMissing(): void
    {
        $this->form->handleRequest(['other_form' => ['name' => 'John']]);

        $this->assertFalse($this->form->isSubmitted());
    }

    public function testIsRootReturnsTrueForFormsWithoutParent(): void
    {
        $this->assertTrue($this->form->isRoot());
    }

    public function testIsRootReturnsFalseForChildForms(): void
    {
        $child = new Form('child');
        $child->setParent($this->form);

        $this->assertFalse($child->isRoot());
    }

    public function testGetRootReturnsTopLevelForm(): void
    {
        $child = new Form('child');
        $child->setParent($this->form);

        $grandchild = new Form('grandchild');
        $grandchild->setParent($child);

        $this->assertSame($this->form, $grandchild->getRoot());
    }

    public function testGetParentReturnsParentForm(): void
    {
        $child = new Form('child');
        $child->setParent($this->form);

        $this->assertSame($this->form, $child->getParent());
    }

    public function testSetParentEstablishesBidirectionalRelationship(): void
    {
        $child = new Form('child');

        $child->setParent($this->form);

        $this->assertSame($this->form, $child->getParent());
    }

    public function testCreateViewReturnsFormView(): void
    {
        $this->form->add('name', 'text');

        $view = $this->form->createView();

        $this->assertInstanceOf(\SpiderForm\V2\Form\FormView::class, $view);
    }

    public function testCreateViewIncludesChildren(): void
    {
        $this->form->add('name', 'text');
        $this->form->add('email', 'email');

        $view = $this->form->createView();

        $this->assertCount(2, $view->children);
        $this->assertArrayHasKey('name', $view->children);
        $this->assertArrayHasKey('email', $view->children);
    }

    public function testGetErrorsReturnsEmptyArrayWhenNoErrors(): void
    {
        $errors = $this->form->getErrors();

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function testHasErrorsReturnsFalseWhenNoErrors(): void
    {
        $this->assertFalse($this->form->hasErrors());
    }

    public function testIsEmptyReturnsTrueForNewForm(): void
    {
        $this->assertTrue($this->form->isEmpty());
    }

    public function testIsEmptyReturnsFalseAfterSetData(): void
    {
        $this->form->setData(['name' => 'John']);

        $this->assertFalse($this->form->isEmpty());
    }

    public function testAddFormInstanceDirectly(): void
    {
        $childForm = new Form('child');

        $this->form->add('child', $childForm);

        $this->assertTrue($this->form->has('child'));
        $this->assertSame($childForm, $this->form->get('child'));
    }

    public function testNestedFormDataGathering(): void
    {
        $addressForm = new Form('address');
        $addressForm->add('street', 'text');
        $addressForm->add('city', 'text');

        $this->form->add('name', 'text');
        $this->form->add('address', $addressForm);

        $this->form->submit([
            'name' => 'John',
            'address' => [
                'street' => '123 Main St',
                'city' => 'New York'
            ]
        ]);

        $data = $this->form->getData();

        $this->assertEquals('John', $data['name']);
        $this->assertEquals('123 Main St', $data['address']['street']);
        $this->assertEquals('New York', $data['address']['city']);
    }

    public function testGetConfigReturnsFormConfig(): void
    {
        $config = $this->form->getConfig();

        $this->assertInstanceOf(\SpiderForm\V2\Form\FormConfigInterface::class, $config);
    }

    public function testGetMetadataReturnsEmptyArrayByDefault(): void
    {
        $metadata = $this->form->getMetadata();

        $this->assertIsArray($metadata);
    }

    public function testSetMetadataStoresMetadata(): void
    {
        $metadata = ['label' => 'Test', 'required' => true];

        $this->form->setMetadata($metadata);

        $this->assertEquals($metadata, $this->form->getMetadata());
    }

    public function testRenderIncludesCsrfTokenWhenCsrfProtectionEnabled(): void
    {
        // Create form with CSRF protection enabled
        $config = new FormConfig(
            name: 'test_form',
            type: 'form',
            compound: true,
            csrfProtection: true
        );

        $form = new Form('test_form', $config);

        // Create a mock renderer
        $renderer = $this->createMock(\SpiderForm\V2\Contracts\RendererInterface::class);

        // Create a mock theme
        $theme = $this->createMock(\SpiderForm\V2\Contracts\ThemeInterface::class);
        $theme->method('getFormTemplate')->willReturn('form.twig');

        // Configure renderer to capture the context
        $capturedContext = null;
        $renderer->method('render')
            ->willReturnCallback(function ($template, $context) use (&$capturedContext) {
                $capturedContext = $context;
                return '<form></form>';
            });

        // Set renderer and theme
        $form->setRenderer($renderer);
        $form->setTheme($theme);

        // Render the form
        $form->render();

        // Assert that csrf_token is included in the context
        $this->assertArrayHasKey('csrf_token', $capturedContext);
        $this->assertNotNull($capturedContext['csrf_token']);
        $this->assertIsString($capturedContext['csrf_token']);
    }

    public function testRenderIncludesNullCsrfTokenWhenCsrfProtectionDisabled(): void
    {
        // Create form with CSRF protection disabled
        $config = new FormConfig(
            name: 'test_form',
            type: 'form',
            compound: true,
            csrfProtection: false
        );

        $form = new Form('test_form', $config);

        // Create a mock renderer
        $renderer = $this->createMock(\SpiderForm\V2\Contracts\RendererInterface::class);

        // Create a mock theme
        $theme = $this->createMock(\SpiderForm\V2\Contracts\ThemeInterface::class);
        $theme->method('getFormTemplate')->willReturn('form.twig');

        // Configure renderer to capture the context
        $capturedContext = null;
        $renderer->method('render')
            ->willReturnCallback(function ($template, $context) use (&$capturedContext) {
                $capturedContext = $context;
                return '<form></form>';
            });

        // Set renderer and theme
        $form->setRenderer($renderer);
        $form->setTheme($theme);

        // Render the form
        $form->render();

        // Assert that csrf_token is null when CSRF protection is disabled
        $this->assertArrayHasKey('csrf_token', $capturedContext);
        $this->assertNull($capturedContext['csrf_token']);
    }
}
