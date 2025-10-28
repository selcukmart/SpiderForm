<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Builder\DatePickerManager;
use FormGenerator\V2\Builder\TimePickerManager;
use FormGenerator\V2\Builder\DateTimePickerManager;
use FormGenerator\V2\Contracts\{TextDirection, OutputFormat};
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Renderer\TwigRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FormBuilder::class)]
class FormBuilderNewFeaturesTest extends TestCase
{
    private FormBuilder $formBuilder;
    private TwigRenderer $renderer;
    private Bootstrap5Theme $theme;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new TwigRenderer(__DIR__ . '/../../../src/V2/Theme/templates');
        $this->theme = new Bootstrap5Theme();
        $this->formBuilder = FormBuilder::create('test-form')
            ->setRenderer($this->renderer)
            ->setTheme($this->theme);
    }

    // ========== TextDirection Tests ==========

    #[Test]
    public function it_sets_ltr_direction(): void
    {
        $result = $this->formBuilder->setDirection(TextDirection::LTR);

        $this->assertInstanceOf(FormBuilder::class, $result);
        $this->assertSame(TextDirection::LTR, $this->formBuilder->getDirection());
    }

    #[Test]
    public function it_sets_rtl_direction(): void
    {
        $result = $this->formBuilder->setDirection(TextDirection::RTL);

        $this->assertInstanceOf(FormBuilder::class, $result);
        $this->assertSame(TextDirection::RTL, $this->formBuilder->getDirection());
    }

    #[Test]
    public function it_applies_ltr_direction_to_form_element(): void
    {
        $html = $this->formBuilder
            ->setDirection(TextDirection::LTR)
            ->addText('name', 'Name')->add()
            ->build();

        $this->assertHtmlContainsAttribute($html, 'dir', 'ltr');
    }

    #[Test]
    public function it_applies_rtl_direction_to_form_element(): void
    {
        $html = $this->formBuilder
            ->setDirection(TextDirection::RTL)
            ->addText('name', 'Name')->add()
            ->build();

        $this->assertHtmlContainsAttribute($html, 'dir', 'rtl');
    }

    #[Test]
    public function it_applies_rtl_direction_to_input_fields(): void
    {
        $html = $this->formBuilder
            ->setDirection(TextDirection::RTL)
            ->addText('name', 'الاسم')->add()
            ->addEmail('email', 'البريد')->add()
            ->build();

        // Check that inputs have dir attribute
        $this->assertStringContainsString('dir="rtl"', $html);
    }

    #[Test]
    public function it_applies_rtl_to_date_picker_automatically(): void
    {
        $html = $this->formBuilder
            ->setDirection(TextDirection::RTL)
            ->addDate('birth_date', 'تاريخ')->add()
            ->build();

        // Check that picker JavaScript has rtl: true
        $this->assertStringContainsString('rtl', $html);
        $this->assertStringContainsString('DatePicker_', $html);
    }

    #[Test]
    public function it_returns_null_direction_by_default(): void
    {
        $this->assertNull($this->formBuilder->getDirection());
    }

    // ========== Locale Tests ==========

    #[Test]
    public function it_sets_locale(): void
    {
        $locale = DatePickerManager::LOCALE_TR;
        $result = $this->formBuilder->setLocale($locale);

        $this->assertInstanceOf(FormBuilder::class, $result);
        $this->assertSame($locale, $this->formBuilder->getLocale());
    }

    #[Test]
    public function it_returns_null_locale_by_default(): void
    {
        $this->assertNull($this->formBuilder->getLocale());
    }

    #[Test]
    public function it_applies_locale_to_date_picker_automatically(): void
    {
        $html = $this->formBuilder
            ->setLocale(DatePickerManager::LOCALE_TR)
            ->addDate('date', 'Tarih')->add()
            ->build();

        // Check that picker JavaScript contains Turkish locale strings
        $this->assertStringContainsString('Ocak', $html); // January in Turkish
        $this->assertStringContainsString('DatePicker_', $html);
    }

    #[Test]
    public function it_applies_locale_to_time_picker_automatically(): void
    {
        $html = $this->formBuilder
            ->setLocale(TimePickerManager::LOCALE_TR)
            ->addTime('time', 'Saat')->add()
            ->build();

        // Check that picker JavaScript is generated
        $this->assertStringContainsString('TimePicker_', $html);
        $this->assertStringContainsString('Saat', $html); // Hours in Turkish
    }

    #[Test]
    public function it_applies_locale_to_datetime_picker_automatically(): void
    {
        $html = $this->formBuilder
            ->setLocale(DateTimePickerManager::LOCALE_TR)
            ->addDatetime('datetime', 'Tarih ve Saat')->add()
            ->build();

        // Check that picker JavaScript is generated
        $this->assertStringContainsString('DateTimePicker_', $html);
    }

    #[Test]
    public function it_applies_both_direction_and_locale(): void
    {
        $html = $this->formBuilder
            ->setDirection(TextDirection::RTL)
            ->setLocale(DateTimePickerManager::LOCALE_AR)
            ->addDate('date', 'تاريخ')->add()
            ->build();

        // Check RTL direction
        $this->assertHtmlContainsAttribute($html, 'dir', 'rtl');

        // Check Arabic locale (January in Arabic)
        $this->assertStringContainsString('يناير', $html);
    }

    // ========== Output Format Tests ==========

    #[Test]
    public function it_builds_as_html_by_default(): void
    {
        $output = $this->formBuilder
            ->addText('name', 'Name')->add()
            ->build();

        $this->assertIsHtml($output);
        $this->assertHtmlContainsTag($output, 'form');
    }

    #[Test]
    public function it_builds_as_html_explicitly(): void
    {
        $output = $this->formBuilder
            ->addText('name', 'Name')->add()
            ->build(OutputFormat::HTML);

        $this->assertIsHtml($output);
        $this->assertHtmlContainsTag($output, 'form');
    }

    #[Test]
    public function it_builds_as_json(): void
    {
        $output = $this->formBuilder
            ->addText('name', 'Name')
                ->required()
                ->add()
            ->addEmail('email', 'Email')->add()
            ->build(OutputFormat::JSON);

        $this->assertJson($output);

        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('inputs', $data);
        $this->assertCount(2, $data['inputs']);
    }

    #[Test]
    public function it_includes_direction_in_json_output(): void
    {
        $output = $this->formBuilder
            ->setDirection(TextDirection::RTL)
            ->addText('name', 'Name')->add()
            ->build(OutputFormat::JSON);

        $data = json_decode($output, true);
        $this->assertArrayHasKey('direction', $data);
        $this->assertSame('rtl', $data['direction']);
    }

    #[Test]
    public function it_includes_locale_in_json_output(): void
    {
        $output = $this->formBuilder
            ->setLocale(DatePickerManager::LOCALE_TR)
            ->addDate('date', 'Date')->add()
            ->build(OutputFormat::JSON);

        $data = json_decode($output, true);
        $this->assertArrayHasKey('locale', $data);
        $this->assertIsArray($data['locale']);
        $this->assertArrayHasKey('months', $data['locale']);
    }

    #[Test]
    public function it_builds_as_xml(): void
    {
        $output = $this->formBuilder
            ->addText('name', 'Name')
                ->required()
                ->add()
            ->build(OutputFormat::XML);

        // Check XML structure
        $this->assertStringStartsWith('<?xml', $output);
        $this->assertStringContainsString('<form', $output);
        $this->assertStringContainsString('</form>', $output);
        $this->assertStringContainsString('<inputs>', $output);
        $this->assertStringContainsString('<input', $output);
    }

    #[Test]
    public function it_includes_direction_in_xml_output(): void
    {
        $output = $this->formBuilder
            ->setDirection(TextDirection::LTR)
            ->addText('name', 'Name')->add()
            ->build(OutputFormat::XML);

        $this->assertStringContainsString('direction="ltr"', $output);
    }

    #[Test]
    public function it_includes_locale_in_xml_output(): void
    {
        $output = $this->formBuilder
            ->setLocale(DatePickerManager::LOCALE_EN)
            ->addDate('date', 'Date')->add()
            ->build(OutputFormat::XML);

        $this->assertStringContainsString('<locale>', $output);
        $this->assertStringContainsString('</locale>', $output);
    }

    #[Test]
    public function it_uses_buildAsHtml_method(): void
    {
        $output = $this->formBuilder
            ->addText('name', 'Name')->add()
            ->buildAsHtml();

        $this->assertIsHtml($output);
        $this->assertHtmlContainsTag($output, 'form');
    }

    #[Test]
    public function it_uses_buildAsJson_method(): void
    {
        $output = $this->formBuilder
            ->addText('name', 'Name')->add()
            ->buildAsJson();

        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
    }

    #[Test]
    public function it_uses_buildAsXml_method(): void
    {
        $output = $this->formBuilder
            ->addText('name', 'Name')->add()
            ->buildAsXml();

        $this->assertStringStartsWith('<?xml', $output);
        $this->assertStringContainsString('<form', $output);
    }

    #[Test]
    public function json_output_includes_all_form_properties(): void
    {
        $output = $this->formBuilder
            ->setAction('/submit')
            ->setMethod('POST')
            ->setDirection(TextDirection::RTL)
            ->setLocale(DatePickerManager::LOCALE_TR)
            ->addText('name', 'Name')->required()->add()
            ->buildAsJson();

        $data = json_decode($output, true);

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('action', $data);
        $this->assertArrayHasKey('direction', $data);
        $this->assertArrayHasKey('locale', $data);
        $this->assertArrayHasKey('inputs', $data);

        $this->assertSame('/submit', $data['action']);
        $this->assertSame('POST', $data['method']);
        $this->assertSame('rtl', $data['direction']);
    }

    // ========== Picker Integration Tests ==========

    #[Test]
    public function it_does_not_apply_locale_to_picker_with_explicit_locale(): void
    {
        $html = $this->formBuilder
            ->setLocale(DatePickerManager::LOCALE_EN) // Form-level: English
            ->addDate('date', 'Date')
                ->setPickerLocale(DatePickerManager::LOCALE_TR) // Override with Turkish
                ->add()
            ->build();

        // Should use Turkish locale, not English
        $this->assertStringContainsString('Ocak', $html); // January in Turkish
    }

    #[Test]
    public function it_generates_picker_scripts_for_inputs_with_pickers(): void
    {
        $html = $this->formBuilder
            ->setLocale(DatePickerManager::LOCALE_EN)
            ->addDate('date1', 'Date 1')->add()
            ->addTime('time1', 'Time 1')->add()
            ->addDatetime('datetime1', 'DateTime 1')->add()
            ->build();

        $this->assertStringContainsString('DatePicker_', $html);
        $this->assertStringContainsString('TimePicker_', $html);
        $this->assertStringContainsString('DateTimePicker_', $html);
        $this->assertStringContainsString('<script>', $html);
    }

    #[Test]
    public function it_respects_disabled_pickers(): void
    {
        $html = $this->formBuilder
            ->setLocale(DatePickerManager::LOCALE_EN)
            ->addDate('date1', 'Date 1')
                ->disablePicker()
                ->add()
            ->build();

        // Should not generate picker script
        $this->assertStringNotContainsString('DatePicker_', $html);
    }

    // ========== Integration Tests ==========

    #[Test]
    public function it_chains_all_new_methods(): void
    {
        $result = $this->formBuilder
            ->setDirection(TextDirection::RTL)
            ->setLocale(DatePickerManager::LOCALE_AR)
            ->addDate('date', 'تاريخ')->add()
            ->addTime('time', 'وقت')->add();

        $this->assertInstanceOf(FormBuilder::class, $result);
        $this->assertSame(TextDirection::RTL, $result->getDirection());
        $this->assertIsArray($result->getLocale());
    }

    #[Test]
    public function it_works_with_multiple_output_formats(): void
    {
        $this->formBuilder
            ->setDirection(TextDirection::LTR)
            ->setLocale(DatePickerManager::LOCALE_EN)
            ->addText('name', 'Name')->required()->add()
            ->addDate('date', 'Date')->add();

        // HTML output
        $html = $this->formBuilder->build(OutputFormat::HTML);
        $this->assertIsHtml($html);

        // JSON output
        $json = $this->formBuilder->build(OutputFormat::JSON);
        $this->assertJson($json);

        // XML output
        $xml = $this->formBuilder->build(OutputFormat::XML);
        $this->assertStringStartsWith('<?xml', $xml);
    }
}
