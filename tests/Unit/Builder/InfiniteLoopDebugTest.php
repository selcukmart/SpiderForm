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
 * Test to debug infinite loop issue in form generation
 *
 * This test reproduces the issue from the user's example form
 */
#[CoversClass(FormBuilder::class)]
class InfiniteLoopDebugTest extends TestCase
{
    private ?SmartyRenderer $renderer = null;
    private ?Bootstrap3Theme $theme = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Create renderer and theme similar to user's example
        try {
            // Check if Smarty is available
            if (class_exists('\Smarty')) {
                $smarty = new \Smarty();
                $smarty->setTemplateDir(__DIR__ . '/../../templates');
                $smarty->setCompileDir(__DIR__ . '/../../cache/compile');
                $smarty->setCacheDir(__DIR__ . '/../../cache');

                $this->renderer = new SmartyRenderer(
                    $smarty,
                    __DIR__ . '/../../templates',
                    __DIR__ . '/../../cache/compile',
                    __DIR__ . '/../../cache'
                );
            }
        } catch (\Exception $e) {
            // If Smarty setup fails, we'll skip renderer-dependent tests
        }

        $this->theme = new Bootstrap3Theme();
    }

    #[Test]
    public function it_should_build_simple_form_without_infinite_loop(): void
    {

        $form = FormBuilder::create('test-form-simple')
            ->setAction('')
            ->setMethod('POST');

        if ($this->renderer) {
            $form->setRenderer($this->renderer);
        }
        if ($this->theme) {
            $form->setTheme($this->theme);
        }

        $form->addText('name', 'Name')
            ->required()
            ->maxLength(128)
            ->placeholder('Enter name')
            ->add();

        $form->addSubmit('save', 'Submit');

        $formObj = $form->buildForm();

        $this->assertNotNull($formObj);
    }

    #[Test]
    public function it_should_build_form_with_section_without_infinite_loop(): void
    {

        $form = FormBuilder::create('test-form-section')
            ->setAction('')
            ->setMethod('POST');

        if ($this->renderer) {
            $form->setRenderer($this->renderer);
        }
        if ($this->theme) {
            $form->setTheme($this->theme);
        }

        $form->addSection('Test Section', 'Test description');

        $form->addText('name', 'Name')
            ->required()
            ->maxLength(128)
            ->placeholder('Enter name')
            ->add();

        $form->addRadio('type', 'Type')
            ->required()
            ->options(['type1' => 'Type 1', 'type2' => 'Type 2'])
            ->helpText('<div>Help text</div>')
            ->add();

        $form->addHidden('hidden_field', 'hidden_value');

        $form->addSubmit('save', 'Submit');


        // Set a timeout to detect infinite loops
        set_time_limit(5); // 5 seconds max

        $startTime = microtime(true);
        $formObj = $form->buildForm();
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        $this->assertNotNull($formObj);
        $this->assertLessThan(3, $executionTime, "Form building took too long, possible infinite loop");
    }

    #[Test]
    public function it_should_reproduce_user_example_form(): void
    {

        $form = FormBuilder::create('exam-insert')
            ->setAction('')
            ->setMethod('POST');

        if ($this->renderer) {
            $form->setRenderer($this->renderer);
        }
        if ($this->theme) {
            $form->setTheme($this->theme);
        }

        $form->addSection('Optik Form Bilgileri', 'Optik form bilgilerini giriniz');

        $form->addText('name', 'Optik Form Adı')
            ->required()
            ->maxLength(128)
            ->placeholder('Optik Form Adı')
            ->addClass('omr-form-input-name')
            ->add();

        $form->addText('photo', 'Optik Form Foto URL')
            ->maxLength(256)
            ->placeholder('Optik Form Foto URL')
            ->addClass('omr-form-input-photo')
            ->add();

        $form->addRadio('type', 'Optik Form Tipi')
            ->required()
            ->options(['type1' => 'Type 1', 'type2' => 'Type 2'])
            ->helpText('<div class="omr-form-help-block">
                <strong>Not 1:</strong> Bu form tipine göre sistem sizden optik formun sütun başlama ve bitiş bilgilerini talep edecektir.<br>
                <strong>Not 2:</strong> Optik form tipiniz burada yoksa buradan ekleyiniz
            </div>')
            ->addClass('omr-form-radio-type')
            ->add();

        $form->addHidden('company_id_not_auto_selected', '123');

        $form->addSubmit('save', 'Kaydet');


        // Set a timeout to detect infinite loops
        set_time_limit(5); // 5 seconds max

        $startTime = microtime(true);

        try {
            $formObj = $form->buildForm();
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;

            $this->assertNotNull($formObj);
            $this->assertLessThan(3, $executionTime, "Form building took too long, possible infinite loop");
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[Test]
    public function it_should_test_buildAsHtml_method(): void
    {

        if (!$this->renderer || !$this->theme) {
            $this->markTestSkipped('Renderer or theme not available');
        }

        $form = FormBuilder::create('test-html')
            ->setAction('')
            ->setMethod('POST')
            ->setRenderer($this->renderer)
            ->setTheme($this->theme);

        $form->addSection('Test Section', 'Description');

        $form->addText('name', 'Name')
            ->required()
            ->add();

        $form->addHidden('id', '1');

        $form->addSubmit('save', 'Submit');


        set_time_limit(5);
        $startTime = microtime(true);

        try {
            $html = $form->buildAsHtml();
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;

            $this->assertIsString($html);
            $this->assertNotEmpty($html);
            $this->assertLessThan(3, $executionTime, "HTML building took too long, possible infinite loop");
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[Test]
    public function it_should_detect_circular_reference_in_sections(): void
    {

        $form = FormBuilder::create('circular-test')
            ->setAction('')
            ->setMethod('POST');

        if ($this->renderer) {
            $form->setRenderer($this->renderer);
        }
        if ($this->theme) {
            $form->setTheme($this->theme);
        }

        // Add multiple sections with inputs
        $form->addSection('Section 1', 'First section');
        $form->addText('field1', 'Field 1')->add();

        $form->addSection('Section 2', 'Second section');
        $form->addText('field2', 'Field 2')->add();

        $form->addSection('Section 3', 'Third section');
        $form->addText('field3', 'Field 3')->add();

        $form->endSection();

        $form->addSubmit('save', 'Submit');

        set_time_limit(5);
        $startTime = microtime(true);

        $formObj = $form->buildForm();
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        $this->assertNotNull($formObj);
        $this->assertLessThan(3, $executionTime);
    }
}
