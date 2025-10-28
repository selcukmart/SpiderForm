<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\DateTimePickerManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DateTimePickerManager::class)]
class DateTimePickerManagerTest extends TestCase
{
    #[Test]
    public function it_has_english_locale_constant(): void
    {
        $locale = DateTimePickerManager::LOCALE_EN;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('months', $locale);
        $this->assertArrayHasKey('weekdays', $locale);
        $this->assertArrayHasKey('hours', $locale);
        $this->assertArrayHasKey('minutes', $locale);
    }

    #[Test]
    public function it_has_turkish_locale_constant(): void
    {
        $locale = DateTimePickerManager::LOCALE_TR;

        $this->assertIsArray($locale);
        $this->assertSame('Ocak', $locale['months'][0]);
        $this->assertSame('Saat', $locale['hours']);
    }

    #[Test]
    public function it_has_arabic_locale_constant(): void
    {
        $locale = DateTimePickerManager::LOCALE_AR;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('months', $locale);
        $this->assertArrayHasKey('selectDate', $locale);
        $this->assertArrayHasKey('selectTime', $locale);
        $this->assertCount(12, $locale['months']);
    }

    #[Test]
    public function it_has_hebrew_locale_constant(): void
    {
        $locale = DateTimePickerManager::LOCALE_HE;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('months', $locale);
        $this->assertArrayHasKey('weekdays', $locale);
        $this->assertCount(12, $locale['months']);
    }

    #[Test]
    public function it_generates_script_with_default_options(): void
    {
        $script = DateTimePickerManager::generateScript('test-input', []);

        $this->assertStringContainsString('<script>', $script);
        $this->assertStringContainsString('</script>', $script);
        $this->assertStringContainsString('DateTimePicker_test-input', $script);
        $this->assertStringContainsString('test-input', $script);
    }

    #[Test]
    public function it_generates_script_with_tabbed_interface(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', []);

        // Check for tab navigation elements
        $this->assertStringContainsString('tab', $script);
    }

    #[Test]
    public function it_generates_script_with_rtl_option(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('rtl', $script);
        $this->assertStringContainsString('true', $script);
    }

    #[Test]
    public function it_generates_script_with_date_format(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'dateFormat' => 'dd-mm-yyyy'
        ]);

        $this->assertStringContainsString('dd-mm-yyyy', $script);
    }

    #[Test]
    public function it_generates_script_with_time_format(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'timeFormat' => '24'
        ]);

        $this->assertStringContainsString('24', $script);
    }

    #[Test]
    public function it_generates_script_with_arabic_locale(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'locale' => DateTimePickerManager::LOCALE_AR
        ]);

        // Check for Arabic text (January in Arabic)
        $this->assertStringContainsString('يناير', $script);
    }

    #[Test]
    public function it_generates_script_with_hebrew_locale(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'locale' => DateTimePickerManager::LOCALE_HE
        ]);

        // Check for Hebrew content
        $this->assertIsString($script);
        $this->assertStringContainsString('DateTimePicker', $script);
    }

    #[Test]
    public function it_includes_css_styles_in_script(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', []);

        $this->assertStringContainsString('<style>', $script);
        $this->assertStringContainsString('</style>', $script);
        $this->assertStringContainsString('.datetimepicker-container', $script);
    }

    #[Test]
    public function it_includes_rtl_styles_when_rtl_is_enabled(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('.rtl', $script);
        $this->assertStringContainsString('direction: rtl', $script);
    }

    #[Test]
    public function it_generates_javascript_class(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', []);

        $this->assertStringContainsString('class DateTimePicker', $script);
        $this->assertStringContainsString('constructor', $script);
        $this->assertStringContainsString('init()', $script);
    }

    #[Test]
    public function it_includes_event_handlers(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', []);

        $this->assertStringContainsString('addEventListener', $script);
        $this->assertStringContainsString('click', $script);
    }

    #[Test]
    public function it_generates_unique_namespace_per_input(): void
    {
        $script1 = DateTimePickerManager::generateScript('input1', []);
        $script2 = DateTimePickerManager::generateScript('input2', []);

        $this->assertStringContainsString('DateTimePicker_input1', $script1);
        $this->assertStringContainsString('DateTimePicker_input2', $script2);
        $this->assertStringNotContainsString('DateTimePicker_input2', $script1);
        $this->assertStringNotContainsString('DateTimePicker_input1', $script2);
    }

    #[Test]
    public function it_generates_script_with_now_button(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'showNow' => true
        ]);

        $this->assertStringContainsString('now', $script);
    }

    #[Test]
    public function it_merges_default_options_with_provided_options(): void
    {
        $script = DateTimePickerManager::generateScript('datetime-input', [
            'dateFormat' => 'yyyy-mm-dd',
            'timeFormat' => '12',
            'locale' => DateTimePickerManager::LOCALE_TR
        ]);

        // Should have custom date format
        $this->assertStringContainsString('yyyy-mm-dd', $script);

        // Should have Turkish locale
        $this->assertStringContainsString('Ocak', $script);
    }
}
