<?php

declare(strict_types=1);

namespace SpiderForm\Tests\Unit\Builder;

use SpiderForm\Tests\TestCase;
use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Theme\Bootstrap3Theme;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test to debug infinite loop issue during form RENDERING
 */
#[CoversClass(FormBuilder::class)]
class InfiniteLoopRenderTest extends TestCase
{
    #[Test]
    public function it_should_test_tostring_conversion(): void
    {
        echo "\n=== Testing __toString() conversion (echo $form) ===\n";

        $form = FormBuilder::create('test-tostring')
            ->setAction('')
            ->setMethod('POST');

        // Don't set renderer/theme - this should trigger an error not infinite loop
        $form->addSection('Test Section');
        $form->addText('name', 'Name')->add();
        $form->addHidden('id', '1');
        $form->addSubmit('save', 'Submit');

        echo "Building form object...\n";
        $formObj = $form->buildForm();

        echo "Converting to string (this calls __toString)...\n";
        set_time_limit(5);
        $startTime = microtime(true);

        try {
            $str = (string) $formObj;
            $endTime = microtime(true);

            echo sprintf("Result: %s\n", $str);
            echo sprintf("Time: %.4f seconds\n", $endTime - $startTime);

            $this->assertIsString($str);
        } catch (\Exception $e) {
            echo "Expected error (no renderer set): " . $e->getMessage() . "\n";
            $this->assertTrue(true); // This is expected
        }
    }

    #[Test]
    public function it_should_test_createview_recursion(): void
    {
        echo "\n=== Testing createView() for recursion issues ===\n";

        $form = FormBuilder::create('test-createview')
            ->setAction('')
            ->setMethod('POST');

        $form->addSection('Section 1');
        $form->addText('field1', 'Field 1')->add();
        $form->addText('field2', 'Field 2')->add();

        $form->addSection('Section 2');
        $form->addRadio('radio1', 'Radio 1')
            ->options(['a' => 'A', 'b' => 'B'])
            ->add();

        $form->addHidden('hidden1', 'value');
        $form->addSubmit('save', 'Submit');

        echo "Building form object...\n";
        $formObj = $form->buildForm();

        echo "Creating view (this should not loop)...\n";
        set_time_limit(5);
        $startTime = microtime(true);

        $view = $formObj->createView();
        $endTime = microtime(true);

        echo sprintf("✓ View created in %.4f seconds\n", $endTime - $startTime);

        $this->assertNotNull($view);
        $this->assertLessThan(1, $endTime - $startTime, "createView took too long");
    }

    #[Test]
    public function it_should_test_nested_sections(): void
    {
        echo "\n=== Testing deeply nested sections ===\n";

        $form = FormBuilder::create('test-nested')
            ->setAction('')
            ->setMethod('POST');

        // Add 20 sections to test depth
        for ($i = 1; $i <= 20; $i++) {
            echo "Adding section $i...\n";
            $form->addSection("Section $i", "Description $i");
            $form->addText("field_$i", "Field $i")->add();
        }

        $form->addSubmit('save', 'Submit');

        echo "Building form object...\n";
        set_time_limit(10);
        $startTime = microtime(true);

        $formObj = $form->buildForm();

        echo "Creating view...\n";
        $view = $formObj->createView();

        $endTime = microtime(true);

        echo sprintf("✓ Form with 20 sections built in %.4f seconds\n", $endTime - $startTime);

        $this->assertNotNull($formObj);
        $this->assertNotNull($view);
        $this->assertLessThan(3, $endTime - $startTime, "Too slow, possible performance issue");
    }

    #[Test]
    public function it_should_test_getData_recursion(): void
    {
        echo "\n=== Testing getData() for recursion issues ===\n";

        $form = FormBuilder::create('test-getdata')
            ->setAction('')
            ->setMethod('POST');

        $form->addSection('Section 1');
        $form->addText('name', 'Name')->add();
        $form->addRadio('type', 'Type')
            ->options(['a' => 'A', 'b' => 'B'])
            ->add();

        $form->addHidden('id', '123');
        $form->addSubmit('save', 'Submit');

        echo "Building form object...\n";
        $formObj = $form->buildForm();

        echo "Setting data...\n";
        $formObj->setData([
            'name' => 'Test Name',
            'type' => 'a',
            'id' => '456'
        ]);

        echo "Getting data (checking for recursion)...\n";
        set_time_limit(5);
        $startTime = microtime(true);

        $data = $formObj->getData();
        $endTime = microtime(true);

        echo sprintf("✓ getData() completed in %.4f seconds\n", $endTime - $startTime);
        echo "Data: " . json_encode($data) . "\n";

        $this->assertIsArray($data);
        $this->assertLessThan(1, $endTime - $startTime, "getData took too long");
    }

    #[Test]
    public function it_should_test_submit_and_validate(): void
    {
        echo "\n=== Testing submit() and validate() for recursion ===\n";

        $form = FormBuilder::create('test-submit')
            ->setAction('')
            ->setMethod('POST');

        $form->addSection('User Info');
        $form->addText('username', 'Username')
            ->required()
            ->minLength(3)
            ->add();

        $form->addText('email', 'Email')
            ->required()
            ->email()
            ->add();

        $form->addHidden('user_id', '');
        $form->addSubmit('save', 'Submit');

        echo "Building form object...\n";
        $formObj = $form->buildForm();

        echo "Submitting data...\n";
        set_time_limit(5);
        $startTime = microtime(true);

        $formObj->submit([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'user_id' => '789'
        ]);

        echo "Validating...\n";
        $errors = $formObj->validate();

        $endTime = microtime(true);

        echo sprintf("✓ Submit and validate completed in %.4f seconds\n", $endTime - $startTime);
        echo "Errors: " . json_encode($errors) . "\n";
        echo "Is valid: " . ($formObj->isValid() ? 'Yes' : 'No') . "\n";

        $this->assertIsArray($errors);
        $this->assertLessThan(2, $endTime - $startTime, "Submit/validate took too long");
    }

    #[Test]
    public function it_should_check_circular_parent_child_references(): void
    {
        echo "\n=== Testing for circular parent-child references ===\n";

        $form = FormBuilder::create('test-circular')
            ->setAction('')
            ->setMethod('POST');

        $form->addText('field1', 'Field 1')->add();
        $formObj = $form->buildForm();

        echo "Checking parent-child relationships...\n";

        // Check root
        $this->assertTrue($formObj->isRoot());
        $this->assertNull($formObj->getParent());

        // Check children
        $children = $formObj->all();
        echo "Number of children: " . count($children) . "\n";

        foreach ($children as $name => $child) {
            echo "Checking child: $name\n";
            $parent = $child->getParent();
            $this->assertNotNull($parent);
            $this->assertEquals($formObj->getName(), $parent->getName());

            // Make sure child doesn't reference itself
            $this->assertNotEquals($child->getName(), $child->getParent()->getName());

            // Make sure we can get back to root without infinite loop
            set_time_limit(2);
            $root = $child->getRoot();
            $this->assertEquals($formObj->getName(), $root->getName());
        }

        echo "✓ No circular references detected\n";
    }
}
