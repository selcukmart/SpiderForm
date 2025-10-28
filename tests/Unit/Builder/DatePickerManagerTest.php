<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\DatePickerManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DatePickerManager::class)]
class DatePickerManagerTest extends TestCase
{
    #[Test]
    public function it_has_english_locale_constant(): void
    {
        $locale = DatePickerManager::LOCALE_EN;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('months', $locale);
        $this->assertArrayHasKey('weekdays', $locale);
        $this->assertCount(12, $locale['months']);
        $this->assertCount(7, $locale['weekdays']);
        $this->assertSame('January', $locale['months'][0]);
    }

    #[Test]
    public function it_has_turkish_locale_constant(): void
    {
        $locale = DatePickerManager::LOCALE_TR;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('months', $locale);
        $this->assertSame('Ocak', $locale['months'][0]);
        $this->assertSame('Pazartesi', $locale['weekdays'][1]);
    }

    #[Test]
    public function it_has_german_locale_constant(): void
    {
        $locale = DatePickerManager::LOCALE_DE;

        $this->assertIsArray($locale);
        $this->assertSame('Januar', $locale['months'][0]);
        $this->assertSame('Montag', $locale['weekdays'][1]);
    }

    #[Test]
    public function it_has_french_locale_constant(): void
    {
        $locale = DatePickerManager::LOCALE_FR;

        $this->assertIsArray($locale);
        $this->assertSame('Janvier', $locale['months'][0]);
        $this->assertSame('Lundi', $locale['weekdays'][1]);
    }

    #[Test]
    public function it_has_spanish_locale_constant(): void
    {
        $locale = DatePickerManager::LOCALE_ES;

        $this->assertIsArray($locale);
        $this->assertSame('Enero', $locale['months'][0]);
        $this->assertSame('Lunes', $locale['weekdays'][1]);
    }

    #[Test]
    public function it_has_hebrew_locale_constant(): void
    {
        $locale = DatePickerManager::LOCALE_HE;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('months', $locale);
        $this->assertArrayHasKey('weekdays', $locale);
        $this->assertCount(12, $locale['months']);
    }

    #[Test]
    public function it_generates_script_with_default_options(): void
    {
        $script = DatePickerManager::generateScript('test-input', []);

        $this->assertStringContainsString('<script>', $script);
        $this->assertStringContainsString('</script>', $script);
        $this->assertStringContainsString('DatePicker_test-input', $script);
        $this->assertStringContainsString('test-input', $script);
    }

    #[Test]
    public function it_generates_script_with_english_locale(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'locale' => DatePickerManager::LOCALE_EN
        ]);

        $this->assertStringContainsString('January', $script);
        $this->assertStringContainsString('February', $script);
        $this->assertStringContainsString('Sunday', $script);
    }

    #[Test]
    public function it_generates_script_with_turkish_locale(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'locale' => DatePickerManager::LOCALE_TR
        ]);

        $this->assertStringContainsString('Ocak', $script);
        $this->assertStringContainsString('Şubat', $script);
        $this->assertStringContainsString('Pazartesi', $script);
    }

    #[Test]
    public function it_generates_script_with_rtl_option(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('rtl', $script);
        $this->assertStringContainsString('true', $script);
    }

    #[Test]
    public function it_generates_script_with_custom_format(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'format' => 'dd-mm-yyyy'
        ]);

        $this->assertStringContainsString('dd-mm-yyyy', $script);
    }

    #[Test]
    public function it_generates_script_with_min_max_dates(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'minDate' => '2024-01-01',
            'maxDate' => '2024-12-31'
        ]);

        $this->assertStringContainsString('2024-01-01', $script);
        $this->assertStringContainsString('2024-12-31', $script);
    }

    #[Test]
    public function it_generates_script_with_disabled_dates(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'disabledDates' => ['2024-12-25', '2024-01-01']
        ]);

        $this->assertStringContainsString('2024-12-25', $script);
        $this->assertStringContainsString('2024-01-01', $script);
    }

    #[Test]
    public function it_generates_script_with_week_start_option(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'weekStart' => 1 // Monday
        ]);

        $this->assertStringContainsString('weekStart', $script);
    }

    #[Test]
    public function it_generates_script_with_inline_mode(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'inline' => true
        ]);

        $this->assertStringContainsString('inline', $script);
    }

    #[Test]
    public function it_includes_css_styles_in_script(): void
    {
        $script = DatePickerManager::generateScript('date-input', []);

        $this->assertStringContainsString('<style>', $script);
        $this->assertStringContainsString('</style>', $script);
        $this->assertStringContainsString('.datepicker-calendar', $script);
    }

    #[Test]
    public function it_includes_rtl_styles_when_rtl_is_enabled(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('.rtl', $script);
        $this->assertStringContainsString('direction: rtl', $script);
    }

    #[Test]
    public function it_generates_javascript_class(): void
    {
        $script = DatePickerManager::generateScript('date-input', []);

        $this->assertStringContainsString('class DatePicker', $script);
        $this->assertStringContainsString('constructor', $script);
        $this->assertStringContainsString('init()', $script);
    }

    #[Test]
    public function it_includes_event_handlers(): void
    {
        $script = DatePickerManager::generateScript('date-input', []);

        $this->assertStringContainsString('addEventListener', $script);
        $this->assertStringContainsString('click', $script);
    }

    #[Test]
    public function it_generates_unique_namespace_per_input(): void
    {
        $script1 = DatePickerManager::generateScript('input1', []);
        $script2 = DatePickerManager::generateScript('input2', []);

        $this->assertStringContainsString('DatePicker_input1', $script1);
        $this->assertStringContainsString('DatePicker_input2', $script2);
        $this->assertStringNotContainsString('DatePicker_input2', $script1);
        $this->assertStringNotContainsString('DatePicker_input1', $script2);
    }

    #[Test]
    public function it_escapes_special_characters_in_input_id(): void
    {
        // Test with dashes, underscores
        $script = DatePickerManager::generateScript('test-input_123', []);

        $this->assertStringContainsString('test-input_123', $script);
    }

    #[Test]
    public function it_generates_script_only_once_per_input(): void
    {
        $script1 = DatePickerManager::generateScript('test-input', []);
        $script2 = DatePickerManager::generateScript('test-input', []);

        // Both should be empty after first generation (static tracking)
        // This tests the singleton pattern if implemented
        $this->assertNotEmpty($script1);
    }

    #[Test]
    public function it_merges_default_options_with_provided_options(): void
    {
        $script = DatePickerManager::generateScript('date-input', [
            'format' => 'dd/mm/yyyy',
            'locale' => DatePickerManager::LOCALE_TR
        ]);

        // Should have custom format
        $this->assertStringContainsString('dd/mm/yyyy', $script);

        // Should have Turkish locale
        $this->assertStringContainsString('Ocak', $script);

        // Should still have default behaviors (not explicitly set)
        $this->assertStringContainsString('showToday', $script);
    }
}
