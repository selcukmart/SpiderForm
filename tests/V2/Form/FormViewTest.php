<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Form;

use FormGenerator\V2\Form\FormView;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FormView class
 *
 * @covers \FormGenerator\V2\Form\FormView
 */
class FormViewTest extends TestCase
{
    private FormView $view;

    protected function setUp(): void
    {
        $this->view = new FormView();
    }

    public function testConstructorInitializesProperties(): void
    {
        $view = new FormView();

        $this->assertIsArray($view->vars);
        $this->assertIsArray($view->children);
        $this->assertNull($view->parent);
    }

    public function testConstructorAcceptsParent(): void
    {
        $parent = new FormView();
        $child = new FormView($parent);

        $this->assertSame($parent, $child->parent);
    }

    public function testSetVarsStoresVariables(): void
    {
        $vars = ['name' => 'test', 'value' => 'hello'];

        $this->view->setVars($vars);

        $this->assertEquals($vars, $this->view->vars);
    }

    public function testAddChildAddsChildView(): void
    {
        $child = new FormView();

        $this->view->addChild('email', $child);

        $this->assertArrayHasKey('email', $this->view->children);
        $this->assertSame($child, $this->view->children['email']);
    }

    public function testAddChildSetsParentOnChild(): void
    {
        $child = new FormView();

        $this->view->addChild('email', $child);

        $this->assertSame($this->view, $child->parent);
    }

    public function testAddChildReturnsThis(): void
    {
        $child = new FormView();

        $result = $this->view->addChild('email', $child);

        $this->assertSame($this->view, $result);
    }

    public function testGetChildReturnsChild(): void
    {
        $child = new FormView();
        $this->view->addChild('email', $child);

        $retrieved = $this->view->getChild('email');

        $this->assertSame($child, $retrieved);
    }

    public function testGetChildReturnsNullForNonexistent(): void
    {
        $child = $this->view->getChild('nonexistent');

        $this->assertNull($child);
    }

    public function testHasChildReturnsTrueForExisting(): void
    {
        $child = new FormView();
        $this->view->addChild('email', $child);

        $this->assertTrue($this->view->hasChild('email'));
        $this->assertFalse($this->view->hasChild('nonexistent'));
    }

    public function testRemoveChildRemovesChild(): void
    {
        $child = new FormView();
        $this->view->addChild('email', $child);

        $this->view->removeChild('email');

        $this->assertFalse($this->view->hasChild('email'));
    }

    public function testCountReturnsNumberOfChildren(): void
    {
        $this->view->addChild('name', new FormView());
        $this->view->addChild('email', new FormView());

        $this->assertCount(2, $this->view);
    }

    public function testArrayAccessGet(): void
    {
        $child = new FormView();
        $this->view->addChild('email', $child);

        $this->assertSame($child, $this->view['email']);
    }

    public function testArrayAccessSet(): void
    {
        $child = new FormView();

        $this->view['email'] = $child;

        $this->assertSame($child, $this->view->children['email']);
    }

    public function testArrayAccessExists(): void
    {
        $this->view->addChild('email', new FormView());

        $this->assertTrue(isset($this->view['email']));
        $this->assertFalse(isset($this->view['nonexistent']));
    }

    public function testArrayAccessUnset(): void
    {
        $this->view->addChild('email', new FormView());

        unset($this->view['email']);

        $this->assertFalse(isset($this->view['email']));
    }

    public function testIteratorAllowsIteration(): void
    {
        $child1 = new FormView();
        $child2 = new FormView();

        $this->view->addChild('name', $child1);
        $this->view->addChild('email', $child2);

        $iterated = [];
        foreach ($this->view as $name => $child) {
            $iterated[$name] = $child;
        }

        $this->assertCount(2, $iterated);
        $this->assertSame($child1, $iterated['name']);
        $this->assertSame($child2, $iterated['email']);
    }

    public function testNestedViewStructure(): void
    {
        $addressView = new FormView();
        $addressView->addChild('street', new FormView());
        $addressView->addChild('city', new FormView());

        $this->view->addChild('name', new FormView());
        $this->view->addChild('address', $addressView);

        $this->assertCount(2, $this->view);
        $this->assertCount(2, $this->view['address']);
        $this->assertTrue($this->view['address']->hasChild('street'));
    }

    public function testSetVarsMergesWithExisting(): void
    {
        $this->view->vars = ['name' => 'old', 'label' => 'Old Label'];

        $this->view->setVars(['name' => 'new', 'required' => true]);

        $this->assertEquals('new', $this->view->vars['name']);
        $this->assertTrue($this->view->vars['required']);
    }
}
